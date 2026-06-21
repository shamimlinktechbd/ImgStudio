# Image Studio

Laravel + Python image utility project with guest uploads, preview, resizing, format conversion, background categories, user history, and admin image management.

## Features

- Guest image upload with instant preview page.
- Category-based upload pages: profile, product, document, social, portfolio.
- Resize and convert images to PNG, JPG, or WEBP through `python-service/processor.py`.
- AI background removal through `rembg`.
- Admin-uploaded background library that users can apply after background removal.
- Background category options for transparent/solid/canvas output.
- Login and registration.
- Logged-in users keep upload/action history.
- Admin dashboard with all uploaded images, download, and delete controls.

## Local Setup

```bash
composer install
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`.

Default admin:

- Email: `admin@example.com`
- Password: `password123`

## Python Processor

Install Python 3, Pillow, and the AI background removal packages:

```bash
pip install -r python-service/requirements.txt

```` for windows
python -m pip install -r python-service/requirements.txt
```

If your Python command is not `python`, update `.env`:

```env
PYTHON_BINARY=py
```

## Notes

This project uses SQLite by default in `.env`, so it can run without MySQL. Admins can upload reusable backgrounds at `/admin/backgrounds`; users can select those backgrounds in the image editor. The first AI removal run may take longer because `rembg` downloads/loads its model.
