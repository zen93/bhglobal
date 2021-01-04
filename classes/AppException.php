<?php
  class AppException extends Exception {
    //Error Codes
    const CAPTCHA_ERROR = 0;
    const DB_ERROR = 1;
    const LOGIN_ERROR = 2;
    const SESSION_ERROR = 3;
    const ACTIVATION_ERROR = 4;
    const ACCESS_ERROR = 5;
    const PRIVILEGE_ERROR = 6;
    const BAD_INPUT_ERROR = 7;
    const USER_ERROR = 8;
    const PASS_ERROR = 9;
    const EMAIL_ERROR = 10;
    const MAIL_ERROR = 11;
    const INVALID_CONSTANT = 12;
    const COMPANY_ERROR = 13;
    const PERSON_ERROR = 14;
    const ADDRESS_ERROR = 15;
    const RELATIONSHIP_ERROR = 16;
    const TENDER_ERROR = 17;
    const RECOVERY_ERROR = 18;


    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
          return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function message() {
      return $this->message;
    }
    public function get_code() {
      return $this->code;
    }
  }
?>
