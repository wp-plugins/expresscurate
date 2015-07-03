<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_Keywords
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

    public function getKeywords()
    {
        $definedKeyWords = get_option('expresscurate_defined_tags', true);
        if (empty($definedKeyWords)) {
            return false;
        } else {
            $definedKeywordsArray = explode(',', mb_strtolower($definedKeyWords, "UTF-8"));
            return $definedKeywordsArray;
        }
    }

    public function get_words($args = false, $new_post = false)
    {
        $stop_words = array('a', 'able', 'about', 'above', 'abroad', 'according', 'accordingly', 'across', 'actually', 'adj', 'after', 'afterwards', 'again', 'against', 'ago', 'ahead', 'ain\'t', 'all', 'allow', 'allows', 'almost', 'alone', 'along', 'alongside', 'already', 'also', 'although', 'always', 'am', 'amid', 'amidst', 'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere', 'apart', 'appear', 'appreciate', 'appropriate', 'are', 'aren\'t', 'around', 'as', 'a\'s', 'aside', 'ask', 'asking', 'associated', 'at', 'available', 'away', 'awfully', 'b', 'back', 'backward', 'backwards', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'begin', 'behind', 'being', 'believe', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c', 'came', 'can', 'cannot', 'cant', 'can\'t', 'caption', 'cause', 'causes', 'certain', 'certainly', 'changes', 'clearly', 'c\'mon', 'co', 'co.', 'com', 'come', 'comes', 'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding', 'could', 'couldn\'t', 'course', 'c\'s', 'currently', 'd', 'dare', 'daren\'t', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'different', 'directly', 'do', 'does', 'doesn\'t', 'doing', 'done', 'don\'t', 'down', 'downwards', 'during', 'e', 'each', 'edu', 'eg', 'eight', 'eighty', 'either', 'else', 'elsewhere', 'end', 'ending', 'enough', 'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'evermore', 'every', 'everybody', 'everyone', 'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'f', 'fairly', 'far', 'farther', 'few', 'fewer', 'fifth', 'first', 'five', 'followed', 'following', 'follows', 'for', 'forever', 'former', 'formerly', 'forth', 'forward', 'found', 'four', 'from', 'further', 'furthermore', 'g', 'get', 'gets', 'getting', 'given', 'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'h', 'had', 'hadn\'t', 'half', 'happens', 'hardly', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d', 'he\'ll', 'hello', 'help', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'here\'s', 'hereupon', 'hers', 'herself', 'he\'s', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully', 'how', 'howbeit', 'however', 'hundred', 'i', 'i\'d', 'ie', 'if', 'ignored', 'i\'ll', 'i\'m', 'immediate', 'in', 'inasmuch', 'inc', 'inc.', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'inside', 'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t', 'it', 'it\'d', 'it\'ll', 'its', 'it\'s', 'itself', 'i\'ve', 'j', 'just', 'k', 'keep', 'keeps', 'kept', 'know', 'known', 'knows', 'l', 'last', 'lately', 'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'like', 'liked', 'likely', 'likewise', 'little', 'look', 'looking', 'looks', 'low', 'lower', 'ltd', 'm', 'made', 'mainly', 'make', 'makes', 'many', 'may', 'maybe', 'mayn\'t', 'me', 'mean', 'meantime', 'meanwhile', 'merely', 'might', 'mightn\'t', 'mine', 'minus', 'miss', 'more', 'moreover', 'most', 'mostly', 'mr', 'mrs', 'much', 'must', 'mustn\'t', 'my', 'myself', 'n', 'name', 'namely', 'nd', 'near', 'nearly', 'necessary', 'need', 'needn\'t', 'needs', 'neither', 'never', 'neverf', 'neverless', 'nevertheless', 'new', 'next', 'nine', 'ninety', 'no', 'nobody', 'non', 'none', 'nonetheless', 'noone', 'no-one', 'nor', 'normally', 'not', 'nothing', 'notwithstanding', 'novel', 'now', 'nowhere', 'o', 'obviously', 'of', 'off', 'often', 'oh', 'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'one\'s', 'only', 'onto', 'opposite', 'or', 'other', 'others', 'otherwise', 'ought', 'oughtn\'t', 'our', 'ours', 'ourselves', 'out', 'outside', 'over', 'overall', 'own', 'p', 'particular', 'particularly', 'past', 'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'presumably', 'probably', 'provided', 'provides', 'q', 'que', 'quite', 'qv', 'r', 'rather', 'rd', 're', 'really', 'reasonably', 'recent', 'recently', 'regarding', 'regardless', 'regards', 'relatively', 'respectively', 'right', 'round', 's', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly', 'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious', 'seriously', 'seven', 'several', 'shall', 'shan\'t', 'she', 'she\'d', 'she\'ll', 'she\'s', 'should', 'shouldn\'t', 'since', 'six', 'so', 'some', 'somebody', 'someday', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry', 'specified', 'specify', 'specifying', 'still', 'sub', 'such', 'sup', 'sure', 't', 'take', 'taken', 'taking', 'tell', 'tends', 'th', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'ll', 'thats', 'that\'s', 'that\'ve', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'there\'d', 'therefore', 'therein', 'there\'ll', 'there\'re', 'theres', 'there\'s', 'thereupon', 'there\'ve', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'thing', 'things', 'think', 'third', 'thirty', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'till', 'to', 'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying', 't\'s', 'twice', 'two', 'u', 'un', 'under', 'underneath', 'undoing', 'unfortunately', 'unless', 'unlike', 'unlikely', 'until', 'unto', 'up', 'upon', 'upwards', 'us', 'use', 'used', 'useful', 'uses', 'using', 'usually', 'v', 'value', 'various', 'versus', 'very', 'via', 'viz', 'vs', 'w', 'want', 'wants', 'was', 'wasn\'t', 'way', 'we', 'we\'d', 'welcome', 'well', 'we\'ll', 'went', 'were', 'we\'re', 'weren\'t', 'we\'ve', 'what', 'whatever', 'what\'ll', 'what\'s', 'what\'ve', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'where\'s', 'whereupon', 'wherever', 'whether', 'which', 'whichever', 'while', 'whilst', 'whither', 'who', 'who\'d', 'whoever', 'whole', 'who\'ll', 'whom', 'whomever', 'who\'s', 'whose', 'why', 'will', 'willing', 'wish', 'with', 'within', 'without', 'wonder', 'won\'t', 'would', 'wouldn\'t', 'x', 'y', 'yes', 'yet', 'you', 'you\'d', 'you\'ll', 'your', 'you\'re', 'yours', 'yourself', 'yourselves', 'you\'ve', 'z', 'zero');
        $post_text = '';
        $post_arr = array();
        $post_titles = '';
        if ($args === false && $new_post === false) {
            $posts = array();

            /* $publishedPostCount = wp_count_posts()->publish;
             for ($i = 0; $i < ceil($publishedPostCount / 200); $i++) {
                 $args = array('status' => 'published', 'numberposts' => 200, 'offset' => $i * 200);
                 $posts = array_merge($posts, get_posts($args)) ;
             }*/

            $posts = $this->post_handling_by_portion('posts');
        }
        if (isset($args['id'])) {
            $post = get_post($args['id']);
            $post_text .= $post->post_content;
            $post_titles .= $post->post_title;
        } else {
            if ($new_post === false) {
                $posts = get_posts($args);
                for ($i = 0; $i < count($posts); $i++) {
                    $post_text .= $posts[$i]->post_content;
                    $post_titles .= $posts[$i]->post_title;
                    $post_arr[$i] = $posts[$i]->post_content;
                }
            } else {
                $post_text = $new_post['content'];
                $post_titles = $new_post['title'];
            }
        }
        $post_text = strip_tags($post_text);
        $post_text = preg_replace('/&(amp;)?#?[a-z0-9]+;/', '-', $post_text);
        $post_text = preg_replace('/\b(' . implode('|', $stop_words) . ')\b/iu', '', $post_text);
        $post_text = preg_replace('/\b[^\s]{1,2}\b/iu', '', $post_text);
        $post_text = preg_replace("/[\",.':;\\-\\=\\+\\)\\?\\!\\&\\(\\}\\{\\[\\]\\@]/u", "", $post_text);
        $post_words = array_count_values(preg_split('~[^\p{L}\p{N}\']+~u', $post_text));
        unset($post_words['']);
        $post_words = $this->array_map_keys('mb_strtolower', $post_words, 'UTF-8');
        $post_words = array_filter($post_words);

//      unset($post_words[null]);
//      unset($post_words[' ']);
//      unset($post_words['\'']);
        /*      unset($post_words['-']);
              unset($post_words['nbsp']);*/
        arsort($post_words);

        $total = array_sum($post_words);
        $result = array('titles' => $post_titles, 'content' => $post_text, 'posts' => $post_arr, 'words' => $post_words, 'total' => $total, 'stopwords' => $stop_words);
        return $result;
    }

    public function add_keyword($keyword, $get_stats = false)
    {

        $defined_tags_arr = array();
        $keywords = array();
        if (isset($_REQUEST['keywords'])) {
            $keywords = explode(",", $_REQUEST['keywords']);
        }
        $get_stats = $_REQUEST['get_stats'];
        $defined_tags = get_option("expresscurate_defined_tags", '');
        if ($defined_tags) {
            $defined_tags_arr = $this->array_map('trim', explode(",", $defined_tags));
            unset($defined_tags_arr['']);
        }
        if (count($keywords) > 0) {

            foreach ($keywords as $key => $keyword) {
                $keyword = str_replace('"', '', stripslashes(trim($keyword)));
                $keyword = str_replace("\\\"", '', $keyword);
                $keyword = str_replace("\\'", '', $keyword);
                $keyword = str_replace("\\\\", '', $keyword);
                if (strlen($keyword) > 2) {
                    if (preg_grep("/\b" . $keyword . "\b\w+/iu", $defined_tags_arr)) {
                        $result = array('status' => "warning", 'msg' => __($keyword . ' is already defined'));
                    } else {
                        if ($defined_tags) {

                            $defined_tags .= ", " . $keyword;
                        } else {
                            $defined_tags = $keyword;
                        }
                        $defined_tags = str_replace(', ,', ',', $defined_tags);
                        update_option('expresscurate_defined_tags', $defined_tags);
                    }
                } else {
                    unset($keywords[$key]);
                }
            }
            if ($get_stats == true) {
                $stats = $this->getKeywordStats();
                $result = array('status' => "success", 'stats' => $stats);
            } else {
                $result = array('status' => "success");
            }
        } else {
            $result = array('status' => "warning", 'msg' => __('Calculation Error. Please try refreshing this web page.  If the problem persists, contact us'));
        }
        echo json_encode($result);
        die();
    }

    public function get_post_keyword_stats($keyword, $post_id = false)
    {
        $args = false;

        if ($_REQUEST) {
            $keyword = $_REQUEST['keyword'];
            if (strpos($keyword, ",")) {
                $keywords = explode(',', $keyword);
            }
            $post_id = (isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : false);
        }
        if ($post_id) {
            $args = array('id' => $post_id);
        }
        if (count(@$keywords) > 0) {
            $stats = $this->get_stats($keywords, $args, false, false);
        } else {
            $stats = $this->get_stats(array($keyword), $args, false, false);
        }
        $result = array('status' => "success", 'stats' => $stats);
        echo json_encode($result);
        die();
    }

    public function delete_keyword()
    {
        if ($_REQUEST) {
            $keyword = trim($_REQUEST['keyword']);

            $defined_tags = get_option("expresscurate_defined_tags", '');
            if ($defined_tags) {
                $defined_tags_arr = $this->array_map('trim', explode(",", $defined_tags));
            }
            if (($key = array_search($keyword, $defined_tags_arr)) !== false) {
                unset($defined_tags_arr[$key]);
            }
            $filtered_tags = implode(', ', $defined_tags_arr);
            update_option('expresscurate_defined_tags', $filtered_tags);
            $result = array('status' => "success");
        } else {
            $result = array('status' => "error", 'msg' => "Calculation Error. Please try refreshing this web page.  If the problem persists, contact us");
        }
        echo json_encode($result);
        die();
    }

    public function get_stats($keywords = array(), $args = false, $post_content = false, $get_posts_count = false)
    {
        if (!$post_content) {
            $post_content = $this->get_words($args);
        }
        if (!$keywords) {
            $keywords = get_option('expresscurate_defined_tags', '');
            if ($keywords) {
                $keywords = explode(', ', $keywords);
            } else {
                $keywords = array();
            }
        }
        $keyword_in = array();
        if (count($keywords) > 0) {
            foreach ($keywords as $keyword) {
                $keyword = str_replace('"', '', stripslashes(trim($keyword)));
                $keyword = str_replace("\\\"", '', $keyword);
                $keyword = str_replace("\\'", '', $keyword);
                $keyword = str_replace("\\\\", '', $keyword);
                $keyword_in[$keyword]['added_count'] = 0;
                $keywordReg=preg_quote($keyword,'/');
                preg_replace('/\b' . $keywordReg . '\b/iu', '', $post_content['content'], -1, $keyword_in[$keyword]['count']);
                //str_ireplace(" ".$keyword." ", '', $post_content['content'], $keyword_in[$keyword]['count']);
                $keyword_in[$keyword]['title'] = 0;
                if ($post_content['total'] !== 0) {
                    $keyword_in[$keyword]['percent'] = round(($keyword_in[$keyword]['count'] / $post_content['total']) * 100, 2);
                    $keyword_in[$keyword]['posts_count'] = 0;
                    if ($get_posts_count) {
                        foreach ($post_content['posts'] as $post) {
                            preg_match_all('/\b' . $keywordReg . '\b/iu', $post, $matches, PREG_OFFSET_CAPTURE);
                            if (isset($matches[0][0][0])) {
                                $keyword_in[$keyword]['added_count'] = 1;
                                $keyword_in[$keyword]['posts_count']++;
                            }
                        }
                    }
                } else {
                    $keyword_in[$keyword]['posts_count'] = 0;
                }
                $count = 0;
                preg_replace('/\b' . $keywordReg . '\b/iu', '', $post_content['titles'], -1, $count);
                $keyword_in[$keyword]['title_matches'] = $count;
                if ($count > 0) {
                    // $keyword_in[$keyword]['title'] = round(( $count / $post_titles ) * 100, 2);
                    $post_title_words_array = explode(' ', $post_content['titles']);
                    $post_title_word_count = count($post_title_words_array);
                    $keyword_in[$keyword]['title'] = round(($count / $post_title_word_count) * 100, 2);
                    if ($keyword_in[$keyword]['added_count'] == 0) {
                        $keyword_in[$keyword]['posts_count']++;
                    }
                }
            }
            $keyword_in['total_title_words_count'] = count(explode(' ', $post_content['titles']));
            $keyword_in['total_words'] = $post_content['total'];
            $keys = array();
            foreach ($keyword_in as $key => $value) {
                if (array_key_exists($key, $keys)) {
                    $keys[$key] += $value;
                } else {
                    $keys[$key] = $value;
                }
            }
            asort($keys, SORT_DESC);
        }
        return $keyword_in;
    }

    public function post_handling_by_portion($state, $getTopWords = false, $words = null)
    {
        $publishedPostCount = wp_count_posts()->publish;
        $expresscurate_posts_number = get_option('expresscurate_posts_number');
        $max_reported = ((!empty($expresscurate_posts_number)) ? get_option('expresscurate_posts_number') : 100);
        $number_of_post = min($publishedPostCount, $max_reported);
        $iteration = min($publishedPostCount, 200);
        for ($i = 0; $i < $number_of_post; $i += $iteration) {
            $args = array('status' => 'published', 'numberposts' => min($i + $number_of_post, $max_reported), 'offset' => 0);
            if ($state == "words") {
                $wordsPart = $this->get_words($args, false);
                $portions[] = $wordsPart['words'];
            } else if ($state == "stats") {
                if ($getTopWords) {
                    $keywords = $words;
                } else {
                    $keywords = false;
                }
                $portions[] = $this->get_stats($keywords, $args, false, true);
            } else if ($state == "posts") {
                $posts = get_posts($args);
                $posts = array_merge($posts, get_posts($args));
            }
        }
        return $portions;
    }

    public function getKeywordStats($getTopWords = false)
    {
        $stats = array();
        $resultArray = array();

        if ($getTopWords) {
            $words = array();

            /*for ($i = 0; $i < ceil($publishedPostCount / 200); $i++) {
                $args = array('status' => 'published', 'numberposts' => 200, 'offset' => $i * 200);
                $wordsPart = $this->get_words($args, false);
                $words[] = $wordsPart['words'];
            }*/
            $words = $this->post_handling_by_portion('words');
            if (!empty($words)) {
                for ($i = 1; $i < count($words); $i++) {
                    foreach ($words[$i] as $word => $count) {
                        if (!empty($words[0][$word])) {
                            $words[0][$word] += $count;
                        } else {
                            $words[0][$word] = $count;
                        }
                    }
                }
            }
            $definedKeyWords = get_option('expresscurate_defined_tags', true);
            if (!empty($words[0])) {
                $words = array_keys(array_slice($words[0], 0, 30, true));
            }
            if (!empty($definedKeyWords) && !empty($words)) {
                $definedKeywordsArray = explode(',', strtolower(str_replace(' ', '', $definedKeyWords)));

                foreach ($definedKeywordsArray as $definedKeyword) {
                    if (($key = array_search($definedKeyword, $words)) !== false) {
                        unset($words[$key]);
                    }
                }
            }

        }
        /*        for ($i = 0; $i <= ceil($publishedPostCount / 200); $i++) {
                    $args = array('status' => 'published', 'numberposts' => 200, 'offset' => $i * 200);

                }*/
        $stats = $this->post_handling_by_portion('stats', $getTopWords, $words);

        if (count($stats) > 1) {
            for ($i = 1; $i < count($stats); $i++) {
                $stats[0]['total_words'] += $stats[$i]['total_words'];
                $stats[0]['total_title_words_count'] += $stats[$i]['total_title_words_count'];
                foreach ($stats[$i] as $key => $value) {
                    if ($key == 'count' || $key == 'title_matches' || $key == 'posts_count') {
                        $stats[0][$key] += $value;
                    }
                }
            }

        }
        if (!empty($stats[0])) {
            foreach ($stats[0] as &$stat) {
                if (is_array($stat)) {
                    if (0 !== $stats[0]['total_title_words_count']) {
                        $stat['title'] = round(($stat['title_matches'] / ($stats[0]['total_title_words_count'])) * 100, 2);
                    } else {
                        $stat['title'] = 0;
                    }
                    if (0 !== $stats[0]['total_words']) {
                        $stat['percent'] = round(($stat['count'] / ($stats[0]['total_words'])) * 100, 2);
                    } else {
                        $stat['percent'] = 0;
                    }
                    if ($stat['percent'] < 3) {
                        $color = 'blue';
                    } elseif ($stat['percent'] >= 3 && $stat['percent'] <= 5) {
                        $color = 'green';
                    } elseif ($stat['percent'] > 5) {
                        $color = 'red';
                    }
                    $stat['color'] = $color;
                }
            }
            unset($stats[0]['total_words']);
            unset($stats[0]['total_title_words_count']);
            $resultArray = $stats[0];
        } else {
            $defined_tags = get_option("expresscurate_defined_tags", '');
            if (!empty($defined_tags)) {
                $keywords_arr = $this->array_map('trim', explode(",", $defined_tags));
                foreach ($keywords_arr as $tag) {
                    $resultArray[$tag] = array(
                        "added_count" => 0,
                        "color" => "blue",
                        "count" => 0,
                        "percent" => 0,
                        "posts_count" => 0,
                        "title" => 0,
                        "title_matches" => 0
                    );
                }
            }
        }
        return $resultArray;
    }
    public function get_post_analytics_stats()
    {
        $stats=$this->getKeywordStats(false);
        echo json_encode($stats);die;
    }

    public function array_map($func, $array)
    {
        $new_array = array();
        foreach ($array as $key => $value) {
            $new_array[$key] = call_user_func($func, $value);
        }
        return $new_array;
    }

    public function array_map_keys($func, $array, $utf8)
    {
        $new_array = array();
        foreach ($array as $key => $value) {
            $new_array[call_user_func($func, $key, $utf8)] = $value;
        }
        return $new_array;
    }

    public function suggestKeywordsFromGoogle()
    {

        $term = urlencode($_GET['term']);
        $mixed = urlencode($_GET['mixed']);

        $result = wp_remote_get('https://www.google.com/complete/search?output=toolbar&q=' . $term);

        $return_arr = array();

        if (!is_wp_error($result)) {
            preg_match_all('`suggestion data="([^"]+)"/>`u', $result['body'], $matches);

            if (isset($matches[1]) && (is_array($matches[1]) && $matches[1] !== array())) {
                foreach ($matches[1] as $match) {
                    $return_arr[] = html_entity_decode($match, ENT_COMPAT, 'UTF-8');
                }
            }
        }

        $defined_tags_arr = array();
        if (!empty($mixed)) {
            $defined_tags = get_option("expresscurate_defined_tags", '');
            if ($defined_tags) {
                $defined_tags_arr = $this->array_map('trim', explode(",", $defined_tags));
                foreach ($defined_tags_arr as $keyword) {
                    if (strpos($keyword, $term) === false) {
                        if (($key = array_search($keyword, $defined_tags_arr)) !== false) {
                            unset($defined_tags_arr[$key]);
                        }
                    }
                }
                unset($defined_tags_arr['']);
            }
        }
        $return_arr = array_merge($defined_tags_arr, $return_arr);
        echo json_encode($return_arr);
        die();

    }

}

?>
