<?php
class Expresscurate_Tags
{

    private function checkOpenTag($matches)
    {

        if ((strpos($matches[3], '</a') < strpos($matches[3], '<a')) || strpos($matches[3], '.') !== false) {
            return '<a class="expresscurate_contentTags" href="' . get_tag_link($this->tag_id) . '">#' . strtolower($matches[0]) . '</a>';
        } else {
            return $matches[0];
        }
    }

    private function doReplace($html)
    {
        // TODO for multiline use /m
        return preg_replace_callback('/(\b' . $this->word . '\b)(?=[^>]*(<|$))(?=(.*?>))/Uuis', array(&$this, 'checkOpenTag'), $html,1);
    }

    public function createTag($html, $word, $tag_id)
    {
        // TODO for quote use preg_quote($word, '/')
        // TODO define properties before actually using them
        $this->word = str_replace('/', '\/', $word);
        $this->tag_id = $tag_id;
        return $this->doReplace($html);
    }

    public function removeTagLinks($html)
    {
        $tagLinks = '/<a class="expresscurate_contentTags".*?>(.*?)<\/a>/i';
        $html = preg_replace($tagLinks, '$1', $html);
        preg_match_all('/\s(?<!\w)(?=[^>]*(<|$))#\w+/iu', $html, $tags);
        foreach ($tags[0] as $tag) {
            $html = str_replace($tag, str_replace('#', '', $tag), $html);
        }
        return $html;
    }

    public function removeHighlights($html)
    {
        $spans = '/<span class="expresscurate_keywordsHighlight .*?">(.*?)<\/span>/uis';
        return preg_replace($spans, '$1', $html);
    }


}
