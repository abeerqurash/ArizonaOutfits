from pathlib import Path
import re
ROOT=Path(__file__).resolve().parents[1];STAGE=ROOT/'storefront-feature-update/_staged'
def end(s,a):
 depth=0
 for m in re.finditer(r'</?div\b[^>]*>',s[a:],re.I):
  depth+=-1 if m.group().startswith('</') else 1
  if not depth:return a+m.end()
 raise ValueError('div missing')
for path in (ROOT/'resources/views/blogs/posts').glob('*.blade.php'):
 s=path.read_text(encoding='utf-8-sig');mainmarker='<div class="blog-posts service-content">'
 starts=[m.start() for m in re.finditer(re.escape(mainmarker),s)]
 for a in reversed(starts[1:]):s=s[:a]+s[end(s,a):]
 marker='<div class="news-letter">'
 if marker in s:a=s.index(marker);s=s[:a]+s[end(s,a):]
 s=re.sub(r"\s*@include\('blogs.partials.article-reviews',\s*\[.*?\]\)",'',s,flags=re.S)
 main=s.index(mainmarker);mainend=end(s,main)
 marker='<div class="post-categories">'
 if marker in s[main:mainend]:
  a=s.index(marker,main);s=s[:a]+"@include('blogs.partials.article-author-latest', ['post'=>$post, 'latestPosts'=>$latestPosts])"+s[end(s,a):]
 sectionend=s.index('@endsection');a=s.rfind('</div>',0,sectionend)
 s=s[:a]+"@include('blogs.partials.related-article-cards')\n@include('blogs.partials.article-reviews', ['reviewProducts'=>$reviewProducts])\n"+s[a:]
 assert s.count("@include('blogs.partials.related-article-cards')")==1
 assert 'blog-form-container' not in s
 assert 'article-author-latest' in s
 # Static article body must survive the layout replacement.
 assert '<div class="content">' in s
 (STAGE/path.relative_to(ROOT)).write_text(s,encoding='utf-8')
print('All five article bodies preserved; sidebars and bottom sections updated')
