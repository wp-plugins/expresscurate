<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_BufferClient
{
    private $accessToken;
    
    const POST_FIELD_TEXT = 'text';
    const POST_FIELD_PROFILE = 'profile_ids';
    
    const BEARER = 'Authorization: Bearer ';
    const ACCESS_TOKEN_URL = 'https://www.expresscurate.com/';
    
    const BUFFER_API = 'https://api.bufferapp.com/1';
    const ENDPOINT_GET_PROFILES = '/profiles';
    const ENDPOINT_CREATE_POST = '/updates/create';

    private static $API_ENDPOINTS = array(
        '/profiles' => 'get',
        '/profiles/:id/updates/pending' => 'get',
        '/profiles/:id/updates/sent' => 'get',
            
        '/updates/create' => 'post', // String text, Array profile_ids, Aool shorten, Bool now, Array media ['link'], ['description'], ['picture']
    );
        
    private static $BUFFER_ERRORS = array(
        'invalid-endpoint' => 'The endpoint you supplied does not appear to be valid.',
        '403' => 'Permission denied.',
        '404' => 'Endpoint not found.',
        '405' => 'Method not allowed.',
        '1000' => 'An unknown error occurred.',
        '1001' => 'Access token required.',
        '1002' => 'Not within application scope.',
        '1003' => 'Parameter not recognized.',
        '1004' => 'Required parameter missing.',
        '1005' => 'Unsupported response format.',
        '1010' => 'Profile could not be found.',
        '1011' => 'No authorization to access profile.',
        '1012' => 'Profile did not save successfully.',
        '1013' => 'Profile schedule limit reached.',
        '1014' => 'Profile limit for user has been reached.',
        '1020' => 'Update could not be found.',
        '1021' => 'No authorization to access update.',
        '1022' => 'Update did not save successfully.',
        '1023' => 'Update limit for profile has been reached.',
        '1024' => 'Update limit for team profile has been reached.',
        '1028' => 'Update soft limit for profile reached.',
        '1030' => 'Media filetype not supported.',
        '1031' => 'Media filesize out of acceptable range.',
    );
        
    private static $BUFFER_RESPONSES = array(
        '403' => 'Permission denied.',
        '404' => 'Endpoint not found.',
        '405' => 'Method not allowed.',
        '500' => 'An unknown error occurred.',
        '403' => 'Access token required.',
        '403' => 'Not within application scope.',
        '400' => 'Parameter not recognized.',
        '400' => 'Required parameter missing.',
        '406' => 'Unsupported response format.',
        '404' => 'Profile could not be found.',
        '403' => 'No authorization to access profile.',
        '400' => 'Profile did not save successfully.',
        '403' => 'Profile schedule limit reached.',
        '403' => 'Profile limit for user has been reached.',
        '404' => 'Update could not be found.',
        '403' => 'No authorization to access update.',
        '400' => 'Update did not save successfully.',
        '403' => 'Update limit for profile has been reached.',
        '403' => 'Update limit for team profile has been reached.',
        '403' => 'Update soft limit for profile reached.',
        '400' => 'Media filetype not supported.',
        '400' => 'Media filesize out of acceptable range.',
    );
    
    public function getProfiles() {
        return $this->go(self::ENDPOINT_GET_PROFILES);
    }
    
    public function createPost($post, $now = false) {
        $data = array();
        $data[self::POST_FIELD_PROFILE] = array($post[self::POST_FIELD_PROFILE]);
        $data[self::POST_FIELD_TEXT] = $post[self::POST_FIELD_TEXT];
        $data['top'] = $now;
        
        return $this->go(self::ENDPOINT_CREATE_POST, $data);
    }
        
    private function go($endpoint = '', $data = '') {
        // check for access token
        $accessToken = $this->getAccessToken();
        
        if($accessToken == null) {
            return null;
        }
    
        // check for the endpoint
        if (isset(self::$API_ENDPOINTS[$endpoint])) {
            $methodKey = $endpoint;
        } else {
            $ok = false;
            
            foreach (array_keys($this->endpoints) as $definedEndpoint) {
                if (preg_match('/' . preg_replace('/(\:\w+)/i', '(\w+)', str_replace('/', '\/', $definedEndpoint)) . '/i', $endpoint, $match)) {
                    $ok = true;
                    $methodKey = $definedEndpoint;
                    break;
                }
            }
            
            if (!$ok) {
                return $this->error('invalid-endpoint');
            }
        }
        
        // fix the data wit access token
        if (!$data || !is_array($data)) {
            $data = array();
        }
        $data['access_token'] = $accessToken;
        // call
        // get() or post()?
        $method = self::$API_ENDPOINTS[$methodKey];
        return $this->$method(self::BUFFER_API . $endpoint . '.json', $data);
    }
    
    private function req($url = '', $data = '', $post = true) {
        if (!$url) {
            return false;
        }
        if (!$data || !is_array($data)) {
            $data = array();
        }
        
        $options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => false);

        if ($post) {
            $options += array(
                CURLOPT_POST => 1,
//                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => preg_replace('/%5B[0-9]+%5D/simU', '[]', http_build_query($data))
            );
        } else {
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $rs = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code >= 400) {
            return $this->error($code);
        }
        return json_decode($rs);
    }
    
    private function get($url = '', $data = '') {
       return $this->req($url, $data, false);
    }
    
    private function post($url = '', $data = '') {
        return $this->req($url, $data, true);
    }

    private function error($error) {
        return (string) array('error' => self::$BUFFER_ERRORS[$error]);
    }
    
    private function getAccessToken() {
        // check if the access token is already retrieved
        if($this->accessToken) {
            return $this->accessToken;
        }
        
        // check for access token
        $this->accessToken = get_option('expresscurate_buffer_access_token', null);
        
        // return
        return $this->accessToken;
    }
}
?>
