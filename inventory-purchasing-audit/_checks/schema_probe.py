from pathlib import Path
import re,json
p=Path(r'C:\Users\Laptronics.co\Downloads\arizonaoutfits (9).sql')
s=p.read_text(encoding='utf-8-sig')
for table in ['supplier_documents','supplier_ratings']:
    for pattern in [rf'CREATE TABLE `{table}`[^;]*;',rf'ALTER TABLE `{table}`[^;]*;']:
        for m in re.finditer(pattern,s,re.S):print(m.group(0))
