<?php
  class Recover {
    const EMAIL_DELAY_TIME = 15;
    const EXTERNAL_URL = "https://bhglobalindia.ddns.net";

    private $recoveryKey;
    private $keyExpired;
    private $keyGeneratedOn;
    private $keyExists;

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
        throw new AppException("Key not loaded.", AppException::RECOVERY_ERROR);

      }
    }

    public function is_valid_key() {
      if($this->has_details()) {
        if($this->keyExpired)
          throw new AppException("Token Invalid!", AppException::RECOVERY_ERROR);
        else
          return true;
      }
    }

    public function load_user($user) {
      $this->user = $user;
      $token = new Token();
      $this->recoveryKey = $token->gen_token();
      $this->keyExpired = false;
      $this->keyGeneratedOn = date_create()->format('Y-m-d H:i:s');
      $this->keyExists = false;
    }

    public function load_key($recoveryKey) {
      $this->recoveryKey = sanitize_input($recoveryKey);

      $stmt = $this->conn->prepare("SELECT * FROM recover WHERE recovery_key = ?");
			$stmt->bind_param("s", $this->recoveryKey);
			$stmt->execute();
			$result = $stmt->get_result();

			if($result->num_rows > 0) {
				$changePass = true;
				$isValid = true;
				$row = $result->fetch_assoc();
				$uid = $row["uid"];
				$this->keyExpired = $row["key_expired"];

				if($this->keyExpired) {
					throw new AppException("Token Expired! Generate a new password recovery token.", AppException::RECOVERY_ERROR);
				}
        else {
          $this->user = new User();
          $this->user->load_id($uid);
          $this->recoveryKey = $row['recovery_key'];
          $this->keyGeneratedOn = $row['key_generated_on'];
          $this->lastEmailTime = $row['last_email_time'];

          $this->hasDetails = true;
        }
      }
      else {
        throw new AppException("Token Invalid!", AppException::RECOVERY_ERROR);
      }
    }

    private function check_last_email_time() {
      $stmt = $this->conn->prepare("SELECT key_generated_on, last_email_time FROM recover WHERE uid = ?");
      $stmt->bind_param("i", $this->user->get_id());
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows > 0) {
        //Mail sent
        $this->keyExists = true;
        $row = $result->fetch_assoc();
        $keyTime = new DateTime($row["key_generated_on"]);
        $emailTime = new DateTime($row["last_email_time"]);

        //Check if 15 mins or more
        $sinceTime = $emailTime->diff(new DateTime());
        $mins = $sinceTime->days * 24 * 60;
        $mins += $sinceTime->h * 60;
        $mins += $sinceTime->i;

        if($mins > self::EMAIL_DELAY_TIME) {
          //Regenerate recovery key
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
      //Delete old recovery key
      if($this->keyExists == true) {
        $stmt = $this->conn->prepare("DELETE FROM recover WHERE uid = ?");
        $stmt->bind_param("i", $this->user->get_id());
        $result = $stmt->execute();
        return $result;
      }
      return true;
    }

    public function recover_account($pass, $verifyPass) {
      //Recover account
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
				throw new AppException("Error: Cannot retrieve SALT.", AppException::RECOVERY_ERROR);
			}

			$passHash = hash("sha512", $pass . $salt);
			$stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE uid = ?");
			$stmt->bind_param("si", $passHash, $this->user->get_id());
			$result = $stmt->execute();

			if($result) {
        //Expire Token
        $key_expired = 1;
        $stmt = $this->conn->prepare("UPDATE recover SET key_expired = ? WHERE recovery_key = ?");
        $stmt->bind_param("is", $key_expired, $this->recoveryKey);
        $stmt->execute();
        return true;
			}
			else {
				throw new AppException("Cannot update password!", AppException::ACTIVATION_ERROR);
			}
    }

    public function send_recovery_mail() {
      //Send the mail
      if($this->check_last_email_time()) {
        $this->delete_last_token();
        $stmt = $this->conn->prepare("INSERT INTO recover(uid, recovery_key, key_expired, last_email_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $this->user->get_id(), $this->recoveryKey, $this->keyExpired, $this->keyGeneratedOn);
        $result = $stmt->execute();
        try {
            if($result) {
              $this->keyExists = true;
            }
            else {
              throw new AppException("Cannot insert key into DB", AppException::RECOVERY_ERROR);
            }
            $message = "Hi " . $this->user->get_username() . ", <br>".
            "<p>You have requested a password recovery token.</p>".
            "<p>In order to recover your account, please click on the link below and set a new password for your account.</p>".
            "<p><a href='" . self::EXTERNAL_URL . "/recover.php?k=" . $this->recoveryKey . "'>Click here to change your password!</a>".
            "</p><br> ------THIS IS AN AUTO-GENERATED EMAIL------<br><h3>BH GLOBAL MARINE INDIA PVT. LTD.</h3>";
  					$subject = "Password Recovery Token - BHI";
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
          throw new AppException("Only one mail can be sent every 15 minutes. Please try again later.", AppException::RECOVERY_ERROR);
        }
    }
  }
?>
