<?php
  spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.php';
  });

  function get_truncated_string($string, $length) {
    return (strlen($string) > ($length + 3)) ? trim(substr($string, 0, $length))."..." : $string;
  }

  function get_conn($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if($conn->connect_error) {
      die("Connection failed to DB: " . $conn->connect_error);
    }
    return $conn;
  }

  function authenticate_user($conn, $sessionID, $pageMinLevel) {
    $errorMsg = "";
    $stmt = $conn->prepare("SELECT username, session_id, session_expired, access_level, activated FROM users WHERE session_id = ?");
    $stmt->bind_param("s", $sessionID);
    $stmt->execute();
    //check and return here
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      $user = $row["username"];
      $sid = $row["session_id"];
      $expire = $row["session_expired"];
      $accessLevel = $row["access_level"];
      $isActive = $row["activated"];

      $isValid = (new ACCESS_LEVEL())->IS_VALID_ACCESS_LEVEL($accessLevel);
      //$isActive = (new ACCESS_LEVEL())->IS_ACTIVE($accessLevel);
      $hasPagePrivilege = (new ACCESS_LEVEL())->HAS_PRIVILEGE($accessLevel, $pageMinLevel);

      if($expire == "0" && ($isActive == true) && $hasPagePrivilege) {
        return array(true, $accessLevel);
      }
      else {
        if($expire == "1")
          $errorMsg = "Session Expired. Please login again.";
        if($hasPagePrivilege == false)
          $errorMsg = $user . " does have have the necessary privileges to access this page.";
        if($isActive == false)
          $errorMsg = "Account is not active. Please check your email for the activation email.";
        if($isValid == false)
          $errorMsg = "Assigned access level is INVALID. Please contact the Web Administrator.";
        return array(false, $errorMsg);
      }
    }
    return array(false, "Please login again.");
  }

  function isInternalIP($remoteAddr) {
  	$isInternal = ereg("(192|127)\.(168|0)\.([0-9]{1,3})\.([0-9]{1,3})", $remoteAddr);
  	if($isInternal) {
      return true;
  	} else {
  		return false;
  	}
  }

  function redirect($relativeUrl) {
    header("Location: http://" . $_SERVER['HTTP_HOST'] . $relativeUrl);
    die();
  }

  function logout() {
    header("Location: http://" . $_SERVER['HTTP_HOST'] . "/logout.php");
    die();
  }

  function sanitize_input($string) {
		$string = strip_tags(stripslashes(trim($string)));
		return $string;
	}

  function send_mail($to, $subject="", $message, $from = "", $parameters = "") {
    $to = filter_var($to, FILTER_SANITIZE_EMAIL);
    if($from == "")
      $from = "From: BH Global Marine India Pvt. Ltd";
      else {
        $from = "From: " . $from;
        $from .=  " <bhglobalmarineindia@gmail.com>";
      }
      $headers = $parameters . $from;
    return mail($to, $subject, $message, $headers);
  }

  function gen_token() {
    $len = TOKEN_KEY_LENGTH;
    $strong = TOKEN_KEY_STRENGTH;
    return bin2hex(openssl_random_pseudo_bytes($len, $strong));
  }

  function get_page($url) {
    if(!is_string($url) || $url == "")
      return false;

    $cSession = curl_init();
    curl_setopt($cSession,CURLOPT_URL,$url);
    curl_setopt($cSession,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($cSession,CURLOPT_HEADER, false);
    $result=curl_exec($cSession);
    curl_close($cSession);

    return $result;
  }
  function readable_data($date) {
    $date = explode(" ", $date);
  }
?>
