<?php
	require_once("includes/globals.php");
	require_once("includes/routines.php");

	const RECOVERY_PARAM = "k";

	//Use for captcha
	$isInternalUser = isInternalIP($_SERVER["REMOTE_ADDR"]);
	//Password changed confirmation
	$success = false;
	//Display form
	$showError = false;

	if(!$isInternalUser) {
		//External User, Setup Captcha
		require_once("includes/recaptchalib.php");
		$secret = "6LdpbxQTAAAAAAWy16ilXtnipIsRc_xosH2W_5zt";
		$recaptcha = new ReCaptcha($secret);

		if (isset($_POST["g-recaptcha-response"])) {
	    $response = $recaptcha->verifyResponse(
	        $_SERVER["REMOTE_ADDR"],
	        $_POST["g-recaptcha-response"]
	    );
		}
	}

	$error = $cerror = "";

	if(isset($_GET[RECOVERY_PARAM])) {
		$rkey = $_GET[RECOVERY_PARAM];
		try {
			$recover = new Recover();
			$recover->load_key($rkey);
			if($recover->is_valid_key()) {
				if($_SERVER["REQUEST_METHOD"] == "POST") {
          if(!$isInternalUser) {
						if ($response == null || !$response->success) {
						 	//captcha failed
							throw new AppException("Captcha is incorrect.", AppException::CAPTCHA_ERROR);
					 	}
				 	}
					$pass = $verifyPass = null;
          $pass = $_POST["pass"];
					$verifyPass = $_POST["verifypass"];
          if($recover->recover_account($pass, $verifyPass)) {
            $success = true;
            $successMsg = "Password successfully changed!";
          }
				}
			}
		}
		catch(AppException $e) {
      if($e->get_code() == AppException::PASS_ERROR) {
        $error = $e->message();
        $showError = false;
      }
      elseif ($e->get_code() == AppException::CAPTCHA_ERROR) {
        $cerror = $e->message();
      }
      else {
        $error = $e->message();
        $showError = true;
      }
    }
	}
	else {
    $error= "ERROR: No parameter set!";
    $showError = true;
  }
?>
<!DOCTYPE html>
<html>
  <head>
  	<title>Forgot Password | BH Global Marine India Pvt. Ltd.</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <style>
    #recoveryform {
      width: 500px;
      margin: auto;
    }
		.success {
			color: green;
			font: 1.2em bold;
		}
		.errorMsg {
			color: red;
			font: 1.2em bold;
		}
    </style>
		<?php
			if(!$isInternalUser)
				echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
		?>
  </head>
  <body>
    <div id="wrapper">
      <img id="logo" src="img/BHI Logo.jpg" />
      <h3 id="title">Forgot Your Password?</h3>
  		<div id="recoveryform">
  			<fieldset>
  				<legend>Change your Password</legend>
					<?php if(!$success && !$showError) { ?>
  				<form action="" method="post">
  					<label class="labelblock" for="pass">New Password:</label>
  					<input type="password" name="pass" id="pass" placeholder="Password" required /> <br>
						<label class="labelblock" for="verifyPass">Confirm Password:</label>
  					<input type="password" name="verifypass" id="verifyPass" placeholder="Retype Password" required /> <br>
  					<?php echo "<p class='error'>" . $error . "</p>" ; ?>
  					<?php
  						if(!$isInternalUser) {
  							echo '<div class="g-recaptcha" data-sitekey="6LdpbxQTAAAAAOpqmQc7LVrmaOYJcwrHowi_3uy1"></div>';
  							echo "<p class='error'>" . $cerror . "</p>";
  						}
  					?>
  					<input type="submit" value="Change Password!" id="recoverybutton" />
  				</form>
					<?php } elseif($success) {
						echo "<p class='success'>" . $successMsg . "</p>";
						echo "<p> Go back to <a href='/'>Home.</a></p>";
					} elseif($showError) {
							echo "<p class='errorMsg'>" . $error . "</p>";
							echo "<p> Go back to <a href='/'>Home.</a></p>";
					}
					?>
  			</fieldset>
  		</div>
    </div>

  </body>
</html>
