<?php

class ExpressCurate_GoogleAuth
{
    public $accessToken;

    protected $header = "Authorization: Bearer ";

    //const WALL_URL = 'https://www.expresscurate.com/';
    const WALL_URL = 'http://192.168.0.154:3000/';

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
        }if($info['http_code']== 401){
            $decodedResponse = json_decode($response, true);
            $response = array('status'=> 1 ,'massage'=>$decodedResponse['error'] );
        } else{
            $decodedResponse = json_decode($response, true);
            $response = array('status'=> 2 ,'massage'=>$decodedResponse['error'] );
        }
        curl_close($ch);
        return $response;
    }


    public function getGoogleToken() {
        $key = get_option('expresscurate_google_auth_key',false);
        if(!$key){
            $blog_url = get_bloginfo('url');
            $url = self::WALL_URL . 'api/getsitemapkey/'.urlencode($blog_url);
            $data = json_encode(array('blog_url' =>$blog_url));
            $this->do_request($url, $data);
        }else{
            return $key;
        }
//        $blog_url = get_bloginfo('url');
//        $url = self::WALL_URL . 'api/getsitemapkey/'.urlencode($blog_url);
//        $data = json_encode(array('blog_url' =>$blog_url));
//        $response = $this->do_request($url, $data);
//        $response =  json_decode($response);
//        if($response->response){
//            return   $response->response->token;
//        }else{
//            return false;
//        }
    }

    private function do_request($url, $data) {
        $ch = curl_init();

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer'  ,
                'Content-Type: application/json; charset=utf-8'),
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "UTF-8",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => 1,
        );

        curl_setopt_array($ch, $options);


        $response = curl_exec($ch);
        preg_match_all('/^Location:(.*)$/mi', $response, $matches);

        $redirectUrl = !empty($matches[1]) ? trim($matches[1][0]) : false;

        if($redirectUrl){
            wp_redirect( $redirectUrl, 301 );
        }
        return $response;
    }

}

?>
