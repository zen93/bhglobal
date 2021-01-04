<?php
require_once("includes/routines.php");

const PAGE_ACCESS_LEVEL = AccessLevel::PAGE_SETTINGS;
require_once("includes/validate_session.php");

$success = false;
$successMsg = $error = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    $user = $sessionManager->get_user();
    $pass = $_POST["pass"];
    $verifyPass = $_POST["verifypass"];
    if($user->change_password($pass, $verifyPass)) {
      $success = true;
      $successMsg = "Password changed successfully!";
    }
  }
  catch(AppException $e) {
    $error = $e->message();
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Settings | BHI</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css" />
  </head>
  <body>
    <div id="wrapper">
      <img id="logo" src="img/BHI Logo.jpg" />
      <?php require_once("includes/menu.php"); ?>
      <div id="main">
        <h2>Account Settings</h2>
        <fieldset>
          <legend>Change your Password</legend>
          <?php if(!$success) { ?>
          <form action="" method="post">
  					<label class="labelblock" for="pass">New Password:</label>
  					<input type="password" name="pass" id="pass" placeholder="Password" required /> <br>
						<label class="labelblock" for="verifyPass">Confirm Password:</label>
  					<input type="password" name="verifypass" id="verifyPass" placeholder="Retype Password" required /> <br>
  					<?php echo "<p class='error'>" . $error . "</p>" ; ?>
  					<input type="submit" value="Change Password!" id="recoverybutton" />
  				</form>
          <?php } elseif($success) {
  						echo "<p class='success'>" . $successMsg . "</p>";
  						echo "<p> Go back to <a href='/'>Home.</a></p>";
            }
					?>
        </fieldset>

      </div>
    </div>
  </body>
</html>
