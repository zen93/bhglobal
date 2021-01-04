<?php
final class PersonRole {
  const TECHNICAL = 0;
  const COMMERCIAL = 1;
  const FINANCE = 2;
  const INSTALLATION  = 3;
  const OTHERS = 4;

  public function is_valid($role) {
    if($role >= self::TECHNICAL && $role <= self::OTHERS) {
      return true;
    }
    else {
      return false;
      // throw new AppException("Contact person role is invalid", AppException::PERSON_ERROR);
    }
  }
  public function get_name($constant) {
    if($this->is_valid($constant)) {
      switch ($constant) {
        case self::TECHNICAL:
          return "Technical/Design";
        case self::COMMERCIAL:
          return "Commercial/Purchase";
        case self::FINANCE:
          return "Finance";
        case self::INSTALLATION:
          return "Installation";
        case self::OTHERS:
          return "Others";
      }
    }
  }
}

class Person {
  const NAME_MAX_LENGTH = 100;
  const DESG_MAX_LENGTH = 50;
  const PHONE_MAX_LENGTH = 15;
  const SKYPE_MAX_LENGTH = 32;
  const EMAIL_MAX_LENGTH = 255;

  private $id;
  private $companyID;
  private $name;
  private $designation;
  private $role;
  private $phone;
  private $email;
  private $skype;
  private $notes;
  private $contact;
  private $updates;

  private $conn;
  private $hasDetails;

  function __construct() {
    $this->conn = new Database();
    $this->hasDetails = false;

    $this->email = null;
    $this->skype = null;
    $this->notes = null;
    $this->contact = null;
    $this->updates = null;
  }

  private function has_details() {
    if($this->hasDetails)
      return true;
    else
      throw new AppException("Person not loaded", AppException::PERSON_ERROR);
  }

  public function get_id() {
    if($this->has_details())
      return $this->id;
  }
  public function get_companyid() {
    if($this->has_details())
      return $this->companyID;
  }
  public function get_name() {
    if($this->has_details())
      return htmlspecialchars($this->name);
  }
  public function get_role() {
    if($this->has_details())
      return $this->role;
  }
  public function get_designation() {
    if($this->has_details())
      return htmlspecialchars($this->designation);
  }
  public function get_phone() {
    if($this->has_details())
      return htmlspecialchars($this->phone);
  }
  public function get_email() {
    if($this->has_details())
      return htmlspecialchars($this->email);
  }
  public function get_skype() {
    if($this->has_details())
      return htmlspecialchars($this->skype);
  }
  public function get_notes() {
    if($this->has_details())
      return htmlspecialchars($this->notes);
  }
  public function get_contact() {
    if($this->has_details())
      return htmlspecialchars($this->contact);
  }
  public function get_updates() {
    if($this->has_details())
      return htmlspecialchars($this->updates);
  }

  private function set_details($row) {
    if(isset($row)) {
      $this->id = $row["id"];
      $this->companyID = $row["company_id"];
      $this->name = $row["name"];
      $this->designation = $row["designation"];
      $this->role = $row["role"];
      $this->phone = $row["phone"];
      if(isset($row["email"]))
        $this->email = $row["email"];
      if(isset($row["skype"]))
        $this->skype = $row["skype"];
      if(isset($row["notes"]))
        $this->notes = $row["notes"];
      if(isset($row["contact"]))
        $this->contact = $row["contact"];
      if(isset($row["updates"]))
        $this->updates = $row["updates"];

      $this->hasDetails = true;
    }
    else {
      $this->hasDetails = false;
      throw new AppException("Must provide person details: set_details()", AppException::PERSON_ERROR);
    }
  }
  public function load_id($id) {
    $this->id = sanitize_input($id);

    $stmt = $this->conn->prepare("SELECT * FROM person WHERE id = ?");
    $stmt->bind_param("s", $this->id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $this->set_details($row);
    }
    return $this->hasDetails;
  }

  public function get_all_ids() {
    $id = null;
    $stmt = $this->conn->prepare("SELECT id FROM person");
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      for($i=0;$i<$result->num_rows;$i++) {
        $row = $result->fetch_assoc();
        $id[$i] = $row["id"];
      }
      return $id;
    }
  }

  private function validate_details($companyID, $name, $designation, $role, $phone, $email, $skype, $notes, $contact, $updates) {
    $this->companyID = sanitize_input($companyID);
    $this->name = sanitize_input($name);
    $this->designation = sanitize_input($designation);
    $this->role = sanitize_input($role);
    $this->phone = sanitize_input($phone);
    $this->email = sanitize_input($email);
    $this->skype = sanitize_input($skype);
    $this->notes = sanitize_input($notes);
    $this->contact = sanitize_input($contact);
    $this->updates = sanitize_input($updates);

    if(empty($this->companyID)) {
      throw new AppException("Company ID not set", AppException::PERSON_ERROR);
    }
    if(empty($this->name)) {
      throw new AppException("Name cannot be empty", AppException::PERSON_ERROR);
    }
    if(strlen($this->name) > self::NAME_MAX_LENGTH) {
      throw new AppException("Name cannot be greater than " . self::NAME_MAX_LENGTH . " characters", AppException::PERSON_ERROR);
    }
    if(empty($this->designation)) {
      throw new AppException("Designation cannot be empty", AppException::PERSON_ERROR);
    }
    if(strlen($this->designation) > self::DESG_MAX_LENGTH) {
      throw new AppException("Designation cannot be greater than " . self::DESG_MAX_LENGTH . " characters", AppException::PERSON_ERROR);
    }
    if(!isset($this->role)) {
      throw new AppException("Role cannot be empty", AppException::PERSON_ERROR);
    }
    if(empty($this->phone)) {
      throw new AppException("Phone cannot be empty", AppException::PERSON_ERROR);
    }
    if(strlen($this->phone) > self::PHONE_MAX_LENGTH) {
      throw new AppException("Phone cannot be greater than " . self::PHONE_MAX_LENGTH . " characters", AppException::PERSON_ERROR);
    }

    (new PersonRole())->is_valid($this->role);

    return true;

  }

  public function add_person($companyID, $name, $designation, $role, $phone, $email = null, $skype = null, $notes = null, $contact = null, $updates = null ) {
    $this->validate_details($companyID, $name, $designation, $role, $phone, $email, $skype, $notes, $contact, $updates);

    $stmt = $this->conn->prepare("INSERT INTO person(company_id, name, designation, role, phone, email, skype, notes, contact, updates) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississssss", $this->companyID, $this->name, $this->designation, $this->role, $this->phone, $this->email, $this->skype, $this->notes, $this->contact, $this->updates);
    $result = $stmt->execute();

    if(!$result)
      throw new AppException("Cannot insert person in DB", AppException::PERSON_ERROR);
    else
      return true;
  }

  public function edit_person($name, $designation, $role, $phone, $email, $skype, $notes, $contact, $updates) {
    if($this->has_details()) {
      $this->validate_details($this->companyID, $name, $designation, $role, $phone, $email, $skype, $notes, $contact, $updates);

      $stmt = $this->conn->prepare("UPDATE person SET name = ?, designation = ?, role = ?, phone = ?, email = ?, skype = ?, notes = ?, contact = ?, updates = ? WHERE id = ?");
      $stmt->bind_param("ssissssssi", $this->name, $this->designation, $this->role, $this->phone, $this->email, $this->skype, $this->notes, $this->contact, $this->updates, $this->id);
      $result = $stmt->execute();

      if(!$result)
        throw new AppException("Cannot update person", AppException::PERSON_ERROR);
      else
        return true;
    }
  }

  public function delete_person() {
    if($this->has_details()) {
      $stmt = $this->conn->prepare("DELETE FROM person WHERE id = ?");
      $stmt->bind_param("i", $this->id);
      $result = $stmt->execute();
      if($result == false)
        throw new AppException("Could not delete person", AppException::PERSON_ERROR);
      else
        return true;
    }
  }

}
?>
