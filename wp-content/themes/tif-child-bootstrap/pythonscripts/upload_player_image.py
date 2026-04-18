#!/usr/bin/env python3
"""
Upload a local image file to the WordPress media library as a player photo.

- Prompts for the source image path and player ID
- Strips all EXIF / metadata from the image
- Converts to JPEG and saves with the player ID as the filename
- Uploads to WordPress media library via wp_media_upload.php
- Optionally runs face-normalization after upload

Usage:
    python3 upload_player_image.py
    python3 upload_player_image.py /path/to/photo.jpg
    python3 upload_player_image.py /path/to/photo.webp
"""

import sys
import os
import subprocess
import json
import tempfile
from pathlib import Path

import mysql.connector
from PIL import Image

# ============================================================================
# CONFIGURATION (mirrors getpflimage.py)
# ============================================================================
WP_PATH      = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public"
UPLOADS_DIR  = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/uploads"
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"
DB_USER      = "root"
DB_PASSWORD  = "root"
DB_NAME      = "local"

PHP_SCRIPT   = os.path.join(os.path.dirname(__file__), "wp_media_upload.php")
NORMALIZER   = os.path.join(os.path.dirname(__file__), "normalize_player_images.py")
# ============================================================================


def lookup_player_id(player_name: str) -> str | None:
    """Look up player ID from wp_players by full name."""
    try:
        parts = player_name.strip().split()
        if len(parts) < 2:
            return None
        first = parts[0]
        last  = " ".join(parts[1:])
        conn = mysql.connector.connect(
            host="localhost", user=DB_USER, password=DB_PASSWORD,
            database=DB_NAME, unix_socket=MYSQL_SOCKET
        )
        cursor = conn.cursor()
        cursor.execute(
            "SELECT p_id FROM wp_players WHERE playerFirst = %s AND playerLast = %s LIMIT 1",
            (first, last)
        )
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        return result[0] if result else None
    except Exception as e:
        print(f"DB lookup error: {e}")
        return None


def strip_metadata(src_path: Path, dest_path: Path) -> None:
    """
    Open the image, strip all metadata, and save as a clean JPEG.
    Handles JPG, WebP, PNG, HEIC, etc.
    """
    img = Image.open(src_path)

    # Flatten any transparency / convert exotic modes to RGB
    if img.mode in ("RGBA", "LA", "P"):
        background = Image.new("RGB", img.size, (255, 255, 255))
        if img.mode == "P":
            img = img.convert("RGBA")
        background.paste(img, mask=img.split()[-1] if img.mode in ("RGBA", "LA") else None)
        img = background
    elif img.mode != "RGB":
        img = img.convert("RGB")

    # Save a fresh image — PIL does NOT copy EXIF when you save a converted image
    img.save(dest_path, "JPEG", quality=92, optimize=True)


def upload_to_wordpress(image_path: Path, player_id: str) -> dict:
    """Upload a local JPEG to WordPress via wp_media_upload.php."""
    result = subprocess.run(
        [
            "/usr/local/bin/php", PHP_SCRIPT,
            f"--url=file://{image_path}",
            f"--title={player_id}",
            f"--filename={player_id}",
            f"--socket={MYSQL_SOCKET}",
        ],
        capture_output=True,
        text=True,
        timeout=30,
    )

    output = result.stdout
    json_start = output.find("{")
    if json_start >= 0:
        try:
            return json.loads(output[json_start:])
        except json.JSONDecodeError:
            pass

    return {"success": False, "message": result.stderr or result.stdout or "Unknown error"}


def run_normalizer(player_id: str) -> None:
    """Run normalize_player_images.py on the uploaded player."""
    print(f"\nRunning face normalization on {player_id}...")
    result = subprocess.run(
        [sys.executable, NORMALIZER, "--player", player_id, "--no-backup"],
        capture_output=True,
        text=True,
    )
    # Filter mediapipe noise from output
    for line in result.stdout.splitlines():
        if not line.startswith(("I0", "W0", "INFO")):
            print(line)


def prompt_file(arg: str | None) -> Path:
    """Resolve and validate the source image path."""
    if arg:
        p = Path(arg).expanduser().resolve()
    else:
        raw = input("Source image file path: ").strip().strip("'\"")
        p   = Path(raw).expanduser().resolve()

    if not p.exists():
        print(f"Error: file not found — {p}")
        sys.exit(1)
    if not p.is_file():
        print(f"Error: not a file — {p}")
        sys.exit(1)

    allowed = {".jpg", ".jpeg", ".webp"}
    if p.suffix.lower() not in allowed:
        print(f"Error: unsupported file type '{p.suffix}'. Accepted: jpg, jpeg, webp")
        sys.exit(1)

    img = Image.open(p)
    w, h = img.size
    if h < 400:
        print(f"Error: image is only {h}px tall (minimum 400px required).")
        sys.exit(1)
    print(f"Image size: {w}x{h}px")

    return p


def prompt_player_id() -> str:
    """Prompt for player name → look up ID → confirm."""
    while True:
        name = input("\nPlayer name (or enter ID directly): ").strip()
        if not name:
            continue

        # If it looks like an ID already (starts with 4 digits) use it directly
        if len(name) >= 8 and name[:4].isdigit():
            return name

        found = lookup_player_id(name)
        if found:
            print(f"Found player ID: {found}")
            confirmed = input(f"Player ID [{found}]: ").strip()
            return confirmed if confirmed else found
        else:
            print("Player not found in database.")
            manual = input("Enter player ID manually (or press Enter to try again): ").strip()
            if manual:
                return manual


def main():
    import argparse

    parser = argparse.ArgumentParser(description="Upload a local player image to WordPress.")
    parser.add_argument("file",        nargs="?",       help="Source image file path")
    parser.add_argument("--file",      dest="file_flag", help="Source image file path (named)")
    parser.add_argument("--player",    dest="player",   help="Player ID (skips interactive prompt)")
    parser.add_argument("--normalize", action="store_true", default=None,
                        help="Run face normalization after upload (non-interactive mode default: yes)")
    parser.add_argument("--no-normalize", action="store_true",
                        help="Skip face normalization")
    args = parser.parse_args()

    # Determine non-interactive mode
    file_arg      = args.file_flag or args.file
    non_interactive = bool(file_arg and args.player)

    if non_interactive:
        src_path  = prompt_file(file_arg)
        player_id = args.player.strip()
        print(f"Source: {src_path} ({src_path.stat().st_size // 1024} KB)")
        print(f"Player ID: {player_id}")
    else:
        print("=" * 60)
        print("PFL Player Image Upload")
        print("=" * 60)
        src_path  = prompt_file(file_arg)
        print(f"\nSource: {src_path} ({src_path.stat().st_size // 1024} KB)")
        player_id = prompt_player_id()
        print(f"Player ID: {player_id}")

    # Strip metadata into a temp JPEG
    with tempfile.NamedTemporaryFile(suffix=".jpg", delete=False) as tmp:
        tmp_path = Path(tmp.name)

    try:
        print(f"\nStripping metadata and converting to JPEG...")
        strip_metadata(src_path, tmp_path)
        print(f"Clean image: {tmp_path.stat().st_size // 1024} KB")

        print(f"Uploading to WordPress as '{player_id}.jpg'...")
        result = upload_to_wordpress(tmp_path, player_id)

        if result.get("success"):
            print(f"\n✓ Uploaded: {result.get('attachment_url', '')}")
        else:
            print(f"\n✗ Upload failed: {result.get('message', 'Unknown error')}")
            sys.exit(1)

    finally:
        tmp_path.unlink(missing_ok=True)

    # Normalization
    if args.no_normalize:
        do_normalize = False
    elif non_interactive:
        do_normalize = True  # always normalize when called from web
    else:
        answer = input("\nRun face normalization on this image? [Y/n]: ").strip().lower()
        do_normalize = answer != "n"

    if do_normalize:
        run_normalizer(player_id)

    print("\nDone.")


if __name__ == "__main__":
    main()
