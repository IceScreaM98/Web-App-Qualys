<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try{
        require_once("qualys_core.php");
        require_once("search_tag.php");
        $configs = include('../../../private/qualys/config.php');
}
catch(Exception $e){
		http_response_code(500);
		exit('{"error": "Cannot find file"}');
}

session_start();
if (!isset($_SESSION["email"])) {
		http_response_code(403);
		exit('{"error": "Unauthorized"}');
}
else{
		$utente = $_SESSION["email"];
}


$directory_qualys = $configs['directory_qualys'];
$email_parts = explode("@", $utente);
$email_name = $email_parts[0]; //parte della mail prima della @
$user_directory = $directory_qualys . "users/" . $email_name; //directory dell'user corrente


if (searchTag($utente)){ //se esiste tag restituisco asset associati
    $names = hostByTag($utente, $count);
    $myfile = fopen($user_directory . "/activation_key.json", "r") or exit('{"error": "Unable to open file"'); //leggo il file nella directory dell'utente, in caso di fallimento esco (es non ho i permessi)
    $keyJson = fgets($myfile); //prendo il contenuto del file
    $result = array( //creo l'array con i dati da fare visualizzare
        "names" => $names, //elenco nomi asset
        "count" => $count, //numero asset
        "activationKey" => json_decode($keyJson, true) //valori della chiave
    );
    echo json_encode($result, true);
}
else{ //altrimenti creo activationKey + tag e lo salvo in un file
    $tagId = createTag($utente); //creo il tag
    $key_params = createActivationKey($tagId, $utente); //creo la chiave con il tag appena creato

    $myfile = fopen($user_directory . "/activation_key.json", "w") or exit('{"error": "Unable to open file"'); //creo il file nella directory dell'utente, in caso di fallimento esco (es non ho i permessi)
    fwrite($myfile, json_encode($key_params, true));
    fclose($myfile); //chiudo il file


    echo json_encode($key_params);
}

function createTag($utente){
            global $configs; //array di config per prelevare le credenziali di Qualys
            $API = new SimpleQAPI($configs['qualys_username'],$configs['qualys_password']);	//creo l'oggetto per poter eseguire le chiamate API di Qualys

            $params = array( //parametri della richiesta
                "URL" => "https://qualysapi.qg2.apps.qualys.eu/qps/rest/2.0/create/am/tag/",
                "HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/json", "Accept: application/json"),
                "POSTFIELDS" => "{\"ServiceRequest\": {\"data\":  {\"Tag\": {\"name\": \"".$utente."\", \"parentTagId\":\"44053219\"}}}}"
                );
            $json = $API->request($params); //prelevo il json della risposta
            $tag = json_decode($json, TRUE); //lo trasformo in un array
           
            if(isset($tag["ServiceResponse"]["data"][0]["Tag"]["id"])) //controllo se ho ottenuto indietro da Qualys l'id del tag appena creato
                $userTagId = $tag["ServiceResponse"]["data"][0]["Tag"]["id"];
            else {
                http_response_code(200); //se non lo trovo c'è stato un problema nel contattare Qualys
                exit('{"error": "Cannot create tag"}');
            }
           
            return $userTagId; //restituisco l'id del tag creato
    }
   
function createActivationKey($userTagId, $utente){
            global $configs; //array di config per prelevare le credenziali di Qualys
            $API = new SimpleQAPI($configs['qualys_username'],$configs['qualys_password']);	 //creo l'oggetto per poter eseguire le chiamate API di Qualys

            $params = array( //parametri della richiesta
                "URL" => "https://qualysapi.qg2.apps.qualys.eu/qps/rest/1.0/create/ca/agentactkey/",
                "HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/json", "Accept: application/json"),
                "POSTFIELDS" => "{\"ServiceRequest\": {\"data\": {\"AgentActKey\": {\"title\": \"".$utente."\",\"countPurchased\": \"3\",\"type\": \"COUNT_LIMITED\",\"modules\":{\"list\": {\"ActivationKeyModule\": {\"license\": \"ALL\" }}},\"tags\": {\"list\": {\"Tag\": {\"id\": \"".$userTagId."\"}}}}}}}"
                );
            $json = $API->request($params); //prelevo il json della risposta

            $actKey = json_decode($json, TRUE); //lo trasformo in un array
           
            $key = array(); //creo l'array di risposta contente le informazioni della activationKey

           
            if(isset($actKey["ServiceResponse"]["data"][0]["AgentActKey"]["id"], $actKey["ServiceResponse"]["data"][0]["AgentActKey"]["activationKey"])){ //se ottengo l'id e il valore della chiave
                $key["Id"] = $actKey["ServiceResponse"]["data"][0]["AgentActKey"]["id"]; //salvo i dati nell'array
                $key["Code"] = $actKey["ServiceResponse"]["data"][0]["AgentActKey"]["activationKey"];
            }
            else { //se ottengo l'id e il valore della chiave c'è stato un problema nel contattare Qualys
                http_response_code(200);
                exit('{"error": "Cannot create activation key"}');
            }
       
        return $key; //restituiscco l'id e il valore della activation key appena creata
    }

?>