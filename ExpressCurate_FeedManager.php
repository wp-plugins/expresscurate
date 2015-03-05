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

            $url = expresscurate_normalise_url($url, true);
            $rssUrl = isset($rssUrl) ? $rssUrl : $this->getRssUrl($url);

            if (!isset($curated_links_rss[$rssUrl])) {
                if (filter_var($rssUrl, FILTER_VALIDATE_URL)) {
                    $result['status'] = 'success';
                    $metas = $wpdb->get_results(
                        "SELECT post_id
                   FROM $wpdb->postmeta
                   WHERE meta_key LIKE  '%_expresscurate_link_%' AND meta_value LIKE '%" . $url . "%' GROUP BY post_id");


                    $curated_links_rss[$rssUrl]['feed_url'] = $result['feed_url'] = $rssUrl;
                    $curated_links_rss[$rssUrl]['post_count'] = $result['post_count'] = count($metas);
                    //  var_dump($curated_links_rss);die;
                    $curated_links_rss = json_encode($curated_links_rss);
                    update_option('expresscurate_links_rss', $curated_links_rss);
                } elseif ($rssUrl === null) {
                    $result['status'] = 'No RSS feed found at this URL.';
                } else {
                    $result['status'] = 'Invalid RSS URL.';
                }
            } else {
                $result['status'] = 'URL already exists.';
            }
        } else {
            $result['status'] = 'Something went wrong. Please check the URL. If the problem persists, please contact us.';
        }
        echo json_encode($result);
        die;
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
                $curated_links_rss = json_encode($curated_links_rss);
                update_option('expresscurate_links_rss', $curated_links_rss);
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
        } else {
            $curated_links_rss = array();
        }
        $top_sources_rss = get_option('expresscurate_top_sources_rss', '');

        if ($top_sources_rss) {
            $top_sources_rss = json_decode($top_sources_rss, true);
            $date_after = strtotime($top_sources_rss['date']) . "&";
        } else {
            $top_sources_rss = array();
            $top_sources_rss['links'] = array();
        }

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
                            $normalised_url = expresscurate_normalise_url($meta_values[$key][0]);
                            $domain = parse_url($normalised_url);
                            if (preg_match('/(?P<subdomain>.<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain['host'], $regs)) {
                                $curated_links[$i]['link'] = expresscurate_normalise_url($regs['domain']);
                            } else {
                                $curated_links[$i]['link'] = $normalised_url;
                            }
                            $curated_links[$i]['post_id'] = get_the_ID();
                        }
                        $i++;
                    }
                }
            }
        }
        wp_reset_postdata();

        foreach ($curated_links as $key => $top_link) {
            $websiteUrl = $top_link['link'];
            $rssUrl = $this->getRssUrl($websiteUrl);
            if ($rssUrl && isset($curated_links_rss[$rssUrl])) {
                $feed_status = 'rssStatusYes';
            } else {
                if ($rssUrl) {
                    $feed_status = 'rssStatusAdd';
                } else {
                    $feed_status = 'rssStatusNo';
                }
            }
            $top_sources_rss['links'][$websiteUrl] = array(
                'post_ids' => array($top_link['post_id']),
                'feed_options' => array(
                    'feed_url' => $rssUrl,
                    'feed_status' => $feed_status,
                    'type' => 'feed')
            );
            $top_sources_rss['links'][$websiteUrl]['post_ids'] = array_unique($top_sources_rss['links'][$websiteUrl]['post_ids']);
            $top_sources_rss['links'][$websiteUrl]['post_count'] = count($top_sources_rss['links'][$websiteUrl]['post_ids']);
        }

        @uasort($top_sources_rss['links'], array($this, "sort_by_count"));
        $top_sources_rss['date'] = date('Y-m-d H:i:s');
        $top_sources_rss_save = json_encode($top_sources_rss);
        update_option('expresscurate_top_sources_rss', $top_sources_rss_save);
        return $top_sources_rss;
    }



//
//  public function get_curated_links2() {
//    $curated_links = array();
//    $date_after = '';
//    $curated_links_rss = get_option('expresscurate_links_rss', '');
//    if ($curated_links_rss) {
//      $curated_links_rss = json_decode($curated_links_rss, true);
//    } else {
//      $curated_links_rss = array();
//    }
//    $top_sources_rss = get_option('expresscurate_top_sources_rss', '');
//
//    if ($top_sources_rss) {
//      $top_sources_rss = json_decode($top_sources_rss, true);
//      $date_after = strtotime($top_sources_rss['date']) . "&";
//    } else {
//      $top_sources_rss = array();
//      $top_sources_rss['links'] = array();
//    }
//
//    $curated_posts_query = new WP_Query("meta_key=_is_expresscurate&meta_value=1&posts_per_page=-1&" . $date_after . "order=DESC");
//
//    if ($curated_posts_query->have_posts()) {
//      $i = 0;
//      while ($curated_posts_query->have_posts()) {
//        $curated_posts_query->the_post();
//        $meta_values = get_post_meta(get_the_ID());
//        // var_dump($meta_values);
//        foreach ($meta_values as $key => $value) {
//          if (preg_match('/_expresscurate_link_\d/', $key)) {
//            if ($meta_values[$key][0]) {
//              $normalised_url = expresscurate_normalise_url($meta_values[$key][0]);
//              $domain = parse_url($normalised_url);
//              if (preg_match('/(?P<subdomain>.<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain['host'], $regs)) {
//                $curated_links[$i]['link'] = expresscurate_normalise_url($regs['domain']);
//              } else {
//                $curated_links[$i]['link'] = $normalised_url;
//              }
//              $curated_links[$i]['post_id'] = get_the_ID();
//            }
//            $i++;
//          }
//        }
//      }
//    }
//    wp_reset_postdata();
//
//    foreach ($curated_links as $key => $top_link) {
//     $rssUrl = $this->getRssUrl($top_link['link']);
//       if($rssUrl){
//           $top_link['link'] = $rssUrl;
//       }
//      $feed_url = false;
//      if (isset($top_sources_rss['links'][$top_link['link']])) {
//        $top_sources_rss['links'][$top_link['link']]['post_ids'][] = $top_link['post_id'];
//        if (isset($curated_links_rss[$top_link['link']])) {
//          $feed_status = 'rssStatusYes';
//          $feed_url = $curated_links_rss[$top_link['link']]['feed_url'];
//        } else {
//          if (isset($top_sources_rss['links'][$top_link['link']]['feed_options']) && isset($top_sources_rss['links'][$top_link['link']]['feed_options']['checked']) && $top_sources_rss['links'][$top_link['link']]['feed_options']['checked'] == 1) {
//            $feed_status = $top_sources_rss['links'][$top_link['link']]['feed_options']['feed_status'];
//          } else {
//            $feed_url = $this->getRssUrl($top_link['link']);
//            if ($feed_url) {
//              //if (1 == 1) {
//              $feed_status = 'rssStatusAdd';
//            } else {
//              $feed_status = 'rssStatusNo';
//            }
//            $checked = 1;
//          }
//        }
//        $checked = 1;
//        $top_sources_rss['links'][$top_link['link']]['feed_options'] = array('feed_url' => $feed_url, 'feed_status' => $feed_status, 'checked' => 1, 'type' => 'feed');
//      } else {
//        if (isset($top_sources_rss['links'][$top_link['link']]['feed_options']) && isset($top_sources_rss['links'][$top_link['link']]['feed_options']['checked']) && $top_sources_rss['links'][$top_link['link']]['feed_options']['checked'] == 1) {
//          $feed_status = $top_sources_rss['links'][$top_link['link']]['feed_options']['feed_status'];
//        } else {
//          if ($feed_url = $this->getRssUrl($top_link['link'])) {
//            //if (1 == 1) {
//            $feed_status = 'rssStatusAdd';
//          } else {
//            $feed_status = 'rssStatusNo';
//          }
//          $checked = 1;
//        }
//        $top_sources_rss['links'][$top_link['link']] = array('post_ids' => array($top_link['post_id']), 'feed_options' => array('feed_url' => $feed_url, 'feed_status' => $feed_status, 'checked' => $checked, 'type' => 'feed'));
//      }
//      $top_sources_rss['links'][$top_link['link']]['post_ids'] = array_unique($top_sources_rss['links'][$top_link['link']]['post_ids']);
//      $top_sources_rss['links'][$top_link['link']]['post_count'] = count($top_sources_rss['links'][$top_link['link']]['post_ids']);
//    }
//
//    @uasort($top_sources_rss['links'], array($this, "sort_by_count"));
//    $top_sources_rss['date'] = date('Y-m-d H:i:s');
//    $top_sources_rss_save = json_encode($top_sources_rss);
//    update_option('expresscurate_top_sources_rss', $top_sources_rss_save);
//    return $top_sources_rss;
//  }

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
        $curated_links_rss = get_option('expresscurate_links_rss', '');
        if ($curated_links_rss) {
            $curated_links_rss = json_decode($curated_links_rss, true);
        } else {
            $curated_links_rss = array();
        }
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
            $result = file_get_contents($lookup_url);

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
        $result = json_decode(file_get_contents($lookup_url));

        if ($result && $result->responseData) {
            return $result->responseData->url;
        } elseif ($result && $result->responseData === null) {
            return null;
        }
        return false;
    }

    public function get_feed_content()
    {
        $feed_array = array();
        $data = $_REQUEST;
        $curated_links_rss = get_option('expresscurate_links_rss', '');
        if ($curated_links_rss) {
            $curated_links_rss = json_decode($curated_links_rss, true);
            $deleted_urls = array();
            if (!isset($data['date'])) {
                $feed_content_deleted = get_option('expresscurate_feed_content_deleted', '');
                if ($feed_content_deleted) {
                    $feed_content_deleted = json_decode($feed_content_deleted, true);
                }
                if (is_array($feed_content_deleted)) {
                    foreach ($feed_content_deleted as $deletet_item) {
                        $deleted_urls[] = $deletet_item['link'];
                    }
                }
            }
            if (count($curated_links_rss)) {
                foreach ($curated_links_rss as $url => $feed_url) {
                    $lookup_url = "http://ajax.googleapis.com/ajax/services/feed/load?v=1.0&q=" . urlencode($feed_url['feed_url']);
                    $result = json_decode(file_get_contents($lookup_url), true);
                    $this->collect_feed($result, $deleted_urls, $feed_array, 'feed', $data['date']);
                }
            }

            @uasort($feed_array, array($this, "feedSortByDate"));

            $feed_content = json_encode(array('date' => date('Y-m-d H:i:s'), 'content' => $feed_array));
            update_option('expresscurate_feed_content', $feed_content);

        }

        return $feed_array;
    }


    public function filter_feeds_by_date(){
        $data = $_REQUEST;
        $feed_content = json_decode(get_option('expresscurate_feed_content', ''), true);
        $filtered_feeds = array();
        if(isset($data['date']) && !empty($feed_content['content'])){
            foreach($feed_content['content'] as $link => $feed){
                if($this->date_diff($feed['date'],$data['date'], false) >= 0){
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
                    if (isset($story['keywords'])) {
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

    public function collect_feed($result, $deleted_urls, &$feed_array, $type = 'feed', $date = false)
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
            if ((isset($story['link']) && !in_array($story['link'], $deleted_urls)) || (isset($story['address']) && !in_array($story['address'], $deleted_urls))) {
                $link = isset($story['link']) ? $story['link'] : $story['address'];
                $domain = parse_url($link);
                if (preg_match('/(?P<subdomain>.<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain['host'], $regs)) {
                    $domain = $regs['domain'];
                } else {
                    $domain = $domain['host'];
                }
                $html_parser = new ExpressCurate_HtmlParser($link);
                $keywords = $html_parser->analyzeKeywords();

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
    }

    public function delete_feed_content_items()
    {
        $data = $_REQUEST;
        if (isset($data['items'])) {
            $items = json_decode(stripslashes($data['items']), true);
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
                    $item = json_decode($item, true);
                    if (in_array($item['link'], $exists_url)) {
                        $feed_content_deleted[$item['link']] = $item;
                        unset($feed_contents['content'][$item['link']]);
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
            $exists_url = array();
            foreach ($bookmarks as $bookmark) {
                $exists_url[] = $bookmark['link'];
            }
            foreach ($items as $item) {

                if (!in_array($item['link'], $exists_url)) {
                    $current_user = wp_get_current_user();
                    $item['user'] = $current_user->display_name;
                    $this->collect_bookmark($bookmarks, $item);
                    $result[$item['link']]['status'] = 'success';
                } else {
                    if ($bookmark['comment']) {
                        $bookmarks[$item['link']]['comment'] = $bookmark['comment'];
                        $result[$item['link']]['status'] = 'success';
                    } else {
                        $result[$item['link']]['status'] = 'warning';
                    }
                }
            }
            $bookmarks = json_encode($bookmarks);
            update_option('expresscurate_bookmarks', $bookmarks);
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
        if (isset($data['url'])) {
            $bookmarks = get_option('expresscurate_bookmarks', '');
            if ($bookmarks) {
                $bookmarks = json_decode($bookmarks, true);
            } else {
                $bookmarks = array();
            }
            $exists_url = array();
            foreach ($bookmarks as $bookmark) {
                $exists_url[] = $bookmark['link'];
            }

            $data['url'] = expresscurate_normalise_url($data['url'], true);
            if (!in_array($data['url'], $exists_url)) {

                $contentManager = new ExpressCurate_ContentManager();
                $article = $contentManager->get_article($data['url'], false);
                if (isset($article['status']) && $article['status'] == 'success') {
                    $comment = isset($data['comment']) ? $data['comment'] : '';
                    $article['type'] = isset($data['type']) ? $data['type'] : 'user';
                    $current_user = wp_get_current_user();
                    $article['result']['user'] = $current_user->display_name;
                    $this->collect_bookmark($bookmarks, $article, $data['url'], $comment);
                    $result['status'] = 'success';
                    $result['result'] = $bookmarks[$data['url']];
                    $result['result']['curateLink']=base64_encode(urlencode($data['url']));
                    $bookmarks = json_encode($bookmarks);
                    update_option('expresscurate_bookmarks', $bookmarks);
                } else {
                    if (isset($article['status'])) {
                        $result['status'] = $article['status'];
                        $result['msg'] = $article['msg'];
                    } else {
                        $result['status'] = 'error';
                        $result['msg'] = 'Article does not exists.';
                    }

                }
            } else {
                if (isset($data['comment'])) {
                    $bookmarks[$data['url']]['comment'] = $data['comment'];
                    $result['result'] = $bookmarks[$data['url']];
                    $bookmarks = json_encode($bookmarks);
                    update_option('expresscurate_bookmarks', $bookmarks);
                    $result['status'] = 'success';
                } else {
                    $result['status'] = 'error';
                    $result['msg'] = 'This page is already bookmarked.';
                }
            }
        } else {
            $result['status'] = 'error';
        }

        echo json_encode($result);
        die;
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
        echo json_encode($result);
        die;
    }

    private function collect_bookmark(&$bookmarks, $item, $url = null, $comment = '')
    {
        if ($url) {
            if (isset($item['result']) && isset($item['result']['title'])) {
                $url = expresscurate_normalise_url($url, true);
                $bookmark = array();
                $bookmark['link'] = $url;
                $bookmark['domain'] = $item['result']['domain'];
                $bookmark['title'] = $item['result']['title'];
                $bookmark['author'] = $item['result']['author'];
                $bookmark['user'] = $item['result']['user'];
                $bookmark['date'] = $item['result']['date'];
                $bookmark['bookmark_date'] = date("Y-m-d h:i:s");
                $bookmark['keywords'] = array();
                $bookmark['type'] = isset($item['result']['type']) ? $item['type'] : 'user';
                $bookmark['comment'] = $comment;
                $bookmark['curated'] = 0;
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
        if ($bookmarks && $days_count > 0) {
            $start = strtotime(date('Y-m-d') . "-" . $days_count . " days");
            $end = strtotime(date('Y-m-d'));
            foreach ($bookmarks as $key => $bookmark) {
                $date_array = date_parse($bookmark['bookmark_date']);
                $bookmark_date = strtotime(date('Y-m-d', mktime($date_array['month'], $date_array['day'], $date_array['year'])));
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
                $article = $contentManager->get_article($data['url'], false);
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
