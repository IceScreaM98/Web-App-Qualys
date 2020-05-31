<?php


function myFilter($var){
	return isset($var);
}

function filter_json($jsonText){
	try{
		$jsonArray = json_decode($jsonText, true); //array non filtrato
		
		$index = 0;
		$assetList = array(); //array filtrato

		foreach($jsonArray["assetListData"]["asset"] as $asset){
			$asset = array_filter($asset, "myFilter"); //rimuovo  da ogni asset tutti i campi null
			unset($asset['openPortListData']); //tolgo un po' di campi che non ci interessano
			unset($asset['hardware']);
			unset($asset['networkInterfaceListData']);
			unset($asset['agent']);
			unset($asset['processor']);
			unset($asset['softwareListData']);
			unset($asset['container']);
			unset($asset['sensor']);
			unset($asset['volumeListData']);
			unset($asset['inventory']);
			unset($asset['activity']);
			unset($asset['serviceList']);
			$assetList[$index] = $asset;
			$index++;
		}
		return $assetList;
		}

	catch(Exception $e){ //errore JSON non formattato bene
		echo  "error: " . $e->getMessage();
	}
}


//////INIZIO SCRIPT search_asset.php/////////
/*
	Dato in POST il lastLoggedOnUser restituisco tutti gli
	asset che matchano il lastLoggedOnUser e per ciascuno di essi fornisco le informazioni su:
		-assetName
		-lastLOggedOnUser
		-hostId (utile per le chiamate alle API future del VM)
*/

//se non trovo il file non ha senso continuare lo script
try{
    require_once('qualys_core.php');
}
catch(Exception $e){
    http_response_code(500);
    exit('{"error": "Cannot find file"}');
}

session_start(); //inizio la sessione

if (!isset($_SESSION["token"])){ //se non trovo il valore del token JWT settato significa che l'utente non è loggato e non lo servo
    http_response_code(403);
    exit('{"error": "Forbidden"}');	
}
else{
	$token = $_SESSION["token"];
}

if (!isset($_POST["lastloggedonuser"])) { //se l'utente non mi passa il LastLoggedOnUser non so cosa cercare
    http_response_code(400);
    exit('{"error": "Missing parameter"}');
}
else{
    $qualys_lastLoggedOnUser = $_POST["lastloggedonuser"];
}
	
try{
	$SimpleQAPI = new SimpleQAPI();

	//preparo i dati per la richiesta a Qualys
	$params = array(
		"URL" => "https://gateway.qg2.apps.qualys.eu/am/v1/assets/host/filter/list",
		"HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/x-www-form-urlencoded", "Authorization: Bearer " . $token),
		"POSTFIELDS" => "filter=asset.lastLoggedOnUser:" . $qualys_lastLoggedOnUser . "&includeFields=lastLoggedOnUser,hostId,assetName"
	);

    //ottengo i dati grezzi 
	$json = $SimpleQAPI->request($params);

	//filtro i dati
    if ($json != null) $assetList = filter_json($json); //creo l'array degli asset con le info che mi interessano
    else $assetList = array();
    echo json_encode($assetList);
}  
catch(Exception $e){ //se qualcosa è andato storto termino lo script e avviso l'utente
    http_response_code(500);
    exit('{"error": "Internal Error"}');
}

//////FINE SCRIPT search_asset.php/////////

?>