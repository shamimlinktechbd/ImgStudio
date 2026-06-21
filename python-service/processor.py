import json
import os
import sys
from io import BytesIO
from PIL import Image


BACKGROUNDS = {
    "white": (255, 255, 255, 255),
    "studio": (232, 236, 238, 255),
    "sky": (210, 232, 250, 255),
    "forest": (214, 232, 216, 255),
}


def parse_payload():
    if len(sys.argv) < 2:
        raise ValueError("Missing JSON payload.")
    return json.loads(sys.argv[1])


def target_size(image, width, height):
    original_width, original_height = image.size
    width = int(width) if width else None
    height = int(height) if height else None

    if width and height:
        return width, height
    if width:
        return width, max(1, round(original_height * (width / original_width)))
    if height:
        return max(1, round(original_width * (height / original_height))), height
    return original_width, original_height


def apply_background(image, background, output_format):
    background = background or "original"
    output_format = output_format.lower()

    if background == "transparent" and output_format in ("png", "webp"):
        return image.convert("RGBA")

    if background == "original" and output_format in ("png", "webp"):
        return image.convert("RGBA")

    rgba = image.convert("RGBA")
    color = BACKGROUNDS.get(background, BACKGROUNDS["white"])
    canvas = Image.new("RGBA", rgba.size, color)
    canvas.alpha_composite(rgba)

    if output_format in ("jpg", "jpeg"):
        return canvas.convert("RGB")

    return canvas


def remove_background_with_ai(image):
    try:
        from rembg import remove
    except ImportError as exc:
        raise RuntimeError(
            "AI background removal needs rembg. Run `pip install -r python-service/requirements.txt`."
        ) from exc

    result = remove(image.convert("RGBA"))

    if isinstance(result, Image.Image):
        return result.convert("RGBA")

    if isinstance(result, bytes):
        return Image.open(BytesIO(result)).convert("RGBA")

    raise RuntimeError("AI background removal returned an unsupported result.")


def cover_resize(image, size):
    target_width, target_height = size
    source_width, source_height = image.size
    scale = max(target_width / source_width, target_height / source_height)
    resized = image.resize(
        (round(source_width * scale), round(source_height * scale)),
        getattr(Image, "Resampling", Image).LANCZOS,
    )
    left = max(0, (resized.size[0] - target_width) // 2)
    top = max(0, (resized.size[1] - target_height) // 2)
    return resized.crop((left, top, left + target_width, top + target_height))


def apply_uploaded_background(subject, background_path, output_format):
    subject = subject.convert("RGBA")
    background = Image.open(background_path).convert("RGBA")
    background = cover_resize(background, subject.size)
    background.alpha_composite(subject)

    if output_format in ("jpg", "jpeg"):
        return background.convert("RGB")

    return background


def main():
    payload = parse_payload()
    source = payload["source"]
    destination = payload["destination"]
    output_format = payload.get("format", "png").lower()
    if output_format == "jpeg":
        output_format = "jpg"

    image = Image.open(source)
    background_removed = False

    if payload.get("remove_background"):
        image = remove_background_with_ai(image)
        background_removed = True

    width, height = target_size(image, payload.get("width"), payload.get("height"))

    resample = getattr(Image, "Resampling", Image).LANCZOS
    if image.size != (width, height):
        image = image.resize((width, height), resample)

    if payload.get("background_path"):
        image = apply_uploaded_background(image, payload["background_path"], output_format)
    else:
        image = apply_background(image, payload.get("background"), output_format)
    os.makedirs(os.path.dirname(destination), exist_ok=True)

    save_format = "JPEG" if output_format == "jpg" else output_format.upper()
    save_options = {"quality": 92}
    if save_format == "PNG":
        save_options = {"optimize": True}
    if save_format == "WEBP":
        save_options = {"quality": 88, "method": 6}

    image.save(destination, save_format, **save_options)

    print(json.dumps({
        "path": destination,
        "width": image.size[0],
        "height": image.size[1],
        "format": output_format,
        "background": payload.get("background", "original"),
        "background_asset": payload.get("background_path"),
        "background_removed": background_removed,
    }))


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(str(exc), file=sys.stderr)
        sys.exit(1)
