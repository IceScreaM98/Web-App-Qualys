<?php
class SimpleQAPI2
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
	curl_close($this->curl);
 }


 public function request($url, $header = array("X-Requested-With" => "SimpleQAPI", "Content-Type" => "application/x-www-form-urlencoded"), 
						$postData = NULL, $post = TRUE, $timeout = 80, $connectTimeout = 10){
	try{
		if(strpos($url, "gateway" && $token=NULL))
			echo ''; //login token-based todo
		else if(strpos($url, "/api/"))
		    echo ''; //login base todo
		else if(strpos($url, "/qps/" && $session = NULL))
			echo ''; //login session-based todo
			
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

		
		$response = curl_exec($this->curl);



		$http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		if ($http_code != 200) $response = null;

		curl_reset($this->curl); //resetto tutti i campi

		return $response;

		
	} 
	catch (Exception $e){
		curl_reset($this->curl); //resetto tutti i campi anche se la richiesta non è andata a buon fine
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




?>


