<?php
  define("SERVER_NAME", "localhost");
  define("DB_NAME", "bhglobal");
  define("USERNAME", "bhgdb");
  define("PASSWORD", "bhglobalindia@123");

  define("COOKIE_NAME", "user");

  define("EXTERNAL_URL", "https://bhglobalindia.ddns.net");
  define("INTERNAL_URL", "https://bhi.local");
  define("INTERNAL_IP", "192.168.1.33");

  define("PASSWORD_MIN_LENGTH", 8);
  define("USERNAME_MIN_LENGTH", 3);
  define("USERNAME_MAX_LENGTH", 30);

  define("TOKEN_KEY_LENGTH", 12);
  define("TOKEN_KEY_STRENGTH", true);

  define("EMAIL_DELAY_TIME", 15);

  class ACCESS_LEVEL {
    //const INACTIVE = -1;
    const ADMIN = 0;
    const DIRECTOR = 1;
    const EMPLOYEE = 2;
    const GUEST = 3;

    //Minimum Access Level Required for pages
    const PAGE_USERS = ACCESS_LEVEL::DIRECTOR;
    const PAGE_CUSTOMERS = ACCESS_LEVEL::GUEST;
    const PAGE_TENDERS = ACCESS_LEVEL::GUEST;
    const PAGE_MENU = ACCESS_LEVEL::GUEST;
    const PAGE_DASHBOARD = ACCESS_LEVEL::GUEST;
    const PAGE_SETTINGS = ACCESS_LEVEL::GUEST;

    public function IS_VALID_ACCESS_LEVEL($accessLevel) {
      if($accessLevel <= ACCESS_LEVEL::GUEST and $accessLevel >= ACCESS_LEVEL::ADMIN) {
        return true;
      }
      return false;
    }

/*
    public function IS_ACTIVE($accessLevel) {
      if(ACCESS_LEVEL::IS_VALID_ACCESS_LEVEL($accessLevel) and $accessLevel != ACCESS_LEVEL::INACTIVE) {
        return true;
      }
      return false;
    } */

    public function HAS_PRIVILEGE($accessLevel, $pageMinLevel) {
      if(ACCESS_LEVEL::IS_VALID_ACCESS_LEVEL($accessLevel) and $accessLevel <= $pageMinLevel) {
        return true;
      }
      return false;
    }
  }
?>
