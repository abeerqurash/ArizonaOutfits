from pathlib import Path
import subprocess,json
b=Path(__file__).parent;r=b.parent
files=list((r/'app').rglob('*.php'))+list((r/'routes').rglob('*.php'))+list((r/'config').rglob('*.php'))
errors=[]
for p in files:
 result=subprocess.run([r'C:\xampp\php\php.exe','-l',str(p)],capture_output=True,text=True)
 if result.returncode:errors.append({'file':str(p.relative_to(r)),'error':result.stdout+result.stderr})
(b/'syntax-checks.json').write_text(json.dumps({'files':len(files),'errors':errors},indent=2))
print(f'{len(files)} PHP files checked; {len(errors)} errors.')
