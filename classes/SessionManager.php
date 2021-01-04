<?php
  require_once("User.php");
  require_once("Database.php");
  require_once("includes/routines.php");

  class SessionManager {
    const COOKIE_NAME = "user";
    const USER_PARAM = "username";
    const PASS_PARAM = "pass";
    const HOURS_TO_KEEP_COOKIE = 24;

    const SESSION_ERROR = 2;
    const BAD_INPUT = 2;
    const LOGIN_ERROR = 2;
    const PRIVILEGE_ERROR = 2;

    private $id;
    private $uid;
    private $sessionID;
    private $sessionExpired;
    private $loginTime;
    private $logoutTime;

    private $conn;
    private $user;

    public function __construct() {
      $this->user = new User();
      $this->conn = new Database();
    }

    public function get_access_level() {
      return $this->user->get_access_level();
    }

    public function get_user() {
        return $this->user;
    }

    private function load_session_id($sid) {
      $this->sessionID = sanitize_input($sid);
      $stmt = $this->conn->prepare("SELECT * FROM sessions WHERE session_id = ?");
      $stmt->bind_param("s", $this->sessionID);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows > 0) {
        //sessionid found in db
        $row = $result->fetch_assoc();
        $this->uid = $row["uid"];
        $this->sessionID = $row["session_id"];
        $this->sessionExpired = $row["session_expired"];
        $this->loginTime = $row["login_time"];
        $this->logoutTime = $row["logout_time"];
        //$this->set_details($row);
        return true;
      }
      else {
        throw new AppException("Session Invalid!", AppException::SESSION_ERROR);
      }
    }

    public function has_cookie() {
      if(isset($_COOKIE[self::COOKIE_NAME]) && !empty($_COOKIE[self::COOKIE_NAME])) {
        return true;
      }
      return false;
    }

    private function get_session_id() {
      if(isset($_COOKIE[self::COOKIE_NAME])) {
        return sanitize_input($_COOKIE[self::COOKIE_NAME]);
      }
      else {
        throw new AppException("No cookie set!", AppException::SESSION_ERROR);
      }
    }

    private function validate_session() {
      $sessionID = $this->get_session_id();
      $this->load_session_id($sessionID);
      if(!$this->user->load_id($this->uid))
        throw new AppException("Please login again!", AppException::SESSION_ERROR);
    }

    private function gen_session_id() {
      //generate cryptographically secure session
      $token = new Token();
      return hash("sha512", $this->user->get_username() . $this->user->get_salt() . $token->gen_token());
    }

    private function get_hashed_pass($password) {
      return hash("sha512", $password . $this->user->get_salt());
    }

    public function login() {
      //login the user
      $user = $pass = "";
      $user = sanitize_input($_POST[self::USER_PARAM]);
  		$pass = sanitize_input($_POST[self::PASS_PARAM]);

  		if(empty($user)) {
  			throw new AppException("User cannot be empty.", AppException::USER_ERROR);
  		}

  		elseif(empty($pass)) {
  			throw new AppException("Password cannot be empty.", AppException::PASS_ERROR);
  		}
      else {
        if($this->user->load_username($user)) {
          $hashedPass = $this->get_hashed_pass($pass);
          if(strcmp($hashedPass, $this->user->get_password()) == 0) {

            if(!$this->user->get_activated()) {
              throw new AppException("User has not been activated!", AppException::LOGIN_ERROR);
            }
            elseif((new AccessLevel())->is_valid_access_level($this->user->get_access_level())) {

              $this->loginTime = date_create()->format('Y-m-d H:i:s');
      				$this->sessionExpired = 0;
              $this->sessionID = $this->gen_session_id();


              $stmt = $this->conn->prepare("INSERT INTO sessions (uid, login_time, session_id, session_expired) VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE login_time = ?, session_id = ?, session_expired = ?");
  						$stmt->bind_param("ississi", $this->user->get_id(), $this->loginTime, $this->sessionID, $this->sessionExpired,  $this->loginTime, $this->sessionID, $this->sessionExpired );
  						$stmt->execute();

  						setcookie(self::COOKIE_NAME, $this->sessionID, time() + (3600 * self::HOURS_TO_KEEP_COOKIE));
              //return true;
  						redirect("/dashboard.php");
            }
          }
          else {
            throw new AppException("Password is incorrect", AppException::PASS_ERROR);
          }
        }
        else {
          throw new AppException("Username is invalid", AppException::USER_ERROR);
        }
      }
    }

    public function logout() {
      //logout the user
      if(isset($_COOKIE[self::COOKIE_NAME])) {
        $this->sessionID = $this->get_session_id();
        setcookie(self::COOKIE_NAME, "", time() - 3600);

        $this->logoutTime = date_create()->format('Y-m-d H:i:s');
        $this->sessionExpired = 1;

        $stmt = $this->conn->prepare("UPDATE sessions SET logout_time = ?, session_expired = ? WHERE session_id = ?");
        $stmt->bind_param("sis", $this->logoutTime, $this->sessionExpired, $this->sessionID);
        $stmt->execute();
      }
      else {
        throw new AppException("No Cookie found!", self::SESSION_ERROR);
      }
      header('Location: '. 'http://' . $_SERVER['HTTP_HOST']);
      die();
    }

    public function authenticate_user($pageMinLevel) {
      //check Access Level
      if($this->has_cookie()) {
        $this->validate_session();
        $isValidAccessLevel = (new AccessLevel())->is_valid_access_level($this->user->get_access_level());
        $hasPagePrivilege = (new AccessLevel())->has_privilege($this->user->get_access_level(), $pageMinLevel);
        $isActive = $this->user->get_activated();

        if($isValidAccessLevel && $hasPagePrivilege && $isActive) {
          return true;
        }
        else {
          if(!$isActive)
            throw new AppException("Account is not active. Please check your email for the activation email.", AppException::PRIVILEGE_ERROR);
          if(!$hasPagePrivilege)
            throw new AppException($this->user->get_username() . " does have have the necessary privileges to access this page.", AppException::PRIVILEGE_ERROR);
          if(!$isValidAccessLevel)
            throw new AppException("Invalid Access Level!", AppException::PRIVILEGE_ERROR);
        }
      }
      else {
        throw new AppException("No Cookie Found!", AppException::SESSION_ERROR);
      }
    }
  }
?>
