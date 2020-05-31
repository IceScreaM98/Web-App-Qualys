<?php


//////INIZIO SCRIPT logout.php/////////
/*
    Distruggo la session in PHP ed eseguo il logout da qualys
*/


//se non trovo il file non ha senso continuare lo script
try{
    require_once 'qualys_core.php';
}
catch(Exception $e){
    http_response_code(500);
    exit('{"error": "Cannot find file"}');
}



session_start(); //inizio la sessione


if (isset($_SESSION["qSession"])){ //se non ho la sessionId non ha senso chiedere il logout a qualys
    try{
        $qSession = $_SESSION["qSession"];
        $simpleQAPI = new SimpleQAPI();   
        $params = array("SESSION" => $qSession); 
        $simpleQAPI->request($params); //passo la session all'oggetto  
        $simpleQAPI->sessionLogout(); //faccio il logout da qualys
    }
    catch(Exception $e){ //se qualcosa è andato storto termino lo script e avviso l'utente
        exit('{"error":' . $e->getMessage() . '}'); 
    }
}

setcookie("PHPSESSID", "", time() - 3600, "/");  //forzo la rimozione del cookie 

session_destroy(); //distruggo la sessione

header("Location: ../dashboardAsset/loginPage.html");  //redirect sulla home di login




?>