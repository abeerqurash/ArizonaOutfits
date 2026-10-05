from pathlib import Path
import shutil,json,subprocess,hashlib
ROOT=Path(__file__).resolve().parent.parent;OUT=ROOT/'administration-fixes';fixture=OUT/'_checks/installer-fixture';package=fixture/'administration-fixes';package.mkdir(parents=True,exist_ok=True)
manifest=json.loads((OUT/'manifest.json').read_text())
for x in manifest:
    original=ROOT/x['destination'];target=fixture/x['destination'];target.parent.mkdir(parents=True,exist_ok=True)
    if original.exists():shutil.copyfile(original,target)
    shutil.copyfile(OUT/x['file'],package/x['file'])
for name in ['manifest.json','Install-All.ps1']:shutil.copyfile(OUT/name,package/name)
result=subprocess.run(['powershell','-NoProfile','-ExecutionPolicy','Bypass','-File',str(package/'Install-All.ps1')],capture_output=True,text=True)
assert result.returncode==0,result.stdout+result.stderr
rows=[]
for x in manifest:
    rows.append({'file':x['destination'],'pass':hashlib.sha256((fixture/x['destination']).read_bytes()).hexdigest()==x['sha256']})
backups=list((package/'_installation-backups').iterdir());latest=max(backups,key=lambda p:p.stat().st_mtime)
for x in manifest:
    if x['original_sha256']:rows.append({'backup':x['destination'],'pass':hashlib.sha256((latest/x['destination']).read_bytes()).hexdigest()==x['original_sha256']})
assert all(x['pass'] for x in rows),rows
(OUT/'installer-checks.json').write_text(json.dumps(rows,indent=2),encoding='utf-8')
print(f'Installer fixture: {len(manifest)} replacements and {len(rows)-len(manifest)} original-file backups verified. Real application unchanged.')
