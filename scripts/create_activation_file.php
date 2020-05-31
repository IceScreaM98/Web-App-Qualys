<?php

function is_valid_key($key){ //funzione ausiliaria da fare (dovrà contattare le API di Qualys)
    return true;

}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); //inizio la session
if (!isset($_SESSION["email"])){ //se non è presente la mail non sono loggato
    http_response_code(403);
    exit('{"error": "Forbidden"}');
}

if (!isset($_POST["activation_key"])){ //se non mi viene passata la activation key, non posso creare il file da salvare sul filesystem
    http_response_code(403);
    exit('{"error": "Missing parameters"}');
}

try{ //recupero file di config
    $configs = require_once('../../../private/qualys/config.php');
}
catch(Exception $e){
    http_response_code(500);
    exit($e->getMessage());    
}


$email = $_SESSION["email"];
$activationKey = $_POST["activation_key"];

if (is_valid_key($activationKey) === false) //se l'activation key non è valida, non la salvo sul filesystem ed esco
    exit('{"error" : "invalid activation key"}');

$directory_qualys = $configs['directory_qualys'];
$email_parts = explode("@", $email);
$email_name = $email_parts[0]; //parte della mail prima della @
$user_directory = $directory_qualys . "users/" . $email_name; //directory dell'user corrente

try{
    $myfile = fopen($user_directory . "/activation_key.txt", "w") or exit('{"error": "Unable to open file"'); //creo il file nella directory dell'utente, in caso di fallimento esco (es non ho i permessi)
    fwrite($myfile, $activationKey); //inserisco l'activation key
    fclose($myfile); //chiudo il file
}
catch(Exception $e){
    http_response_code(500);
    exit($e->getMessage());    
}


?>



