<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_SocialManager
{

    const SOCIAL_PUBLISHED_POST_MESSAGES_META = '_expresscurate_social_published_post_messages';
    const SOCIAL_APPROVED_POST_MESSAGES_META = '_expresscurate_social_approved_post_messages';
    const SOCIAL_POST_MESSAGES_META = '_expresscurate_social_post_messages';
    const SOCIAL_POST_COUNTER = '_expresscurate_social_post_counter';

    const APPROVED = 'approved';
    const PUBLISHED = 'published';
    const MESSAGE_ID = 'id';
    const PROFILE = 'profile_ids';
    const MESSAGE = 'message';

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

    public function saveSocialPublishingStatus() {
        $data = $_REQUEST;
        $status = $data['status'];
        if($status == 'off'){
            update_option('expresscurate_buffer_access_token', '');
        }
        update_option('expresscurate_social_publishing', $status);
    }

    public static function saveActiveProfiles()
    {
        $profiles = $_REQUEST['profiles'];
        update_option('expresscurate_social_publishing_profiles', $profiles);
    }

    public static function getActiveProfiles()
    {
        return get_option('expresscurate_social_publishing_profiles', null);
    }

    public function getPublishedPostMessages($post_id)
    {
        if (empty($post_id)) {
            return null;
        }

        $messages = get_post_meta($post_id, self::SOCIAL_PUBLISHED_POST_MESSAGES_META);
        if (empty($messages)) {
            return array();
        }

        return $messages[0];
    }

    public function getApprovedPostMessages($post_id)
    {
        if (empty($post_id)) {
            return null;
        }

        $messages = get_post_meta($post_id, self::SOCIAL_APPROVED_POST_MESSAGES_META);
        if (empty($messages)) {
            return array();
        }

        return $messages[0];
    }

    public function getPostMessages($post_id)
    {
        if (empty($post_id)) {
            return null;
        }

        $messages = get_post_meta($post_id, self::SOCIAL_POST_MESSAGES_META);
        if (empty($messages)) {
            return array();
        }

        return $messages[0];
    }

    public function savePostMessages()
    {

        $data = $_REQUEST;
        $post_id = $data['post_id'];
        $messages = json_decode(stripslashes($data['messages']), true);

        if (empty($post_id)) {
            return null;
        }

        $approved = array();
        $allMessages = array();
        foreach ($messages as $messageId => $message) {
            /*if (!isset($message[self::MESSAGE_ID])) {
                $messageId = uniqid('', true);
                $message[self::MESSAGE_ID] = $messageId;
            }*/

            $messageId=$message[self::MESSAGE_ID];
            if (isset($message[self::APPROVED]) && $message[self::APPROVED] === true) {
                $approved[$messageId] = $message;
            } else {
                $allMessages[$messageId] = $message;
            }

        }

        $publishedPostMessages = $this->getPublishedPostMessages($post_id);

        update_post_meta($post_id, self::SOCIAL_APPROVED_POST_MESSAGES_META, $approved);
        update_post_meta($post_id, self::SOCIAL_POST_MESSAGES_META, $allMessages);
        update_post_meta($post_id, self::SOCIAL_POST_COUNTER, count($approved)+count($publishedPostMessages));
    }

    public function publishPostMessages($post_id = null)
    {
        $approvedPostMessages = $this->getApprovedPostMessages($post_id);
        $publishedPostMessages = $this->getPublishedPostMessages($post_id);

        $buffer = new ExpressCurate_BufferClient();

        foreach ($approvedPostMessages as $messageId => $message) {
            $data = array();
            $data[ExpressCurate_BufferClient::POST_FIELD_TEXT] = $message[self::MESSAGE] . ' ' . get_permalink($post_id);
            $data[ExpressCurate_BufferClient::POST_FIELD_PROFILE] = $message[self::PROFILE];

            $result = $buffer->createPost($data);
            // mark as published
            $publishedPostMessages[$messageId] = $message;
        }
        // save the new statuses
        update_post_meta($post_id, self::SOCIAL_PUBLISHED_POST_MESSAGES_META, $publishedPostMessages);
        update_post_meta($post_id, self::SOCIAL_APPROVED_POST_MESSAGES_META, null);
        update_post_meta($post_id, self::SOCIAL_POST_COUNTER, count($publishedPostMessages));
    }
}
