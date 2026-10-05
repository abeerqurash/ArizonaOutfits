from pathlib import Path
import re
p=Path(__file__).parent/'_staged/resources/views/partials/header.blade.php'
s=p.read_text(encoding='utf-8')
labels={'facebook-f':'Facebook','instagram':'Instagram','x-twitter':'X','linkedin':'LinkedIn','youtube':'YouTube'}
def label(m):
 name=next((name for icon,name in labels.items() if 'fa-'+icon in m.group()),'Social profile')
 return m.group().replace('<a ','<a aria-label="'+name+'" ',1)
s=re.sub(r'<a href="\{\{ \$headerSocial \}\}"[^>]*>.*?</a>',label,s)
p.write_text(s,encoding='utf-8')
