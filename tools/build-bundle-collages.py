"""
Builds the gift bundles' photographs (assets/bundles/*.webp) as collages of
the photographs of what each bundle holds.

There is no photograph of a bundle as such, so its picture is its contents,
laid out on the brand cream in the 3:4 frame every product card uses. Run
again whenever a bundle's contents change in inc/GiftBundles.php — the
CONTENTS map below has to say the same thing.

    python tools/build-bundle-collages.py

Needs Pillow with AVIF support (Pillow 11.2+); the towel photographs are AVIF.
"""

import os
from PIL import Image, ImageDraw

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'assets', 'bundles')

W, H = 1200, 1600
PAD, GAP, RADIUS = 28, 18, 26
CREAM = (247, 239, 228)

# Rows per tile count: how many tiles sit on each row, top to bottom.
ROWS = {1: [1], 2: [1, 1], 3: [1, 2], 4: [2, 2], 5: [2, 3], 6: [3, 3], 7: [2, 3, 2]}


def towel(motif):
    return os.path.join(ROOT, 'assets', 'motifs', motif + '.avif')


def long_towel(key):
    return os.path.join(ROOT, 'assets', 'long-towels', 'dugi-peskir-' + key + '.webp')


def pillow(key):
    return os.path.join(ROOT, 'assets', 'pillows', 'cuddle-puff-jastuk-' + key + '.webp')


CLOTHS = [
    os.path.join(ROOT, 'assets', 'cloths', 'krpica-avokado-limeta.webp'),
    os.path.join(ROOT, 'assets', 'cloths', 'krpica-avokado-zalfija.webp'),
]

# File name (without extension) => photographs, in reading order.
CONTENTS = {
    'poklon-paket-dobrodoslica-za-bebu': [pillow('kruzic-roze'), towel('zeka'), towel('meda'), towel('panda')],
    'poklon-paket-kutak-za-decju-sobu': [pillow('kruzic-zuti'), pillow('kockasti-sivi'), towel('sova')],
    'poklon-paket-za-vrtic': [towel('kucence'), towel('maca'), towel('pingvin'), towel('koala')],
    'poklon-paket-avokado-ljubav': [towel('avokado')] + CLOTHS,
    'poklon-paket-dorucak-u-kuhinji': [towel('tost'), towel('keks'), towel('krofna')] + CLOTHS,
    'poklon-paket-kupatilo-za-celu-porodicu': [long_towel('meda-krem'), long_towel('meda-plavi'), towel('zirafa'), towel('kapibara')],
    'poklon-paket-novi-dom': [pillow('kockasti-braon'), long_towel('meda-krem'), long_towel('meda-bez')] + CLOTHS,
    'poklon-paket-za-dvoje': [pillow('kockasti-roze'), pillow('kockasti-sivi'), long_towel('meda-roze'), long_towel('meda-plavi')],
    'poklon-paket-mali-znak-paznje': [towel('lala'), long_towel('meda-roze')] + CLOTHS,
    'poklon-paket-praznicna-kutija-mekoce': [pillow('kruzic-braon'), long_towel('meda-bez'), towel('pingvin'), towel('meda'), towel('keks')] + CLOTHS,
}


def cover(img, w, h):
    """Scale and centre-crop to fill w x h exactly."""
    scale = max(w / img.width, h / img.height)
    img = img.resize((round(img.width * scale), round(img.height * scale)), Image.LANCZOS)
    left = (img.width - w) // 2
    top = (img.height - h) // 2
    return img.crop((left, top, left + w, top + h))


def rounded(img, radius):
    mask = Image.new('L', img.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, img.width, img.height), radius, fill=255)
    return img, mask


def collage(files):
    rows = ROWS[len(files)]
    canvas = Image.new('RGB', (W, H), CREAM)
    row_h = (H - 2 * PAD - GAP * (len(rows) - 1)) // len(rows)
    it = iter(files)
    y = PAD
    for count in rows:
        tile_w = (W - 2 * PAD - GAP * (count - 1)) // count
        x = PAD
        for _ in range(count):
            img = cover(Image.open(next(it)).convert('RGB'), tile_w, row_h)
            img, mask = rounded(img, RADIUS)
            canvas.paste(img, (x, y), mask)
            x += tile_w + GAP
        y += row_h + GAP
    return canvas


def main():
    os.makedirs(OUT, exist_ok=True)
    for name, files in CONTENTS.items():
        path = os.path.join(OUT, name + '.webp')
        collage(files).save(path, 'WEBP', quality=78, method=6)
        print(name, os.path.getsize(path) // 1024, 'KB')


if __name__ == '__main__':
    main()
