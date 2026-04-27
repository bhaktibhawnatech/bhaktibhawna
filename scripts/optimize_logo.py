#!/usr/bin/env python3
"""Convert white-bg logo to transparent + resize + save as PNG and WebP.

Usage: python optimize_logo.py <input.png> [output_dir]
"""
from PIL import Image, ImageDraw
import os, sys

if len(sys.argv) < 2:
    print(__doc__, file=sys.stderr)
    sys.exit(1)
src = sys.argv[1]
out_dir = sys.argv[2] if len(sys.argv) > 2 else "/tmp"
out_png  = os.path.join(out_dir, "bb-logo.png")
out_webp = os.path.join(out_dir, "bb-logo.webp")

im = Image.open(src).convert("RGBA")
print(f"loaded: {im.size}")

# Flood-fill from all 4 corners with transparency.
# thresh=40 catches near-white antialiasing edges. Only outside-connected
# white area gets removed; white *inside* the design is preserved.
transparent = (255, 255, 255, 0)
for corner in [(0, 0), (im.width-1, 0), (0, im.height-1), (im.width-1, im.height-1)]:
    ImageDraw.floodfill(im, corner, transparent, thresh=40)

# Crop to bounding box of non-transparent pixels (trim transparent border)
bbox = im.getbbox()
if bbox:
    im = im.crop(bbox)
    print(f"trimmed: {im.size}")

# Resize to width 600 (2x retina for max display ~300px)
target_w = 600
ratio = target_w / im.width
target_h = int(im.height * ratio)
im = im.resize((target_w, target_h), Image.LANCZOS)
print(f"resized: {im.size}")

# Save as PNG (palette + alpha can be smaller, but RGBA preserves quality on this colorful logo)
im.save(out_png, "PNG", optimize=True, compress_level=9)

# Save as WebP (transparent, high quality, smaller)
im.save(out_webp, "WebP", quality=88, method=6, lossless=False)

for f in [out_png, out_webp]:
    print(f"  {f}: {os.path.getsize(f):,} bytes")
