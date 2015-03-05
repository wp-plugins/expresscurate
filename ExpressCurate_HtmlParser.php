<?php
require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

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

  public function __construct($url = false) {
    if($url){
        $this->url = $url;
        $path = parse_url($this->url, PHP_URL_PATH);
        $lastof = strrpos($path, "/");
        $path = substr($path, 0, $lastof);
        $this->domain = 'http://' . parse_url($this->url, PHP_URL_HOST);
        $this->path = 'http://' . parse_url($this->url, PHP_URL_HOST) . "/" . $path . "/";
        $this->fragment = parse_url($this->url, PHP_URL_FRAGMENT);
    }

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
    mb_convert_encoding($this->html, 'HTML-ENTITIES', "UTF-8");
    if (strlen($this->html) > 3) {
      $this->title = $this->getTitle();
      $this->keywords = $this->getKeywords();
      $this->description = $this->getDescription();
      $this->cleanHtml();
      $result = $this->getElementsByTags();
      return $result;
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

  private function getElementsByTags() {
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
    //author
    $article_author = '';
    $author = $xpath->query('//*[@rel="author"][1]')->item(0);
    if ($author) {
      $article_author = $author->nodeValue;
    }
    //date
    $article_date = '';
    $date = $xpath->query('//*[@datetime][1]')->item(0);
    if ($date) {
      $article_date = $date->nodeValue;
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
    $result = array('title' => $this->title, 'headings' => array('h1' => $result_h1, 'h2' => $result_h2, 'h3' => $result_h3), 'metas' => array('description' => $this->description, 'keywords' => $smart_tags), 'images' => $result_images, 'paragraphs' => $result_paragraphs_unique, 'author' => $article_author, 'date' => $article_date, 'domain' => $this->domain);
    $data = array('status' => 'success', 'result' => $result);
    return $data;
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
        $options = (preg_match("/(^https:\/\/)/i", $url, $options)!=false)?array('ssl' => array('verify_peer'=> false,"verify_peer_name"=>false)):array('http' => array('user_agent' => $user_agent));
        $context = stream_context_create($options);
      $content = @file_get_contents($url, false, $context);
       // $content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
      $http_status = $http_response_header;

      if(!empty($http_response_header)) {
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
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: text/xml;charset=\"utf-8\""));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
      $content = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
    } else {
      $data = array('status' => 'warning', 'msg' => 'Content from this page cannot be loaded.  Please enable \"allow_url_open\" in php.ini.');
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

  public function analyzeKeywords(){
      $keywordsString =  get_option('expresscurate_defined_tags');
      $blogKeywords = !empty($keywordsString)? explode(', ', $keywordsString) : array();
      $this->doRequest();
      mb_convert_encoding($this->html, 'HTML-ENTITIES', "UTF-8");
      $dom = new DOMDocument('1.0', 'UTF-8');

      @$dom->loadHTML($this->html);
      $finder = new DomXPath($dom);
      $title = $dom->getElementsByTagName('h1')->item(0)->nodeValue;
      $titleArray = preg_split('/\s+/u', $title);

      $this->removeElementsByTagName('script', $dom);
      $this->removeElementsByTagName('style', $dom);
      $this->removeElementsByTagName('link', $dom);

      $article = strip_tags($dom->getElementsByTagName('article')->item(0)->nodeValue);
      if(empty($article)){
          $article = strip_tags($finder->query("//*[contains(@class, 'hentry')]")->item(0)->nodeValue);
      }

      if(empty($article)){
          $article = strip_tags($finder->query("//*[contains(@itemtype, 'http://schema.org/Article')]")->item(0)->nodeValue);
      }

      if(empty($article)){
          $article = strip_tags($finder->query("//*[contains(@itemtype, 'http://schema.org/TechArticle')]")->item(0)->nodeValue);
      }

      if(empty($article)){
          $article = strip_tags($finder->query("//*[contains(@itemtype, 'http://schema.org/ScholarlyArticle')]")->item(0)->nodeValue);
      }

      if(empty($article)){
          $article = strip_tags($dom->getElementsByTagName('body')->item(0)->nodeValue);
      }

      $articleFiltered  =  preg_replace('/\b(a|able|about|above|abroad|according|accordingly|across|actually|adj|after|afterwards|again|against|ago|ahead|ain\'t|all|allow|allows|almost|alone|along|alongside|already|also|although|always|am|amid|amidst|among|amongst|an|and|another|any|anybody|anyhow|anyone|anything|anyway|anyways|anywhere|apart|appear|appreciate|appropriate|are|aren\'t|around|as|a\'s|aside|ask|asking|associated|at|available|away|awfully|b|back|backward|backwards|be|became|because|become|becomes|becoming|been|before|beforehand|begin|behind|being|believe|below|beside|besides|best|better|between|beyond|both|brief|but|by|c|came|can|cannot|cant|can\'t|caption|cause|causes|certain|certainly|changes|clearly|c\'mon|co|co.|com|come|comes|concerning|consequently|consider|considering|contain|containing|contains|corresponding|could|couldn\'t|course|c\'s|currently|d|dare|daren\'t|definitely|described|despite|did|didn\'t|different|directly|do|does|doesn\'t|doing|done|don\'t|down|downwards|during|e|each|edu|eg|eight|eighty|either|else|elsewhere|end|ending|enough|entirely|especially|et|etc|even|ever|evermore|every|everybody|everyone|everything|everywhere|ex|exactly|example|except|f|fairly|far|farther|few|fewer|fifth|first|five|followed|following|follows|for|forever|former|formerly|forth|forward|found|four|from|further|furthermore|g|get|gets|getting|given|gives|go|goes|going|gone|got|gotten|greetings|h|had|hadn\'t|half|happens|hardly|has|hasn\'t|have|haven\'t|having|he|he\'d|he\'ll|hello|help|hence|her|here|hereafter|hereby|herein|here\'s|hereupon|hers|herself|he\'s|hi|him|himself|his|hither|hopefully|how|howbeit|however|hundred|i|i\'d|ie|if|ignored|i\'ll|i\'m|immediate|in|inasmuch|inc|inc.|indeed|indicate|indicated|indicates|inner|inside|insofar|instead|into|inward|is|isn\'t|it|it\'d|it\'ll|its|it\'s|itself|i\'ve|j|just|k|keep|keeps|kept|know|known|knows|l|last|lately|later|latter|latterly|least|less|lest|let|let\'s|like|liked|likely|likewise|little|look|looking|looks|low|lower|ltd|m|made|mainly|make|makes|many|may|maybe|mayn\'t|me|mean|meantime|meanwhile|merely|might|mightn\'t|mine|minus|miss|more|moreover|most|mostly|mr|mrs|much|must|mustn\'t|my|myself|n|name|namely|nd|near|nearly|necessary|need|needn\'t|needs|neither|never|neverf|neverless|nevertheless|new|next|nine|ninety|no|nobody|non|none|nonetheless|noone|no-one|nor|normally|not|nothing|notwithstanding|novel|now|nowhere|o|obviously|of|off|often|oh|ok|okay|old|on|once|one|ones|one\'s|only|onto|opposite|or|other|others|otherwise|ought|oughtn\'t|our|ours|ourselves|out|outside|over|overall|own|p|particular|particularly|past|per|perhaps|placed|please|plus|possible|presumably|probably|provided|provides|q|que|quite|qv|r|rather|rd|re|really|reasonably|recent|recently|regarding|regardless|regards|relatively|respectively|right|round|s|said|same|saw|say|saying|says|second|secondly|see|seeing|seem|seemed|seeming|seems|seen|self|selves|sensible|sent|serious|seriously|seven|several|shall|shan\'t|she|she\'d|she\'ll|she\'s|should|shouldn\'t|since|six|so|some|somebody|someday|somehow|someone|something|sometime|sometimes|somewhat|somewhere|soon|sorry|specified|specify|specifying|still|sub|such|sup|sure|t|take|taken|taking|tell|tends|th|than|thank|thanks|thanx|that|that\'ll|thats|that\'s|that\'ve|the|their|theirs|them|themselves|then|thence|there|thereafter|thereby|there\'d|therefore|therein|there\'ll|there\'re|theres|there\'s|thereupon|there\'ve|these|they|they\'d|they\'ll|they\'re|they\'ve|thing|things|think|third|thirty|this|thorough|thoroughly|those|though|three|through|throughout|thru|thus|till|to|together|too|took|toward|towards|tried|tries|truly|try|trying|t\'s|twice|two|u|un|under|underneath|undoing|unfortunately|unless|unlike|unlikely|until|unto|up|upon|upwards|us|use|used|useful|uses|using|usually|v|value|various|versus|very|via|viz|vs|w|want|wants|was|wasn\'t|way|we|we\'d|welcome|well|we\'ll|went|were|we\'re|weren\'t|we\'ve|what|whatever|what\'ll|what\'s|what\'ve|when|whence|whenever|where|whereafter|whereas|whereby|wherein|where\'s|whereupon|wherever|whether|which|whichever|while|whilst|whither|who|who\'d|whoever|whole|who\'ll|whom|whomever|who\'s|whose|why|will|willing|wish|with|within|without|wonder|won\'t|would|wouldn\'t|x|y|yes|yet|you|you\'d|you\'ll|your|you\'re|yours|yourself|yourselves|you\'ve|z|zero, replacement)\\b/','', $article);
      $articleFiltered  =  preg_replace('/\p{P}+/u',' ',$articleFiltered);
      $articleArray = preg_split('/\s+/u', $articleFiltered);
      $articleWordsCount = count($articleArray);

      foreach($blogKeywords as $keyword) {
          $count = 0;
          $inTitle = 'No';
         /* foreach($articleArray as $word){
              if (mb_strtolower($word) == mb_strtolower($keyword)){
               $count++;
              }
          }*/
          if (in_array(mb_strtolower($keyword), $articleArray)){
              $count++;
          }
          foreach($titleArray as $titleWord){
              if (mb_strtolower($titleWord) == mb_strtolower($keyword)){
                  $inTitle = 'Yes';
                  break;
              }
          }
            if($count !== 0){
                $this->keywords[$keyword] = array('percent' => round($count/$articleWordsCount, 4), 'title'=>$inTitle);
            }

      }

      return $this->keywords;
  }

    function getTextBetweenTags($tag, $html, $strict=0)
    {
        /*** a new dom object ***/
//        $dom = new domDocument;
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->strictErrorChecking = FALSE ;
        $html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );
        libxml_use_internal_errors(true);
        /*** load the html into the object ***/
        if($strict==1) {
            @$dom->loadXML($html);
            $loadHTML = true;
        } else {
            $dom->loadHTML($html);
            if(count(libxml_get_errors())>0){
                update_option("expresscurate_html_error","Unable to load malstructered HTML. Please edit html structure and save again");
            }
            else {
                $loadHTML = true;
                update_option("expresscurate_html_error",'');
            }

        }

        if($loadHTML){
            /*** discard white space ***/
            $dom->preserveWhiteSpace = false;

            /*** the tag by its tag name ***/
            $content = $dom->getElementsByTagname($tag);

            /*** the array to return ***/
            $out = array();
            foreach ($content as $item) {
                /*** add node value to the out array ***/
                $out[] = $item->nodeValue;
            }
            /*** return the results ***/
            return $out;
        }
    }


}

?>
