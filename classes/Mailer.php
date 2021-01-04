<?php
  class Mailer {
    const EMAIL_DELAY_TIME = 15;

    private $to;
    private $from;
    private $message;
    private $subject;
    private $parameters;
    private $headers;

    function __construct($to, $from="", $subject, $message) {
      $to = filter_var($to, FILTER_SANITIZE_EMAIL);
      if(filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
        throw new AppException("Invalid Email!", AppException::MAIL_ERROR);
      }
      if(empty($to) || empty($subject) || empty($message)) {
        throw new AppException("Parameters cannot be empty!", AppException::MAIL_ERROR);
      }
      $this->to = $to;
      $this->subject = $subject;
      $this->message = $message;
      if($from == "")
        $from = "From: BH Global Marine India Pvt. Ltd";
        else {
          $this->from = "From: " . $from;
          $this->from .=  " <bhglobalmarineindia@gmail.com>";
        }
      $this->parameters = "MIME-Version: 1.0" . "\r\n" . "Content-type:text/html;charset=UTF-8" . "\r\n";
      $this->headers = $this->parameters . $this->from;
    }

    function send_mail() {
      if(!mail($this->to, $this->subject, $this->message, $this->headers)){
        throw new AppException("Mail cannot be sent!", AppException::MAIL_ERROR);
      }
    }

  }
?>
