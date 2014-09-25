<?php

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_Keywords {

  public function get_words($args = false, $new_post = false) {
    $stop_words = array('a', 'able', 'about', 'above', 'abroad', 'according', 'accordingly', 'across', 'actually', 'adj', 'after', 'afterwards', 'again', 'against', 'ago', 'ahead', 'ain\'t', 'all', 'allow', 'allows', 'almost', 'alone', 'along', 'alongside', 'already', 'also', 'although', 'always', 'am', 'amid', 'amidst', 'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere', 'apart', 'appear', 'appreciate', 'appropriate', 'are', 'aren\'t', 'around', 'as', 'a\'s', 'aside', 'ask', 'asking', 'associated', 'at', 'available', 'away', 'awfully', 'b', 'back', 'backward', 'backwards', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'begin', 'behind', 'being', 'believe', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c', 'came', 'can', 'cannot', 'cant', 'can\'t', 'caption', 'cause', 'causes', 'certain', 'certainly', 'changes', 'clearly', 'c\'mon', 'co', 'co.', 'com', 'come', 'comes', 'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding', 'could', 'couldn\'t', 'course', 'c\'s', 'currently', 'd', 'dare', 'daren\'t', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'different', 'directly', 'do', 'does', 'doesn\'t', 'doing', 'done', 'don\'t', 'down', 'downwards', 'during', 'e', 'each', 'edu', 'eg', 'eight', 'eighty', 'either', 'else', 'elsewhere', 'end', 'ending', 'enough', 'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'evermore', 'every', 'everybody', 'everyone', 'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'f', 'fairly', 'far', 'farther', 'few', 'fewer', 'fifth', 'first', 'five', 'followed', 'following', 'follows', 'for', 'forever', 'former', 'formerly', 'forth', 'forward', 'found', 'four', 'from', 'further', 'furthermore', 'g', 'get', 'gets', 'getting', 'given', 'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'h', 'had', 'hadn\'t', 'half', 'happens', 'hardly', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d', 'he\'ll', 'hello', 'help', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'here\'s', 'hereupon', 'hers', 'herself', 'he\'s', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully', 'how', 'howbeit', 'however', 'hundred', 'i', 'i\'d', 'ie', 'if', 'ignored', 'i\'ll', 'i\'m', 'immediate', 'in', 'inasmuch', 'inc', 'inc.', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'inside', 'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t', 'it', 'it\'d', 'it\'ll', 'its', 'it\'s', 'itself', 'i\'ve', 'j', 'just', 'k', 'keep', 'keeps', 'kept', 'know', 'known', 'knows', 'l', 'last', 'lately', 'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'like', 'liked', 'likely', 'likewise', 'little', 'look', 'looking', 'looks', 'low', 'lower', 'ltd', 'm', 'made', 'mainly', 'make', 'makes', 'many', 'may', 'maybe', 'mayn\'t', 'me', 'mean', 'meantime', 'meanwhile', 'merely', 'might', 'mightn\'t', 'mine', 'minus', 'miss', 'more', 'moreover', 'most', 'mostly', 'mr', 'mrs', 'much', 'must', 'mustn\'t', 'my', 'myself', 'n', 'name', 'namely', 'nd', 'near', 'nearly', 'necessary', 'need', 'needn\'t', 'needs', 'neither', 'never', 'neverf', 'neverless', 'nevertheless', 'new', 'next', 'nine', 'ninety', 'no', 'nobody', 'non', 'none', 'nonetheless', 'noone', 'no-one', 'nor', 'normally', 'not', 'nothing', 'notwithstanding', 'novel', 'now', 'nowhere', 'o', 'obviously', 'of', 'off', 'often', 'oh', 'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'one\'s', 'only', 'onto', 'opposite', 'or', 'other', 'others', 'otherwise', 'ought', 'oughtn\'t', 'our', 'ours', 'ourselves', 'out', 'outside', 'over', 'overall', 'own', 'p', 'particular', 'particularly', 'past', 'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'presumably', 'probably', 'provided', 'provides', 'q', 'que', 'quite', 'qv', 'r', 'rather', 'rd', 're', 'really', 'reasonably', 'recent', 'recently', 'regarding', 'regardless', 'regards', 'relatively', 'respectively', 'right', 'round', 's', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly', 'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious', 'seriously', 'seven', 'several', 'shall', 'shan\'t', 'she', 'she\'d', 'she\'ll', 'she\'s', 'should', 'shouldn\'t', 'since', 'six', 'so', 'some', 'somebody', 'someday', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry', 'specified', 'specify', 'specifying', 'still', 'sub', 'such', 'sup', 'sure', 't', 'take', 'taken', 'taking', 'tell', 'tends', 'th', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'ll', 'thats', 'that\'s', 'that\'ve', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'there\'d', 'therefore', 'therein', 'there\'ll', 'there\'re', 'theres', 'there\'s', 'thereupon', 'there\'ve', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'thing', 'things', 'think', 'third', 'thirty', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'till', 'to', 'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying', 't\'s', 'twice', 'two', 'u', 'un', 'under', 'underneath', 'undoing', 'unfortunately', 'unless', 'unlike', 'unlikely', 'until', 'unto', 'up', 'upon', 'upwards', 'us', 'use', 'used', 'useful', 'uses', 'using', 'usually', 'v', 'value', 'various', 'versus', 'very', 'via', 'viz', 'vs', 'w', 'want', 'wants', 'was', 'wasn\'t', 'way', 'we', 'we\'d', 'welcome', 'well', 'we\'ll', 'went', 'were', 'we\'re', 'weren\'t', 'we\'ve', 'what', 'whatever', 'what\'ll', 'what\'s', 'what\'ve', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'where\'s', 'whereupon', 'wherever', 'whether', 'which', 'whichever', 'while', 'whilst', 'whither', 'who', 'who\'d', 'whoever', 'whole', 'who\'ll', 'whom', 'whomever', 'who\'s', 'whose', 'why', 'will', 'willing', 'wish', 'with', 'within', 'without', 'wonder', 'won\'t', 'would', 'wouldn\'t', 'x', 'y', 'yes', 'yet', 'you', 'you\'d', 'you\'ll', 'your', 'you\'re', 'yours', 'yourself', 'yourselves', 'you\'ve', 'z', 'zero');
    $post_text = '';
    $post_arr = array();
    $post_titles = '';
    if ($args === false && $new_post === false) {
      $args = array('status' => 'published', 'numberposts' => 1000000);
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
    $post_text = preg_replace('/\b(' . implode('|', $stop_words) . ')\b/iu', '', $post_text);
    $post_text = preg_replace('/\b[^\s]{1,2}\b/i', '', $post_text);
    $post_text = preg_replace("/[\",.':;\\-\\=\\+\\)\\?\\!\\&\\(\\}\\{\\[\\]\\@]/", "", $post_text);
    $post_words = array_count_values(str_word_count($post_text, 1));
    $post_words = $this->array_map_keys('strtolower', $post_words);
    $post_words = array_change_key_case($post_words, CASE_LOWER);
    $post_words = array_filter($post_words);

    unset($post_words[null]);
    unset($post_words['']);
    unset($post_words['\'']);
    unset($post_words[' ']);
    unset($post_words['-']);
    unset($post_words['nbsp']);
    arsort($post_words);

    $total = array_sum($post_words);
    $result = array('titles' => $post_titles, 'content' => $post_text, 'posts' => $post_arr, 'words' => $post_words, 'total' => $total);
    return $result;
  }

  public function add_keyword($keyword, $get_stats = false) {
    $defined_tags_arr = array();
    if ($_REQUEST) {
      $keywords = explode(",", $_REQUEST['keywords']);
      $get_stats = $_REQUEST['get_stats'];
    }
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
          if (preg_grep("/\b" . $keyword . "\b\w+/i", $defined_tags_arr)) {
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
        $stats = $this->get_stats(array($keywords), false, false, false);
        $result = array('status' => "success", 'stats' => $stats);
      } else {
        $result = array('status' => "success");
      }
    } else {
      $result = array('status' => "warning", 'msg' => __('Something went wrong'));
    }

    echo json_encode($result);
    die();
  }

  public function get_post_keyword_stats($keyword, $post_id = false) {
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

  public function delete_keyword() {
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
      $result = array('status' => "error", 'msg' => "Something went wrong");
    }
    echo json_encode($result);
    die();
  }

  public function get_stats($keywords = array(), $args = false, $post_content = false, $get_posts_count = false) {
    if ($_POST && isset($_POST['keywords'])) {
      $keywords = $this->array_map('trim', explode(",", $_POST['keywords']));
      if (isset($_POST['post_title'])) {
        $post_content = $this->get_words(false, array('title' => $_POST['post_title'], 'content' => $_POST['post_content']));
      } else {
        $post_content = $this->get_words($args);
      }
    } else {
      if (!$post_content) {
        $post_content = $this->get_words($args);
      } else {
        
      }
      if (!$keywords) {
        $keywords = get_option('expresscurate_defined_tags', '');
        if ($keywords) {
          $keywords = explode(', ', $keywords);
        } else {
          $keywords = array();
        }
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
        preg_replace('/\b' . $keyword . '\b/iu', '', $post_content['content'], -1, $keyword_in[$keyword]['count']);
        //str_ireplace(" ".$keyword." ", '', $post_content['content'], $keyword_in[$keyword]['count']);
        $keyword_in[$keyword]['title'] = 0;
        // $keyword_in[$keyword]['count'] = ((isset($post_content['words'][$keyword]) || isset($post_content['words']["#".$keyword])) ? $post_content['words'][$keyword] : 0);
        if ($post_content['total'] !== 0) {
          $keyword_in[$keyword]['percent'] = round(( $keyword_in[$keyword]['count'] / $post_content['total']) * 100, 2);
          $keyword_in[$keyword]['posts_count'] = 0;
          if ($get_posts_count) {
            foreach ($post_content['posts'] as $post) {
              preg_match_all('/\b' . $keyword . '\b/iu', $post, $matches, PREG_OFFSET_CAPTURE);
              if (isset($matches[0][0][0])) {
                $keyword_in[$keyword]['added_count'] = 1;
                $keyword_in[$keyword]['posts_count']++;
              }
            }
          }
        } else {
          $keyword_in[$keyword]['percent'] = 0;
          $keyword_in[$keyword]['posts_count'] = 0;
        }
        if ($keyword_in[$keyword]['percent'] < 3) {
          $color = 'blue';
        } elseif ($keyword_in[$keyword]['percent'] >= 3 && $keyword_in[$keyword]['percent'] <= 5) {
          $color = 'green';
        } elseif ($keyword_in[$keyword]['percent'] > 5) {
          $color = 'red';
        }
        $keyword_in[$keyword]['color'] = $color;
        $count = 0;
        $post_titles = strlen($post_content['titles']);
        preg_replace('/\b' . $keyword . '\b/iu', '', $post_content['titles'], -1, $count);
        if ($count > 0) {
          $keyword_in[$keyword]['title'] = round(( $count / $post_titles ) * 100, 2);
          if ($keyword_in[$keyword]['added_count'] == 0) {
            $keyword_in[$keyword]['posts_count']++;
          }
        }
      }

      $keys = array();
      foreach ($keyword_in as $key => $value) {
        if (array_key_exists($key, $keys)) {
          $keys[$key] += $value;
        } else {
          $keys[$key] = $value;
        }
      }

      array_multisort($keys, SORT_DESC, $keyword_in);
    }
    if ($_POST) {
      $result = array('status' => "success", 'stats' => $keyword_in);
      echo json_encode($result);
      die();
    } else {
      return $keyword_in;
    }
  }

  private function array_map($func, $array) {
    $new_array = array();
    foreach ($array as $key => $value) {
      $new_array[$key] = call_user_func($func, $value);
    }
    return $new_array;
  }

  private function array_map_keys($func, $array) {
    $new_array = array();
    foreach ($array as $key => $value) {
      $new_array[call_user_func($func, $key)] = $value;
    }
    return $new_array;
  }

}

?>
