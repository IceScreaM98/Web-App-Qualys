<?php


function filter($xml){ 
    try{
        $elem = new SimpleXMLElement($xml); //creo l'oggetto xml per navigare al suo interno
        $host_list = $elem->{'RESPONSE'}->{'HOST_LIST'};
        $arrayVulnerabilities = array();  //array finale contente per ogni asset il numero delle sue vulnerability categorizzate per severity
        $vulnerabilityConfirmed = array(); //vulnerabilty confermate 
        $vulnerabilityPotential = array(); //vulnerabilty potenziali
        $index = 0; //ciclare su più asset
        foreach ($host_list->{'HOST'} as $host) { //ciclo sugli asset 
            $id = $host->{'ID'}; //hostID
            for ($i = 0; $i < 5; $i++){  //per ogni asset riazzero il contatore delle vulnerabilty
                $vulnerabilityConfirmed[$i] = 0;
                $vulnerabilityPotential[$i] = 0;
            }
            $vulnerabilities = $host->{'DETECTION_LIST'};
            foreach($vulnerabilities->{'DETECTION'} as $vulnerability){ //ciclo sulle vulnerabilty di un singolo asset
                $is_confirmed = ($vulnerability->{'TYPE'} == "Confirmed");
                $level_of_severity = $vulnerability->{'SEVERITY'};
                if ($is_confirmed) $vulnerabilityConfirmed[$level_of_severity-1]++;
                else $vulnerabilityPotential[$level_of_severity-1]++;

            }
            $assetVulnerabilty = array(); //costruisco l'array di risposta per un singolo asset
            $assetVulnerabilty["hostId"] = strval($id); 
            $assetVulnerabilty["vulnerabilityConfirmed"] = $vulnerabilityConfirmed;
            $assetVulnerabilty["vulnerabilityPotential"] = $vulnerabilityPotential;
        
            $arrayVulnerabilities[$index] = $assetVulnerabilty; //lo inserisco nella risposta
            $index++;
        }
    }
    catch(Exception $e){ //in caso il parsing non andasse a buon fine
        http_response_code(500);
        exit('{"error": "Cannot parse XML response"}');       
    }
    return json_encode($arrayVulnerabilities, true); //restituisco in json l'array per il frontend
}

//////INIZIO SCRIPT search_vulnerabilities.php/////////
/*
	Data in POST la lista degli hostID restituisco tutti gli
	asset che hanno il proprio hostID nella lista pssata in POST e per ciascuno di essi fornisco le informazioni sulle vulnerabilities:
        -numero di minacce di livello 1 confermate/potenziali
        -numero di minacce di livello 2 confermate/potenziali
        -numero di minacce di livello 3 confermate/potenziali
        -numero di minacce di livello 4 confermate/potenziali
        -numero di minacce di livello 5 confermate/potenziali
    N.B. in caso di lavoro elevato si potrebbe specificare il range di minacce a cui siamo interessati
*/

//se non trovo il file non ha senso continuare lo script
try{
    require_once('qualys_core.php');
}
catch(Exception $e){
    http_response_code(500);
    exit('{"error": "Cannot find file"}');
}

session_start();  //inizio la sessione

if (!isset($_SESSION["qSession"])){ //se non trovo il valore della qSession settata significa che l'utente non è loggato e non lo servo
    http_response_code(403);
    exit('{"error": "Forbidden"}');	
}
else{
	$qSession= $_SESSION["qSession"];
}

if (!isset($_POST["hostIdList"])) {   //se l'utente non mi passa la lista degli hostId non so cosa cercare
    http_response_code(400);
    exit('{"error": "Missing parameter"}');
}
else{
    $qualys_hostIdList = $_POST["hostIdList"];
}
	
try{
    $SimpleQAPI = new SimpleQAPI();  

    //preparo i dati per la richiesta a Qualys
    $params = array(
        "URL" => "https://qualysapi.qg2.apps.qualys.eu/api/2.0/fo/asset/host/vm/detection/?action=list",
        "HTTPHEADER" => array("X-Requested-With: SimpleQAPI", "Content-Type: application/x-www-form-urlencoded", "Cookie: ". $qSession), //genero il cookie per Qualys
        "POSTFIELDS" => "ids=" . $qualys_hostIdList
    );

    //ottengo i dati grezzi 
    $xml = $SimpleQAPI->request($params);
    //filtro i dati
    if ($xml != null) $filteredXml = filter($xml); //estraggo dall'xml solo i numeri di vulnerabilities e l'hostID
    else $filteredXml = "";

    echo $filteredXml;

}  
catch(Exception $e){  //se qualcosa è andato storto termino lo script e avviso l'utente
    http_response_code(500);
    exit('{"error": "Internal Error"}');
}

//////FINE SCRIPT search_vulnerabilities.php/////////

?>