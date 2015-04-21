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
    
    const BEARER = 'Authorization: Bearer ';
    const ACCESS_TOKEN_URL = 'https://www.expresscurate.com/';
    
    const ENDPOINT_GET_PROFILES = '/profiles';
    const ENDPOINT_CREATE_POST = '/updates/create';

    private static $API_ENDPOINTS = array(
        '/profiles' => 'get',
        '/profiles/:id/updates/pending' => 'get',
        '/profiles/:id/updates/sent' => 'get',
            
        '/updates/create' => 'post',                                // String text, Array profile_ids, Aool shorten, Bool now, Array media ['link'], ['description'], ['picture']
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
      /*
       { 
    "avatar" : "http://a3.twimg.com/profile_images/1405180232.png",
    "formatted_username" : "@skinnyoteam",
    "id" : "4eb854340acb04e870000010",
    "service" : "twitter",
  },
    */
    
        return $this->go(self::ENDPOINT_GET_PROFILES);
    }
    
    public function createPost($post, $time) {
    /*
    profile_ids
required
array   An array of profile id’s that the status update should be sent to. Invalid profile_id’s will be silently ignored.
text
optional
string  The status update text
shorten
optional
boolean If shorten is false links within the text will not be automatically shortened, otherwise they will.
now
optional
boolean If now is set, this update will be sent immediately to all profiles instead of being added to the buffer.
top
optional
boolean If top is set, this update will be added to the top of the buffer and will become the next update sent.
media
optional
associative array   An associative array of media to be attached to the update, currently accepts link, description, title, picture and thumbnail parameters. For image-based updates, picture and thumbnail parameters are both required.
attachment
optional
boolean In the absence of the media parameter, controls whether a link in the text should automatically populate the media parameter. Defaults to true.
scheduled_at
optional
timestamp or ISO 8601 formatted date-time   A date describing when the update should be posted. Overrides any top or now parameter. When using ISO 8601 format, if no UTC offset is specified, UTC is assumed.
    */
    
        return $this->go(self::ENDPOINT_CREATE_POST);
    }
    
    public function publishPostNow($post) {
        // /updates/:id/share
    }
    
    public function publishPostNext($post) {
        // /updates/:id/move_to_top
    }
        
    private function go($endpoint = '', $data = '') {
        if (in_array($endpoint, array_keys($this->endpoints))) {
            $done_endpoint = $endpoint;
        } else {
            $ok = false;
            
            foreach (array_keys($this->endpoints) as $done_endpoint) {
                if (preg_match('/' . preg_replace('/(\:\w+)/i', '(\w+)', str_replace('/', '\/', $done_endpoint)) . '/i', $endpoint, $match)) {
                    $ok = true;
                    break;
                }
            }
            
            if (!$ok) {
                return $this->error('invalid-endpoint');
            }
        }
        
        if (!$data || !is_array($data)) {
            $data = array();
        }
        $data['access_token'] = $this->getAccessToken();
        
        //get() or post()?
        $method = $this->endpoints[$done_endpoint]; 
        return $this->$method($this->buffer_url . $endpoint . '.json', $data);
    }
    
    private function req($url = '', $data = '', $post = true) {
        if (!$url) {
            return false;
        }
        if (!$data || !is_array($data)) {
            $data = array();
        }
        
        /*
        $url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode($siteUrl)
            . '/sitemaps/' . urlencode($feedPath);
        $ch = curl_init($url);
        $options = array(
            CURLOPT_PUT            => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER     => array(self::BEARER . $accessToken)
        );
        curl_setopt_array($ch, $options);
        
        // submit
        $response = curl_exec($ch);
        */
        
        $options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => false);
        
        if ($post) {
            $options += array(
                CURLOPT_POST => $post,
                CURLOPT_POSTFIELDS => $data
            );
        } else {
            $url .= '?' . http_build_query($data);
        }
        
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
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
    
    private function getAccessToken() {
        // check if the access token is already retrieved
        if($this->accessToken) {
            return $this->accessToken;
        }
        
        // check for refresh token
        $refreshToken = get_option('expresscurate_buffer_refresh_token', null);
        if($refreshToken) {
            // get access token
            $this->accessToken = $this->exchangeRefreshTokenWithAccessToken($refreshToken);
            return $this->accessToken;
        }
        
        // the refresh token is not set
        return null;
    }

    private function exchangeRefreshTokenWithAccessToken($refreshToken) {
        if($refreshToken) {
            $ch = curl_init();
            $options = array(
                CURLOPT_URL => self::ACCESS_TOKEN_URL . '/api/connector/buffer/accesstoken',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'refresh_token=' . $refreshToken,
                CURLOPT_ENCODING => "UTF-8",
                CURLOPT_AUTOREFERER => true,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => false
            );
            curl_setopt_array($ch, $options);
            
            $accessTokenDataJson = curl_exec($ch);
            $accessTokenData = json_decode($accessTokenDataJson, true);
            $accessToken = $accessTokenData['access_token'];
        
            return $accessToken;
        } else {
            return null;
        }
    }
}
?>
