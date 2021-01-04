<?php
	require_once("includes/globals.php");
	require_once("includes/routines.php");

	//Use for captcha
	$isInternalUser = isInternalIP($_SERVER["REMOTE_ADDR"]);
	//Mail sent confirmation
	$success = false;
	$changePass = false;

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

	if($_SERVER["REQUEST_METHOD"] == "POST") {
		//Username received through POST request
		$isValid = true;

		if(!$isInternalUser) {
			if ($response == null || !$response->success) {
			 	//captcha failed
				$cerror = "Captcha is incorrect.";
				$isValid = false;
		 	}
	 	}
		if($isValid) {
			try {
				$user = new User();
				$user->load_username($_POST["username"]);
				$recover = new Recover();
				$recover->load_user($user);
				if(!$recover->send_recovery_mail())
				throw new AppException("Cannot send mail", AppException::RECOVERY_ERROR);
				else {
					$success = true;
					$successMsg = "Recovery mail successfully sent to registered email for " . $user->get_username() . ".";
				}
			}
			catch(AppException $e) {
				if($e->get_code() == AppException::USER_ERROR)
					$error = "Invalid username!";
				else
					$error = $e->message();
			}
		}
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
  				<legend>Send Password Recovery Mail</legend>
					<?php if(!$success) { ?>
  				<form action="" method="post">
  					<label class="labelblock" for="username">Enter your Username:</label>
  					<input type="text" name="username" id="username" placeholder="Username" required />
  					<?php if(!empty($error)) echo "<p class='error'>" . $error . "</p>" ; ?>
  					<?php
  						if(!$isInternalUser) {
  							echo '<div class="g-recaptcha" data-sitekey="6LdpbxQTAAAAAOpqmQc7LVrmaOYJcwrHowi_3uy1"></div>';
  							echo "<p class='error'>" . $cerror . "</p>";
  						}
  					?>
  					<input type="submit" value="Send Password Recovery Email!" id="recoverybutton" />
  				</form>
					<?php } else {
						echo "<p class='success'>" . $successMsg . "</p>";
						echo "<p> Go back to <a href='/'>Home.</a></p>";
					} ?>
  			</fieldset>
  		</div>
    </div>

  </body>
</html>
