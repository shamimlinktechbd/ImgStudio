import base64
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
    raw = sys.argv[1].strip()
    if raw.startswith("{"):
        return json.loads(raw)
    try:
        decoded = base64.b64decode(raw).decode("utf-8")
        return json.loads(decoded)
    except Exception:
        return json.loads(raw)


def target_size(image, width, height, mode="stretch"):
    original_width, original_height = image.size
    width = int(width) if width else None
    height = int(height) if height else None

    if not width and not height:
        return original_width, original_height

    if width and not height:
        return width, max(1, round(original_height * (width / original_width)))

    if height and not width:
        return max(1, round(original_width * (height / original_height))), height

    if mode == "contain":
        ratio = min(width / original_width, height / original_height)
        return max(1, round(original_width * ratio)), max(1, round(original_height * ratio))

    return width, height


def enhance_clarity(image, strength=0.65, denoise=True, contrast_boost=True):
    """
    Restores sharpness and clarity to blurry, out-of-focus, or soft images.
    Applies luminance channel unsharp masking in LAB color space, bilateral noise-suppression,
    and adaptive CLAHE micro-contrast boost without creating color artifacts.
    """
    import cv2
    import numpy as np

    has_alpha = image.mode == "RGBA"
    alpha_channel = None
    if has_alpha:
        alpha_channel = image.split()[3]
        image_rgb = image.convert("RGB")
    else:
        image_rgb = image.convert("RGB")

    cv_img = cv2.cvtColor(np.array(image_rgb), cv2.COLOR_RGB2BGR)
    lab = cv2.cvtColor(cv_img, cv2.COLOR_BGR2LAB)
    l_chan, a_chan, b_chan = cv2.split(lab)

    strength = float(strength)
    strength = max(0.1, min(1.0, strength))

    if denoise:
        d = 5 if strength < 0.7 else 7
        filtered_l = cv2.bilateralFilter(l_chan, d=d, sigmaColor=22.0, sigmaSpace=22.0)
    else:
        filtered_l = l_chan

    # Multi-scale unsharp masking
    sigma_fine = 1.0 + strength * 1.5
    gaussian_fine = cv2.GaussianBlur(filtered_l, (0, 0), sigmaX=sigma_fine)
    unsharp_fine = cv2.addWeighted(filtered_l, 1.0 + strength * 1.8, gaussian_fine, -(strength * 1.8), 0)

    sigma_med = 2.5 + strength * 2.0
    gaussian_med = cv2.GaussianBlur(filtered_l, (0, 0), sigmaX=sigma_med)
    unsharp_med = cv2.addWeighted(filtered_l, 1.0 + strength * 1.0, gaussian_med, -(strength * 1.0), 0)

    blended_unsharp = cv2.addWeighted(unsharp_fine, 0.65, unsharp_med, 0.35, 0)

    if contrast_boost:
        clip_limit = 1.2 + (strength * 1.4)
        clahe = cv2.createCLAHE(clipLimit=clip_limit, tileGridSize=(8, 8))
        clahe_l = clahe.apply(blended_unsharp)
        enhanced_l = cv2.addWeighted(blended_unsharp, 0.45, clahe_l, 0.55, 0)
    else:
        enhanced_l = blended_unsharp

    # Detail kernel sharpening
    center = 4.0 + (1.2 / max(0.2, strength))
    kernel = np.array([
        [0, -1, 0],
        [-1, center, -1],
        [0, -1, 0]
    ], dtype=np.float32)
    kernel = kernel / np.sum(kernel)
    sharpened_l = cv2.filter2D(enhanced_l, -1, kernel)

    final_l = cv2.addWeighted(enhanced_l, 0.35, sharpened_l, 0.65, 0)
    final_lab = cv2.merge((final_l, a_chan, b_chan))
    final_bgr = cv2.cvtColor(final_lab, cv2.COLOR_LAB2BGR)
    final_rgb = cv2.cvtColor(final_bgr, cv2.COLOR_BGR2RGB)

    result_pil = Image.fromarray(final_rgb)
    if has_alpha and alpha_channel:
        result_pil.putalpha(alpha_channel)

    return result_pil


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
        from rembg import remove, new_session
    except ImportError as exc:
        raise RuntimeError(
            "AI background removal needs rembg. Run `pip install -r python-service/requirements.txt`."
        ) from exc

    try:
        session = new_session("u2netp")
        result = remove(image.convert("RGBA"), session=session)
    except Exception:
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


def compress_to_target_kb(image, target_kb, output_format="jpg"):
    target_bytes = int(float(target_kb) * 1024)
    save_format = "JPEG" if output_format in ("jpg", "jpeg") else output_format.upper()

    if save_format == "JPEG":
        if image.mode in ("RGBA", "LA", "P"):
            canvas = Image.new("RGB", image.size, (255, 255, 255))
            if image.mode == "RGBA":
                canvas.paste(image, mask=image.split()[3])
            else:
                canvas.paste(image.convert("RGB"))
            image = canvas
        elif image.mode != "RGB":
            image = image.convert("RGB")

    def encode_to_bytes(img, q):
        buf = BytesIO()
        if save_format == "JPEG":
            img.save(buf, "JPEG", quality=int(q), optimize=True, progressive=True)
        elif save_format == "WEBP":
            img.save(buf, "WEBP", quality=int(q), method=6)
        elif save_format == "PNG":
            img.save(buf, "PNG", optimize=True)
        else:
            img.save(buf, save_format)
        return buf.getvalue()

    if save_format == "PNG":
        curr = image
        data = encode_to_bytes(curr, 9)
        while len(data) > target_bytes:
            w, h = curr.size
            if w <= 64 or h <= 64:
                break
            resample = getattr(Image, "Resampling", Image).LANCZOS
            curr = curr.resize((max(32, int(w * 0.82)), max(32, int(h * 0.82))), resample)
            data = encode_to_bytes(curr, 9)
        return curr, data, None

    curr = image
    min_q = 12
    max_q = 95
    best_data = None
    best_q = min_q

    for _ in range(12):
        data_min = encode_to_bytes(curr, min_q)
        if len(data_min) <= target_bytes:
            low = min_q
            high = max_q
            while low <= high:
                mid = (low + high) // 2
                data = encode_to_bytes(curr, mid)
                if len(data) <= target_bytes:
                    best_data = data
                    best_q = mid
                    low = mid + 1
                else:
                    high = mid - 1
            if best_data is not None:
                break

        w, h = curr.size
        if w <= 80 or h <= 80:
            best_data = data_min
            best_q = min_q
            break

        new_w = max(60, int(w * 0.82))
        new_h = max(60, int(h * 0.82))
        resample = getattr(Image, "Resampling", Image).LANCZOS
        curr = curr.resize((new_w, new_h), resample)

    if best_data is None:
        best_data = encode_to_bytes(curr, min_q)
        best_q = min_q

    return curr, best_data, best_q


def main():
    payload = parse_payload()
    source = payload["source"]
    destination = payload["destination"]
    output_format = payload.get("format", "png").lower()
    if output_format == "jpeg":
        output_format = "jpg"
    mode = payload.get("mode", "stretch")

    cutout_source = payload.get("cutout_source")
    cutout_destination = payload.get("cutout_destination")
    background_removed = False

    if cutout_source and os.path.exists(cutout_source):
        image = Image.open(cutout_source)
        background_removed = True
    else:
        image = Image.open(source)
        if payload.get("remove_background"):
            image = remove_background_with_ai(image)
            background_removed = True
            if cutout_destination:
                os.makedirs(os.path.dirname(cutout_destination), exist_ok=True)
                image.save(cutout_destination, "PNG", optimize=True)

    unblurred = False
    if payload.get("unblur") or payload.get("enhance_clarity"):
        strength = float(payload.get("unblur_strength", 0.65))
        denoise = bool(payload.get("unblur_denoise", True))
        contrast_boost = bool(payload.get("unblur_contrast", True))
        image = enhance_clarity(image, strength=strength, denoise=denoise, contrast_boost=contrast_boost)
        unblurred = True

    req_w = payload.get("width")
    req_h = payload.get("height")
    
    if mode == "cover" and req_w and req_h:
        image = cover_resize(image, (int(req_w), int(req_h)))
    else:
        width, height = target_size(image, req_w, req_h, mode=mode)
        resample = getattr(Image, "Resampling", Image).LANCZOS
        if image.size != (width, height):
            image = image.resize((width, height), resample)

    if payload.get("background_path"):
        image = apply_uploaded_background(image, payload["background_path"], output_format)
    else:
        image = apply_background(image, payload.get("background"), output_format)

    dest_dir = os.path.dirname(destination)
    if dest_dir:
        os.makedirs(dest_dir, exist_ok=True)

    save_format = "JPEG" if output_format == "jpg" else output_format.upper()
    quality_used = None
    target_kb = payload.get("target_kb")

    if target_kb:
        image, compressed_bytes, quality_used = compress_to_target_kb(
            image, float(target_kb), output_format
        )
        with open(destination, "wb") as f:
            f.write(compressed_bytes)
    else:
        if save_format == "JPEG" and image.mode in ("RGBA", "LA", "P"):
            canvas = Image.new("RGB", image.size, (255, 255, 255))
            if image.mode == "RGBA":
                canvas.paste(image, mask=image.split()[3])
            else:
                canvas.paste(image.convert("RGB"))
            image = canvas
        elif save_format == "JPEG" and image.mode != "RGB":
            image = image.convert("RGB")

        user_quality = payload.get("quality")
        if user_quality:
            q = max(5, min(100, int(user_quality)))
            quality_used = q
            save_options = {"quality": q}
        else:
            save_options = {"quality": 92}
            quality_used = 92

        if save_format == "JPEG":
            save_options["optimize"] = True
            save_options["progressive"] = True
        elif save_format == "PNG":
            save_options = {"optimize": True}
        elif save_format == "WEBP":
            save_options = {"quality": quality_used if user_quality else 88, "method": 6}

        image.save(destination, save_format, **save_options)

    final_size = os.path.getsize(destination)

    print(json.dumps({
        "path": destination,
        "width": image.size[0],
        "height": image.size[1],
        "format": output_format,
        "size_bytes": final_size,
        "quality_used": quality_used,
        "target_kb": target_kb,
        "background": payload.get("background", "original"),
        "background_asset": payload.get("background_path"),
        "background_removed": background_removed,
        "unblurred": unblurred,
        "unblur_strength": payload.get("unblur_strength"),
    }))


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(str(exc), file=sys.stderr)
        sys.exit(1)
