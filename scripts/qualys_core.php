<?php

class SimpleQAPI
{
 private $_curl;
 private $_username;
 private $_password;
 private $_token = NULL;
 private $_qualysSession = NULL;
 private $_options = array(
						"URL" => NULL,
						"HTTPHEADER" => array("X-Requested-With: CurlSample", "Content-Type: application/x-www-form-urlencoded"),
						"POST" => TRUE,
						"POSTFIELDS" => NULL,
						"TIMEOUT" => 80,
						"CONNECTTIMEOUT" => 10,
						"RETURNTRANSFER" => TRUE,
						"USERPWD" => NULL,
						"VERBOSE" => 0,
						"STDERR" => NULL,
						"WRITEHEADER" => NULL,
						"HEADER" => FALSE,
						"COOKIE" => NULL
					);
 
 public function __construct($username = NULL, $password = NULL , $token = NULL, $qsession = NULL){ //do la possibilità di passare i parametri anche nel costruttore
	$this->_username = $username;
	$this->_password = $password;
	$this->_token = $token;
	$this->_qualysSession = $qsession;
	$this->_curl = curl_init(); //creo il canale con cURL
 }
 
public function __destructor(){ //rilascio le risorse
	curl_close($this->_curl);
 }

 public function request($parameters){  //posso passare dei dati specifici per costruire richieste ad hoc in cURL
	try{


		if(isset($parameters["USER"], $parameters["PASSWORD"])){
				
				$this->_username = $parameters["USER"];
				$this->_password = $parameters["PASSWORD"];
				
		}

		if (isset($parameters["TOKEN"]))
			$this->_token = $parameters["TOKEN"];

		if (isset($parameters["SESSION"]))
			$this->_qualysSession = $parameters["SESSION"];
		
	
		if (isset($parameters["URL"])) //non ha senso creare una richiesta senza URL
			return $this->execute($parameters);
		
	} catch (Exception $e){
		throw $e;
	}
 }
 
 private function execute($parameters){
	try{

		curl_reset($this->_curl); //meglio pulire sempre i campi e non lasciarlo all'utente

		$values = array_intersect_key($parameters, $this->_options);	//crea un array dall'input con solo i parametri accettati
		
		if(strpos($values["URL"],"/qps/") !== FALSE)								//gestione dell'accesso base per le API "qps"
			curl_setopt($this->_curl,CURLOPT_USERPWD, $this->_username.":".$this->_password);
		
		while (key($this->_options) !== NULL) {				//ciclo per il settaggio dei parametri curl
			$element = current($this->_options);				
			if(isset($values[key($this->_options)]))		//setto l'opzione se esiste il parametro nell'array in input
				curl_setopt($this->_curl, constant("CURLOPT_".key($this->_options)), $values[key($this->_options)]);
			else if(isset($element))				//setto l'opzione se non esiste il parametro nell'array in input e non è null nell'array globale
				curl_setopt($this->_curl, constant("CURLOPT_".key($this->_options)), $element);
			next($this->_options);					//aumenta il puntatore
		}

		reset($this->_options);						//resetta il puntatore per la prossima richiesta
		
		$response = curl_exec($this->_curl);

		if(curl_getinfo($this->_curl, CURLINFO_HTTP_CODE) == 200 || curl_getinfo($this->_curl, CURLINFO_HTTP_CODE) == 201) //il token JWT restituisce 201
			return $response;
		else 
			return NULL;
	} catch(Exception $e){
		throw $e;
	}
 }
 

 //utilizzo user/psw contenuti all'interno della classe in tutti e 3 i metodi successivi

 public function tokenLogin(){ //restituisce un tokenJWT per il global Asset View
	 $params = array(
				"URL" => "https://gateway.qg2.apps.qualys.eu/auth",
				"POSTFIELDS" => "username=" . $this->_username . "&password=".$this->_password . "&token=true"
	 );
	 
	 $this->_token = $this->execute($params);
	 return $this->_token;
 }
				
 public function sessionLogin(){ //restituisce una QualysSessionID per il VM, AI, ...
	 $params = array(
				"URL" => "https://qualysapi.qg2.apps.qualys.eu/api/2.0/fo/session/",
				"POSTFIELDS" => "action=login&username=" . $this->_username . "&password=" . $this->_password,
				"HEADER" => TRUE
	 );
	 $header = $this->execute($params);
	 if ($header != NULL){
		$index = strpos($header, "QualysSession");
		$this->_qualysSession = substr($header, $index, 65);
	 }

	 return $this->_qualysSession;
 }
 
 public function sessionLogout(){ //revoca una QualysSessionID per il VM, AI, ...
	 $params = array(
				"URL" => "https://qualysapi.qg2.apps.qualys.eu/api/2.0/fo/session/",
				"POSTFIELDS" => "action=logout",
				"HTTPHEADER" => array("X-Requested-With: CurlSample", "Content-Type: application/x-www-form-urlencoded", "Cookie:" . $this->_qualysSession)		
	 );
	 
	 return $this->execute($params);
 }
 
}

?>