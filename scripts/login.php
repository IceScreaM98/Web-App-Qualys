<?php


//////INIZIO SCRIPT login.php/////////
/*
    Dato in POST qualys_username e qualys_password (si spera in un futuro con HTTPS)
    inserisco nella session del PHP un token e una qualysSession dsa utilizzare per poter richiamare i vari
    script di ricerca come search_asset.php, ...
    Se i dati di login non sono corretti verrà restituito un opprtuno errore
*/


//se non trovo il file non ha senso continuare lo script
try{
    require_once('qualys_core.php');
}
catch(Exception $e){
    http_response_code(500);
    exit('{"error": "Cannot find file"}');
}





if (!isset($_POST["username"]) || !isset($_POST["password"])){ //se l'utente non mi passa user/psw non posso autenticarlo
    http_response_code(400);
    exit('{"error": "Missing username/password"}');      
}

//un minimo di controllo sulle stringhe passate
$username = $_POST["username"];
$password = $_POST["password"];
$username = filter_var($username, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
$password = filter_var($password, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);

try{
    //istanzio l'oggetto con user/psw per ottenere il token e la qualysSession
    $simpleQAPI = new SimpleQAPI($username, $password);

    //l'oggetto in caso di risposta negativa restituisce NULL quando cerco di ottenere il token/qualysSession
    $sessionId = $simpleQAPI->sessionLogin();  //ottengo la qualysSession

    $token = $simpleQAPI->tokenLogin(); //ottengo il token

    if (isset($token) && isset($sessionId)) {  //caso autenticazione positiva
        session_start(); //inizio la sessione
        $_SESSION["qSession"] = $sessionId;
        $_SESSION["token"] = $token;
        echo '{"login": "successful"}';
    }
    else echo '{"login": "failed"}'; //se anche solo uno dei due è NULL l'autenticazione è fallita
}
catch(Exception $e){ //se qualcosa è andato storto termino lo script e avviso l'utente
    exit('{"error":' . $e->getMessage() . '}'); 
}
?>