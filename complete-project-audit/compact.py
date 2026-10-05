from pathlib import Path
import re
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'complete-project-audit/compact';o.mkdir(exist_ok=True)
for p in (r/'app').rglob('*.php'):
 s=p.read_text(encoding='utf-8-sig');s=re.sub(r'/\*.*?\*/','',s,flags=re.S);s='\n'.join(x for x in s.splitlines() if x.strip());t=o/p.relative_to(r);t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
for p in [r/'routes/web.php',r/'routes/auth.php',r/'bootstrap/app.php']:
 s=re.sub(r'/\*.*?\*/','',p.read_text(encoding='utf-8-sig'),flags=re.S);s='\n'.join(x for x in s.splitlines() if x.strip());t=o/p.relative_to(r);t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
