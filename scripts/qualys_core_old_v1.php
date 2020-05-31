<?php

class SimpleQAPI
{
	private $_ch; //dove salvo la richiesta cURL
	private $_headers = array("X-Requested-With" => "SimpleQAPI", "Content-Type" => "application/x-www-form-urlencoded"); //header per le varie richieste, il campo "X-Requested-With" di default serve per utilizzare le API 
	private $_session; //dove accedere al token in caso di utilizzo di moduli che richiedano l'autenticazione Basic 
	private $_token; //dove accedere al token in caso di utilizzo di moduli che richiedano l'autenticazione Bearer

	public $_response; //per accedere alla risposta ottenuta dalle API di QUalys


	public function __construct(){ //creo oggetto per la richiesta cURL
		$this->_ch = curl_init();
	}

	public function __destruct() { //rilascio le risorse di cURL
		curl_close($this->_ch);
	}

	private function _create_curl_base($params){ //metodo che costruisce la richiesta cURL di base che richiede array in forma(... => ...)

		/*if (isset($params["username"]) && isset($params["password"])) {
			curl_setopt($this->_ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($this->_ch, CURLOPT_USERPWD, urlencode($params["username"]) . ':' . urlencode($params["password"]));
		}*/

		if (isset($params["url"])) curl_setopt($this->_ch, CURLOPT_URL, 'https://' . $params["url"]);
		else throw new Exception("Error - no URL specified");

		if (isset($params["headers"])) $headers = $params["headers"];
		else $headers = $this->_headers;
		curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);

		if (isset($params["post_fields"])) {
			curl_setopt($this->_ch, CURLOPT_POST, TRUE);
			curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $params["post_fields"]);
		}

		if (isset($params["connect_timeout"])) $connectTimeout = $params["connect_timeout"];
		else $connectTimeout = 10;
		curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);

		if (isset($params["timeout"])) $timeout = $params["timeout"];
		else $timeout = 60;
		curl_setopt($this->_ch, CURLOPT_TIMEOUT, $timeout);

		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, TRUE);

		$this->_response = curl_exec($this->_ch);

		curl_reset($this->_ch); //tolgo tutti i settaggi precedenti

		if (curl_errno($this->_ch)) throw new Exception(curl_error($this->_ch));
	}

	public function _get_token($username, $password){
		if (!isset($username) || !isset($password)) throw new Exception("Error - no credentials specified");
		if (isset($this->_token)) return $this->_token;
		$params = array(
			"post_fields" => "username=" . $username . "&password=" . $password . "&token=true",
			"url" => "gateway.qg2.apps.qualys.eu/auth"
		);
		$this->_create_curl_base($params);
		$this->_token = $this->_response;
		return $this->_token;
	}

	public function _login($username, $password){
		if (!isset($username) || !isset($password)) throw new Exception("Error - no credentials specified");
		if (isset($this->_session)) return $this->_session;
		$params = array(
			"post_fields" => "username=" . $username . "&password=" . $password . "&action=login",
			"headers" => array("X-Requested-With: SimpleQAPI"),
			"url" => "qualysapi.qg2.apps.qualys.eu/api/2.0/fo/session/"
		);
		$this->_create_curl_base($params);
		$this->_session = $this->_response;
		return $this->_session;
	}

	public function _logout(){
		$params = array(
			"post" => true,
			"post_fields" => "action=logout",
			"url" => "qualysapi.qg2.apps.qualys.eu/api/2.0/fo/session/"
		);
		$this->_create_curl_base($params);
	}

};


/*class SimpleQAPI2
{
 private $curl;
 private $username;
 private $password;
 private $token = NULL;
 private $qualysSession = NULL;

 public function __construct(){
	$this->curl = curl_init();
 }

 public function __destruct() { //rilascio le risorse di cURL
	curl_close($this->_ch);
 }


 public function request($url, $header = array("X-Requested-With" => "SimpleQAPI", "Content-Type" => "application/x-www-form-urlencoded"), 
							$postData = NULL, $post = TRUE, $timeout = 80, $connectTimeout = 10){
	try{
		if(strpos($url, "gateway" && $token=NULL))
			echo ''; //login token-based
		else if(strpos($url, "/api/"))
		    echo ''; //login base
		else if(strpos($url, "/qps/" && $session = NULL))
			echo ''; //login session-based
			
		$options = array(
			CURLOPT_URL => $url,						//url della chiamata
			CURLOPT_POST => $post,						//true se la richiesta è post, false altrimenti (default true)
			CURLOPT_POSTFIELDS => $postData,			//eventuali dati della richiesta, default NULL per GET
			CURLOPT_TIMEOUT => $timeout,				//timeout richiesta, default 80 secondi
			CURLOPT_CONNECTTIMEOUT => $connectTimeout,	//timeout connessione, default 10 secondi
			CURLOPT_RETURNTRANSFER => TRUE,				//la richiesta ritorna i dati come stringa
			CURLOPT_HTTPHEADER => $header				//header, default "X-Requested-With: SimpleQAPI", "Content-Type: application/x-www-form-urlencoded"
		);
		
		curl_setopt_array($this->curl, $options);

		
		return curl_exec($this->curl);

		
	} catch (Exception $e){
		return $e;
	}
 }

 public function curlOption($const, $value){				//aggiunta o modifica opzioni curl
	try{
		curl_setopt($this->curl, $const, $value);
	} catch (Exception $e){
		return $e;
	}
 }
}



*/
?>


