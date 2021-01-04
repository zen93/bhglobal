<?php
$authenticated = false;
$accessLevel = -1;

try {
  $sessionManager = new SessionManager();
  $authenticated = $sessionManager->authenticate_user(PAGE_ACCESS_LEVEL);
  if($authenticated) {
    $accessLevel = $sessionManager->get_access_level();
    (new AccessLevel())->is_valid_access_level($accessLevel);
  }
}
catch(AppException $e) {
  $authenticated = false;
  echo "<a href='https://". $_SERVER['HTTP_HOST'] ."/'> Click here to go back to login</a> <br>";
  die("<h3> Error: " . $e->message() . "</h3>");
}
if(!$authenticated)
  die("Not authenticated!");
?>
