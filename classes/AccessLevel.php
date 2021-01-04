<?php
final class AccessLevel {
  const ADMIN = 0;
  const DIRECTOR = 1;
  const EMPLOYEE = 2;
  const GUEST = 3;

  //Minimum Access Level Required for pages
  const PAGE_USERS = self::DIRECTOR;
  const PAGE_CUSTOMERS = self::GUEST;
  const PAGE_TENDERS = self::GUEST;
  const PAGE_MENU = self::GUEST;
  const PAGE_DASHBOARD = self::GUEST;
  const PAGE_SETTINGS = self::GUEST;
  const PAGE_PUBLIC = self::GUEST;

  public function get_name($constant) {
    if($this->is_valid_access_level($constant)) {
      switch ($constant) {
        case self::ADMIN:
          return "Admin";
        case self::DIRECTOR:
          return "Director";
        case self::EMPLOYEE:
          return "Employee";
        case self::GUEST:
          return "Guest";
      }
    }
  }

  public function is_valid_access_level($accessLevel) {
    if($accessLevel <= self::GUEST and $accessLevel >= self::ADMIN) {
      return true;
    }
    else {
      throw new AppException("Invalid Access Level!", AppException::ACCESS_ERROR);
    }
  }

  public function has_privilege($accessLevel, $pageMinLevel) {
    if(self::is_valid_access_level($accessLevel) and $accessLevel <= $pageMinLevel) {
      return true;
    }
    else {
      return false;
    }
  }
}
?>
