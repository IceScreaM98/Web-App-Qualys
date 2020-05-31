<?php

try{
  require_once($_SERVER["DOCUMENT_ROOT"] . '/QualysUNIPR/phpCAS-master/vendor/autoload.php');
}
catch(Exception $e){
  http_response_code(500);
  exit('Cannot find library files');
}

try{
  $CAS = new phpCAS();

  $CAS->client(CAS_VERSION_2_0,'cas.unipr.it',443,'');

  $CAS->setNoCasServerValidation();

  $CAS->forceAuthentication();

  $mailCAS = $CAS->getUser();

  if (isset($mailCAS)) {
    session_start();
    $_SESSION["email"] = $mailCAS;
    echo "Successful login";
  }
  else
    echo "Bad login";

}
catch(Exception $e){
  exit('Cannot complete user authentication');
}

?>

