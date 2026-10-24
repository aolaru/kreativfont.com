"""Prepare local font faces and independent held-out specimens. No API client or network calls."""
import hashlib
import json
import re
import shutil
import sys
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

dataset, output = map(lambda value: Path(value).resolve(), sys.argv[1:3])
manifest = json.loads((dataset/'manifest.json').read_text())
root = output/'uploads'/'kreativ-fonts'
root.mkdir(parents=True, exist_ok=True)
families = {}
for sample in manifest['samples']:
    prov = sample['provenance']
    if prov.get('source') != 'catalogue' or sample['variant'] != 'clean': continue
    family = sample['font']; slug = re.sub(r'[^a-z0-9]+', '-', family.lower()).strip('-')
    variant = {'regular':'regular','bold':'700','italic':'italic'}[sample['style']]
    dest = root/slug/f'{slug}-{variant}.ttf'; dest.parent.mkdir(exist_ok=True)
    shutil.copyfile(prov['path'], dest)
    families.setdefault(family, []).append((sample['style'], dest))
for family, slug in [('Gelasio','gelasio'),('Jomolhari','jomolhari')]:
    source = output/'fonts'/f'{slug}-regular.ttf'
    if source.exists():
        dest=root/slug/source.name;dest.parent.mkdir(exist_ok=True);shutil.copyfile(source,dest)
        families[family]=[('regular',dest)]
for family,slug in [('Bitter','bitter'),('Montserrat','montserrat'),('Lora','lora')]:
    source=output/'fonts'/f'{slug}-700italic.ttf'
    if source.exists():
        dest=root/slug/source.name;dest.parent.mkdir(exist_ok=True);shutil.copyfile(source,dest)
        families[family].append(('bold_italic',dest))
rows=[{'id':index+1,'name':family,'url':'https://kreativfont.com/fonts/'+re.sub(r'[^a-z0-9]+','-',family.lower()).strip('-'),'preview_url':''} for index,family in enumerate(sorted(families))]
samples=[]
heldout=output/'heldout';heldout.mkdir(exist_ok=True)
for family,faces in families.items():
    if family in ['Gelasio','Jomolhari']:continue
    for style,font_path in faces:
        text='Affable jugglers weave type 538'
        font=ImageFont.truetype(str(font_path),62)
        image=Image.new('RGB',(1250,180),'white');ImageDraw.Draw(image).text((32,36),text,font=font,fill='#202020')
        file=heldout/f'face-{len(samples)+1:03d}.png';image.save(file)
        samples.append({'id':file.stem,'file':str(file),'font':family,'style':style,'kind':'known_font','text':text,'font_sha256':hashlib.sha256(font_path.read_bytes()).hexdigest(),'sha256':hashlib.sha256(file.read_bytes()).hexdigest()})
(output/'fixtures.json').write_text(json.dumps({'rows':rows,'heldout':samples,'uploads':str(root.parent),'saved_manifest':str(dataset/'manifest.json'),'limits':'Local component test with known transcription and a reduced 14-family font pool. Not an end-to-end AI accuracy benchmark.'},indent=2))
print(json.dumps({'families':len(rows),'faces':sum(len(faces) for faces in families.values()),'heldout_images':len(samples),'output':str(output)}))
