<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_GoogleClient
{
    private $accessToken;
    
    const BEARER = 'Authorization: Bearer ';
    const ACCESS_TOKEN_URL = 'https://www.expresscurate.com';

    public function submitSitemap($siteUrl, $feedPath) {
        // verify access token
        $accessToken = $this->getAccessToken();
        if($accessToken == null) {
            // no access token, break!
            $response = array('status'=> 1,'message' => 'ExpressCurate shall be authorized to access to Google Search Console (aka Webmaster Tools).');
            return $response;
        }
    
        // prepare the query
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
        
        // check the result
        $info = curl_getinfo($ch);
        curl_close($ch);
        $httpCode = $info['http_code'];
        if($httpCode >= 200 && $httpCode < 300 && 0 == $info['download_content_length']) {
            $response = array('status'=> 0 ,'message'=>'Successfully submitted' );
        } else if($httpCode == 401) {
            $decodedResponse = json_decode($response, true);
            $response = array('status'=> 1,'message' => 'ExpressCurate shall be authorized to access to Google Search Console (aka Webmaster Tools).');
        } else {
            $decodedResponse = json_decode($response, true);
            $response = array('status'=> 2,'message' => isset($decodedResponse['error']) ? $decodedResponse['error'] : 'Something went wrong. Please, make sure you have authorized ExpressCurate to access to Google Search Console (aka Webmaster Tools).');
        }
        return $response;
    }

    public function accessToken() {
        return $this->getAccessToken();
    }

    private function getAccessToken() {
        // check if the access token is already retrieved
        if($this->accessToken) {
            return $this->accessToken;
        }

        // check for refresh token
        $refreshToken = get_option('expresscurate_google_refresh_token', null);
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
                CURLOPT_URL => self::ACCESS_TOKEN_URL . '/api/connector/google/webmasters/accesstoken',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'refresh_token=' . $refreshToken,
                CURLOPT_POST            => 1,
                CURLOPT_ENCODING => "UTF-8",
                CURLOPT_AUTOREFERER => true,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => false
            );
            curl_setopt_array($ch, $options);

            $accessTokenDataJson = curl_exec($ch);
            $accessTokenData = json_decode($accessTokenDataJson, true);
            $accessToken = $accessTokenData['accessToken'];

            return $accessToken;
        } else {
            return null;
        }
    }
}
?>
