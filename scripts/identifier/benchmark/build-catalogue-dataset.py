"""Create a labelled evaluation set. Run before evaluation; never modify it based on predictions.

Existing catalogue font assets + installed fonts; generated scenarios are labelled
synthetic. Existing website crops are imported with browser-rendered font evidence.
Font binaries stay in ignored local source-fonts/, never in the deployment package.
"""
import hashlib
import io
import json
import random
import re
import sys
import urllib.parse
import urllib.request
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont, ImageFilter

ROOT = 'https://kreativfont.com'
CATALOGUE = ['bitter', 'montserrat', 'lora', 'alegreya', 'eb-garamond', 'libre-baskerville', 'open-sans', 'source-sans-3', 'inter', 'poppins', 'dancing-script', 'roboto-flex', 'playfair-display', 'merriweather', 'dm-sans', 'libre-franklin', 'noto-sans', 'work-sans']
SYSTEM = Path('/System/Library/Fonts')
SUP = SYSTEM / 'Supplemental'
INSTALLED = [SUP/'Arial.ttf', SYSTEM/'Helvetica.ttc', SUP/'Verdana.ttf', SUP/'Tahoma.ttf', SUP/'Trebuchet MS.ttf', SUP/'Futura.ttc', SYSTEM/'Avenir.ttc', SUP/'Georgia.ttf', SUP/'Times New Roman.ttf', SUP/'Baskerville.ttc', SUP/'Courier New.ttf', SUP/'Impact.ttf']
TEXTS = ['Bright ideas take shape', 'A quiet afternoon in June', 'Quick brown fox 0123456789', 'Design begins with a question']

def fetch(url):
    with urllib.request.urlopen(url, timeout=25) as response:
        return response.read()

def normalize(name):
    name = re.split(r'\s+[–—-]\s+|,', name)[0]
    name = re.sub(r'(?:\s+Font)?\s+Free Download(?:\s*\(Commercial Use\))?$|\s+Font$', '', name, flags=re.I)
    return re.sub(r'[^\w]', '', name).casefold()

def coverage(family, aliases=()):
    url = ROOT + '/wp-json/wp/v2/posts?' + urllib.parse.urlencode({'search':family,'per_page':100,'_fields':'id,title,link'})
    try:
        posts = json.loads(fetch(url))
        for post in posts:
            if post['link'].startswith(ROOT+'/fonts/') and normalize(post['title']['rendered']) in {normalize(n) for n in [family,*aliases]}:
                return {'listed':True,'post_id':post['id'],'url':post['link'],'method':'Exact normalized title in public font-page search'}
        return {'listed':None if len(posts)==100 else False,'method':'No exact public font-page title in bounded search','search_url':url}
    except Exception as error:
        return {'listed':None,'method':'Coverage lookup failed','error':str(error)}

def main():
    dest=Path(sys.argv[1]).resolve();dest.mkdir(parents=True,exist_ok=True)
    if (dest/'manifest.json').exists():raise RuntimeError('Refusing to replace a frozen manifest')
    sources=dest/'source-fonts';sources.mkdir(exist_ok=True)
    samples=[];families=[];download_failures=[];coverage_cache={}
    def add(image, family, style, variant, provenance, aliases=(), kind='known_font'):
        sid='eval-'+str(len(samples)+1).zfill(3);file=sid+('.jpg' if variant=='compressed' else '.png')
        target=dest/file
        if variant=='compressed':image.save(target,quality=45,subsampling=2)
        else:image.save(target)
        aliases=list(dict.fromkeys([*aliases,*([family+' Regular',family+' Bold',family+' Italic',family+' Bold Italic'] if family else [])]))
        if family and family not in coverage_cache:coverage_cache[family]=coverage(family,aliases)
        samples.append({'id':sid,'file':file,'kind':kind,'font':family,'aliases':aliases,'style':style,'variant':variant,
            'source_type':'synthetic','sha256':hashlib.sha256(target.read_bytes()).hexdigest(),'width':image.width,'height':image.height,
            'catalogue_coverage':coverage_cache.get(family),'provenance':provenance})
    for slug in CATALOGUE:
        try:
            metadata_url=f'{ROOT}/wp-content/uploads/kreativ-fonts/{slug}/metadata.json'
            meta=json.loads(fetch(metadata_url));variants={f['variant']:f for f in meta['source_files']}
            regular=variants.get('regular') or variants.get('400')
            if not regular:raise RuntimeError('No regular TTF')
            styles={'regular':regular}
            if len([f for f in families if 'bold' in f['styles'] and 'italic' in f['styles']])<8:
                if '700' in variants:styles['bold']=variants['700']
                if 'italic' in variants:styles['italic']=variants['italic']
            local={}
            for style, entry in styles.items():
                url=entry['url']
                if not url.startswith(f'{ROOT}/wp-content/uploads/kreativ-fonts/{slug}/') or not url.endswith('.ttf'):raise RuntimeError('Unexpected source-font URL')
                font_bytes=fetch(url);font_file=sources/f'{slug}-{style}.ttf';font_file.write_bytes(font_bytes)
                face=ImageFont.truetype(str(font_file),64)
                local[style]={'path':font_file,'font_sha256':hashlib.sha256(font_bytes).hexdigest(),'source_url':url,'rendered_name':face.getname()}
            families.append({'family':meta['family'],'styles':local,'source':'catalogue'})
            if len(families)>=12 and len([f for f in families if 'bold' in f['styles'] and 'italic' in f['styles']])>=8:break
        except Exception as error:download_failures.append({'slug':slug,'error':str(error)})
    if len(families)<12:raise RuntimeError(f'Only {len(families)} catalogue families available: {download_failures}')
    for source in INSTALLED:
        face=ImageFont.truetype(str(source),64,index=0);family,style=face.getname()
        families.append({'family':family,'source':'installed','styles':{'regular':{'path':source,'font_sha256':hashlib.sha256(source.read_bytes()).hexdigest(),'rendered_name':[family,style],'collection_index':0}}})
    for index, record in enumerate(families):
        for style, spec in record['styles'].items():
            face=ImageFont.truetype(str(spec['path']),64,index=spec.get('collection_index',0))
            style_label='medium' if style=='regular' and 'medium' in face.getname()[1].casefold() else style
            face_alias=[record['family']+' '+face.getname()[1]]
            text=TEXTS[index%len(TEXTS)];image=Image.new('RGB',(1150,190),'white')
            ImageDraw.Draw(image).text((32,38),text,font=face,fill='#202020')
            prov={key:str(value) if isinstance(value,Path) else value for key,value in spec.items()}
            prov.update({'text':text,'render_size':64,'source':record['source'],'condition':'Independent Pillow rasterization, known source font; one line; no family label in image'})
            add(image,record['family'],style_label,'clean',prov,face_alias)
            if style=='regular' and index in [0,1,2,3,4,5,12,13,14,15,16,17]:
                if index%2==0:
                    degraded=image.resize((690,114),Image.Resampling.LANCZOS).filter(ImageFilter.GaussianBlur(.25))
                    variant='compressed'
                else:
                    degraded=image.rotate(3.5,resample=Image.Resampling.BICUBIC,expand=True,fillcolor='white')
                    variant='angled'
                add(degraded,record['family'],style_label,variant,{**prov,'condition':'Synthetic degradation, not a real photograph'},face_alias)
    excluded=[];real=[]
    for filename in ['real-world.json','real-world-italic.json']:
        source=dest/filename
        if source.exists():real.extend(json.loads(source.read_text())['samples'])
    italic=0
    for sample in real:
        if sample['font']=='TN web use only':
            excluded.append({'id':sample['id'],'reason':'Obfuscated internal font name: a reliable canonical-family mapping was not established.'});continue
        if sample['id'].startswith('italic-'):
            if italic>=4:continue
            italic+=1
        mapping={'Inter Variable':('Inter',['Inter Variable']),'Mija Bold':('Mija',['Mija Bold']),'.SF NS':('SF Pro',['.SF NS','San Francisco','SF Pro Display','SF Pro Text','SFNS'])}
        family,aliases=mapping.get(sample['font'],(sample['font'],[]))
        sample['font']=family;sample['aliases']=aliases+[family+' Regular',family+' Bold',family+' Italic']
        sample['provenance']['family_mapping']='Browser actual family; explicit CSS family for Mija/Inter; macOS system-ui SF Pro mapping from https://developer.apple.com/fonts/ for .SF NS'
        if sample['provenance']['computed_style']['weight']=='500':sample['style']='medium'
        sample['catalogue_coverage']=coverage(family,aliases)
        if hashlib.sha256((dest/sample['file']).read_bytes()).hexdigest()!=sample['sha256']:raise RuntimeError('Changed website capture')
        samples.append(sample)
    for index,color in enumerate(['white','#263145']):
        add(Image.new('RGB',(900,180),color),None,'none','blank',{'control':'no glyphs'},kind='no_text')
    rules=Image.new('RGB',(900,180),'white');draw=ImageDraw.Draw(rules)
    for y in [40,90,140]:draw.line((30,y,860,y),fill='black',width=4)
    add(rules,None,'none','rules',{'control':'no glyphs'},kind='no_text')
    rng=random.Random(20261024)
    add(Image.frombytes('RGB',(450,90),bytes(rng.randrange(256) for _ in range(450*90*3))),None,'none','noise',{'control':'no glyphs'},kind='no_text')
    random.Random(20261024).shuffle(samples)
    manifest={'schema_version':1,'name':'Kreativ paired catalogue evaluation v1','frozen_before_inference':True,
        'sample_count':len(samples),'known_families':len({s['font'] for s in samples if s['font']}),
        'limitations':'One pass per pipeline; family-level scores, not exact-face/weight accuracy. Correlated families/styles. Synthetic conditions are separate from actual public-website captures; no real camera photographs. Public screenshots use verified rendered-font metadata. Coverage is an independent bounded public-title lookup, not absence proof. No tuning after inspection.',
        'baseline':'Original 0.7.0 prompt/schema, GPT-4o; archived 44-sample results reported separately, not pooled with the new dataset.',
        'excluded_before_inference':excluded,'download_failures':download_failures,'samples':samples}
    payload=json.dumps(manifest,indent=2)+'\n';(dest/'manifest.json').write_text(payload)
    print(json.dumps({'manifest':str(dest/'manifest.json'),'sha256':hashlib.sha256(payload.encode()).hexdigest(),'samples':len(samples),'families':manifest['known_families'],
        'styles':{style:sum(s['style']==style for s in samples) for style in {s['style'] for s in samples}},'real':sum(s['source_type']=='real_website' for s in samples),'excluded':excluded,'download_failures':download_failures},indent=2))

if __name__=='__main__':main()
