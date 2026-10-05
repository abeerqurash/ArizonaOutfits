from pathlib import Path
import re,sys
R=Path(r'C:\xampp\htdocs\ArizonaOutfits')
for spec in sys.argv[1:]:
    p,_,names=spec.partition('::');s=(R/p).read_text(encoding='utf-8-sig')
    starts=list(re.finditer(r'^    (?:public|private|protected)(?: static)? function\s+(\w+)\(',s,re.M))
    print('\nFILE',p)
    if names=='LIST':
        print(', '.join(m[1] for m in starts));continue
    wanted=set(names.split(',')) if names else None
    if not wanted: spans=[(0,len(s),'ALL')]
    else:spans=[(m.start(),starts[i+1].start() if i+1<len(starts) else len(s),m[1]) for i,m in enumerate(starts) if m[1] in wanted]
    for a,b,n in spans:
        body=s[a:b];body=re.sub(r'/\*.*?\*/','',body,flags=re.S)
        print('LINE',s.count('\n',0,a)+1,n,':',re.sub(r'\s+',' ',body))
