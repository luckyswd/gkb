from PIL import Image, ImageDraw
import json
SRC='/w/src/'; OUT='/w/out/'
import os; os.makedirs(OUT,exist_ok=True)

def blue(p):
    r,g,b=p[:3]; return b>140 and r<140 and b-r>60

def bbox(im, box):
    x0,y0,x1,y1=box; px=im.load(); xs=[];ys=[]
    for y in range(y0,y1):
        for x in range(x0,x1):
            if blue(px[x,y]): xs.append(x); ys.append(y)
    return (min(xs),min(ys),max(xs)+1,max(ys)+1) if xs else None

def rows_profile(im, box):
    x0,y0,x1,y1=box; px=im.load()
    return [sum(1 for x in range(x0,x1) if blue(px[x,y])) for y in range(y0,y1)]

# русский локап с баннера Ж-9 (белый фон)
ban=Image.open(SRC+'elektrorezerv-zhitomir-9-ru.jpg').convert('RGB')
full=bbox(ban,(40,288,520,400))                 # иконка + текст
txt_x0=None
# текст начинается правее иконки: ищем первый пустой столбец после иконки
px=ban.load()
cols=[any(blue(px[x,y]) or sum(px[x,y])<450 for y in range(full[1],full[3])) for x in range(full[0],full[2])]
gap=None
for i in range(10,len(cols)):
    if not any(cols[i:i+8]): gap=i; break
text_x0=full[0]+gap+8
while not any(blue(px[text_x0,y]) or sum(px[text_x0,y])<450 for y in range(full[1],full[3])): text_x0+=1
# нижняя граница текста — по тёмно-серым строкам подписи
def ink(p): return blue(p) or sum(p)<500
xs=[];ys=[]
for y in range(288,400):
    for x in range(text_x0,520):
        if ink(px[x,y]): xs.append(x); ys.append(y)
text_box=(text_x0,min(ys),max(xs)+1,max(ys)+1)
ys=[];xs=[]
for y in range(288,400):
    for x in range(40,520):
        if ink(px[x,y]): xs.append(x); ys.append(y)
lock_box=(min(xs),min(ys),max(xs)+1,max(ys)+1)
ru_text=ban.crop(text_box); ru_lock=ban.crop(lock_box)
ru_text.save(OUT+'_ru_text.png'); ru_lock.save(OUT+'_ru_lock.png')
report={'ru_text':text_box,'ru_lock':lock_box}

def paste_fit(im, region, piece, align='bottomleft'):
    x0,y0,x1,y1=region; w,h=x1-x0,y1-y0
    s=min(w/piece.width,h/piece.height); p=piece.resize((max(1,round(piece.width*s)),max(1,round(piece.height*s))),Image.LANCZOS)
    ImageDraw.Draw(im).rectangle(region,fill=(255,255,255))
    if align=='bottomleft': im.paste(p,(x0,y1-p.height))
    else: im.paste(p,(x0+(w-p.width)//2,y0+(h-p.height)//2))
    return s

# вертикальные плашки
for name in ['60cb050916913.jpg','60cb062aa472e-1.jpg','60cb09d3c28c2.jpg','6196020949d1c.jpg']:
    im=Image.open(SRC+name).convert('RGB'); W,H=im.size
    zone=(int(W*0.70),int(H*0.30),W,H)
    prof=rows_profile(im,zone)
    # сегменты строк с синим
    segs=[];start=None
    for i,v in enumerate(prof+[0]):
        if v and start is None: start=i
        if not v and start is not None:
            segs.append([start,i]); start=None
    # склеиваем сегменты с разрывом < 12 px
    merged=[]
    for s in segs:
        if merged and s[0]-merged[-1][1]<12: merged[-1][1]=s[1]
        else: merged.append(s)
    merged=[m for m in merged if m[1]-m[0]>20]
    text_seg=max(merged,key=lambda m:m[1]-m[0])   # самый высокий — вертикальный текст
    tz=(zone[0],zone[1]+text_seg[0],zone[2],zone[1]+text_seg[1])
    tb=bbox(im,tz)
    # проверка: фон вокруг белый
    pad=4; region=(tb[0]-pad,tb[1]-pad,tb[2]+pad,tb[3]+pad)
    rot=ru_text.rotate(90,expand=True)
    s=min((tb[3]-tb[1])/rot.height,(W-8-tb[0])/rot.width)
    pr=rot.resize((round(rot.width*s),round(rot.height*s)),Image.LANCZOS)
    ImageDraw.Draw(im).rectangle(region,fill=(255,255,255))
    im.paste(pr,(tb[0],tb[3]-pr.height))
    im.save(OUT+name,quality=92,subsampling=0)
    report[name]={'size':(W,H),'segments':[(zone[1]+a,zone[1]+b) for a,b in merged],'text_bbox':tb,'scale':round(s,3)}

# img-04.png: горизонтальный локап (иконка + текст), выноска сохраняется
im=Image.open(SRC+'gkb-img-04.png'); mode=im.mode; im=im.convert('RGBA')
rgb=im.convert('RGB'); pxr=rgb.load(); alpha=im.split()[3].load()
def vis(x,y): return alpha[x,y]>40
# выноска: синие пиксели правее локапа (x 370..440) — по ним прямая y = a*x + b
pts=[(x,y) for x in range(372,440) for y in range(150,280) if vis(x,y) and blue(pxr[x,y])]
n=len(pts); mx=sum(p[0] for p in pts)/n; my=sum(p[1] for p in pts)/n
a=sum((p[0]-mx)*(p[1]-my) for p in pts)/sum((p[0]-mx)**2 for p in pts); b=my-a*mx
line_col=tuple(sorted([pxr[p] for p in pts],key=sum)[len(pts)//2])
# локап: тёмные/синие пиксели над прямой выноски
xs=[];ys=[]
for y in range(100,200):
    for x in range(120,372):
        if vis(x,y) and (blue(pxr[x,y]) or sum(pxr[x,y])<500) and abs(y-(a*x+b))>3:
            xs.append(x); ys.append(y)
region=(min(xs)-2,min(ys)-2,max(xs)+3,max(ys)+3)
x0,y0,x1,y1=region; w,h=x1-x0,y1-y0
s=min(w/ru_lock.width,h/ru_lock.height)
p=ru_lock.resize((round(ru_lock.width*s),round(ru_lock.height*s)),Image.LANCZOS).convert('RGBA')
pp=p.load()
for yy in range(p.height):
    for xx in range(p.width):
        r,g,bb,aa=pp[xx,yy]; m=min(r,g,bb)
        if m>235: pp[xx,yy]=(r,g,bb,0)
        elif m>150: pp[xx,yy]=(r,g,bb,int(255*(255-m)/105))
ia=im.load()
def on_line(x): return any(alpha[x,y]>40 for y in range(int(a*x+b)-2,int(a*x+b)+3))
orig_start=x1
x=x1
while x>x0 and (on_line(x) or on_line(x-1) or on_line(x-2)):
    orig_start=x; x-=1
# у начала выноски она касается подписи — отступаем на 4 px, чтобы не оставить хвост буквы
orig_start+=4
for yy in range(y0,y1):
    for xx in range(x0,x1):
        if abs(yy-(a*xx+b))>3 or xx<orig_start:
            ia[xx,yy]=(0,0,0,0)
py=y0+(h-p.height)//2
im.alpha_composite(p,(x0,py))
# выноска от правого нижнего угла подписи до исходной прямой
d=ImageDraw.Draw(im)
start_y=py+p.height+3; start_x=(start_y-b)/a
start_x=min(max(start_x,x0+p.width*0.5),x0+p.width); start_y=a*start_x+b
end_x=orig_start+1; end_y=a*end_x+b
K=4; layer=Image.new('RGBA',(im.width*K,im.height*K),(0,0,0,0))
ImageDraw.Draw(layer).line([(start_x*K,start_y*K),((end_x+2)*K,(a*(end_x+2)+b)*K)],fill=line_col+(255,),width=K)
im.alpha_composite(layer.resize(im.size,Image.LANCZOS))
im.save(OUT+'img-04-ru.png')
report['img-04']={'mode':mode,'region':region,'scale':round(s,3),'line':(round(a,3),round(b,1)),'color':line_col}
print(json.dumps(report,ensure_ascii=False,indent=1))
