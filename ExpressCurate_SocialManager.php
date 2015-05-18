<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_SocialManager {

    const SOCIAL_PUBLISHED_POST_MESSAGES_META = '_expresscurate_social_published_post_messages';
    const SOCIAL_APPROVED_POST_MESSAGES_META = '_expresscurate_social_approved_post_messages';
    const SOCIAL_POST_MESSAGES_META = '_expresscurate_social_post_messages';

    const APPROVED = 'approved';
    const PUBLISHED = 'published';
    const MESSAGE_ID = 'id';

    private static $instance;

    function __construct() {
        // action shall be added from actions controller
    }

    public static function getInstance() {
        if ( ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function saveActiveProfiles() {
        $profiles = $_REQUEST['profiles'];
        update_option('expresscurate_social_publishing_profiles', $profiles);
    }
    
    public static function getActiveProfiles() {
        return get_option('expresscurate_social_publishing_profiles', null);
    }
    
    public function getPublishedPostMessages($post_id) {
        if(empty($post_id)) {
            return null;
        }
        
        $messages = get_post_meta($post_id, self::SOCIAL_PUBLISHED_POST_MESSAGES_META);
        if(empty($messages)) {
            return array();
        }
        
        return $messages[0];
    }
    
    public function getApprovedPostMessages($post_id) {
        if(empty($post_id)) {
            return null;
        }
        
        $messages = get_post_meta($post_id, self::SOCIAL_APPROVED_POST_MESSAGES_META);
        if(empty($messages)) {
            return array();
        }
        
        return $messages[0];
    }

    public function getPostMessages($post_id) {
        if(empty($post_id)) {
            return null;
        }
        
        $messages = get_post_meta($post_id, self::SOCIAL_POST_MESSAGES_META);
        if(empty($messages)) {
            return array();
        }
        
        return $messages[0];
    }
    
    public function savePostMessages() {
        $data=$_REQUEST;
        $post_id = $data['post_id'];
        $messages=json_decode(stripslashes($data['messages']));

        if(empty($post_id) || empty($messages)) {
            return null;
        }

        $approved = $this->getApprovedPostMessages($post_id);
        foreach($messages as $messageId => $message) {


            $messageIdIndex=self::MESSAGE_ID;
            $messageStatus=self::APPROVED;
            if(!isset($message->$messageIdIndex)) {
                $messageId = uniqid();
                $message->$messageIdIndex = $messageId;
            }

            $key = ExpressCurate_BufferClient::POST_FIELD_PROFILE;
            $message->$key = "5540b4e3dc302f2a02a9c09d";

            $messages[$messageId] = $message;

        
            if(isset($message->$messageStatus)) {
                $approved[$messageId] = $message;
            }


        }

        update_post_meta($post_id, self::SOCIAL_APPROVED_POST_MESSAGES_META, $approved);
        update_post_meta($post_id, self::SOCIAL_POST_MESSAGES_META, $messages);

        $this->publishPostMessages($post_id);
    }
    
    public function publishPostMessages($post_id = null) {
        $approvedPostMessages = $this->getApprovedPostMessages($post_id);
        $publishedPostMessages = $this->getPublishedPostMessages($post_id);
        
        $buffer = new ExpressCurate_BufferClient();

        foreach($approvedPostMessages as $messageId => $message) {
           // var_dump($message);
            $result=$buffer->createPost(array(
                'text' => $message->message,
                'profile_ids' => $message->profile_ids
            ));
           // var_dump($result);die;
            // mark as published
            $publishedPostMessages[] = $message;
        }

        // save the new statuses
        update_post_meta($post_id, self::SOCIAL_PUBLISHED_POST_MESSAGES_META, $publishedPostMessages);
        update_post_meta($post_id, self::SOCIAL_APPROVED_POST_MESSAGES_META, array());
    }
}
