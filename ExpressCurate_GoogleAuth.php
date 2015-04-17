<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_GoogleAuth
{
    public $accessToken;
    protected $header = "Authorization: Bearer ";
    protected $client_id = null;
    protected $client_secret = null;
    protected $accessTokenUrl = null;
    protected $redirectUri= null;
    const WALL_URL = 'https://www.expresscurate.com/';
    //const WALL_URL = 'http://192.168.0.78:3000/';
    const ACCESS_TOKEN_URL = 'https://www.googleapis.com/oauth2/v3/token';


    public function submit_sitemap($siteUrl, $feedPath)
    {
        $url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode($siteUrl)
            . '/sitemaps/' . urlencode($feedPath);
        $ch = curl_init($url);
        $options = array(
            CURLOPT_PUT => 1,
            CURLOPT_RETURNTRANSFER => 1,
        );
        curl_setopt_array($ch, $options);
        if ($this->header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->header . $this->accessToken));
        }
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if($info['http_code']>=200 && $info['http_code']<300 && 0 == $info['download_content_length']){
            $response = array('status'=> 0 ,'massage'=>'Sucsessully submited' );
        }elseif($info['http_code']== 401){
        $decodedResponse = json_decode($response, true);
        $response = array('status'=> 1 ,'massage'=>$decodedResponse['error'] );
    } else{
        $decodedResponse = json_decode($response, true);
        $response = array('status'=> 2 ,'massage'=>$decodedResponse['error'] );
    }
        curl_close($ch);
        return $response;
    }
    public function getRefreshToken() {
        $key = get_option('expresscurate_google_refresh_token',false);
        if(!$key){
            $blog_url = get_bloginfo('url');
            $url = self::WALL_URL . 'api/getsitemapkey/'.urlencode($blog_url);
            $data = json_encode(array('blog_url' =>$blog_url));
            $this->do_request($url,'GET', $data);
        }
    }
    public function getAccessToken() {
        $this->getUserCredentials();
        $refreshToken = get_option('expresscurate_google_refresh_token',false);
 	return $this->exchangeCodeToAccessToken($refreshToken);
    }

    private function exchangeCodeToAccessToken($refreshToken)
    {
        if($refreshToken){
            $postVals = "client_id=" . $this->client_id
                . "&client_secret=" . $this->client_secret
                . "&grant_type=refresh_token"
                . "&refresh_token=" .$refreshToken;
            $accessTokenDataJson = $this->do_request_for_token(self::ACCESS_TOKEN_URL, 'POST', $postVals);
            $accessTokenData = json_decode($accessTokenDataJson, true);
            $this->accessToken = $accessTokenData['access_token'];
            return true;
        }else{
            return false;
        }
    }
    private function getUserCredentials()
    {
        $url = self::WALL_URL ."api/getgoogleclient";
        $json = $this->do_request_for_token($url, 'POST',array());
        $date = json_decode($json);
        $this->client_id = $date->client_id;
        $this->client_secret = $date->client_secret;
    }
    private function do_request($url,$requestType, $data) {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer' ,
                'Content-Type: application/json; charset=utf-8'),
            CURLOPT_CUSTOMREQUEST => $requestType,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "UTF-8",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false
        );

        if ('POST' === strtoupper($requestType)) {
            $options[CURLOPT_POST] = true;
        }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        preg_match_all('/^Location:(.*)$/mi', $response, $matches);
        $redirectUrl = !empty($matches[1]) ? trim($matches[1][0]) : false;
        if($redirectUrl){
            wp_redirect( $redirectUrl, 301 );
        }
        return $response;
    }

    private function do_request_for_token($url,$requestType, $data) {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_ENCODING => "UTF-8",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        return $response;
    }
}
?>
