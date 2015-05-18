<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_HtmlParser
{
    // resource details
    private $url;
    private $domain;
    private $fragment;
    private $protocol;
    private $path;
    private $raw;
    private $referer;

    // may be an html or a raw data (used for images, etc)
    private $data = null;
    private $dataHTTPStatus = null;
    private $dataUTF8 = null;

    // html dom details
    private $dom = null;
    private $article = null;
    private $articles = null;
    private $xpath = null;

    // article details
    private $title = null;
    private $keywords = null;
    private $description = null;

    // asynch download settings
    private $asynchHandle = null;
    private static $ASYNC_SUPPORT_CURL_MULTI_HANDLER = null;
    private static $REQUEST_TIMEOUT = 10;

    public static function supportsAsynch()
    {
        return is_callable('curl_init');
    }

    public static function supportsDownload()
    {
        return is_callable('curl_init') || preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));
    }

    public function __construct($url, $raw = false, $referer = null)
    {
        if ($url) {
            $parsedURL = parse_url($url);

            $path = $parsedURL['path'];
            $lastof = strrpos($path, "/");
            $path = substr($path, 0, $lastof);

            $this->domain = 'http://' . $parsedURL['host'];
            $this->path = 'http://' . $parsedURL['host'] . "/" . $path . "/";
            $this->fragment = $parsedURL['fragment'];
            $this->protocol = $parsedURL['scheme'];
            $query = $parsedURL['query'];

            // google redirection fix
            if (strpos($this->protocol . "://www.google.com/url", $url) == 0) {
                $url_query = explode("&", $query);
                foreach ($url_query as $param) {
                    if (strpos($param, 'url=') === 0) {
                        $url = str_replace("url=", "", $param);
                        $url = urldecode($url);
                        break;
                    }
                }
            }
            $this->url = html_entity_decode($url);
        }
        $this->raw = $raw;
        $this->referer = $referer;
    }

    public function getFile()
    {
        if (self::supportsAsynch()) {
            $this->downloadAsynch();
            self::ensureAsynchData();
            return $this->getAsynchData();
        } else {
            return $this->download();
        }
    }

    public function getHTTPStatus()
    {
        return $this->dataHTTPStatus;
    }

    public function isHTTPStatusOK()
    {
        if ((is_array($this->dataHTTPStatus) && ($this->dataHTTPStatus[0] == "HTTP/1.1 200 OK" || strpos($this->dataHTTPStatus[0], '200'))) || $this->dataHTTPStatus == "HTTP/1.1 200 OK" || $this->dataHTTPStatus == 200 || $this->dataHTTPStatus == 'HTTP\/1.1 200 OK') {
            return true;
        }
        if (is_array($this->dataHTTPStatus) && ($this->dataHTTPStatus[0] == "HTTP/1.1 301 Moved Permantenly" || strpos($this->dataHTTPStatus[0], '301')) || ($this->dataHTTPStatus[0] == "HTTP/1.1 302 Found" || strpos($this->dataHTTPStatus[0], '302')) || ($this->dataHTTPStatus[0] == "HTTP/1.1 303 See Other" || strpos($this->dataHTTPStatus[0], '303')) || ($this->dataHTTPStatus[0] == "HTTP/1.1 307 Temporary Redirect" || strpos($this->dataHTTPStatus[0], '307')) || ($this->dataHTTPStatus[0] == "HTTP/1.1 308 Permanent Redirect" || strpos($this->dataHTTPStatus[0], '308'))){
            return true;
        }
        return false;
    }

    public function getHTTPStatusCode()
    {
        if ($this->isHTTPStatusOK()) {
            return 200;
        }

        if ((is_array($this->dataHTTPStatus) && ($this->dataHTTPStatus[0] == "HTTP/1.1 403 Forbidden" || strpos($this->dataHTTPStatus[0], '403'))) || $this->dataHTTPStatus == "HTTP/1.1 403 Forbidden" || $this->dataHTTPStatus == 403) {
            return 403;
        }

        return $http_response_header[0];
    }

    public function downloadAsynch()
    {
        if ($this->data != null) {
            return;
        }

        if (self::supportsAsynch() == false) {
            return $this->download();
        }

        if (self::$ASYNC_SUPPORT_CURL_MULTI_HANDLER == null) {
            $mh = curl_multi_init();
            set_time_limit(0);

            self::$ASYNC_SUPPORT_CURL_MULTI_HANDLER = $mh;
        }

        // setup the single curl
        $ch = $this->createCURL($this->url);

        // add the single handle to the multi handle
        curl_multi_add_handle(self::$ASYNC_SUPPORT_CURL_MULTI_HANDLER, $ch);
        // start the download
        curl_multi_exec(self::$ASYNC_SUPPORT_CURL_MULTI_HANDLER, $running);

        // keep the handle for later
        $this->asynchHandle = $ch;
    }

    private function createCURL($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, ExpressCurate_Actions::USER_AGENT);
        if ($this->referer) {
            curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/xhtml+xml, application/xml", "Accept-Charset: utf-8"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$REQUEST_TIMEOUT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        return $ch;
    }

    private static function ensureAsynchData()
    {
        do {
            curl_multi_exec(self::$ASYNC_SUPPORT_CURL_MULTI_HANDLER, $running);
        } while ($running > 0);
    }

    private function getAsyncData()
    {
        $content = curl_multi_getcontent($this->asynchHandle);
        $this->dataHTTPStatus = curl_getinfo($this->asynchHandle, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($this->asynchHandle, CURLINFO_CONTENT_TYPE);

        curl_multi_remove_handle(self::$ASYNC_SUPPORT_CURL_MULTI_HANDLER, $this->asynchHandle);

        if (!$this->raw) {
            $content = self::sanitizeContent($content);

            if ($contentType) {
                list($charset, $encoding) = explode("=", $contentType);
                $encoding = strtoupper(trim($encoding));

                $supportedEncoding = array_search($encoding, mb_list_encodings()) !== false;

                if (!$supportedEncoding) {
                    $encoding = mb_detect_encoding($content);
                }

                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
            }
        }

        $this->data = $content;

        return $this->data;
    }


    public function getRealURL(){
        foreach(get_headers($this->url) as $header) {
            if (strpos($header, "Location:") === 0) {
                $this->url = trim(substr($header, 9));
            }
        }
        return $this->url;
    }

    public function download()
    {
        if ($this->data != null) {
            return;
        }
        set_time_limit(0);
      if (self::supportsAsynch()) {
          // setup the single curl
          $ch = $this->createCURL($this->url);
          $content = curl_exec($ch);
          //$this->dataHTTPStatus = curl_getinfo($this->asynchHandle, CURLINFO_HTTP_CODE);
          $this->dataHTTPStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
          curl_close($ch);
      } else {
          $header = '';
          if ($this->referer) {
              $header .= 'Referer: ' . $this->referer . '\r\n';
          }
          if (!$this->raw) {
              $header .= 'Accept: application/xhtml+xml, application/xml, text/html\r\n';
              $header .= 'Accept-Charset: UTF-8';
          }
          $options = array('http' => array(
            'user_agent' => ExpressCurate_Actions::USER_AGENT,
            'follow_location' => 1,
            'max_redirects' => 5,
            'request_fulluri ' => TRUE,
            'timeout' => self::$REQUEST_TIMEOUT,
            'header' => $header));
        if (strpos($this->url, 'https://') === 0) {
            $options['ssl'] = array('verify_peer' => false, 'verify_peer_name' => false);
        }
        $context = stream_context_create($options);
        $content = @file_get_contents($this->url, false, $context);
        //var_dump($content);
        // $http_response_header gets loaded once file get contents is called, php native stuff
        $this->dataHTTPStatus = $http_response_header;

        // try to resolve the content encoding if text/html content
        if (!$this->raw) {
            if (!empty($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (substr(strtolower($header), 0, 13) == "content-type:") {
                        $contentTypeData = explode(";", $header);
                        if (count($contentTypeData) == 2) {
                            list( , $contentType) = $contentTypeData;
                        }
                    }
                }
            }
        }
      }
        // make sure if there is a response at all
        if ($this->isHTTPStatusOK() === false) {
            // terminate
            $this->data = null;
            return null;
        }

        // there is data with OK code, process if required
        if (!$this->raw) {
            $content = self::sanitizeContent($content);

            if ($contentType) {
                list( , $encoding) = explode("=", $contentType);
                $encoding = strtoupper(trim($encoding));

                $supportedEncoding = array_search($encoding, mb_list_encodings()) !== false;

                if (!$supportedEncoding) {
                    $encoding = mb_detect_encoding($content);
                }

                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

            }
        }

        // save and return
        $this->data = $content;
        return $this->data;
    }

    private static function sanitizeContent($content)
    {
        //$content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
        //$content = preg_replace('/<--[\S\s]*?-->/msi', '', $content);
        //$content = preg_replace('/(<noscript[^>]*>|<\/noscript>)/msi', '', $content);
        //$content = preg_replace('~>\s+<~', '><', $content);
        //$content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);

        $content = preg_replace('/^.*(?=<html>)/i', '', $content);
        $content = str_replace("\0", " ", $content);

        return $content;
    }

    private function parseDom()
    {
        if ($this->dom == null) {
            // initialize
            $dom = new DOMDocument('1.0', 'UTF-8');
            @$dom->loadHTML($this->data);

            // cleanup
            $this->removeElementsByTagName('script', $dom);
            $this->removeElementsByTagName('style', $dom);
            $this->removeElementsByTagName('link', $dom);
            $this->removeElementsByTagName('noscript', $dom);

            // assign
            $this->dom = $dom;
            $this->xpath = new DomXPath($dom);
        }
    }

    private function removeElementsByTagName($tagName, $document)
    {
        $nodeList = $document->getElementsByTagName($tagName);
        for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0;) {
            $node = $nodeList->item($nodeIdx);
            $node->parentNode->removeChild($node);
        }
    }

    private function parseArticle()
    {
        if ($this->article == null) {
            // TODO check the xpath object problem, the final article shall support the same query method

            $article = $this->xpath->query('//article')->item(0);

            if (empty($article)) {
                $article = $this->xpath->query("//*[contains(@class, 'hentry')]")->item(0);
            }

            if (empty($article)) {
                $article = $this->xpath->query("//*[contains(@itemtype, 'http://schema.org/Article')]")->item(0);
            }

            if (empty($article)) {
                $article = $this->xpath->query("//*[contains(@itemtype, 'http://schema.org/TechArticle')]")->item(0);
            }

            if (empty($article)) {
                $article = $this->xpath->query("//*[contains(@itemtype, 'http://schema.org/ScholarlyArticle')]")->item(0);
            }

            if (empty($article)) {
                $article = $this->xpath->query('//body')->item(0);
            }

            $this->article = $article;
        }
    }

    private function parseArticles(){
        if ($this->articles == null) {
            // TODO check the xpath object problem, the final article shall support the same query method

            $article = $this->xpath->query('//article');
            if ($article->length==0) {
                //$article = $this->xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'hentry')]");
                $article = $this->xpath->query('//div[contains(concat("\s+", normalize-space(@class), "\s+"), " hentry ")]');
            }
            if ($article->length==0) {
                $article = $this->xpath->query("//*[contains(@itemtype, 'http://schema.org/Article')]");
            }
            if ($article->length==0) {
                $article = $this->xpath->query("//*[contains(@itemtype, 'http://schema.org/TechArticle')]");
            }

            if ($article->length==0) {
                $article = $this->xpath->query("//*[contains(@itemtype, 'http://schema.org/ScholarlyArticle')]");
            }

            if ($article->length==0) {
                $article = $this->xpath->query('//body');
            }

            $this->articles = $article;
        }
    }

    public function file_get_contents_utf8($url, $get_http_status = false, $set_utf8 = true)
    {
        $content = '';
        $charset = '';
        $utf8 = false;
        $timeout = 10;
        set_time_limit(0);
        $user_agent = ExpressCurate_Actions::USER_AGENT;
        $file_get_enabled = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));
        if (strpos(parse_url($url, PHP_URL_SCHEME) . "://www.google.com/url", $url) == 0) {
            $url_query = explode("&", parse_url($url, PHP_URL_QUERY));
            foreach ($url_query as $param) {
                if (strpos($param, 'url=') === 0) {
                    $url = str_replace("url=", "", $param);
                    $url = urldecode($url);
                    break;
                }
            }
        }
        $normalized_url = html_entity_decode($url);
        if (self::supportsAsynch()) {
            $ch = curl_init();
            //curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_URL, $normalized_url);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

            // TODO fix the accepts header
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: text/xml;charset=\"utf-8\""));

            // TODO configure the return transfer based on asynch choice
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $content = curl_exec($ch);

            // TODO check if the content shall be transformed to UTF8

            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        } elseif ($file_get_enabled) {
            /*$options = (preg_match("/(^https:\/\/)/i", $url, $options)!=false)?array('ssl' => array('verify_peer'=> false,"verify_peer_name"=>false)):array('http' => array('user_agent' => $user_agent));*/
            $options = array('http' => array('user_agent' => $user_agent, ' follow_location' => 1, 'max_redirects' => 5, 'request_fulluri ' => TRUE, 'timeout' => $timeout));
            if (preg_match("/(^https:\/\/)/i", $url) != false) {
                $options['ssl'] = array('verify_peer' => false, "verify_peer_name" => false);
            }
            $context = stream_context_create($options);
            $content = file_get_contents($normalized_url, false, $context);
            // $content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
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

    public function getContents()
    {
        $this->download();

        if (strlen($this->data) > 3) {
            // prepare some data before getting contents
            $this->title = $this->getTitle();
            $this->keywords = $this->getKeywords();
            $this->description = $this->getDescription();

            $this->parseDom();
            $this->parseArticle();
            // get the contents
            return $this->getElementsByTags();
        } else {
            return false;
        }
    }
    
    public function getCloneContents()
    {
        $this->download();

        if (strlen($this->data) > 3) {
            // prepare some data before getting contents
            $this->title = $this->getTitle();
            $this->keywords = $this->getKeywords();
            $this->parseDom();
            $this->parseArticles();
            // get the contents
            return $this->cloneElements();
        } else {
            return false;
        }
    }

    private function getTitle()
    {
        $this->parseDom();

        $title = $this->xpath->query('//title')->item(0)->nodeValue;

        return $title;
    }

    private function getKeywords()
    {
        // Get the 'content' attribute value in a <meta name="keywords" ... />
        $matches = array();
        $max_count = get_option("expresscurate_max_tags", 3);
        // Search for <meta name="keywords" content="keyword1, keword2" />
        preg_match('/<meta.*?name=("|\')keywords("|\').*?content=("|\')(.*?)("|\')/i', $this->data, $matches);
        if (count($matches) > 4) {
            if(strpos(trim($matches[4]),',')){
                return array_filter(explode(",", trim($matches[4])));
            }
            else{
                return array_filter(explode(" ", trim($matches[4])));
            }
        }

        // Order of attributes could be swapped around: <meta content="keyword1, keword2" name="keywords" />
        preg_match('/<meta.*?content=("|\')(.*?)("|\').*?name=("|\')keywords("|\')/i', $this->data, $matches);
        if (count($matches) > 2) {
            if(strpos(trim($matches[2]),',')){
                return array_filter(explode(",", trim($matches[2])));
            }
            else{
                return array_filter(explode(" ", trim($matches[2])));
            }
        }

        // No match
        return null;
    }

    private function getDescription()
    {
        // Get the 'content' attribute value in a <meta name="description" ... />
        $matches = array();

        // Search for <meta name="description" content="Buy my stuff" />
        preg_match('/<meta.*?name=("|\')description("|\').*?content=("|\')(.*?)("|\')/i', $this->data, $matches);
        if (count($matches) > 4) {
            return $matches[4];
        }

        // Order of attributes could be swapped around: <meta content="Buy my stuff" name="description" />
        preg_match('/<meta.*?content=("|\')(.*?)("|\').*?name=("|\')description("|\')/i', $this->data, $matches);
        if (count($matches) > 2) {
            return $matches[2];
        }

        // No match
        return null;
    }

    private function getElementsByTags()
    {

        // TODO make sure this cleanup can be done earlier or later, or maybe shall not affect the base dom with original html at all

        $result_images = array();
        $result_paragraphs = array();
        $result_h1 = '';
        $result_h2 = '';
        $result_h3 = '';


        // TODO this is a new xpath with the new modified dom, not sure if this is required if dom is passed with a reference
        $imgTags = $this->xpath->query(".//img", $this->article);
        $i = 0;
        foreach ($imgTags as $t) {
            $src = $t->getAttribute('src');
            if (strlen($src) > 3) {
                if (strpos($src, 'http://') !== false || strpos($src, 'https://') !== false) {
                    $src = $src;
                } else if (strpos($src, '//') === 0) {
                    if (isset($this->fragment)) {
                        $src = $this->fragment . $src;
                    } else {
                        $src = $this->protocol . ":" . $src;
                    }
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
        $h1Tag = $this->xpath->query(".//h1", $this->article);
        foreach ($h1Tag as $h1) {
            if (strlen($h1->nodeValue) > 3) {
                $result_h1 .= $h1->nodeValue . "\n";
            }
            $h1->parentNode->removeChild($h1);
        }
        // TODO add bullets
        //get H2
        $h2Tag = $this->xpath->query(".//h2", $this->article);
        foreach ($h2Tag as $h2) {
            if (strlen($h2->nodeValue) > 3) {
                $result_h2 .= $h2->nodeValue . "\n";
            }
            $h2->parentNode->removeChild($h2);
        }
        //get H3
        $h3Tag = $this->xpath->query(".//h3", $this->article);
        foreach ($h3Tag as $h3) {
            if (strlen($h3->nodeValue) > 3) {
                $result_h3 .= $h3->nodeValue . "\n";
            }
            $h3->parentNode->removeChild($h3);
        }

        // reset cursor for paragraphs
        $i = 0;

        // get blockquotes
        $blockquoteTag = $this->xpath->query(".//blockquote", $this->article);
        foreach ($blockquoteTag as $blockquote) {
            $result_paragraphs[$i]['value'] = trim(strip_tags($blockquote->nodeValue));
            $result_paragraphs[$i]['tag'] = 'blockquote';
            $i++;
            
            $blockquote->parentNode->removeChild($blockquote);
        }
        
        // get paragraphs
        $paragraphTag = $this->xpath->query(".//p", $this->article);
        foreach ($paragraphTag as $paragraph) {
            $result_paragraphs[$i]['value'] = trim(strip_tags($paragraph->nodeValue));
            $result_paragraphs[$i]['tag'] = 'p';
            $i++;
            
            $paragraph->parentNode->removeChild($paragraph);
        }

        // get to floating texts
        $textTags = $this->xpath->query('//text()', $this->article);
        foreach ($textTags as $t) {
            $result_paragraphs[$i]['value'] = strip_tags($t->nodeValue);
            $result_paragraphs[$i]['tag'] = $t->parentNode->nodeName;
            $i++;
        }

        //author
        $article_author = '';
        $author = $this->xpath->query('.//*[@rel="author"][1]', $this->article)->item(0);
        if ($author) {
            $article_author = $author->nodeValue;
        }
        //date
        $article_date = '';
        $date = $this->xpath->query('.//*[@datetime][1]', $this->article)->item(0);
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
                $count = $this->countMatches($tag);
                if ($count > 0) {
                    $smart_tags[$tag] = $count;
                }
            }
        }

        if (count($this->keywords)) {
            foreach ($this->keywords as $key => $keyword) {
                $count = $this->countMatches($key);
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
        $media = $this->containsMedia();

        $result = array(
            'title' => $this->title,
            'headings' => array('h1' => $result_h1, 'h2' => $result_h2, 'h3' => $result_h3),
            'metas' => array('description' => $this->description, 'keywords' => $smart_tags),
            'images' => $result_images,
            'media' => $media,
            'paragraphs' => $result_paragraphs_unique,
            'author' => $article_author,
            'date' => $article_date,
            'domain' => $this->domain);
        $data = array('status' => 'success', 'result' => $result);
        return $data;
    }

    private function in_object($value,$object) {
        if (is_object($object)) {
            foreach($object as $item) {
                if ($value->getAttribute('class')==$item->getAttribute('class') && $item->nodeName!='body' ) return true;
            }
        }
        return false;
    }

    private function cloneElements()
    {
        $articleContent = array(
            "article_html"=>array(),
            "links"=>array(),
            "domains"=>array(),
            "images"=>array(),
            "keywords"=>array(),
            "titles"=>array()
        );
        // TODO make sure this cleanup can be done earlier or later, or maybe shall not affect the base dom with original html at all

        $main_article = $this->xpath->query("//*[contains(@class, 'hentry')]");
        $main_article = ($main_article->length > 0) ? $main_article->item(0) : $this->xpath->query('//body')->item(0);
        foreach($this->articles as $article){
            $this->getArticlesContent($article,$articleContent);
            if(isset($article->parentNode)){
                $article->parentNode->removeChild($article);
            }
        }

        if(!$this->in_object($main_article,$this->articles)){
            $this->getArticlesContent($main_article,$articleContent);
        }
        foreach($articleContent as $key => $article_info){
            $articleContent[$key] = array_reverse($article_info);
        }
        $data = array('status' => 'success', 'result' => $articleContent);
        return $data;
    }

    private function getArticlesContent($article,&$articleContent){
        $comments = $this->xpath->query(".//comment()",$article);
        foreach($comments as $comment){
            $comment->parentNode->removeChild($comment);
        }
        $input = $this->xpath->query(".//node()[name()='iframe' or name()='input' or name()='button' or name='textarea' or name()='form']",$article);
        foreach ($input as $inp) {
            $inp->parentNode->removeChild($inp);
        }
        $art = $this->xpath->query('.//article',$article);
        if($art->length>0){
            foreach($art as $artcl){
                $artcl->parentNode->removeChild($artcl);
            }
        }
        $i = 0;
        $imgTags = $this->xpath->query(".//node()[name()='img' or contains(@style,'background-image')]", $article);
        $result_images = array();
        foreach ($imgTags as $t) {
            $src = $t->getAttribute('src');
            $src = !empty($src)? $src : $t->getAttribute('data-src');
            $src = !empty($src)? $src : $t->getAttribute('data-src-template');

            if(empty($src)) {
                $styles = explode(';',$t->getAttribute('style'));
                preg_match("/url[\s]*\(([\'\"]*)([^\'\")]*)/i", $styles[0], $output);
                if(!empty($output)) $src = $output[2];
            }
            if (strlen($src) > 3) {
                if (strpos($src, 'http://') !== false || strpos($src, 'https://') !== false) {
                    $src = $src;
                } else if (strpos($src, '//') === 0) {
                    if (isset($this->fragment)) {
                        $src = $this->fragment . $src;
                    } else {
                        $src = $this->protocol . ":" . $src;
                    }
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
        $articleContent['images'][] = $result_images;
        $title_node = $this->xpath->query('.//node()[name()="h1"]',$article);
        $title = "";
        if($title_node->length > 0){
            $title = $title_node->item(0)->nodeValue;
        }
        else{
            $title_node = $this->xpath->query('.//node()[name()="h2"]',$article);
            if($title_node->length > 0){
                $title = $title_node->item(0)->nodeValue;
            }
            else{
                $title_node = $this->xpath->query('.//*[contains(@class,"title")]',$article);
                $title = $title_node->item(0)->nodeValue;
            }
        }
        $articleContent['titles'][] = trim($title);
        if(isset($title_node->item(0)->parentNode)){
            $title_node->item(0)->parentNode->removeChild($title_node->item(0));
        }
        $articleContent['article_html'][] = $this->dom->saveXML($article);
        $link = ($this->xpath->query('.//a',$article)->length > 0)?$this->xpath->query('.//a',$article)->item(0)->getAttribute('href'):"";
        $link = (strpos($link, '/')==0)? $this->domain.$link : $link;
        $articleContent['links'][] = (!empty($link))? $link : "";
        $articleContent['domains'][] = (!empty($link))? parse_url($link,PHP_URL_SCHEME) ."://". parse_url($link,PHP_URL_HOST) : "";
        //smart tags
        $smart_tags = array();
        $max_count = get_option("expresscurate_max_tags", 3);


        $defined_tags = get_option("expresscurate_defined_tags", '');
        if ($defined_tags) {
            $defined_tags = explode(",", $defined_tags);
            foreach ($defined_tags as $tag) {
                $tag = trim($tag);
                $count = $this->countMatches($tag,$title,$article->nodeValue);
                if ($count > 0) {
                    $smart_tags[$tag] = $count;
                }
            }
        }

        if (count($this->keywords)) {
            foreach ($this->keywords as $key => $keyword) {
                $count = $this->countMatches($key,$title,$article->nodeValue);
                if ($count > 0) {
                    $smart_tags[$keyword] = $count;
                }
            }
        }
        if (count($smart_tags) > 0) {
            arsort($smart_tags);
            $smart_tags = array_slice(array_keys(array_reverse($smart_tags)), 0, $max_count);
        }
        $articleContent['keywords'][] = $smart_tags;
    }
    private function countMatches($keyword,$title=false,$content=false)
    {
        $total_occurrence = 0;
        $tag_in_title = array();
        $tag_in_content = array();
        $title = (!empty($title))? $title: $this->title;
        $content = (!empty($content))? $content : $this->data;
        preg_match_all("/(?<!\w)(?=[^>]*(<|$))" . $keyword . "/i", $title, $tag_in_title);
        preg_match_all("/(?<!\w)(?=[^>]*(<|$))" . $keyword . "/i", $content, $tag_in_content);
        $total_occurrence = count($tag_in_title[0]) + count($tag_in_content[0]);
        return $total_occurrence;
    }



    private function arrayUnique($array, $preserveKeys = false)
    {
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

    public function analyzeKeywords()
    {
        $keywordsString = get_option('expresscurate_defined_tags');
        $blogKeywords = !empty($keywordsString) ? explode(', ', $keywordsString) : array();

        $this->download();
        $this->parseDom();
        $this->parseArticle();

        $title = $this->dom->getElementsByTagName('h1')->item(0)->nodeValue;
        $titleArray = preg_split('/\s+/u', $title);

        $article = strip_tags($this->article->nodeValue);
        $articleFiltered = preg_replace('/\b(a|able|about|above|abroad|according|accordingly|across|actually|adj|after|afterwards|again|against|ago|ahead|ain\'t|all|allow|allows|almost|alone|along|alongside|already|also|although|always|am|amid|amidst|among|amongst|an|and|another|any|anybody|anyhow|anyone|anything|anyway|anyways|anywhere|apart|appear|appreciate|appropriate|are|aren\'t|around|as|a\'s|aside|ask|asking|associated|at|available|away|awfully|b|back|backward|backwards|be|became|because|become|becomes|becoming|been|before|beforehand|begin|behind|being|believe|below|beside|besides|best|better|between|beyond|both|brief|but|by|c|came|can|cannot|cant|can\'t|caption|cause|causes|certain|certainly|changes|clearly|c\'mon|co|co.|com|come|comes|concerning|consequently|consider|considering|contain|containing|contains|corresponding|could|couldn\'t|course|c\'s|currently|d|dare|daren\'t|definitely|described|despite|did|didn\'t|different|directly|do|does|doesn\'t|doing|done|don\'t|down|downwards|during|e|each|edu|eg|eight|eighty|either|else|elsewhere|end|ending|enough|entirely|especially|et|etc|even|ever|evermore|every|everybody|everyone|everything|everywhere|ex|exactly|example|except|f|fairly|far|farther|few|fewer|fifth|first|five|followed|following|follows|for|forever|former|formerly|forth|forward|found|four|from|further|furthermore|g|get|gets|getting|given|gives|go|goes|going|gone|got|gotten|greetings|h|had|hadn\'t|half|happens|hardly|has|hasn\'t|have|haven\'t|having|he|he\'d|he\'ll|hello|help|hence|her|here|hereafter|hereby|herein|here\'s|hereupon|hers|herself|he\'s|hi|him|himself|his|hither|hopefully|how|howbeit|however|hundred|i|i\'d|ie|if|ignored|i\'ll|i\'m|immediate|in|inasmuch|inc|inc.|indeed|indicate|indicated|indicates|inner|inside|insofar|instead|into|inward|is|isn\'t|it|it\'d|it\'ll|its|it\'s|itself|i\'ve|j|just|k|keep|keeps|kept|know|known|knows|l|last|lately|later|latter|latterly|least|less|lest|let|let\'s|like|liked|likely|likewise|little|look|looking|looks|low|lower|ltd|m|made|mainly|make|makes|many|may|maybe|mayn\'t|me|mean|meantime|meanwhile|merely|might|mightn\'t|mine|minus|miss|more|moreover|most|mostly|mr|mrs|much|must|mustn\'t|my|myself|n|name|namely|nd|near|nearly|necessary|need|needn\'t|needs|neither|never|neverf|neverless|nevertheless|new|next|nine|ninety|no|nobody|non|none|nonetheless|noone|no-one|nor|normally|not|nothing|notwithstanding|novel|now|nowhere|o|obviously|of|off|often|oh|ok|okay|old|on|once|one|ones|one\'s|only|onto|opposite|or|other|others|otherwise|ought|oughtn\'t|our|ours|ourselves|out|outside|over|overall|own|p|particular|particularly|past|per|perhaps|placed|please|plus|possible|presumably|probably|provided|provides|q|que|quite|qv|r|rather|rd|re|really|reasonably|recent|recently|regarding|regardless|regards|relatively|respectively|right|round|s|said|same|saw|say|saying|says|second|secondly|see|seeing|seem|seemed|seeming|seems|seen|self|selves|sensible|sent|serious|seriously|seven|several|shall|shan\'t|she|she\'d|she\'ll|she\'s|should|shouldn\'t|since|six|so|some|somebody|someday|somehow|someone|something|sometime|sometimes|somewhat|somewhere|soon|sorry|specified|specify|specifying|still|sub|such|sup|sure|t|take|taken|taking|tell|tends|th|than|thank|thanks|thanx|that|that\'ll|thats|that\'s|that\'ve|the|their|theirs|them|themselves|then|thence|there|thereafter|thereby|there\'d|therefore|therein|there\'ll|there\'re|theres|there\'s|thereupon|there\'ve|these|they|they\'d|they\'ll|they\'re|they\'ve|thing|things|think|third|thirty|this|thorough|thoroughly|those|though|three|through|throughout|thru|thus|till|to|together|too|took|toward|towards|tried|tries|truly|try|trying|t\'s|twice|two|u|un|under|underneath|undoing|unfortunately|unless|unlike|unlikely|until|unto|up|upon|upwards|us|use|used|useful|uses|using|usually|v|value|various|versus|very|via|viz|vs|w|want|wants|was|wasn\'t|way|we|we\'d|welcome|well|we\'ll|went|were|we\'re|weren\'t|we\'ve|what|whatever|what\'ll|what\'s|what\'ve|when|whence|whenever|where|whereafter|whereas|whereby|wherein|where\'s|whereupon|wherever|whether|which|whichever|while|whilst|whither|who|who\'d|whoever|whole|who\'ll|whom|whomever|who\'s|whose|why|will|willing|wish|with|within|without|wonder|won\'t|would|wouldn\'t|x|y|yes|yet|you|you\'d|you\'ll|your|you\'re|yours|yourself|yourselves|you\'ve|z|zero, replacement)\\b/', '', $article);
        $articleFiltered = preg_replace('/\p{P}+/u', ' ', $articleFiltered);
        $articleArray = preg_split('/\s+/u', $articleFiltered);
        $articleWordsCount = count($articleArray);

        $result = array();

        foreach ($blogKeywords as $keyword) {
            $count = 0;
            $inTitle = 'No';

            if (in_array(mb_strtolower($keyword), $articleArray)) {
                $count++;
            }
            foreach ($titleArray as $titleWord) {
                if (mb_strtolower($titleWord) == mb_strtolower($keyword)) {
                    $inTitle = 'Yes';
                    break;
                }
            }
            if ($count !== 0) {
                $result[$keyword] = array('percent' => round($count / $articleWordsCount, 4), 'title' => $inTitle);
            }
        }

        return $result;
    }

    public function containsMedia()
    {
        $this->parseDom();

        $imgTags = $this->xpath->query("//img");
        $img_array = array();
        foreach ($imgTags as $t) {
            $src = $t->getAttribute('src');
            $img_array[] = $src;
        }
        $obj = $this->xpath->query("//node()[(name()='iframe' or name()='video' or name()='source' or name()= 'object' or name()='embed') and (contains(@src,'youtube.com') or contains(@src ,'vimeo.com') or contains(@src,'youtu.be'))]");
        $videoArrays = array();
        foreach ($obj as $objs) {
            array_push($videoArrays, $objs->getAttribute('src'));
        }
        $mediaTags = array("images" => count($img_array), "videos" => count($videoArrays));
        return $mediaTags;
    }
}

?>
