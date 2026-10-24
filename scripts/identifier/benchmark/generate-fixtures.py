"""Render labelled synthetic specimens from installed fonts, never copy font files.

Usage: python generate-fixtures.py /absolute/output/directory
Requires Pillow and the explicitly named macOS fonts. Fails rather than falling back.
"""
import hashlib
import json
import random
import sys
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

SUPPLEMENTAL = Path('/System/Library/Fonts/Supplemental')
SYSTEM = Path('/System/Library/Fonts')
FONTS = [
    (SUPPLEMENTAL / 'Arial.ttf', ['Arial MT']),
    (SYSTEM / 'Helvetica.ttc', []),
    (SYSTEM / 'HelveticaNeue.ttc', ['HelveticaNeue']),
    (SUPPLEMENTAL / 'Verdana.ttf', []),
    (SUPPLEMENTAL / 'Tahoma.ttf', []),
    (SUPPLEMENTAL / 'Trebuchet MS.ttf', ['Trebuchet']),
    (SUPPLEMENTAL / 'GillSans.ttc', ['Gill Sans MT']),
    (SUPPLEMENTAL / 'Futura.ttc', []),
    (SYSTEM / 'Avenir.ttc', []),
    (SUPPLEMENTAL / 'Georgia.ttf', []),
    (SUPPLEMENTAL / 'Times New Roman.ttf', ['Times New Roman PS']),
    (SUPPLEMENTAL / 'Baskerville.ttc', []),
    (SUPPLEMENTAL / 'Didot.ttc', []),
    (SUPPLEMENTAL / 'Courier New.ttf', ['Courier New PS']),
    (SYSTEM / 'Menlo.ttc', []),
    (SYSTEM / 'Monaco.ttf', []),
    (SUPPLEMENTAL / 'Impact.ttf', []),
    (SUPPLEMENTAL / 'Copperplate.ttc', []),
    (SUPPLEMENTAL / 'Comic Sans MS.ttf', ['Comic Sans']),
    (SUPPLEMENTAL / 'Brush Script.ttf', ['Brush Script MT']),
]


def main():
    destination = Path(sys.argv[1]).resolve()
    destination.mkdir(parents=True, exist_ok=True)
    samples = []

    def save(image, label, variant, font=None, aliases=None, provenance=None):
        sample_id = f'sample-{len(samples) + 1:03d}'
        filename = sample_id + ('.jpg' if variant == 'compressed' else '.png')
        target = destination / filename
        if target.exists():
            raise RuntimeError(f'Refusing to overwrite existing fixture: {target}')
        if variant == 'compressed':
            image.save(target, quality=40, subsampling=2)
        else:
            image.save(target)
        samples.append(dict(id=sample_id, file=filename, kind=label, font=font,
                            aliases=aliases or [], variant=variant,
                            sha256=hashlib.sha256(target.read_bytes()).hexdigest(),
                            width=image.width, height=image.height,
                            provenance=provenance))

    for font_path, aliases in FONTS:
        if not font_path.is_file():
            raise RuntimeError(f'Required font is not installed: {font_path}')
        font = ImageFont.truetype(str(font_path), 54, index=0)
        family, style = font.getname()
        # Generic specimen text never includes the expected font name.
        image = Image.new('RGB', (1100, 210), 'white')
        pen = ImageDraw.Draw(image)
        pen.text((35, 22), 'Hamburgefontsiv Gg Rr Qq', font=font, fill='#181818')
        pen.text((35, 108), 'The quick fox 0123456789', font=font, fill='#181818')
        provenance = dict(font_file=font_path.name, collection_index=0, family=family,
                          style=style, font_sha256=hashlib.sha256(font_path.read_bytes()).hexdigest())
        save(image, 'known_font', 'clean', family, aliases, provenance)
        degraded = image.resize((660, 126), Image.Resampling.LANCZOS)
        save(degraded, 'known_font', 'compressed', family, aliases, provenance)

    # Four deterministic controls contain no glyphs. These are abstention tests,
    # not an "unknown font" test or a representative measure of all bad uploads.
    save(Image.new('RGB', (900, 180), 'white'), 'no_text', 'blank')
    save(Image.new('RGB', (900, 180), '#31384b'), 'no_text', 'solid')
    rules = Image.new('RGB', (900, 180), 'white')
    pen = ImageDraw.Draw(rules)
    for y in (35, 80, 125):
        pen.line((30, y, 860, y), fill='#202020', width=5)
    save(rules, 'no_text', 'rules')
    rng = random.Random(20261024)
    noise = Image.frombytes('RGB', (450, 90), bytes(rng.randrange(256) for _ in range(450 * 90 * 3)))
    save(noise, 'no_text', 'noise')
    manifest = dict(schema_version=1, name='Kreativ installed-font baseline v1',
                    sample_count=len(samples), known_families=20,
                    limitations='20 installed families, two correlated renderings per family; four no-glyph controls. No claim of representative commercial-font, logo or non-Latin coverage. Single pass; scores are font-family level, not style/weight identification.',
                    samples=samples)
    target = destination / 'manifest.json'
    if target.exists():
        raise RuntimeError('Refusing to overwrite an existing manifest')
    target.write_text(json.dumps(manifest, indent=2) + '\n')
    print(json.dumps(dict(manifest=str(target), samples=len(samples), families=20)))


if __name__ == '__main__':
    main()
