<?php
namespace App\Services;
use DOMDocument;
use DOMElement;
use DOMNode;
class HtmlContentSanitizer
{
    public function clean(?string $html): string
    {
        if (trim((string)$html)==='') return '';
        $doc=new DOMDocument('1.0','UTF-8');$old=libxml_use_internal_errors(true);
        try { $doc->loadHTML('<?xml encoding="UTF-8"><html><body>'.(string)$html.'</body></html>', LIBXML_NONET); }
        finally {libxml_clear_errors();libxml_use_internal_errors($old);}
        $body=$doc->getElementsByTagName('body')->item(0);if(!$body)return '';
        $this->filter($body);$result='';foreach($body->childNodes as $child)$result.=$doc->saveHTML($child);
        return trim($result);
    }
    private function filter(DOMNode $parent): void
    {
        $tags=['h1','h2','h3','h4','h5','h6','p','br','strong','b','em','i','u','s','ul','ol','li','a','img','table','thead','tbody','tfoot','tr','th','td','hr','blockquote','code','pre','span','div'];
        foreach(iterator_to_array($parent->childNodes) as $node){
            if($node->nodeType===XML_COMMENT_NODE){$parent->removeChild($node);continue;}
            if(!$node instanceof DOMElement)continue;
            $tag=strtolower($node->tagName);
            if(in_array($tag,['script','style','iframe','object','embed','svg','math','template','form','input','button','textarea','select','link','meta','base'],true)){$parent->removeChild($node);continue;}
            $this->filter($node);
            if(!in_array($tag,$tags,true)){while($node->firstChild)$parent->insertBefore($node->firstChild,$node);$parent->removeChild($node);continue;}
            foreach(iterator_to_array($node->attributes) as $attr){
                $name=strtolower($attr->name);$value=$attr->value;
                $allowed=in_array($name,['title','alt'],true)
                    || ($tag==='a' && $name==='href' && SafeContentUrl::allowed($value,true))
                    || ($tag==='img' && $name==='src' && SafeContentUrl::allowed($value))
                    || (in_array($tag,['td','th'],true) && in_array($name,['colspan','rowspan'],true) && ctype_digit($value) && (int)$value<=100)
                    || (in_array($tag,['table','td','th','img'],true) && in_array($name,['width','height'],true) && preg_match('/^\d{1,4}%?$/',$value));
                if(!$allowed)$node->removeAttributeNode($attr);
            }
        }
    }
}