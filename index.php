<?php
	spl_autoload_register(function ($class) {
		include 'classes/' . $class . '.php';
	});
	require_once("includes/globals.php");
	require_once("includes/routines.php");

	$sessionManager = new SessionManager();
	if($sessionManager->has_cookie()) {
		//Has cookie but needs to validate
		try {
			$sessionManager->authenticate_user(AccessLevel::PAGE_PUBLIC);
			//session active
			redirect("/dashboard.php");
		}
		catch(AppException $e) {
				$error = $e->message() . "<br> ";
				$error .= "<a href='/logout.php'>Click here to logout</a>";
				die($error);
		}
	}

	//Use for captcha
	$isInternalUser = isInternalIP($_SERVER["REMOTE_ADDR"]);

	if(!$isInternalUser) {
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

	$uerror = $perror = $cerror = "";

	if($_SERVER["REQUEST_METHOD"] == "POST") {
		$sessionManager = new SessionManager();
		try {
			if(!$isInternalUser) {
				if ($response == null || !$response->success) {
				 	//captcha failed
					throw new AppException("Captcha failed", AppException::CAPTCHA_ERROR);
			 	}
			}
			$sessionManager->login();
		}
		catch(AppException $e) {
			$error = $e->message();
			$code = $e->get_code();
			if($code == AppException::USER_ERROR)
				$uerror = $error;
			if($code == AppException::PASS_ERROR)
				$perror = $error;
			if($code == AppException::CAPTCHA_ERROR)
				$cerror = $error;
			else {
				$perror = $error;
			}
		}

	}
	?>
<!DOCTYPE html>
<html>
<head>
	<title>Login Portal | BH Global Marine India Pvt. Ltd.</title>
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<link rel="stylesheet" type="text/css" href="css/login.css" />
	<?php
		if(!$isInternalUser)
			echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
	?>
</head>
<body>
	<div id="wrapper">
		<img id="logo" src="img/BHI Logo.jpg" />
		<h3 id="title">BH Global Marine India Login Portal</h3>
		<div id="loginform">
			<fieldset>
				<legend>Enter your login details </legend>
				<form action="" method="post">
					<label class="labelblock" for="username">Username:</label>
					<input type="text" name="username" id="username" placeholder="Username" required /> <br>
					<?php echo "<p class='error'>" . $uerror . "</p>" ; ?>
					<label class="labelblock" for="pass">Password:</label>
					<input type="password" name="pass" id="pass" placeholder="Password" required /><br>
					<?php echo "<p class='error'>" . $perror . "</p>" ; ?>
					<?php
						if(!$isInternalUser) {
							echo '<div class="g-recaptcha" data-sitekey="6LdpbxQTAAAAAOpqmQc7LVrmaOYJcwrHowi_3uy1"></div>';
							echo "<p class='error'>" . $cerror . "</p>";
						}
					?>
					<input type="submit" value="Login!" id="loginbutton" />
					<a id="forgot" href="/forgot.php">Forgot password?</a>
				</form>
			</fieldset>
		</div>
	</div>
</body>
</html>
