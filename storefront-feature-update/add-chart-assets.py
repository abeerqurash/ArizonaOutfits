from pathlib import Path
import shutil, json, hashlib
base = Path(__file__).parent
sources = {
    'men': Path('C:/Users/Laptronics.co/Downloads/Arizona Outfits Men’s Jacket Size Guide.png'),
    'women': Path('C:/Users/Laptronics.co/Downloads/Arizona Outfits Women’s Size Guide.png'),
}
files = json.loads((base/'changed.json').read_text())
for gender, source in sources.items():
    relative = f'public/asset/images/size-chart-{gender}.png'
    destination = base/'_staged'/relative
    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(source, destination)
    assert hashlib.sha256(source.read_bytes()).digest() == hashlib.sha256(destination.read_bytes()).digest()
    if relative not in files:
        files.append(relative)
    print(f'{gender}: copied original PNG ({destination.stat().st_size} bytes)')
(base/'changed.json').write_text(json.dumps(files, indent=2))
p = base/'package.py'
s = p.read_text(encoding='utf-8')
s = s.replace('Copy each complete text file into the destination below.', 'Copy each complete text replacement into the destination below. Copy PNG assets as image files.')
s = s.replace("name=f'{i:02d}_{Path(p).name}.txt';", "name=f'{i:02d}_{Path(p).name}' + ('' if Path(p).suffix.lower()=='.png' else '.txt');")
s = s.replace('complete .txt replacements. FILES.md maps every file to its exact destination.', 'complete .txt replacements and two original PNG assets. FILES.md maps every file to its exact destination.')
s = s.replace('Remove the .txt suffix at the destination. Create the new files where listed.', 'Remove the .txt suffix from text replacements at the destination. Copy the PNG\\n   assets directly to their listed image paths. Create the new files where listed.')
start = s.index('SIZE CHART IMAGES — WAITING')
end = s.index('\nBLOGS', start)
s = s[:start] + '''SIZE CHART IMAGES
The Men and Women popup tabs display your supplied original PNG charts.
Copy the two image assets to:
  public/asset/images/size-chart-men.png
  public/asset/images/size-chart-women.png
The updated config/size_charts.php points to these PNG paths.
If the previous update is already installed, copy only these two PNG assets and
the updated config replacement, then run artisan config:clear.
''' + s[end:]
s = s.replace('complete replacement text files', 'replacement files (38 text replacements and 2 PNG assets)')
p.write_text(s, encoding='utf-8')
p = base/'RESULTS.md'
s = p.read_text(encoding='utf-8').replace('38 complete replacement text files.', '38 complete replacement text files and two original PNG image assets.')
s = s.replace('**The two actual image files are still awaited.**', 'Both supplied original charts are included, with PNG paths configured for the Men/Women tabs.')
p.write_text(s, encoding='utf-8')
