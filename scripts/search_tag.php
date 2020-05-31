<?php



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try{
	require_once("qualys_core.php");
    $configs = require_once('../../../private/qualys/config.php');
}
catch(Exception $e){
		http_response_code(500);
		exit('{"error": "Cannot find file"}');
}

function searchTag($utente){
    	global $configs;
		//crea l'oggetto base
		$API = new SimpleQAPI($configs['qualys_username'],$configs['qualys_password']);			//inserire username e password Qualys
		
		//preparo i dati per la ricerca tag
		$params = array(
				"URL" => "https://qualysapi.qg2.apps.qualys.eu/qps/rest/2.0/search/am/tag",
				"HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/json", "Accept:application/json"),
				"POSTFIELDS" => "{\"ServiceRequest\":{\"filters\":{\"Criteria\":[{\"field\":\"name\",\"operator\":\"EQUALS\",\"value\":\"".$utente."\"}]}}}"
		);
		
		//ottengo i dati grezzi
		$json = $API->request($params);
		
		//filtro i dati
		$tag = json_decode($json, TRUE);
		
		if(!isset($tag)){
			http_response_code(204);
			exit('{"error": "No response data"}');
		}
		else if($tag["ServiceResponse"]["count"] == 0){
			http_response_code(200);
			return FALSE;
		}
		else return TRUE;
}
	
function hostByTag($utente, &$count){
    global $configs;
		//crea l'oggetto base
		$API = new SimpleQAPI($configs['qualys_username'],$configs['qualys_password']);
		
		//preparo i dati per la richiesta del numero di asset
		$params = array(
				"URL" => "https://qualysapi.qg2.apps.qualys.eu/qps/rest/2.0/search/am/hostasset",
				"HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/json", "Accept:application/json"),
				"POSTFIELDS" => "{\"ServiceRequest\": {\"filters\":{\"Criteria\":[{\"field\": \"tagName\",\"operator\": \"EQUALS\",\"value\": \"".$utente."\"}]}}}"
		);
		
		//ottengo i dati grezzi
		$json = $API->request($params);
		
		if(!isset($json)){
			return NULL;
		}
		
		//filtro i dati
		$asset = json_decode($json, TRUE);
		
		$assetNames = array();
		
		//prendo il contatore
		if(isset($asset["ServiceResponse"]["count"]))
			$count = $asset["ServiceResponse"]["count"];
			else {
				http_response_code(204);
				exit('{"error": "Cannot retrieve asset count"}');
			}
			
		if($count >= 1){
			//prendo i nomi degli asset
			foreach($asset["ServiceResponse"]["data"] as $name){
				array_push($assetNames, $name["HostAsset"]["name"]);
			}
		}
			
			return $assetNames;
}
	
?>
