<?php

try{
  require_once($_SERVER["DOCUMENT_ROOT"] . '/QualysUNIPR/phpCAS-master/vendor/autoload.php');
}
catch(Exception $e){
  http_response_code(500);
  exit('Cannot find library files');
}

session_start();
if (!isset($_SESSION["email"]))
  exit('User not logged');

try{
  $CAS = new phpCAS();

  $CAS->client(CAS_VERSION_2_0,'cas.unipr.it',443,'');

  $CAS->setNoCasServerValidation();

  $CAS->handleLogoutRequests();

  session_destroy();

  echo 'Successful logout';

}
catch(Exception $e){
  exit('Cannot complete user logout');
}

?>
