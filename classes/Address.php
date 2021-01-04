<?php
class Address {
  const PINCODE_MAX_LENGTH = 12;
  const ADDRESS_MAX_LENGTH = 255;
  const COUNTRY_MAX_LENGTH = 255;

  private $id;
  private $companyID;
  private $address1;
  private $address2;
  private $address3;
  private $country;
  private $pincode;

  private $conn;
  private $hasDetails;

  function __construct() {
    $this->conn = new Database();
    $this->hasDetails = false;

    $this->id = null;
    $this->companyID = null;
    $this->address1 = null;
    $this->address2 = null;
    $this->address3 = null;
    $this->address3 = null;
    $this->country = null;
    $this->pincode = null;
  }

  public function get_address1() {
    if($this->has_details())
      return htmlspecialchars($this->address1);
  }
  public function get_address2() {
    if($this->has_details())
      return htmlspecialchars($this->address2);
  }
  public function get_address3() {
    if($this->has_details())
      return htmlspecialchars($this->address3);
  }
  public function get_country() {
    if($this->has_details())
      return htmlspecialchars($this->country);
  }
  public function get_pincode() {
    if($this->has_details())
      return htmlspecialchars($this->pincode);
  }


  private function has_details() {
    if($this->hasDetails)
      return true;
    else
      throw new AppException("Address not loaded", AppException::ADDRESS_ERROR);
  }

  private function set_details($row) {
    if(isset($row)) {
      $this->id = $row["id"];
      $this->companyID = $row["company_id"];
      $this->address1 = $row["address1"];
      $this->address2 = $row["address2"];
      if(isset($row["address3"]))
        $this->address3 = $row["address3"];
      $this->country = $row["country"];
      if(isset($row["address2"]))
        $this->pincode = $row["pincode"];

      $this->hasDetails = true;
    }
    else {
      $this->hasDetails = false;
      throw new AppException("Must provide address details: set_details()", AppException::ADDRESS_ERROR);
    }
  }

  public function load_id($id) {
    $this->id = sanitize_input($id);

    $stmt = $this->conn->prepare("SELECT * FROM address WHERE id = ?");
    $stmt->bind_param("s", $this->id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $this->set_details($row);
    }
    return $this->hasDetails;
  }

  public function load_company_id($companyID) {
    $this->companyID = sanitize_input($companyID);

    $stmt = $this->conn->prepare("SELECT * FROM address WHERE company_id = ?");
    $stmt->bind_param("s", $this->companyID);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $this->set_details($row);
    }
    return $this->hasDetails;
  }

  private function address_exists($companyID) {
    $companyID = sanitize_input($companyID);
    if(!empty($companyID)) {
      $stmt = $this->conn->prepare("SELECT * FROM address WHERE company_id = ?");
      $stmt->bind_param("s", $companyID);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows > 0) {
        return true;
      }
    }
    return false;
  }

  private function validate_details($companyID, $address1, $address2, $address3, $country, $pincode) {
    $this->companyID = sanitize_input($companyID);
    $this->address1 = sanitize_input($address1);
    $this->address2 = sanitize_input($address2);
    $this->address3 = sanitize_input($address3);
    $this->country = sanitize_input($country);
    $this->pincode = sanitize_input($pincode);

    if(!isset($this->companyID)) {
      throw new AppException("Company ID not set", AppException::ADDRESS_ERROR);
    }
    if(empty($this->address1)) {
      throw new AppException("Address Line 1 cannot be empty", AppException::ADDRESS_ERROR);
    }
    if(empty($this->address2)) {
      throw new AppException("Address Line 2 cannot be empty", AppException::ADDRESS_ERROR);
    }
    if(strlen($address1) > self::ADDRESS_MAX_LENGTH || strlen($address2) > self::ADDRESS_MAX_LENGTH || strlen($address3) > self::ADDRESS_MAX_LENGTH) {
      throw new AppException("Address Line must be less than " . self::ADDRESS_MAX_LENGTH ." characters", AppException::ADDRESS_ERROR);
    }
    if(empty($this->country)) {
      throw new AppException("Country cannot be empty", AppException::ADDRESS_ERROR);
    }
    if(strlen($this->country) > self::COUNTRY_MAX_LENGTH) {
      throw new AppException("Country must be less than " . self::COUNTRY_MAX_LENGTH . " characters", AppException::ADDRESS_ERROR);
    }
    if(strlen($this->pincode) > self::PINCODE_MAX_LENGTH) {
      throw new AppException("Pincode cannot be greater than 12 characters", AppException::ADDRESS_ERROR);
    }
    return true;
  }

  public function add_address($companyID, $address1, $address2, $address3, $country, $pincode) {
    $this->validate_details($companyID, $address1, $address2, $address3, $country, $pincode);
    if($this->address_exists($this->companyID))
      throw new AppException("Address for this company already exists", AppException::ADDRESS_ERROR);

    $stmt = $this->conn->prepare("INSERT INTO address(company_id, address1, address2, address3, country, pincode) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $this->companyID, $this->address1, $this->address2, $this->address3, $this->country, $this->pincode);
    $result = $stmt->execute();

    if(!$result)
      throw new AppException("Cannot insert address", AppException::ADDRESS_ERROR);
    else
      return true;
  }

  public function edit_address($companyID, $address1, $address2, $address3, $country, $pincode) {
    if($this->has_details()) {
      $this->validate_details($companyID, $address1, $address2, $address3, $country, $pincode);

      $stmt = $this->conn->prepare("UPDATE address SET address1 = ?, address2 = ?, address3 = ?, country = ?, pincode = ? WHERE id = ?");
      $stmt->bind_param("sssssi", $this->address1, $this->address2, $this->address3, $this->country, $this->pincode, $this->id);
      $result = $stmt->execute();
      if(!$result)
        throw new AppException("Cannot update address!", AppException::ADDRESS_ERROR);
      else {
        return true;
      }
    }

  }

  public function delete_address() {
    if($this->has_details()) {
      $stmt = $this->conn->prepare("DELETE FROM address WHERE id = ?");
      $stmt->bind_param("i", $this->id);
      $result = $stmt->execute();
      if($result == false)
        throw new AppException("Could not delete address", AppException::ADDRESS_ERROR);
      else
        return true;
    }
  }

}
?>
