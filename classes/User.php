<?php
  class User {
      const PASSWORD_MIN_LENGTH = 8;
      const PASSWORD_MAX_LENGTH = 200;
      const USERNAME_MIN_LENGTH = 3;
      const USERNAME_MAX_LENGTH = 30;

      private $uid;
      private $username;
      private $password;
      private $salt;
      private $email;
      private $accessLevel;
      private $activated;

      private $hasDetails;
      private $conn;

      function __construct() {
        $this->conn = new Database();
        $this->hasDetails = false;
      }

      private function has_details() {
        if($this->hasDetails)
          return true;
        else
          throw new AppException("User not loaded", AppException::USER_ERROR);
      }

      public function get_all_ids() {
        $uid = null;
        $stmt = $this->conn->prepare("SELECT uid FROM users");
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
          for($i=0;$i<$result->num_rows;$i++) {
            $row = $result->fetch_assoc();
            $uid[$i] = $row["uid"];
          }
          return $uid;
        }
        else {
          throw new AppException("No users found!", AppException::USER_ERROR);
        }
      }

      public function get_access_level() {
        if($this->has_details())
          return $this->accessLevel;
      }

      public function get_id() {
        if($this->has_details())
          return $this->uid;
      }

      public function get_activated() {
        if($this->has_details())
          return $this->activated;
      }

      public function get_password() {
        //returns hashed password
        if($this->has_details())
          return $this->password;
      }

      private function gen_hashed_password() {
        if($this->has_details())
          return hash("sha512", ($this->password . $this->get_salt()));
      }

      public function get_username() {
        if($this->has_details())
          return htmlspecialchars($this->username);
      }

      public function get_email() {
        if($this->has_details())
          return htmlspecialchars($this->email);
      }

      public function get_salt() {
        if($this->has_details())
          return $this->salt;
      }

      private function set_details($row) {
        //Parameter is the returned row from result in query
        if(isset($row)) {
          $this->uid = $row["uid"];
          $this->username = $row["username"];
          $this->password = $row["password"];
          $this->email = $row["email"];
          $this->salt = $row["salt"];
          $this->accessLevel = $row["access_level"];
          $this->activated = $row["activated"];

          $this->hasDetails = true;
        }
        else {
          $this->hasDetails = false;
          throw new AppException("Must provide user details: set_details()", AppException::USER_ERROR);
        }
      }

      private function set_password() {
        if($this->has_details()) {
            $hashedPass = $this->gen_hashed_password();
            $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE uid = ?");
            $stmt->bind_param("si", $hashedPass, $this->uid);
            $result = $stmt->execute();
            if(!$result)
              throw new AppException("Error Processing Request", AppException::PASS_ERROR);
            else {
              return true;
            }
        }
      }

      public function change_password($password, $verifyPass) {
        $this->password = sanitize_input($password);
        $verifyPass = sanitize_input($verifyPass);
        if(strcmp($this->password, $verifyPass) == 0) {
          if(empty($this->password))
            throw new AppException("Password cannot be empty", AppException::PASS_ERROR);
          $passlen = strlen($this->password);
          if($passlen < self::PASSWORD_MIN_LENGTH || $passlen > self::PASSWORD_MAX_LENGTH) {
            throw new AppException("Password must be between " . self::PASSWORD_MIN_LENGTH . " and " . self::PASSWORD_MAX_LENGTH . " characters", AppException::PASS_ERROR);
          }
          if($this->set_password()) {
            return true;
          }
        }
        else
          throw new AppException("Passwords do not match", AppException::PASS_ERROR);

      }

      public function load_id($uid) {
        //get details using ID
        $this->uid = sanitize_input($uid);

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE uid = ?");
        $stmt->bind_param("s", $this->uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $this->set_details($row);
        }
        return $this->hasDetails;
      }

      public function load_username($username) {
        //get details using username
        $this->username = sanitize_input($username);

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $this->username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
    			//sessionid found in db
    			$row = $result->fetch_assoc();
    			$this->set_details($row);
        }
        return $this->hasDetails;
      }

      private function gen_salt() {
        $token = new Token();
        return hash("sha512", $token->gen_token());
      }

      private function user_exists($username) {
        //check if user exists
        $username = strtolower(sanitize_input($username));
        if(!empty($username)) {
          $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
          $stmt->bind_param("s", $username);
          $stmt->execute();
          $result = $stmt->get_result();
          if($result->num_rows > 0) {
            return true;
          }
        }
        return false;
      }

      private function validate_details($username, $email, $accessLevel) {
        $this->username = strtolower(sanitize_input($username));
        $this->email = strtolower(sanitize_input($email));
        $this->accessLevel = sanitize_input($accessLevel);

        //Validate input
        if(empty($this->username)) {
          throw new AppException("Username cannot be empty", AppException::USER_ERROR);
        }
        if(strlen($this->username) < self::USERNAME_MIN_LENGTH || strlen($username) > self::USERNAME_MAX_LENGTH) {
          throw new AppException("Username should be at least 3 characters and not greater than 30 characters", AppException::USER_ERROR);
        }
        if(empty($this->email)) {
          throw new AppException("Email cannot be empty", AppException::EMAIL_ERROR);
        }
        if(empty($this->accessLevel)) {
          throw new AppException("Access Level needs to be set", AppException::ACCESS_ERROR);
        }
        else {
          (new AccessLevel)->is_valid_access_level($this->accessLevel);
        }
        return true;
      }

      public function add_user($username, $email, $accessLevel) {
        //add a new user
        //validates user details
        $this->validate_details($username, $email, $accessLevel);

        //check if user already exists
        if($this->user_exists($this->username)) {
          throw new AppException("User already exists!", AppException::USER_ERROR);
        }
        //Insert user details into DB
        $this->activated = 0;
        $this->salt = $this->gen_salt();

        $stmt = $this->conn->prepare("INSERT INTO users(username, salt, email, access_level, activated) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $this->username, $this->salt, $this->email, $this->accessLevel, $this->activated);
        $result = $stmt->execute();

        if(!$result) {
          throw new AppException("Cannot insert user into DB!", AppException::USER_ERROR);
        }
        //Activation token
        if($this->load_username($this->username)) {
          $activate = new Activate();
          $activate->load_user($this);
          try {
            if($activate->send_activation_mail()) {
              return true;
            }
          }
          catch(AppException $e) {
            //Rollback user
            $this->delete_user();
            throw new AppException($e->message(), $e->get_code());
          }
        }
        else {
          $this->delete_user();
          throw new AppException("Cannot load user!", AppException::USER_ERROR);
        }
      }

      public function edit_user($username, $email, $accessLevel) {
        //edit existing user
        if($this->has_details()) {
          $prevEmail = $this->email;
          $prevUser = $this->username;
          //validates user details
          $this->validate_details($username, $email, $accessLevel);

          //check if username is taken by someone
          if(!strcmp($prevUser, $this->username) == 0) {
            if($this->user_exists($this->username)) {
              throw new AppException("Another user is using this username!", AppException::USER_ERROR);
            }
          }
          //checks if it should send activation mail
          //echo $prevEmail . " : " . $this->email . " : " . $this->activated;
          if((strcmp($prevEmail, $this->email) == 0) && $this->activated == 1) {
            $this->activated = 1;
          }
          else {
            $this->activated = 0;
            $this->password = "";
            $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE uid = ?");
            $stmt->bind_param("si", $this->password, $this->uid);
            $result = $stmt->execute();

            if(!$result) {
              throw new AppException("Cannot update email!", AppException::USER_ERROR);
            }
          }
          $this->salt = $this->gen_salt();

          //Insert user details into DB
          $stmt = $this->conn->prepare("UPDATE users SET username = ?, salt = ?, email = ?, access_level = ?, activated = ? WHERE uid = ?");
          $stmt->bind_param("sssiii", $this->username, $this->salt, $this->email, $this->accessLevel, $this->activated, $this->uid);
          $result = $stmt->execute();

          if(!$result) {
            throw new AppException("Cannot update user details!", AppException::USER_ERROR);
          }

          if($this->activated == 0) {
            //Activate user
            $activate = new Activate();
            $activate->load_user($this);
            try {
              if($activate->send_activation_mail()) {
                return true;
              }
            }
            catch(AppException $e) {
              throw new AppException($e->message(), $e->get_code());
            }
          }
          else {
            return true;
          }
        }
        else {
          throw new AppException("User does not exist!", AppException::USER_ERROR);
        }
      }

      public function delete_user() {
        //delete existing user
        if($this->has_details()) {
          $stmt = $this->conn->prepare("DELETE FROM users WHERE uid = ?");
          $stmt->bind_param("i", $this->uid);
          $result = $stmt->execute();
          if($result == false)
            throw new AppException("Could not delete user", AppException::USER_ERROR);
          else
            return true;
        }
      }
  }
?>
