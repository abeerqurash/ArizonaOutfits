from pathlib import Path
import re,sys
root=Path(r'C:\xampp\htdocs\ArizonaOutfits')
for spec in sys.argv[1:]:
    file,_,wanted=spec.partition('::'); text=(root/file).read_text(encoding='utf-8-sig');lines=text.splitlines()
    starts=list(re.finditer(r'^    (?:public|private|protected) function\s+(\w+)\(',text,re.M));selected=set(wanted.split(',')) if wanted else None
    spans=[]
    if selected:
        for i,m in enumerate(starts):
            if m.group(1) in selected:spans.append((text.count('\n',0,m.start()),text.count('\n',0,starts[i+1].start()) if i+1<len(starts) else len(lines)))
    else:spans=[(0,len(lines))]
    print('\nFILE:',file)
    for a,b in spans:
        for j in range(a,b):
            line=lines[j];s=line.strip()
            if not s or s.startswith(('*','//','/*','|-','| ')):continue
            print(f'{j+1}: {line}')
