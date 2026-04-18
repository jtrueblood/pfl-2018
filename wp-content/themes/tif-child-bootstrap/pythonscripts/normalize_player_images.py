#!/usr/bin/env python3
"""
Normalize player photos so every face is the same size and position.

Uses MediaPipe face detection to locate the face in each image, then
crops and resizes so the head lands consistently in the output frame.

Usage:
    python3 normalize_player_images.py --player 2001BradQB
    python3 normalize_player_images.py --hof
    python3 normalize_player_images.py --all
    python3 normalize_player_images.py --hof --dry-run
    python3 normalize_player_images.py --hof --no-backup

Requirements:
    pip install mediapipe opencv-python-headless numpy
"""

import os
import sys
import shutil
import argparse
from pathlib import Path

import cv2
import numpy as np
import mediapipe as mp
from mediapipe.tasks import python as mp_python
from mediapipe.tasks.python import vision as mp_vision
import mysql.connector

# ============================================================================
# CONFIGURATION
# ============================================================================
UPLOADS_DIR  = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/uploads"
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"
DB_USER      = "root"
DB_PASSWORD  = "root"
DB_NAME      = "local"

# Output dimensions (portrait 3:4)
OUTPUT_W = 600
OUTPUT_H = 800

# Where the face CENTER should sit in the output frame (0.0–1.0)
TARGET_FACE_CX = 0.50   # horizontally centered
TARGET_FACE_CY = 0.20   # 20% from the top (head near top, body fills lower 2/3)

# Face bounding box height as a fraction of OUTPUT_H
TARGET_FACE_H_RATIO = 0.33  # face occupies ~1/3 of the frame height

# Padding color for regions outside the source image (neutral gray)
PAD_COLOR = (180, 180, 180)

# MediaPipe face detection model (downloaded once on first run)
MODEL_PATH = Path(__file__).parent / "blaze_face_short_range.tflite"
MODEL_URL  = (
    "https://storage.googleapis.com/mediapipe-models/face_detector/"
    "blaze_face_short_range/float16/1/blaze_face_short_range.tflite"
)
# ============================================================================


def find_image(player_id: str) -> Path | None:
    """Return path to the player image file (any supported extension)."""
    uploads = Path(UPLOADS_DIR)
    for ext in ("jpg", "jpeg", "webp", "png"):
        p = uploads / f"{player_id}.{ext}"
        if p.exists():
            return p
    return None


def ensure_model():
    """Download the face detection model if not already present."""
    if not MODEL_PATH.exists():
        print("Downloading face detection model (one-time)...")
        import requests as req_lib
        r = req_lib.get(MODEL_URL, timeout=30)
        r.raise_for_status()
        MODEL_PATH.write_bytes(r.content)
        print("Model ready.\n")


def detect_face(img_bgr: np.ndarray):
    """
    Detect the primary face in a BGR image using MediaPipe Tasks API.
    Returns (center_x, center_y, face_w, face_h) in pixels, or None.
    """
    h, w = img_bgr.shape[:2]
    rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
    mp_image = mp.Image(image_format=mp.ImageFormat.SRGB, data=rgb)

    base_options = mp_python.BaseOptions(model_asset_path=str(MODEL_PATH))
    options = mp_vision.FaceDetectorOptions(
        base_options=base_options,
        min_detection_confidence=0.4,
    )
    detector = mp_vision.FaceDetector.create_from_options(options)
    result = detector.detect(mp_image)

    if not result.detections:
        return None

    # Pick the highest-confidence detection
    det = max(result.detections, key=lambda d: d.categories[0].score)
    bb  = det.bounding_box  # origin_x, origin_y, width, height in pixels

    fx = max(0, bb.origin_x)
    fy = max(0, bb.origin_y)
    fw = max(1, min(bb.width,  w - fx))
    fh = max(1, min(bb.height, h - fy))

    cx = fx + fw / 2.0
    cy = fy + fh / 2.0

    return cx, cy, fw, fh


def crop_to_target(img_bgr: np.ndarray, face_cx: float, face_cy: float,
                   face_w: int, face_h: int) -> np.ndarray:
    """
    Crop and resize so the face sits at the target position and size.
    Pads with PAD_COLOR if the crop extends beyond the source image.
    """
    src_h, src_w = img_bgr.shape[:2]

    # Scale: make face_h match TARGET_FACE_H_RATIO * OUTPUT_H
    scale = (OUTPUT_H * TARGET_FACE_H_RATIO) / face_h

    # Crop dimensions in source-image space
    crop_w_src = OUTPUT_W / scale
    crop_h_src = OUTPUT_H / scale

    # Top-left of the crop in source space
    crop_x = face_cx - TARGET_FACE_CX * crop_w_src
    crop_y = face_cy - TARGET_FACE_CY * crop_h_src

    # Compute padding needed if crop extends outside the image
    pad_left   = max(0, int(-crop_x))
    pad_top    = max(0, int(-crop_y))
    pad_right  = max(0, int(crop_x + crop_w_src - src_w))
    pad_bottom = max(0, int(crop_y + crop_h_src - src_h))

    if any([pad_left, pad_top, pad_right, pad_bottom]):
        img_bgr = cv2.copyMakeBorder(
            img_bgr,
            pad_top, pad_bottom, pad_left, pad_right,
            cv2.BORDER_CONSTANT,
            value=PAD_COLOR,
        )

    # Adjust crop origin for padding offsets
    cx_adj = max(0, int(crop_x) + pad_left)
    cy_adj = max(0, int(crop_y) + pad_top)
    cw_adj = int(crop_w_src)
    ch_adj = int(crop_h_src)

    cropped = img_bgr[cy_adj : cy_adj + ch_adj, cx_adj : cx_adj + cw_adj]

    # Resize to final output dimensions
    return cv2.resize(cropped, (OUTPUT_W, OUTPUT_H), interpolation=cv2.INTER_LANCZOS4)


def process_player(player_id: str, dry_run: bool = False, backup: bool = True) -> bool:
    """Process one player image. Returns True on success."""
    img_path = find_image(player_id)
    if not img_path:
        print(f"  [{player_id}] No image found — skipping")
        return False

    img = cv2.imread(str(img_path))
    if img is None:
        # Try loading as WebP via PIL fallback
        try:
            from PIL import Image
            pil = Image.open(img_path).convert("RGB")
            img = cv2.cvtColor(np.array(pil), cv2.COLOR_RGB2BGR)
        except Exception:
            print(f"  [{player_id}] Could not read image — skipping")
            return False

    src_h, src_w = img.shape[:2]
    print(f"  [{player_id}] {img_path.name} ({src_w}x{src_h}) — detecting face...")

    face = detect_face(img)
    if face is None:
        print(f"  [{player_id}] No face detected — skipping")
        return False

    cx, cy, fw, fh = face
    confidence_pct = int((fh / src_h) * 100)
    print(f"  [{player_id}] Face at ({cx:.0f},{cy:.0f}), size {fw}x{fh} ({confidence_pct}% of height)")

    if dry_run:
        print(f"  [{player_id}] DRY RUN — would save {OUTPUT_W}x{OUTPUT_H} to {player_id}.jpg")
        return True

    result = crop_to_target(img, cx, cy, fw, fh)

    # Backup original before overwriting
    if backup:
        backup_path = img_path.with_suffix(f".orig{img_path.suffix}")
        if not backup_path.exists():  # don't overwrite an existing backup
            shutil.copy2(img_path, backup_path)

    # Save as JPG
    out_path = Path(UPLOADS_DIR) / f"{player_id}.jpg"
    cv2.imwrite(str(out_path), result, [cv2.IMWRITE_JPEG_QUALITY, 92])

    # Remove original if it was a different format
    if img_path.suffix.lower() not in (".jpg", ".jpeg"):
        img_path.unlink()

    print(f"  [{player_id}] Saved {OUTPUT_W}x{OUTPUT_H} → {out_path.name}")
    return True


def get_hof_pids() -> list[str]:
    """Return PIDs for all Hall of Fame inductees."""
    conn = mysql.connector.connect(
        host="localhost", user=DB_USER, password=DB_PASSWORD,
        database=DB_NAME, unix_socket=MYSQL_SOCKET
    )
    cursor = conn.cursor()
    cursor.execute(
        "SELECT pid FROM wp_awards WHERE award = 'Hall of Fame Inductee' ORDER BY year DESC"
    )
    pids = [row[0] for row in cursor.fetchall()]
    cursor.close()
    conn.close()
    return pids


def get_all_pids_with_images() -> list[str]:
    """Return player IDs that have an image file in the uploads directory."""
    uploads = Path(UPLOADS_DIR)
    pids = []
    for f in sorted(uploads.iterdir()):
        if f.suffix.lower() in (".jpg", ".jpeg", ".webp", ".png"):
            stem = f.stem
            # Player image filenames look like '2001BradQB' (starts with 4-digit year)
            if len(stem) >= 8 and stem[:4].isdigit():
                pids.append(stem)
    return pids


def main():
    parser = argparse.ArgumentParser(
        description="Normalize player photos — consistent face position and size."
    )
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("--player", metavar="PID",
                       help="Process a single player (e.g. 2001BradQB)")
    group.add_argument("--hof",    action="store_true",
                       help="Process all Hall of Fame players")
    group.add_argument("--all",    action="store_true",
                       help="Process every player that has an image")

    parser.add_argument("--dry-run",   action="store_true",
                        help="Detect faces but do not save any changes")
    parser.add_argument("--no-backup", action="store_true",
                        help="Skip creating .orig backup files")
    args = parser.parse_args()

    backup = not args.no_backup
    ensure_model()

    if args.player:
        pids = [args.player]
    elif args.hof:
        pids = get_hof_pids()
        print(f"Processing {len(pids)} HOF players...\n")
    else:
        pids = get_all_pids_with_images()
        print(f"Processing {len(pids)} players with images...\n")

    success, skipped = 0, 0
    for pid in pids:
        if process_player(pid, dry_run=args.dry_run, backup=backup):
            success += 1
        else:
            skipped += 1

    print(f"\nDone — {success} processed, {skipped} skipped.")


if __name__ == "__main__":
    main()
