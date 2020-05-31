<?php

	try{
		require_once("qualys_core.php");
	}catch(Exception $e){
		http_response_code(500);
		exit('{"error": "Cannot find file"}');
	}
	
	if (!isset($_POST["qid"]) || !isset($_POST["utente"])) { //se l'utente non mi passa i parametri non posso eseguire lo script
		http_response_code(400);
		exit('{"error": "Missing parameter"}');
	}
	else{
		$qid = strtolower($_POST["qid"]);
		$utente = $_POST["utente"];
	}
	
	try{
		//invia la richiesta per trovare l'hostID (Global AI)
		$API = new SimpleQAPI("","");
		$token = $API->tokenLogin();

		//preparo i dati per la richiesta a Qualys
		$params = array(
			"URL" => "https://gateway.qg2.apps.qualys.eu/am/v1/assets/host/filter/list",
			"HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/x-www-form-urlencoded", "Authorization: Bearer " . $token),
			"POSTFIELDS" => "filter=agent.agentID:" . $qid . "&includeFields=agentID"
		);

		//ottengo i dati grezzi 
		$json = $API->request($params);

		//filtro i dati
		$asset = json_decode($json, TRUE);
	
		//prendo l'id
		if(isset($asset["assetListData"]["asset"][0]["assetId"]))
			$assetId = $asset["assetListData"]["asset"][0]["assetId"];
		else {
			http_response_code(200);
			exit('{"error": "Cannot retrieve asset ID"}');
		}
		
		//invia la richiesta per sapere se esiste il tag per quel profilo (AM)
		$params = array(
			"URL" => "https://qualysapi.qg2.apps.qualys.eu/qps/rest/2.0/search/am/tag",
			"HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/json", "Accept: application/json"),
			"POSTFIELDS" => "{\"ServiceRequest\":{\"filters\":{\"Criteria\":[{\"field\":\"name\",\"operator\":\"EQUALS\",\"value\":\"".$utente."\"}]}}}"
		);

		//ottengo i dati grezzi 
		$json = $API->request($params);

		//filtro i dati
		$tag = json_decode($json, TRUE);
		
		if(!isset($tag["ServiceResponse"]["data"])){		//il tag non esiste ancora, lo creo con una richiesta (AM)
			$params = array(
			"URL" => "https://qualysapi.qg2.apps.qualys.eu/qps/rest/2.0/create/am/tag/",
			"HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/json", "Accept: application/json"),
			"POSTFIELDS" => "{\"ServiceRequest\": {\"data\":  {\"Tag\": {\"name\": \"".$utente."\", \"parentTagId\":\"44053219\"}}}}"
        );
			$json = $API->request($params);
			$child = json_decode($json, TRUE);
			
			if(isset($child["ServiceResponse"]["data"][0]["Tag"]["id"]))
				$userTagId = $child["ServiceResponse"]["data"][0]["Tag"]["id"];
			else {
				http_response_code(200);
				exit('{"error": "Cannot create tag"}');
			}
		}
		else{												//il tag esiste, prendo il suo id
			$userTagId = $tag["ServiceResponse"]["data"][0]["Tag"]["id"];
		}
		
		//update per aggiornare l'host (AM), eseguire la stessa richiesta più volte non ritorna un fallimento
		$params = array(
			"URL" => "https://qualysapi.qg2.apps.qualys.eu/qps/rest/2.0/update/am/asset/".$assetId,
			"HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/json", "Accept: application/json"),
			"POSTFIELDS" => "{\"ServiceRequest\":{\"data\": {\"Asset\": {\"tags\": {\"add\": {\"TagSimple\": {\"id\": \"".$userTagId."\"}}}}}}}"
			);
			
		$json = $API->request($params);
		$hostUpdated = json_decode($json, TRUE);
		
		//se tutto è andato bene comunico all'utente che l'operazione è andata a buon fine
		if(isset($hostUpdated["ServiceResponse"]["responseCode"]) && $hostUpdated["ServiceResponse"]["responseCode"] == "SUCCESS")
			http_response_code(200);
			echo "Operazione completata correttamente";
		else {
			http_response_code(200);
			exit('{"error": "Cannot associate asset with tag"}');
		}
			
	} 
		
	catch(Exception $e){ //se qualcosa è andato storto, termino lo script e avviso l'utente
		http_response_code(500);
		exit('{"error": "Internal Error"}');
	}
	
?>