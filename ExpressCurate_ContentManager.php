<?php

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_ContentManager {

  public function get_article() {
    $url = $this->_post('expresscurate_source', '');
    if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
      $url = 'http://' . $url;
    }
    if (strlen($url) < 1) {
      $data = array('status' => 'error', 'error' => 'Please enter URL!');
      echo json_encode($data);
      die();
    }

    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
      $data = array('status' => 'error', 'error' => 'Please enter a valid URL!');
      echo json_encode($data);
      die();
    }
    if ($this->_get('check', '') == 1) {
      $data_check = array();
      $curated_urls = $this->get_meta_values('expresscurate_link_', $url);
      if (isset($curated_urls[0]) && isset($curated_urls[0]['meta_value'])) {
        $data_check["status"] = "notification";
        $data_check["msg"] = "This page has been curated before";
      }
      echo json_encode($data_check);
      die();
    } else {
      $tags = $this->_post('tags', '');

      $HtmlParser = new ExpressCurate_HtmlParser($url);
      $HtmlParser->getHtml($tags);
    }
  }

  public function get_meta_values($key = '', $url = '', $type = 'post', $status = 'publish') {
    global $wpdb;
    if (empty($key))
      return;
    $metas = $wpdb->get_results("
        SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key LIKE '{$key}%'
        AND pm.meta_value = '{$url}'
        AND p.post_status = 'publish' 
        AND p.post_type = 'post'", ARRAY_A);

    return $metas;
  }

  public function _post($data, $default) {
    return isset($_POST[$data]) ? $_POST[$data] : $default;
  }

  public function _get($data, $default) {
    return isset($_GET[$data]) ? $_GET[$data] : $default;
  }

}

class ExpressCurate_HtmlParser {

  private $dom;
  private $url;
  private $domain;
  private $fragment;
  private $path;
  private $html;
  private $title = null;
  private $keywords = null;
  private $description = null;

  public function __construct($url) {
    $this->url = $url;
    $path = parse_url($this->url, PHP_URL_PATH);
    $lastof = strrpos($path, "/");
    $path = substr($path, 0, $lastof);
    $this->domain = 'http://' . parse_url($this->url, PHP_URL_HOST);
    $this->path = 'http://' . parse_url($this->url, PHP_URL_HOST) . "/" . $path . "/";
    $this->fragment = parse_url($this->url, PHP_URL_FRAGMENT);
  }

  public function doRequest() {
    $this->html = $this->file_get_contents_utf8($this->url);
  }

  public function strip_tags_content($text, $tags = array(), $invert = FALSE) {
    $tags = array_unique($tags);
    if (is_array($tags) AND count($tags) > 0) {
      if ($invert == FALSE) {
        preg_match_all('@<(' . implode("|", $tags) . ')\b.*?>.*?</\1>@si', $text, $matches);
        $result = implode("", $matches[0]);
      } else {
        $result = preg_replace('@<(?!(?:' . implode("|", $tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
      }
    } elseif ($invert == FALSE) {
      return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
    }
    return $result;
  }

  private function getDescription() {
    // Get the 'content' attribute value in a <meta name="description" ... />
    $matches = array();

    // Search for <meta name="description" content="Buy my stuff" />
    preg_match('/<meta.*?name=("|\')description("|\').*?content=("|\')(.*?)("|\')/i', $this->html, $matches);
    if (count($matches) > 4) {
      return $matches[4];
    }

    // Order of attributes could be swapped around: <meta content="Buy my stuff" name="description" />
    preg_match('/<meta.*?content=("|\')(.*?)("|\').*?name=("|\')description("|\')/i', $this->html, $matches);
    if (count($matches) > 2) {
      return $matches[2];
    }

    // No match
    return null;
  }

  private function getKeywords() {
    // Get the 'content' attribute value in a <meta name="keywords" ... />
    $matches = array();
    $max_count = get_option("expresscurate_max_tags", 3);
    // Search for <meta name="keywords" content="keyword1, keword2" />
    preg_match('/<meta.*?name=("|\')keywords("|\').*?content=("|\')(.*?)("|\')/i', $this->html, $matches);
    if (count($matches) > 4) {
      return array_filter(explode(",", trim($matches[4])));
      //return array_slice(array_filter(explode(", ", trim($matches[4]))), 0, $max_count);
    }

// Order of attributes could be swapped around: <meta content="keyword1, keword2" name="keywords" />
    preg_match('/<meta.*?content=("|\')(.*?)("|\').*?name=("|\')keywords("|\')/i', $this->html, $matches);
    if (count($matches) > 2) {
      return array_filter(explode(",", trim($matches[2])));
//return array_slice(array_filter(explode(", ", trim($matches[2]))), 0, $max_count);
    }

// No match
    return null;
  }

  private function getTitle() {
    $this->dom = new DOMDocument();
    @$this->dom->loadHTML(mb_convert_encoding($this->html, 'HTML-ENTITIES', "UTF-8"));
    $this->dom->encoding = 'UTF-8';
    $xpath = new DOMXPath($this->dom);
    $title = $xpath->query('//title')->item(0)->nodeValue;
//file_put_contents ('text.txt' , $title);
//$title = mb_convert_encoding($title, 'HTML-ENTITIES', "UTF-8");
//iconv(mb_detect_encoding($title, mb_detect_order(), true), "UTF-8", $title);
    return $title;
  }

  public function getHtml() {
    $this->doRequest();
    $this->title = $this->getTitle();
    $this->keywords = $this->getKeywords();
    $this->description = $this->getDescription();
    $this->cleanHtml();
    $this->getElementsByTagsName();
  }

  public function cleanHtml() {
    $matches = array();
    $this->html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $this->html);
//$this->html = preg_replace('/[\r|\n]+/msi', '', $this->html);
    $this->html = preg_replace('/<--[\S\s]*?-->/msi', '', $this->html);
    $this->html = preg_replace('/<noscript[^>]*>[\S\s]*?' .
            '<\/noscript>/msi', '', $this->html);
    $this->html = preg_replace('~>\s+<~', '><', $this->html);
    $this->html = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $this->html);
    $this->html = mb_convert_encoding($this->html, 'HTML-ENTITIES', "UTF-8");
  }

  public function getElementsByTagsName() {
    $this->html = preg_replace('/^.*(?=<html>)/i', '', $this->html);

    $this->dom = new DOMDocument();
    @$this->dom->loadHTML($this->html);

    $this->removeElementsByTagName('script', $this->dom);
    $this->removeElementsByTagName('style', $this->dom);
    $this->removeElementsByTagName('link', $this->dom);

    $this->dom->saveHtml();
    $result_images = array();
    $result_paragraphs = array();
    $result_h1 = '';
    $result_h2 = '';
    $result_h3 = '';
    $xpath = new DOMXPath($this->dom);
    $imgTags = $xpath->query("//img");
    $i = 0;
    foreach ($imgTags as $t) {
      $src = $t->getAttribute('src');
      if (strlen($src) > 3) {
        if (strpos($src, 'http://') !== false || strpos($src, 'https://') !== false) {
          $src = $src;
        } else if (strpos($src, '//') === 0) {
          $src = $this->fragment . $src;
        } elseif (strpos($src, '/') === 0) {
          $src = $this->domain . $src;
        } else {
          $src = $this->path . $src;
        }
        $src = preg_replace('%([^:])([/]{2,})%', '\\1/', $src);

        if (!in_array($src, $result_images)) {
          $result_images[] = ($src);
        }
        $i++;
      }
    }

//get text
    $pTags = $xpath->query('//p');

    $pi = 0;
    foreach ($pTags as $t) {
      if (strlen(trim($t->nodeValue)) > 100 && $pi < 150) {
        $result_paragraphs[] = trim($t->nodeValue);
        $pi++;
      }
      $t->parentNode->removeChild($t);
    }

    $textTags = $xpath->query('//text()');

    foreach ($textTags as $t) {
      if (strlen(trim($t->nodeValue)) > 100 && $pi < 150) {
        $result_paragraphs[] = trim($t->nodeValue);
        $pi++;
      }
    }
//get H1
    $h1Tag = $xpath->query('//h1');
    foreach ($h1Tag as $h1) {
      if (strlen($h1->nodeValue) > 3) {
        $result_h1 .= $h1->nodeValue . "\n";
      }
    }
//get H2
    $h2Tag = $xpath->query('//h2');
    foreach ($h2Tag as $h2) {
      if (strlen($h2->nodeValue) > 3) {
        $result_h2 .= $h2->nodeValue . "\n";
      }
    }
//get H3
    $h3Tag = $xpath->query('//h3');
    foreach ($h3Tag as $h3) {
      if (strlen($h3->nodeValue) > 3) {
        $result_h3 .= $h3->nodeValue . "\n";
      }
    }

//smart tags
    $max_count = get_option("expresscurate_max_tags", 3);
    $smart_tags = array();

    if (count($this->keywords)) {
//$keywords = array_flip($this->keywords);
      foreach ($this->keywords as $key => $keyword) {
        $smart_tags[$keyword] = $this->countMathes($key);
      }
      arsort($smart_tags);
      $smart_tags = array_slice(array_keys(array_reverse($smart_tags)), 0, $max_count);
    }

    $result = array('title' => $this->title, 'headings' => array('h1' => $result_h1, 'h2' => $result_h2, 'h3' => $result_h3), 'metas' => array('description' => $this->description, 'keywords' => $smart_tags), 'images' => $result_images, 'paragraphs' => $result_paragraphs);

    $data = array('status' => 'success', 'result' => $result);
    echo json_encode($data, JSON_HEX_QUOT & JSON_HEX_TAG & JSON_HEX_AMP & JSON_HEX_APOS & JSON_NUMERIC_CHECK & JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES & JSON_FORCE_OBJECT & JSON_UNESCAPED_UNICODE);
    die();
  }

  private function countMathes($keyword) {
    $total_occurrence = 0;
    $tag_in_title = array();
    $tag_in_content = array();
    preg_match_all("/(?<!\w)(?=[^>]*(<|$))" . $keyword . "/i", $this->title, $tag_in_title);
    preg_match_all("/(?<!\w)(?=[^>]*(<|$))" . $keyword . "/i", $this->html, $tag_in_content);
    $total_occurrence = count($tag_in_title[0]) + count($tag_in_content[0]);
    return $total_occurrence;
  }

  private function file_get_contents_utf8($url) {
    $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36'));
    $context = stream_context_create($options);
    $content = file_get_contents($url, false, $context);
    $charset = '';
    $utf8 = false;
    foreach ($http_response_header as $header) {
      if (substr(strtolower($header), 0, 13) == "content-type:") {
        if (count(explode(";", $header)) > 1) {
          list($contentType, $charset) = explode(";", $header);
        }
      }
    }

    if ($charset && strpos(strtolower($charset), 'utf-8')) {
      $utf8 = true;
    } else {
      $charset = mb_detect_encoding($content);
      if (strpos(strtolower($charset), 'utf-8')) {
        $utf8 = true;
      }
    }
    if (!$utf8) {
      $content = utf8_encode($content);
    }
    return $content;
  }

  private function removeElementsByTagName($tagName, $document) {
    $nodeList = $document->getElementsByTagName($tagName);
    for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0;) {
      $node = $nodeList->item($nodeIdx);
      $node->parentNode->removeChild($node);
    }
  }

}

?>