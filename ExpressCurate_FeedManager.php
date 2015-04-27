<?php
require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_FeedManager
{
    const CONTENT_FEED_MAX_SIZE = 500;

    private static $instance;

    function __construct()
    {
        // action shall be added from actions controller
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function add_feed()
    {
        global $wpdb;
        if(!ExpressCurate_HtmlParser::supportsDownload()){
            $result = array("status"=>"You should activate either curl extension or allow_url_fopen setting.");
        } else {
            $url = isset($url) ? $url : $_REQUEST['url'];
            $url = trim($url);

            if (isset($url)) {
                $result = array();
                $curated_links_rss = get_option('expresscurate_links_rss', '');
                if ($curated_links_rss) {
                    $curated_links_rss = json_decode($curated_links_rss, true);
                } else {
                    $curated_links_rss = array();
                }

                $rssUrl = $this->getRssUrl($url);

                if ($rssUrl === null) {
                    $result['status'] = 'No RSS feed found at this URL.';
                } else if (isset($curated_links_rss[$rssUrl])) {
                    $result['status'] = 'URL already exists.';
                } else {
                    if (filter_var($rssUrl, FILTER_VALIDATE_URL)) {
                        $result['status'] = 'success';
                        
                        // retrieve the feed title
                        $feedMeta = $this->getFeedMeta($rssUrl);
                        $link = $feedMeta['link'];
                        $link = empty($link) ? $rssUrl : $link;
                        
                        // get the number of posts that are curated from this feed
                        $metas = $wpdb->get_results(
                            "SELECT post_id
                             FROM $wpdb->postmeta
                             WHERE meta_key LIKE  '%_expresscurate_link_%' AND meta_value LIKE '%" . $link . "%' GROUP BY post_id");

                        // construct the rss data
                        $curated_links_rss[$rssUrl]['feed_url'] = $result['feed_url'] = $rssUrl;
                        $curated_links_rss[$rssUrl]['link'] = $result['link'] = $link;
                        $curated_links_rss[$rssUrl]['post_count'] = $result['post_count'] = count($metas);
                        $curated_links_rss[$rssUrl]['feed_title'] = $result['feed_title'] = $feedMeta['title'];
                        
                        // save
                        update_option('expresscurate_links_rss', json_encode($curated_links_rss));
                    } else {
                        $result['status'] = 'Invalid RSS URL.';
                    }
                }
            } else {
                $result['status'] = 'Something went wrong. Please check the URL. If the problem persists, please contact us.';
            }
        }
        echo json_encode($result);

        die;
    }
    
    private function getFeedMeta($feedURL) {
        $loadURL = "http://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q=" . urlencode($feedURL);
        $htmlparser = new ExpressCurate_HtmlParser($loadURL);
        $res = $htmlparser->download();
        $result = json_decode($res, true);
        
        if(isset($result['responseData']) && isset($result['responseData']['feed'])) {
            $meta = array();
            $meta['title'] = $result['responseData']['feed']['title'];
            $meta['feed']  = $result['responseData']['feed']['feedUrl'];
            $meta['link']  = $result['responseData']['feed']['link'];
            return $meta;
        } else {
            return null;
        }
    }

    public function delete_feed()
    {
        $result = array();
        $data = $_REQUEST;
        if ($data['url']) {
            $curated_links_rss = get_option('expresscurate_links_rss', '');
            if ($curated_links_rss) {
                $curated_links_rss = json_decode($curated_links_rss, true);
            } else {
                $curated_links_rss = array();
            }
            if (!isset($curated_links_rss[$data['url']])) {
                $result['status'] = 'record_not_found';
            } else {
                unset($curated_links_rss[$data['url']]);
                update_option('expresscurate_links_rss', json_encode($curated_links_rss));
                $result['status'] = 'success';
            }
            // remove rss from top sources
            $top_sources_rss = get_option('expresscurate_top_sources_rss', '');
            if ($top_sources_rss) {
                $top_sources_rss = json_decode($top_sources_rss, true);
            } else {
                $top_sources_rss = array();
            }
            if (isset($top_sources_rss['links'][$data['url']])) {
                $top_sources_rss['links'][$data['url']]['feed_options']['feed_status'] = 'rssStatusAdd';
                $top_sources_rss = json_encode($top_sources_rss);
                update_option('expresscurate_top_sources_rss', $top_sources_rss);
                $result['status'] = 'success';
            }
        } else {
            $result['status'] = 'warning';
        }
        echo json_encode($result);
        die;
    }

    public function get_curated_links()
    {
        $curated_links = array();
        $date_after = '';
        $curated_links_rss = get_option('expresscurate_links_rss', '');
        if ($curated_links_rss) {
            $curated_links_rss = json_decode($curated_links_rss, true);
            
            $links = array();
            foreach($curated_links_rss as $link => $data) {
                $url = $data['feed_url'];
                $links[$url] = 1;
            }
            $curated_links_rss = $links;
        } else {
            $curated_links_rss = array();
        }
        $top_sources_rss = get_option('expresscurate_top_sources_rss', '');

        if ($top_sources_rss) {
            // check if migrated
            $migrated1 = (isset($top_sources_rss['migrated']) && $top_sources_rss['migrated']== 1);
            // if not migrated, clean the data up, calculate stats from scratch
            $top_sources_rss = $migrated1 ? json_decode($top_sources_rss, true) : array();
            $date_after = $migrated1 ? strtotime($top_sources_rss['date']) . "&" : '';
        } else {
            $top_sources_rss = array();
            $top_sources_rss['links'] = array();
        }
        $top_sources_rss['migrated'] = 1;

        $curated_posts_query = new WP_Query("meta_key=_is_expresscurate&meta_value=1&posts_per_page=-1&" . $date_after . "order=DESC");

        if ($curated_posts_query->have_posts()) {
            $i = 0;
            while ($curated_posts_query->have_posts()) {
                $curated_posts_query->the_post();
                $meta_values = get_post_meta(get_the_ID());
                // var_dump($meta_values);
                foreach ($meta_values as $key => $value) {
                    if (preg_match('/_expresscurate_link_\d/', $key)) {
                        if ($meta_values[$key][0]) {
                            // parse the host
                            $url = parse_url($meta_values[$key][0]);
                            $host = $url['host'];
                            
                            // get only the main domain
                            preg_match('/([a-z0-9\-_]{1,63})\.([a-z]{2,6})$/i', $host, $regs);
                            $link = $regs[1] . '.' . $regs[2];
                            
                            // filter
                            if(empty($link) || $link == '.') {
                                continue;
                            }
                            
                            // add
                            $curated_links[$i]['host'] = $host;
                            $curated_links[$i]['link'] = $link;
                            $curated_links[$i]['post_id'] = get_the_ID();
                            $i++;
                        }
                    }
                }
            }
        }
        wp_reset_postdata();
        
        $rssLinks = array();
        
        if(ExpressCurate_HtmlParser::supportsDownload()) {
            foreach ($curated_links as $key => $top_link) {
                $websiteHost = $top_link['host'];
                $website = $top_link['link'];
                
                if(isset($rssLinks[$websiteHost])) {
                    $rssUrl = $rssLinks[$websiteHost];
                } else {
                    $rssUrl = $this->getRssUrl($websiteHost);
                    $rssLinks[$websiteHost] = $rssUrl;
                }
                
                if ($rssUrl && isset($curated_links_rss[$rssUrl])) {
                    $feed_status = 'rssStatusYes';
                } else {
                    if ($rssUrl) {
                        $feed_status = 'rssStatusAdd';
                    } else {
                        $feed_status = 'rssStatusNo';
                    }
                }
                
                $top_sources_rss['links'][$website] = array(
                    'post_ids' => array($top_link['post_id']),
                    'feed_options' => array(
                        'feed_url' => $rssUrl,
                        'feed_status' => $feed_status,
                        'type' => 'feed')
                );
                $top_sources_rss['links'][$website]['post_ids'] = array_unique($top_sources_rss['links'][$website]['post_ids']);
                $top_sources_rss['links'][$website]['post_count'] = count($top_sources_rss['links'][$website]['post_ids']);
            }

            @uasort($top_sources_rss['links'], array($this, "sort_by_count"));
            $top_sources_rss['date'] = date('Y-m-d H:i:s');
            $top_sources_rss_save = json_encode($top_sources_rss);
            update_option('expresscurate_top_sources_rss', $top_sources_rss_save);
        }
        return $top_sources_rss;
    }

    public function get_feed_list()
    {
        $content = get_option('expresscurate_feed_content', '');
        if ($content) {
            $content = json_decode($content, true);
        } else {
            $content = array();
        }
        return $content;
    }

    public function get_rss_list()
    {
        global $wpdb;
        
        $curated_links_rss = get_option('expresscurate_links_rss', '');
        if ($curated_links_rss) {
            $curated_links_rss = json_decode($curated_links_rss, true);
            
            $save = false;
            foreach($curated_links_rss as $rss => $data) {
                if(isset($data['feed_title']) && strlen(trim($data['feed_title'])) > 0) {
                    continue;
                }
                
                // correct the url and get the meta data
                $rssUrl = $this->getRssUrl($rss);
                $feedMeta = $this->getFeedMeta($rssUrl);
                $link = $feedMeta['link'];
                $link = empty($link) ? $rssUrl : $link;
                
                $data = array();
                $data['feed_title'] = $feedMeta['title'];
                $data['feed_url'] = $rssUrl;
                $data['link'] = $link;
                
                // remove the old row and add the new one
                unset($curated_links_rss[$rss]);
                $curated_links_rss[$link] = $data;
                
                // make sure to save in the end
                $save = true;
            }
            
            // update the post count
            foreach($curated_links_rss as $link => $data) {
                // get the number of posts that are curated from this feed
                $posts = $wpdb->get_results(
                            "SELECT post_id
                             FROM $wpdb->postmeta
                             WHERE meta_key LIKE  '%_expresscurate_link_%' AND meta_value LIKE '%" . $link . "%' GROUP BY post_id");
                
                $curated_links_rss[$link]['post_count'] = count($posts);
            }
            
            // save
            update_option('expresscurate_links_rss', json_encode($curated_links_rss));
        } else {
            $curated_links_rss = array();
        }
        
        unset($curated_links_rss['']);
        return $curated_links_rss;
    }

    /*
     * Performs a look up for a feed for the given URL
     *
     * @param string $url The URL to be checked
     *
     * @return mixed|null JSON response or null if $url or $result is not set
     * */
    public function doRssLookup($url) {
        if(isset($url)) {
            $lookup_url = "http://ajax.googleapis.com/ajax/services/feed/lookup?v=1.0&q=" . urlencode($url);
            //$result = file_get_contents($lookup_url);
            $htmlparser = new ExpressCurate_HtmlParser($lookup_url);
            $result = $htmlparser->download();

            if (isset($result)) {
                return $result;
            }
        }

        return null;
    }


    /*
     * TODO: Remove this function and replace all calls with doRssLookup(). Make sure that everything works correctly.
     * */
    public function getRssUrl($url)
    {
        if (strpos($url, 'http') === false) {
            $url = 'http://' . $url;
        }

        $lookup_url = "http://ajax.googleapis.com/ajax/services/feed/lookup?v=1.0&q=" . urlencode($url);
        $htmlparser = new ExpressCurate_HtmlParser($lookup_url);
        $res = $htmlparser->download();
        $result = json_decode($res);
        if ($result && $result->responseData) {
            return $result->responseData->url;
        } elseif ($result && $result->responseData === null) {
            return null;
        }
        return false;
    }
    
    

    public function get_feed_content()
    {
        // read the existing feed to modify below
        $feedContent = get_option('expresscurate_feed_content');
        if (empty($feedContent)) {
            $feed_array = array();
        } else {
            $feedContent = json_decode($feedContent, true);
            $feed_array = $feedContent['content'];
        }

        // blabla
        $data = $_REQUEST;
        $pull_feed_interval = (get_option('expresscurate_pull_hours_interval')) ? get_option('expresscurate_pull_hours_interval') : 1;
        $date = ($data["date"]) ? urldecode($data["date"]) : date('Y-m-d H:i:s', strtotime("-" . $pull_feed_interval . " hour"));
        $curated_links_rss = get_option('expresscurate_links_rss', '');
        if ($curated_links_rss) {
            // get the deleted suggestions
            $deleted_urls = array();
            $feed_content_deleted = get_option('expresscurate_feed_content_deleted', '');
            if (empty($feed_content_deleted)) {
                $feed_content_deleted = array();
            } else {
                $feed_content_deleted = json_decode($feed_content_deleted, true);
            }
            if (is_array($feed_content_deleted)) {
                foreach ($feed_content_deleted as $deletet_item) {
                    $deleted_urls[] = $deletet_item;
                }
            }
            
            // go over the feeds and try to pull
            $curated_links_rss = json_decode($curated_links_rss, true);
            if (count($curated_links_rss)) {
                foreach ($curated_links_rss as $url => $feed_url) {
                    // pull content
                    $lookup_url = "http://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q=" . urlencode($feed_url['feed_url']);
                    $htmlparser = new ExpressCurate_HtmlParser($lookup_url);
                    $res = $htmlparser->download();
                    // decode and collect
                    $result = json_decode($res, true);
                    $this->collect_feed($result, $deleted_urls, $feed_array, 'feed', $date);
                }
            }

            // check if the feed is empty and sort
            if(!empty($feed_array)) {
                @uasort($feed_array, array($this, "feedSortByDate"));
                
                // check if the feed is full or not
                if(count($feed_array) > self::CONTENT_FEED_MAX_SIZE) {
                    // remove the last elements
                    $feed_array = array_slice($feed_array,0,self::CONTENT_FEED_MAX_SIZE);
                    // TODO implement

                }
            }
            
            // save the latest feed
            $feed_content = json_encode(array('date' => date('Y-m-d H:i:s'), 'content' => $feed_array));
            update_option('expresscurate_feed_content', $feed_content);
            return $feed_content;
        }
    }
    
    private function collect_feed($result, $deleted_urls, &$feed_array, $type = 'feed', $date = false)
    {
        $feeds = array();
        if ($type == 'wall' && $result) {
            $feeds = $result;
        } elseif ($type == 'feed') {
            if (isset($result['responseData']['feed']['entries'])) {
                $feeds = $result['responseData']['feed']['entries'];
            }
        }
        foreach ($feeds as $story) {
            // get the post url
            $link = isset($story['link']) ? $story['link'] : $story['address'];
            if(empty($link) || isset($deleted_urls[$link])) {
                continue;
            }
            
            // parse the post url
            $parsedLink = parse_url($link);
            
            // google alerts support
            // check for google redirect urls and pick up the original post link
            $protocol = $parsedLink['scheme'];
            if (strpos($protocol . "://www.google.com/url", $url) == 0) {
                $query = $parsedLink['query'];
                $url_query = explode("&", $query);
                foreach ($url_query as $param) {
                    if (strpos($param, 'url=') === 0) {
                        $link = str_replace("url=", "", $param);
                        $link = urldecode($link);
                        $parsedLink = parse_url($link);
                        break;
                    }
                }
            }
            
            // check if this link is already in the feed
            if(isset($feed_array[$link])) {
                continue;
            }
            
                $domain = parse_url($link);
                if (preg_match('/(?P<subdomain>.<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedLink['host'], $regs)) {
                    $domain = $regs['domain'];
                } else {
                    $domain = $domain['host'];
                }
                
                // download and analyze
                $html_parser = new ExpressCurate_HtmlParser($link);
                $link = $html_parser->getRealURL();
                // check if the final page (initial or redirected address) is already in feed
                if(isset($feed_array[$link])) {
                    continue;
                }
                
                $keywords = $html_parser->analyzeKeywords();
                $media = $html_parser->containsMedia();
                
                $publishDate = isset($story['publishedDate']) ? $story['publishedDate'] : $story['date'];
                $expressCurateDate = new ExpressCurate_Date();
                $publishDate = $expressCurateDate->dateWithTimeUtc(strtotime($publishDate));
                
                $story_array = array(
                    'title' => $story['title'],
                    'desc' => isset($story['contentSnippet']) ? $story['contentSnippet'] : '',
                    'link' => $link,
                    'date' => $publishDate,
                    'domain' => $domain,
                    'author' => $story['author'],
                    'curated' => 0,
                    'keywords' => $keywords,
                    'media' => $media
                );

                if ($date) {
                    $starting_date = strtotime($date);
                    $story_date = strtotime($story_array['date']);
                    $diff = $story_date - $starting_date;
                }
                if (!$date || ($date && $diff >= 0)) {
                    if ($type = 'wall') {
                        $story_array['type'] = $story['channel'];
                    } else {
                        $story_array['type'] = 'feed';
                    }
                    $feed_array[$link] = $story_array;
                }
        }
    }

    public function manual_pull_feed() {
        // get feed data
        $feeds = json_decode($this->get_feed_content(), true);
        
        // reschedule the cronjob
        //if(!empty($feeds['content'])) {
        wp_clear_scheduled_hook('expresscurate_pull_feeds');
        $pull_feed_interval = (get_option('expresscurate_pull_hours_interval')) ? get_option('expresscurate_pull_hours_interval') : 1;
        wp_schedule_event(strtotime("+" . $pull_feed_interval . " hour"), 'hourly', 'expresscurate_pull_feeds');
        $feeds["minutes_to_next_pull"] = human_time_diff(wp_next_scheduled('expresscurate_pull_feeds'), time());
        //}
        
        // return
        echo json_encode($feeds);
        die;
    }

    public function show_content_feed_items()
    {
        include(sprintf("%s/templates/feed_list.php", dirname(__FILE__)));
        die;
    }
    
    public function filter_feeds_by_date() {
        $data = $_REQUEST;
        $feed_content = json_decode(get_option('expresscurate_feed_content', ''), true);
        $filtered_feeds = array();
        if(isset($data['date']) && !empty($feed_content['content'])){
            foreach($feed_content['content'] as $link => $feed){
                if((strtotime($feed['date']) - $data['date']) >= 0){
                    $filtered_feeds[$link] = $feed;
                }
            }
        }
        return $filtered_feeds;
    }

    public function send_content_alert()
    {

        if (get_option('expresscurate_enable_content_alert') == 'on') {
            $feed_content = json_decode(get_option('expresscurate_feed_content', ''), true);

            if ($this->date_diff(date('Y-m-d H:i:s'), get_option('expresscurate_content_alert_lastDate', true)) > get_option('expresscurate_content_alert_frequency', true)
                && isset($feed_content['content']) && $feed_content['content'] !== NULL
            ) {

                $emailStories = array();
                foreach ($feed_content['content'] as $link => $story) {
                    if (!empty($story['keywords'])) {
                        if ($this->date_diff(get_option('expresscurate_content_alert_lastDate', true), $story['date'], false) < 0) {
                            $emailStories[$link] = $story;
                        }
                    }
                }
                if (!empty($emailStories)) {
                    $expressCurateEmail = new ExpressCurate_Email();
                    $expressCurateEmail->sendContentAlertEmail($emailStories);

                }

            }
        }

    }

    public function delete_feed_content_items()
    {
        $data = $_REQUEST;
        if (isset($data['items'])) {
            $items = $data['items'];
            $result = array();
            $feed_contents = get_option('expresscurate_feed_content', '');
            $feed_content_deleted = get_option('expresscurate_feed_content_deleted', '');
            if ($feed_content_deleted) {
                $feed_content_deleted = json_decode($feed_content_deleted, true);
            }
            if ($feed_contents) {
                $feed_contents = json_decode($feed_contents, true);
                $exists_url = array();
                foreach ($feed_contents['content'] as $content) {
                    $exists_url[] = $content['link'];
                }
                foreach ($items as $item) {
                    if (in_array($item, $exists_url)) {
                        $feed_content_deleted[$item] = $item;
                        unset($feed_contents['content'][$item]);
                    }
                }
                $feed_contents = json_encode($feed_contents);
                $feed_content_deleted = json_encode($feed_content_deleted);
                update_option('expresscurate_feed_content', $feed_contents);
                update_option('expresscurate_feed_content_deleted', $feed_content_deleted);

                $result['status'] = 'success';
            } else {
                $result['status'] = 'warning';
            }
        } else {
            $result['status'] = 'error';
        }
        echo json_encode($result);
        die;
    }

    public function add_bookmarks()
    {
        $result = array();
        $data = $_REQUEST;
        if (isset($data['items'])) {
            $items = json_decode(stripslashes($data['items']), true);

            $bookmarks = get_option('expresscurate_bookmarks', '');
            if ($bookmarks) {
                $bookmarks = json_decode($bookmarks, true);
            } else {
                $bookmarks = array();
            }

            foreach ($items as $item) {
                $bookmarkURL = $item['link'];
                if (isset($bookmarks[$bookmarkURL])) {
                        $result[$bookmarkURL]['status'] = 'warning';
                }else {
                    $current_user = wp_get_current_user();
                    $item['user'] = $current_user->display_name;
                    
                    $this->collectBookmark($bookmarks, $item);
                    
                    $result[$bookmarkURL]['status'] = 'success';
                }
            }
            
            // save the updated bookmarks
            update_option('expresscurate_bookmarks', json_encode($bookmarks));
        } else {
            $result['status'] = 'error';
        }
        echo json_encode($result);
        die;
    }

    public function set_bookmark()
    {
        $data = $_REQUEST;
        $result = array();
        if(!ExpressCurate_HtmlParser::supportsDownload()) {
            $result['status'] = 'error';
            $result['msg'] = 'You should activate either curl extension or allow_url_fopen setting.';
        } else {
            // check if the url to bookmark is specified
            if (isset($data['url'])) {
                // get the url
                $bookmarkURL = $data['url'];
                
                // clean-up the provided url
                // parse the post url
                $parsedLink = parse_url($bookmarkURL);
            
                // google alerts supportbookmarkURL   // check for google redirect urls and pick up the original post link
                $protocol = $parsedLink['scheme'];
                if (strpos($protocol . "://www.google.com/url", $bookmarkURL) == 0) {
                    $query = $parsedLink['query'];
                    $url_query = explode("&", $query);
                    foreach ($url_query as $param) {
                        if (strpos($param, 'url=') === 0) {
                            $bookmarkURL = str_replace("url=", "", $param);
                            $bookmarkURL = urldecode($bookmarkURL);
                            break;
                        }
                    }
                }
                
                // read the existing bookmarks
                $bookmarks = get_option('expresscurate_bookmarks', '');
                if ($bookmarks) {
                    $bookmarks = json_decode($bookmarks, true);
                } else {
                    $bookmarks = array();
                }
                
                // check if the provided url is already bookmarked
                if (isset($bookmarks[$bookmarkURL])) {
                    // is a new comment provided?
                    if (isset($data['comment'])) {
                        // save the new comment
                        $bookmarks[$bookmarkURL]['comment'] = $data['comment'];
                        
                        // construct the result
                        $result['status'] = 'success';
                        $result['result'] = $bookmarkURL;
                        
                        // save the updated bookmarks
                        update_option('expresscurate_bookmarks', json_encode($bookmarks));
                    } else {
                        // construct the result
                        // nothing to do, and url is already bookmarked
                        $result['status'] = 'error';
                        $result['msg'] = 'This page is already bookmarked.';
                    }
                } else {
                    // the page is new, bookmark
                    // load the article
                    $contentManager = new ExpressCurate_ContentManager();
                    $article = $contentManager->getArticle($bookmarkURL, false);
                    
                    // check if the article is loaded successfully
                    if (isset($article['status']) && $article['status'] == 'success') {
                        // get the comment
                        $comment = isset($data['comment']) ? $data['comment'] : '';
                        
                        // set the type and author
                        $article['type'] = isset($data['type']) ? $data['type'] : 'user';
                        $current_user = wp_get_current_user();
                        $article['result']['user'] = $current_user->display_name;
                        
                        // ...
                        $this->collectBookmark($bookmarks, $article, $bookmarkURL, $comment);
                        
                        // construct the result
                        $result['status'] = 'success';
                        $result['result'] = $bookmarks[$bookmarkURL];
                        $result['result']['curateLink'] = base64_encode(urlencode($bookmarkURL));
                        
                        // save the updated bookmarks
                        update_option('expresscurate_bookmarks', json_encode($bookmarks));
                    } else {
                        if (isset($article['status'])) {
                            $result['status'] = $article['status'];
                            $result['msg'] = $article['msg'];
                        } else {
                            $result['status'] = 'error';
                            $result['msg'] = 'Article does not exists.';
                        }
                    }
                }
            } else {
                $result['status'] = 'error';
                $result['msg'] = 'Please, provide URL to bookmark.';
            }
        }
        echo json_encode($result);
        die;
    }
    
    private function collectBookmark(&$bookmarks, $item, $url = null, $comment = '') {
        if ($url) {
            if (isset($item['result']) && isset($item['result']['title'])) {
                // create the new bookmark
                $bookmark = array();
                $bookmark['link'] = $url;
                $bookmark['domain'] = $item['result']['domain'];
                $bookmark['title'] = $item['result']['title'];
                $bookmark['author'] = $item['result']['author'];
                $bookmark['user'] = $item['result']['user'];
                $bookmark['date'] = $item['result']['date'];
                $bookmark['bookmark_date'] = date("Y-m-d h:i:s");
                $bookmark['keywords'] = array();
                $bookmark['media'] = $item['result']['media'];
                $bookmark['comment'] = $comment;
                $bookmark['curated'] = 0;
                
                $bookmark['type'] = isset($item['result']['type']) ? $item['type'] : 'user';
                
                // add the bookmark to the list
                $bookmarks[$url] = $bookmark;
            }
        } else {
            if ($item['title']) {
                if (!isset($item['keywords'])) {
                    $item['keywords'] = array();
                }
                $item['comment'] = $comment;
                $item['bookmark_date'] = date('Y-m-d H:i:s');
                
                $bookmarks[$item['link']] = $item;
            }
        }
    }

    public function get_bookmark()
    {
        $data = $_REQUEST;
        $result = array();
        $bookmark = '';
        if (isset($data['url'])) {
            $bookmarks = get_option('expresscurate_bookmarks', '');
            if ($bookmarks) {
                $bookmarks = json_decode(stripslashes($bookmarks), true);
            } else {
                $bookmarks = array();
            }
            if (isset($bookmarks[$data['url']])) {
                $bookmark = $bookmarks[$data['url']];
                if ($bookmark) {
                    $result['status'] = 'success';
                    $result['data'] = $bookmark;
                } else {
                    $result['status'] = 'warning';
                    $result['msg'] = 'Bookmark not found';
                }
            }
        } else {
            $result['status'] = 'error';
            $result['msg'] = 'Url is empty';
        }
        echo json_encode($result,JSON_UNESCAPED_SLASHES);
        die;
    }

    public function delete_bookmarks()
    {
        $data = $_REQUEST;
        $result = array();
        if (isset($data['items'])) {
            $items = json_decode(stripslashes($data['items']), true);
            $bookmarks = get_option('expresscurate_bookmarks', '');
            if ($bookmarks) {
                $bookmarks = json_decode($bookmarks, true);
                $exists_url = array();
                foreach ($bookmarks as $bookmark) {
                    $exists_url[] = $bookmark['link'];
                }
                foreach ($items as $item) {
                    if (in_array($item['link'], $exists_url)) {
                        unset($bookmarks[$item['link']]);
                    }
                }
                $bookmarks = json_encode($bookmarks);
                update_option('expresscurate_bookmarks', $bookmarks);
                $result['status'] = 'success';
            } else {
                $result['status'] = 'warning';
            }
        } else {
            $result['status'] = 'error';
        }
        echo json_encode($result);
        die;
    }

    public function get_bookmarks()
    {
        $bookmarks = get_option('expresscurate_bookmarks', '');
        if ($bookmarks) {
            $bookmarks = json_decode($bookmarks, true);
        } else {
            $bookmarks = array();
        }
        return $bookmarks;
    }

    public function search_feed_bookmark()
    {
        $needle = !empty($_GET['searchKeyword']) ? $_GET['searchKeyword'] : ' ';
        $resulArray = array();
        $hayStack = array();

        if (!empty($_GET['contentType'])) {
            if ($_GET['contentType'] == 'feed') {
                $hayStack = json_decode(get_option('expresscurate_feed_content', ''), true);
            } else if ($_GET['contentType'] == 'bookmark') {
                $hayStack = json_decode(get_option('expresscurate_bookmarks', ''), true);
            }
        } else {
            $bookmarks = json_decode(get_option('expresscurate_bookmarks', ''), true);
            $feedContent = json_decode(get_option('expresscurate_feed_content', ''), true);
            if (!empty($feedContent)) {
                $feeds = $feedContent['content'];
            } else {
                $feeds = array();
            }
            $hayStack = array_merge($bookmarks, $feeds);
        }

        foreach ($hayStack as $content) {
            if (mb_stripos($content['title'], $needle) != false) {
                $resulArray[] = $content;
            }
        }

        echo json_encode($resulArray);
        die;
    }

    public function count_bookmarks_by_days($bookmarks, $days_count)
    {
        $count = 0;
        if (!empty($bookmarks) && $days_count > 0) {
            $start = strtotime(date('Y-m-d') . "-" . $days_count . " days");
            $end = strtotime(date('Y-m-d'));
            foreach ($bookmarks as $key => $bookmark) {
                $date_array = date_parse($bookmark['bookmark_date']);
                $bookmark_date = strtotime(date('Y-m-d', mktime(0,0,0,$date_array['month'], $date_array['day'], $date_array['year'])));
                if ($bookmark_date >= $start && $bookmark_date <= $end) {
                    $count++;
                }
            }
        }
        return $count;
    }

    private function sort_by_count($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        return ($a > $b) ? -1 : 1;
    }

    public function date_diff($date1, $date2, $byHours = true)
    {
        if ($byHours) {
            $hours = round(strtotime($date1) - strtotime($date2)) / (60 * 60);
        } else {
            $hours = strtotime($date1) - strtotime($date2);
        }

        return $hours;
    }

    public function add_post_source()
    {
        $data = $_REQUEST;
        $result = array();
        if (isset($data['post_id']) && isset($data['url'])) {
            $items = get_post_meta($data['post_id'], '_expresscurate_curated_data', true);
            $exists = array();
            if ($items) {
                foreach ($items as $key => $item) {
                    $exists[] = $item['link'];
                }
            }
            if (in_array($data['url'], $exists)) {
                $result['status'] = 'warning';
            } else {
                $contentManager = new ExpressCurate_ContentManager();
                $article = $contentManager->getArticle($data['url'], false);
                $result['status'] = $article['status'];
                if ($article['status'] == 'success') {
                    $result['result'] = array('title' => $article['result']['title'], 'link' => $data['url'], 'domain' => $article['result']['domain']);
                    $items[] = $result['result'];
                    update_post_meta($data['post_id'], '_expresscurate_curated_data', $items);
                }
            }
        } else {
            $result['status'] = 'error';
        }
        echo json_encode($result);
        die;
    }

    public function delete_post_source()
    {
        $data = $_REQUEST;
        $result = array();
        if (isset($data['post_id']) && isset($data['item'])) {
            $item_for_delete = json_decode(stripslashes($data['item']), true);
            $items = get_post_meta($data['post_id'], '_expresscurate_curated_data', true);
            $exists = array();
            if ($items) {
                foreach ($items as $key => $item) {
                    if (in_array($item_for_delete['link'], $item)) {
                        unset($items[$key]);
                        break;
                    }
                }
            }
            update_post_meta($data['post_id'], '_expresscurate_curated_data', $items);
            $result['status'] = 'success';
        } else {
            $result['status'] = 'error';
        }
        echo json_encode($result);
        die;
    }

    public function feedSortByDate($a, $b)
    {
        return (strtotime($b['date']) - strtotime($a['date']));
    }

}

?>
