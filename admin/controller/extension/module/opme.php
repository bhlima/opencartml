<?php
/**
 * Classe Opme reescrita da Classe Meli.php
 * Alterações foram necessárias para funcionar no Opencart
 * Alterado por Flavio Lima  bhlima/gmail/com
 */
class Opme {

    /**
     * @version 1.0.0
     *  Reescrendo meli porque a original não instancia nem a pau.
     */
    const VERSION = "1.1.0";
    

    /**
     * @var $API_ROOT_URL is a main URL to access the Meli API's.
     * @var $AUTH_URL is a url to redirect the user for login.
     */
    protected static $API_ROOT_URL = "https://api.mercadolibre.com";
    protected static $OAUTH_URL = "/oauth/token";
    public static $AUTH_URL = array(
        "MLA" => "https://auth.mercadolibre.com.ar", // Argentina 
        "MLB" => "https://auth.mercadolivre.com.br", // Brasil
        "MCO" => "https://auth.mercadolibre.com.co", // Colombia
        "MCR" => "https://auth.mercadolibre.com.cr", // Costa Rica
        "MEC" => "https://auth.mercadolibre.com.ec", // Ecuador
        "MLC" => "https://auth.mercadolibre.cl", // Chile
        "MLM" => "https://auth.mercadolibre.com.mx", // Mexico
        "MLU" => "https://auth.mercadolibre.com.uy", // Uruguay
        "MLV" => "https://auth.mercadolibre.com.ve", // Venezuela
        "MPA" => "https://auth.mercadolibre.com.pa", // Panama
        "MPE" => "https://auth.mercadolibre.com.pe", // Peru
        "MPT" => "https://auth.mercadolibre.com.pt", // Prtugal
        "MRD" => "https://auth.mercadolibre.com.do"  // Dominicana
    );

    /**
     * Configuration for CURL
     */
    public static $CURL_OPTS = array(
        CURLOPT_USERAGENT => "MELI-PHP-SDK-1.1.0",
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 60
    );

    /**
     * Metodo Construtor
     * @return string
     */
    public function __construct($client_id, $client_secret, $redirect_uri, $access_token = null, $refresh_token = null) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
        $this->redirect_uri = $redirect_uri;
    }


    /**
     * 
     * @param type $redirect_uri
     * @param type $auth_url
     * @return string
     */
    public function getAuthUrl($redirect_uri) {
        $this->redirect_uri = $redirect_uri;
        $params = array("client_id" => $this->client_id, "response_type" => "code", "redirect_uri" => $redirect_uri);
        $auth_uri = "https://auth.mercadolivre.com.br/authorization?" . http_build_query($params);
        return $auth_uri;
    }

     /**
     * 
     * @param type $path
     * @param type $params
     * @param type $assoc
     * @return type
     */
    public function get($path, $params = null, $assoc = false) {
        $exec = $this->execute($path, null, $params, $assoc);
        return $exec;
    }
      
    /**
     * 
     * @param type $code
     * @param type $redirect_uri
     * @return type
     */
    public function authorize($code, $redirect_uri) {

        if ($redirect_uri)
            $this->redirect_uri = $redirect_uri;

        $body = array(
            "grant_type" => "authorization_code",
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "code" => $code,
            "redirect_uri" => $this->redirect_uri
        );

        $opts = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body
        );

        $request = $this->execute(self::$OAUTH_URL, $opts);

        if ($request["httpCode"] == 200) {
            $this->access_token = $request["body"]->access_token;

            if ($request["body"]->refresh_token)
                $this->refresh_token = $request["body"]->refresh_token;

            return $request;
        } else {
            return $request;
        }
    }

    /**
     * 
     * @param type $path
     * @param type $opts
     * @param type $params
     * @param type $assoc
     * @return type
     */
    public function execute($path, $opts = array(), $params = array(), $assoc = false) {
        $uri = $this->make_path($path, $params);

        $ch = curl_init($uri);
        curl_setopt_array($ch, self::$CURL_OPTS);

        if (!empty($opts))
            curl_setopt_array($ch, $opts);

        $return["body"] = json_decode(curl_exec($ch), $assoc);
        $return["httpCode"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $return;
    }

        /**
     * Check and construct an real URL to make request
     * 
     * @param string $path
     * @param array $params
     * @return string
     */
    public function make_path($path, $params = array()) {
        if (!preg_match("/^http/", $path)) {
            if (!preg_match("/^\//", $path)) {
                $path = '/'.$path;
            }
            $uri = self::$API_ROOT_URL.$path;
        } else {
            $uri = $path;
        }

        if(!empty($params)) {
            $paramsJoined = array();

            foreach($params as $param => $value) {
               $paramsJoined[] = "$param=$value";
            }
            $params = '?'.implode('&', $paramsJoined);
            $uri = $uri.$params;
        }

        return $uri;
    }  
}
