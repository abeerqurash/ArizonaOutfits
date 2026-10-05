from pathlib import Path
root=Path(__file__).parent/'_staged/resources/views'
for path in root.rglob('*.blade.php'):
 s=path.read_text(encoding='utf-8');position=0
 while True:
  a=s.find('@php(',position)
  if a<0:break
  i=a+5;start=i;depth=1;quote=None;escape=False
  while depth:
   c=s[i]
   if quote:
    if escape:escape=False
    elif c=='\\':escape=True
    elif c==quote:quote=None
   elif c in ['"',"'"]:quote=c
   elif c=='(':depth+=1
   elif c==')':depth-=1
   i+=1
  replacement='@php\n'+s[start:i-1].rstrip(';')+';\n@endphp'
  s=s[:a]+replacement+s[i:];position=a+len(replacement)
 path.write_text(s,encoding='utf-8')
print('Inline PHP directives normalized')
