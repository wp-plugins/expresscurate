<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class Expresscurate_Tags
{
    private function checkOpenTag($matches)
    {
        //if ((strpos($matches[4], '</a') < strpos($matches[4], '<a')) || strpos($matches[4], '.') !== false) {
        //if(!empty($matches[3])){
        return '<a class="expresscurate_contentTags" href="' . get_tag_link($this->tag_id) . '">#' . $matches[0]. '</a>';
        //} else {
           // return $matches[0];
      //  }
    }

    private function doReplace($html)
    {
        // TODO for multiline use /m
        return preg_replace_callback('/(?![^<]*<\/a)(\b' . $this->word . '\b)(?=[^>]*(<|$))(?=(.*?))/uUis', array(&$this, 'checkOpenTag'), $html,1);
        //return preg_replace_callback('/(#?)(\b' . $this->word . '\b)(?=[^>]*(<|$))(?=(.*?))/Uuis', array(&$this, 'checkOpenTag'), $html,1);
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
        preg_match_all('/(?<!\w)(?=[^>]*(<|$))#\w+/iu', $html, $tags);
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
