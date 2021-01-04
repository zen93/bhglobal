<?php
final class CompanyType {
  const SHIP_BUILDER = 0;
  const SHIP_OWNER = 1;
  const SHIP_REPAIRER = 2;
  const SYSTEM_INTEGRATOR = 3;
  const MANUFACTURER = 4;
  const DEALERS = 5;
  const TECH_SERVICE_PROVIDER = 6;
  const OTHERS = 7;

  public function is_valid($type) {
    if($type >= self::SHIP_BUILDER && $type <= self::OTHERS) {
      return true;
    }
    else {
      return false;
    }
  }

  public function get_name($constant) {
    if($this->is_valid($constant)) {
      switch($constant) {
        case self::SHIP_BUILDER:
          return "Ship Builder";
        case self::SHIP_OWNER:
          return "Ship Owner";
        case self::SHIP_REPAIRER:
          return "Ship Repairer";
        case self::SYSTEM_INTEGRATOR:
          return "System Integrator";
        case self::MANUFACTURER:
          return "Manufacturer";
        case self::DEALERS:
          return "Dealers/Traders";
        case self::TECH_SERVICE_PROVIDER:
          return "Tech. Service Provider";
        case self::OTHERS:
        return "Others";
      }
    }
  }
}

final class CompanySector {
  const DEFENCE = 0;
  const COMMERCIAL = 1;
  const OTHERS = 2;

  public function is_valid($sector) {
    if($sector >= self::DEFENCE && $sector <= self::OTHERS) {
      return true;
    }
    else {
      return false;
    }
  }

  public function get_name($constant) {
     if($this->is_valid($constant)) {
       switch($constant) {
         case self::DEFENCE:
          return "Defence";
         case self::COMMERCIAL:
          return "Commercial";
        case self::OTHERS:
        return "Others";
       }
     }
  }
}

final class CompanyOwnership {
  const PRIVATE_COMPANY = 0;
  const CENTRAL_GOVT = 1;
  const STATE_GOVT = 2;
  const OTHERS = 3;

  public function is_valid($ownership) {
    if($ownership >= self::PRIVATE_COMPANY && $ownership <= self::OTHERS) {
      return true;
    }
    else {
      return false;
    }
  }

  public function get_name($constant) {
    if($this->is_valid($constant)) {
      switch($constant) {
        case self::PRIVATE_COMPANY:
          return "Private";
        case self::CENTRAL_GOVT:
          return "Central Govt.";
        case self::STATE_GOVT:
          return "State Govt.";
        case self::OTHERS:
        return "Others";
      }
    }
  }
}

class Company {
  const NAME_MAX_LENGTH = 120;
  const PHONE_MAX_LENGTH = 15;
  const PHONE_MIN_LENGTH = 8;
  const WEBSITE_MIN_LENGTH = 4;
  const WEBSITE_MAX_LENGTH = 255;


  private $id;
  private $name;
  private $address;
  private $type;
  private $sector;
  private $ownership;
  private $phone;
  private $website;

  private $conn;
  private $hasDetails;

  function __construct() {
    $this->address = new Address();
    $this->conn = new Database();
    $this->hasDetails = false;
  }
  private function has_details() {
    if($this->hasDetails)
      return true;
    else {
      throw new AppException("Company not loaded", AppException::COMPANY_ERROR);
    }
  }

  public function get_address() {
    if($this->has_details())
      return $this->address;
  }
  public function get_id() {
    if($this->has_details())
      return $this->id;
  }
  public function get_name() {
    if($this->has_details())
      return htmlspecialchars($this->name);
  }
  public function get_phone() {
    if($this->has_details())
      return htmlspecialchars($this->phone);
  }
  public function get_website() {
    if($this->has_details())
      return htmlspecialchars($this->website);
  }
  public function get_type() {
    if($this->has_details())
      return $this->type;
  }
  public function get_sector() {
    if($this->has_details())
      return $this->sector;
  }
  public function get_ownership() {
    if($this->has_details())
      return $this->ownership;
  }

  private function set_details($row) {
    if(isset($row)) {
      $this->id = $row["id"];
      $this->name = $row["name"];
      $this->type = $row["type"];
      $this->sector = $row["sector"];
      $this->ownership = $row["ownership"];
      $this->phone = $row["phone"];
      $this->website = $row["website"];

      $this->hasDetails = true;
    }
    else {
      $this->hasDetails = false;
      throw new AppException("Must provide company details: set_details()", AppException::COMPANY_ERROR);
    }
  }

  public function load_id($id) {
    $this->id = sanitize_input($id);

    $stmt = $this->conn->prepare("SELECT * FROM company WHERE id = ?");
    $stmt->bind_param("s", $this->id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $this->set_details($row);
      $this->address->load_company_id($this->id);
    }
    return $this->hasDetails;
  }

  public function get_all_ids() {
    //returns all company ids
    $stmt = $this->conn->prepare("SELECT * FROM company");
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
      for($i=0;$i<$result->num_rows;$i++) {
        $row = $result->fetch_assoc();
        $id[$i] = $row["id"];
      }
      return $id;
    }
    else {
      throw new AppException("No companies found!", AppException::COMPANY_ERROR);
    }
  }

/*  public function get_all_child_company_ids() {
    //returns all child company ids
    if($this->has_details()) {
      $stmt = $this->conn->prepare("SELECT child_id FROM relationships WHERE parent_id = ?");
      $stmt->bind_param("i", $this->id);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows>0) {
        for($i=0;$i<$result->num_rows;$i++) {
          $row = $result->fetch_assoc();
          $ids[$i] = $row["id"];
        }
        return $ids;
      }
      else {
        throw new AppException("No child companies found", AppException::COMPANY_ERROR);
      }
    }
  }

  public function get_all_parent_company_ids() {
    //returns all parent company ids
    if($this->has_details()) {
      $stmt = $this->conn->prepare("SELECT parent_id FROM relationships WHERE child_id = ?");
      $stmt->bind_param("i", $this->id);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows>0) {
        for($i=0;$i<$result->num_rows;$i++) {
          $row = $result->fetch_assoc();
          $ids[$i] = $row["id"];
        }
        return $ids;
      }
      else {
        throw new AppException("No parent companies found", AppException::COMPANY_ERROR);
      }
    }
  } */

  public function get_all_person_ids() {
    //returns all employee ids
    if($this->has_details()) {
      $stmt = $this->conn->prepare("SELECT id FROM person WHERE company_id = ?");
      $stmt->bind_param("i", $this->id);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows > 0) {
        for($i=0;$i<$result->num_rows;$i++) {
          $row = $result->fetch_assoc();
          $ids[$i] = $row["id"];
        }
        return $ids;
      }
      else {
        throw new AppException("No persons found", AppException::PERSON_ERROR);
      }
    }
  }

  private function validate_details($name, $type, $sector, $ownership, $phone, $website) {
    //validates company details
    $this->name = sanitize_input($name);
    $this->type = sanitize_input($type);
    // $this->role = sanitize_input($role);
    $this->sector = sanitize_input($sector);
    $this->ownership = sanitize_input($ownership);
    $this->phone = sanitize_input($phone);
    $this->website = sanitize_input($website);

    if(empty($this->name))
      throw new AppException("Company Name cannot be empty", AppException::COMPANY_ERROR);
    elseif(strlen($this->name) > self::NAME_MAX_LENGTH)
      throw new AppException("Company name cannot be greater than " . self::NAME_MAX_LENGTH . " characters", AppException::COMPANY_ERROR);

    if(!isset($this->type))
      throw new AppException("Company type cannot be empty", AppException::COMPANY_ERROR);
    elseif(!(new CompanyType())->is_valid($this->type))
      throw new AppException("Company type is invalid", AppException::COMPANY_ERROR);

    if(!isset($this->sector))
      throw new AppException("Company sector cannot be empty", AppException::COMPANY_ERROR);
    elseif(!(new CompanySector())->is_valid($this->sector))
      throw new AppException("Company sector is invalid", AppException::COMPANY_ERROR);

    if(!isset($this->ownership)) // 0 equals empty
      throw new AppException("Company ownership cannot be empty", AppException::COMPANY_ERROR);
    elseif(!(new CompanyOwnership())->is_valid($this->ownership))
      throw new AppException("Company ownership is invalid", AppException::COMPANY_ERROR);

    if(empty($this->phone))
      throw new AppException("Company phone cannot be empty", AppException::COMPANY_ERROR);
    elseif(strlen($this->phone) < self::PHONE_MIN_LENGTH || strlen($this->phone) > self::PHONE_MAX_LENGTH)
      throw new AppException("Company phone cannot be less than " . self::PHONE_MIN_LENGTH . " or greater than " . self::PHONE_MAX_LENGTH . " characters", AppException::COMPANY_ERROR);

    if(empty($this->website))
      throw new AppException("Company website cannot be empty", AppException::COMPANY_ERROR);
    elseif(strlen($this->website) < self::WEBSITE_MIN_LENGTH || strlen($this->website) > self::WEBSITE_MAX_LENGTH)
      throw new AppException("Company website cannot be less than " . self::WEBSITE_MIN_LENGTH . " or greater than " . self::WEBSITE_MAX_LENGTH . " characters", AppException::COMPANY_ERROR);

    return true;
  }

  public function add_company($name, $type, $sector, $ownership, $phone, $website, $address1, $address2, $address3, $country, $pincode) {
    $this->validate_details($name, $type, $sector, $ownership, $phone, $website);

    $stmt = $this->conn->prepare("INSERT INTO company(name, type, sector, ownership, phone, website) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiiss", $this->name, $this->type, $this->sector, $this->ownership, $this->phone, $this->website);
    $result = $stmt->execute();

    if(!$result)
      throw new AppException("Cannot insert company details in DB", AppException::COMPANY_ERROR);

    $id =$stmt->insert_id;
    try {
      if($this->address->add_address($id, $address1, $address2, $address3, $country, $pincode)) {
        return true;
      }
    }
    catch(AppException $e) {
      //rollback changes
      $this->load_id($id);
      $this->delete_company();
      throw new AppException($e->message(), $e->get_code());
    }
  }

  public function edit_company($name, $type, $sector, $ownership, $phone, $website, $address1, $address2, $address3, $country, $pincode) {
    if($this->has_details()) {
      $this->validate_details($name, $type, $sector, $ownership, $phone, $website);
      $this->address->edit_address($this->id, $address1, $address2, $address3, $country, $pincode);

      $stmt = $this->conn->prepare("UPDATE company SET name = ?, type = ?, sector = ?, ownership = ?, phone = ?, website = ? WHERE id = ?");
      $stmt->bind_param("siiissi", $this->name, $this->type, $this->sector, $this->ownership, $this->phone, $this->website, $this->id);
      $result = $stmt->execute();

      if(!$result)
        throw new AppException("Cannot update company details", AppException::COMPANY_ERROR);
      else {
        return true;
      }
    }
  }

  public function delete_company() {
    //Delete company
    if($this->has_details()) {
        $stmt = $this->conn->prepare("DELETE FROM company WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();

        if($result == false)
          throw new AppException("Cannot delete company!", AppException::COMPANY_ERROR);
        else {
          return true;
        }
    }
  }
}
?>
