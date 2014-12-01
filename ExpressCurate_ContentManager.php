<?php

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_ContentManager {

  public function check_get_url() {
    $file_get_enabled = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));
    if (!$file_get_enabled && !is_callable('curl_init')) {
      return false;
    } else {
      return true;
    }
  }

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
    if (!$this->check_get_url()) {
      $data_check["status"] = "error";
      $data_check["error"] = "Could not make HTTP request: Please enable <b>allow_url_open</b> in php.ini";
      echo json_encode($data_check);
      die();
    } else {
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
    $this->html = $this->file_get_contents_utf8($this->url, false);
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
    if (strlen($this->html) > 3) {
      $this->title = $this->getTitle();
      $this->keywords = $this->getKeywords();
      $this->description = $this->getDescription();
      $this->cleanHtml();
      $this->getElementsByTagsName();
    }
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
    $this->html = str_replace("\0", " ",$this->html);
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
      $t->parentNode->removeChild($t);
    }
//get H1
    $h1Tag = $xpath->query('//h1');
    foreach ($h1Tag as $h1) {
      if (strlen($h1->nodeValue) > 3) {
        $result_h1 .= htmlentities($h1->nodeValue, ENT_QUOTES, "UTF-8") . "\n";
      }
      $h1->parentNode->removeChild($h1);
    }
//get H2
    $h2Tag = $xpath->query('//h2');
    foreach ($h2Tag as $h2) {
      if (strlen($h2->nodeValue) > 3) {
        $result_h2 .= htmlentities($h2->nodeValue, ENT_QUOTES, "UTF-8") . "\n";
      }
      $h2->parentNode->removeChild($h2);
    }
//get H3
    $h3Tag = $xpath->query('//h3');
    foreach ($h3Tag as $h3) {
      if (strlen($h3->nodeValue) > 3) {
        $result_h3 .= htmlentities($h3->nodeValue, ENT_QUOTES, "UTF-8") . "\n";
      }
      $h3->parentNode->removeChild($h3);
    }
//get text
    $i = 0;
    $articleTags = $xpath->query('/html/body/article');
    foreach ($articleTags as $t) {
      //$result_paragraphs[] = strip_tags($this->escapeJsonString(trim($t->nodeValue)));
      $result_paragraphs[$i]['value'] = strip_tags(htmlentities(trim($t->nodeValue), ENT_QUOTES, "UTF-8"));
      $result_paragraphs[$i]['tag'] = 'article';
      $t->parentNode->removeChild($t);
      $i++;
    }


    $textTags = $xpath->query('/html/body//text()');

    foreach ($textTags as $t) {
      if ($t->length > 15 && $t->parentNode->tagName != 'a' && $t->parentNode->tagName != 'h1' && $t->parentNode->tagName != 'h2' && $t->parentNode->tagName != 'h3') {
        //$result_paragraphs[] = strip_tags($this->escapeJsonString(trim($t->nodeValue)));
        if ($t->parentNode->nodeName == "blockquote" || $t->parentNode->parentNode->nodeName == "blockquote" || $t->parentNode->parentNode->parentNode->nodeName == "blockquote") {
          if ($t->parentNode->nodeName == "blockquote") {
            $result_paragraphs[$i]['value'] = strip_tags(htmlentities($t->parentNode->nodeValue, ENT_QUOTES, "UTF-8"));
          } elseif ($t->parentNode->parentNode && $t->parentNode->parentNode->nodeName == "blockquote") {
            $result_paragraphs[$i]['value'] = strip_tags(htmlentities($t->parentNode->parentNode->nodeValue, ENT_QUOTES, "UTF-8"));
          } elseif ($t->parentNode->parentNode->parentNode && $t->parentNode->parentNode->parentNode->nodeName == "blockquote") {
            $result_paragraphs[$i]['value'] = strip_tags(htmlentities($t->parentNode->parentNode->parentNode->nodeValue, ENT_QUOTES, "UTF-8"));
          }
          $result_paragraphs[$i]['tag'] = "blockquote";
        } else {
          $result_paragraphs[$i]['value'] = strip_tags(htmlentities($t->nodeValue, ENT_QUOTES, "UTF-8"));
          $result_paragraphs[$i]['tag'] = $t->parentNode->nodeName;
        }
        $i++;
      }
    }

//smart tags
    $max_count = get_option("expresscurate_max_tags", 3);
    $smart_tags = array();

    $defined_tags = get_option("expresscurate_defined_tags", '');
    if ($defined_tags) {
      $defined_tags = explode(",", $defined_tags);
      foreach ($defined_tags as $tag) {
        $tag = trim($tag);
        $count = $this->countMathes($tag);
        if ($count > 0) {
          $smart_tags[$tag] = $count;
        }
      }
    }

    if (count($this->keywords)) {
      foreach ($this->keywords as $key => $keyword) {
        $count = $this->countMathes($key);
        if ($count > 0) {
          $smart_tags[$keyword] = $count;
        }
      }
    }

    if (count($smart_tags) > 0) {
      arsort($smart_tags);
      $smart_tags = array_slice(array_keys(array_reverse($smart_tags)), 0, $max_count);
    }
    $result_paragraphs_unique = $this->arrayUnique($result_paragraphs);
    $result = array('title' => $this->title, 'headings' => array('h1' => $result_h1, 'h2' => $result_h2, 'h3' => $result_h3), 'metas' => array('description' => $this->description, 'keywords' => $smart_tags), 'images' => $result_images, 'paragraphs' => $result_paragraphs_unique);

    $data = array('status' => 'success', 'result' => $result);
    echo json_encode($data);
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

  public function file_get_contents_utf8($url, $get_http_status = false, $set_utf8 = true) {
    $content = '';
    $charset = '';
    $utf8 = false;
    $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36';
    $file_get_enabled = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));
    if ($file_get_enabled) {
      $options = array('http' => array('user_agent' => $user_agent));
      $context = stream_context_create($options);
      $content = @file_get_contents($url, false, $context);
      $http_status = $http_response_header;

      if (!empty($http_response_header)) {
          foreach ($http_response_header as $header) {
              if (substr(strtolower($header), 0, 13) == "content-type:") {
                  if (count(explode(";", $header)) > 1) {
                      list($contentType, $charset) = explode(";", $header);
                  }
              }
          }
      }

      $headers = headers_list();
      // get the content type header
      foreach ($headers as $header) {
        if (substr(strtolower($header), 0, 13) == "content-type:") {
          list($contentType, $charset) = explode(";", trim(substr($header, 14), 2));
          if (strtolower(trim($charset)) == "charset=utf-8") {
            $utf8 = true;
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
      if (!$utf8 && $set_utf8) {
        $content = utf8_encode($content);
      }
    } elseif (is_callable('curl_init')) {
      $ch = curl_init($url);
      //curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: text/xml;charset=\"utf-8\""));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
      $content = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
    } else {
      $data = array('status' => 'warning', 'msg' => 'Could not make HTTP request: Please set \'allow_url_open\' in php.ini');
      echo json_encode($data);
      die();
    }
    if (!$get_http_status) {
      return $content;
    } else {
      return array('content' => $content, 'http_status' => $http_status);
    }
  }

  private function arrayUnique($array, $preserveKeys = false) {
    // Unique Array for return
    $arrayRewrite = array();
    // Array with the md5 hashes
    $arrayHashes = array();
    foreach ($array as $key => $item) {
      // Serialize the current element and create a md5 hash
      $hash = md5(serialize($item));
      // If the md5 didn't come up yet, add the element to
      // to arrayRewrite, otherwise drop it
      if (!isset($arrayHashes[$hash])) {
        // Save the current element hash
        $arrayHashes[$hash] = $hash;
        // Add element to the unique Array
        if ($preserveKeys) {
          $arrayRewrite[$key] = $item;
        } else {
          $arrayRewrite[] = $item;
        }
      }
    }
    return $arrayRewrite;
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