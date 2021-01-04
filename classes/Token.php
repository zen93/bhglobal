<?php
  class Token {
    const TOKEN_KEY_LENGTH = 12;
    const TOKEN_KEY_STRENGTH = true;

    public function gen_token() {
      $len = self::TOKEN_KEY_LENGTH;
      $strong = self::TOKEN_KEY_STRENGTH;
      return bin2hex(openssl_random_pseudo_bytes($len, $strong));
    }
  }
?>
