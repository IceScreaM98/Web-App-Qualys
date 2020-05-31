<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); //inizio la session
if (!isset($_SESSION["email"])){ //se non è presente la mail non sono loggato
    http_response_code(403);
    exit('{"error": "Forbidden"}');
}

try{ //recupero file di config
    $configs = require_once('../../../private/qualys/config.php');
}
catch(Exception $e){
    http_response_code(500);
    exit($e->getMessage());    
}


$email = $_SESSION["email"];
$directory_qualys = $configs['directory_qualys']; //recupero la directory dove vengono salvati gli user
$email_parts = explode("@", $email);
$email_name = $email_parts[0]; //ottengo lo user prima della @

$user_directory = $directory_qualys . "users/" . $email_name; //recupero la directory specifica dell'user corrente


clearstatcache(); //meglio evitare incoerenze dovute al salvataggio in cache dei file

if (is_dir($user_directory) === false) { //se la directory non è stata creata, creo la directory
    $success = mkdir($user_directory, 0755, true);
    if ($success === true){ //directory creata con successo
        echo "directory creata";
        header("Location: ../dashboard/index.php"); //redirect alla main page
    }
    else{  //directory impossibile da creare (es non ho i permessi)
        http_response_code(500);
        exit("directory impossibile da creare");
        header("Location: ../dashboard/error.html"); //redirect alla pagina di errore
    }
}
else //direcotry già creata
     echo "directory gia creata"; 
     header("Location: ../dashboard/index.php"); //redirect alla main page

?>




