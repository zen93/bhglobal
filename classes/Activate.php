<?php
  class Activate {
    const EMAIL_DELAY_TIME = 15;
    const EXTERNAL_URL = "https://bhglobalindia.ddns.net";

    private $activationKey;
    private $keyExpired;
    private $keyGeneratedOn;
    private $keyExists;
    private $lastEmailTime;

    private $hasDetails;
    private $conn;
    private $user;

    public function __construct() {
      $this->conn = new Database();
    }

    private function has_details() {
      if($this->hasDetails)
        return true;
      else {
        throw new AppException("Key not loaded.", AppException::ACTIVATION_ERROR);

      }
    }

    public function load_user($user) {
      $this->user = $user;
      $token = new Token();
      $this->activationKey = $token->gen_token();
      $this->keyExpired = false;
      $this->keyGeneratedOn = date_create()->format('Y-m-d H:i:s');
      $this->keyExists = false;
      $this->lastEmailTime = null;
    }

    public function load_key($activationKey) {
      $this->activationKey = sanitize_input($activationKey);

      $stmt = $this->conn->prepare("SELECT * FROM activate WHERE activation_key = ?");
			$stmt->bind_param("s", $this->activationKey);
			$stmt->execute();
			$result = $stmt->get_result();

			if($result->num_rows > 0) {
				$changePass = true;
				$isValid = true;
				$row = $result->fetch_assoc();
				$uid = $row["uid"];
				$this->keyExpired = $row["key_expired"];

				if($this->keyExpired) {
					throw new AppException("Token Expired! Generate a new account activation token.", AppException::ACTIVATION_ERROR);
				}
        else {
          $this->user = new User();
          $this->user->load_id($uid);
          $this->activationKey = $row['activation_key'];
          $this->keyGeneratedOn = $row['key_generated_on'];
          $this->lastEmailTime = $row['last_email_time'];

          $this->hasDetails = true;
        }
      }
      else {
        throw new AppException("Token Invalid!", AppException::ACTIVATION_ERROR);
      }
    }

    public function is_valid_key() {
      if($this->has_details()) {
        if($this->keyExpired)
          throw new AppException("Token Invalid!", AppException::ACTIVATION_ERROR);
        else
          return true;
      }
    }

    private function check_last_email_time() {
      $stmt = $this->conn->prepare("SELECT key_generated_on, last_email_time FROM activate WHERE uid = ?");
      $stmt->bind_param("i", $this->user->get_id());
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows > 0) {
        //Mail sent
        $this->keyExists = true;
        $row = $result->fetch_assoc();
        $keyTime = new DateTime($row["key_generated_on"]);
        $emailTime = new DateTime($row["last_email_time"]);

        // echo "Email " . $emailTime->format('Y-m-d H:i:s') . " Key " . $keyTime->format('Y-m-d H:i:s');
        //Check if 15 mins or more
        $sinceTime = $emailTime->diff(new DateTime());
        $mins = $sinceTime->days * 24 * 60;
        $mins += $sinceTime->h * 60;
        $mins += $sinceTime->i;

        if($mins > self::EMAIL_DELAY_TIME) {
          //Regenerate activation key
          $sendMail = true;
        }
        else {
          $sendMail = false;
        }
      }
      else {
        $this->keyExists = false;
        $sendMail = true;
      }
      return $sendMail;
    }

    private function delete_last_token() {
      //Delete old activation key
      if($this->keyExists == true) {
        $stmt = $this->conn->prepare("DELETE FROM activate WHERE uid = ?");
        $stmt->bind_param("i", $this->user->get_id());
        $result = $stmt->execute();
        return $result;
      }
      return true;
    }

    public function activate() {
      if(!empty($this->user->get_password())) {
        $activated = 1;
        $stmt = $this->conn->prepare("UPDATE users SET activated = ? WHERE uid = ?");
        $stmt->bind_param("ii", $activated, $this->user->get_id());
        $result = $stmt->execute();
        if(!$result)
        throw new AppException("Cannot activate user", AppException::ACTIVATION_ERROR);
        else {
          return true;
        }
      }
      else
        throw new AppException("User must verify email", AppException::ACTIVATION_ERROR);
    }

    public function deactivate() {
      if(!empty($this->user->get_password())) {
        $activated = 0;
        $stmt = $this->conn->prepare("UPDATE users SET activated = ? WHERE uid = ?");
        $stmt->bind_param("ii", $activated, $this->user->get_id());
        $result = $stmt->execute();
        if(!$result)
        throw new AppException("Cannot deactivate user", AppException::ACTIVATION_ERROR);
        else {
          return true;
        }
      }
      else
        throw new AppException("User must verify email", AppException::ACTIVATION_ERROR);
    }



    public function activate_account($pass, $verifyPass) {
      //Activate account
      $this->has_details();

			$pass = sanitize_input($pass);
			$verifyPass = sanitize_input($verifyPass);

			if(empty($pass)) {
				throw new AppException("Password cannot be empty.", AppException::PASS_ERROR);
			}
			elseif (strlen($pass) < User::PASSWORD_MIN_LENGTH || strlen($pass) > User::PASSWORD_MAX_LENGTH) {
				throw new AppException("Password must be between " . User::PASSWORD_MIN_LENGTH . " and " . User::PASSWORD_MAX_LENGTH . " characters.", AppException::PASS_ERROR);
			}
			if(strcmp($pass, $verifyPass) != 0) {
				throw new AppException("Passwords do not match.", AppException::PASS_ERROR);
			}

			//Update Password and expire token
			$salt = null;
			$stmt = $this->conn->prepare("SELECT salt FROM users WHERE uid = ?");
			$stmt->bind_param("i", $this->user->get_id());
			$stmt->execute();
			$result = $stmt->get_result();
			if($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$salt = $row["salt"];
			}
			else {
				throw new AppException("Error: Cannot retrieve SALT.", AppException::ACTIVATION_ERROR);
			}

			$passHash = hash("sha512", $pass . $salt);
			$stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE uid = ?");
			$stmt->bind_param("si", $passHash, $this->user->get_id());
			$result = $stmt->execute();

			if($result) {
        //Activate user
        $activated = 1;
        $stmt = $this->conn->prepare("UPDATE users SET activated = ? WHERE uid = ?");
        $stmt->bind_param("ii", $activated, $this->user->get_id());
        $result = $stmt->execute();
        if($result) {
          //Expire Token
          $key_expired = 1;
          $stmt = $this->conn->prepare("UPDATE activate SET key_expired = ? WHERE activation_key = ?");
          $stmt->bind_param("is", $key_expired, $this->activationKey);
          $stmt->execute();
          return true;
        }
        else {
          throw new AppException("Cannot activate user!", AppException::ACTIVATION_ERROR);
        }
			}
			else {
				throw new AppException("Cannot update password!", AppException::ACTIVATION_ERROR);
			}
    }

    public function send_activation_mail() {
      //Send the mail
      if($this->check_last_email_time()) {
        $this->delete_last_token();
        $stmt = $this->conn->prepare("INSERT INTO activate(uid, activation_key, key_expired, last_email_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $this->user->get_id(), $this->activationKey, $this->keyExpired, $this->keyGeneratedOn);
        $result = $stmt->execute();
        try {
            if($result) {
              $this->keyExists = true;
            }
            else {
              throw new AppException("Cannot insert key into DB", AppException::ACTIVATION_ERROR);
            }
            $message = "Hello " . $this->user->get_username() . ", <br>".
            "<p>Your account at BH Global Marine India Pvt. Ltd. has been created. However, it is has to be activated.</p>".
            "<p>In order to activate your account, please click on the link below and set a password for your account.</p>".
            "<p><a href='" . self::EXTERNAL_URL . "/activate.php?k=" . $this->activationKey . "'>Click here to change your password!</a>".
            "</p><br> ------THIS IS AN AUTO-GENERATED EMAIL------<br><h3>BH GLOBAL MARINE INDIA PVT. LTD.</h3>";
  					$subject = "Acitvate Account - BHI";
  					$from = "Admin";
            $mailer = new Mailer($this->user->get_email(), $from, $subject, $message);
  					$mailer->send_mail();
            return true;
          }
          catch(AppException $e) {
            //Rollback changes
            $this->delete_last_token();
            throw new AppException($e->message(), $e->get_code());
          }
        }
        else {
          throw new AppException("Only one mail can be sent every 15 minutes. Please try again later.", AppException::ACTIVATION_ERROR);
        }
    }
  }
?>
