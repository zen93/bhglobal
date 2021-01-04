<?php
final class RelationshipType {
  const BRANCH = 0;
  const SUBSIDIARY = 1;
  const SUPPORT = 2;
  const OTHERS = 3;

  public function is_valid($relationship) {
    if($relationship >= self::BRANCH && $relationship <= self::OTHERS) {
      return true;
    }
    else {
      return false;
    }
  }

  public function get_name($constant) {
    if($this->is_valid($constant)) {
      switch($constant) {
        case self::BRANCH:
          return "Branch";
        case self::SUBSIDIARY:
          return "Subsidiary";
        case self::SUPPORT:
          return "Support";
        case self::OTHERS:
          return "Others";
      }
    }
  }
}

class Relationship {
  private $id;
  private $parentID;
  private $childID;
  private $type;

  private $conn;
  private $hasDetails;

  function __construct() {
    $this->conn = new Database();
    $this->hasDetails = false;
  }

  public function get_id() {
    if($this->has_details())
      return $this->id;
  }
  public function get_parent_id() {
    if($this->has_details())
      return $this->parentID;
  }
  public function get_child_id() {
    if($this->has_details())
      return $this->childID;
  }
  public function get_type() {
    if($this->has_details())
      return htmlspecialchars($this->type);
  }
  public function get_all_children($companyID) {
    //returns all child company ids
    $companyID = sanitize_input($companyID);

    $stmt = $this->conn->prepare("SELECT id FROM relationships WHERE parent_id = ?");
    $stmt->bind_param("i", $companyID);
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
      throw new AppException("No child companies found", AppException::RELATIONSHIP_ERROR);
    }
  }

  public function get_all_parent($companyID) {
    //returns all child company ids
    $companyID = sanitize_input($companyID);

    $stmt = $this->conn->prepare("SELECT id FROM relationships WHERE child_id = ?");
    $stmt->bind_param("i", $companyID);
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
      throw new AppException("No parent companies found", AppException::RELATIONSHIP_ERROR);
    }
  }

  private function has_details() {
    if($this->hasDetails)
      return true;
    else {
      throw new AppException("Relationship not loaded", AppException::RELATIONSHIP_ERROR);
    }
  }

  private function relationship_exists() {
    $stmt = $this->conn->prepare("SELECT * FROM relationships WHERE parent_id = ? AND child_id = ? AND type = ?");
    $stmt->bind_param("iii", $this->parentID, $this->childID, $this->type);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
      return false;
    }
    else {
      return true;
    }
  }

  private function validate_details($parentID, $childID, $type) {
    $this->parentID = sanitize_input($parentID);
    $this->childID = sanitize_input($childID);
    $this->type = sanitize_input($type);

    if ((!isset($this->parentID))) {
      throw new AppException("Parent empty", AppException::RELATIONSHIP_ERROR);
    }
    if ((!isset($this->childID))) {
      throw new AppException("Child empty", AppException::RELATIONSHIP_ERROR);
    }
    if((!isset($this->type))) {
      throw new AppException("Type empty", AppException::RELATIONSHIP_ERROR);
    }
    if(!(new RelationshipType)->is_valid($this->type))
      throw new AppException("Invalid relationship type", AppException::RELATIONSHIP_ERROR);
    if($parentID == $childID)
      throw new AppException("Cannot add relationship to self!", AppException::RELATIONSHIP_ERROR);
    if(!$this->relationship_exists())
      throw new AppException("Relationship already exists!", AppException::RELATIONSHIP_ERROR);

    return true;
  }

  public function load_id($id) {
    $this->id = sanitize_input($id);

    $stmt = $this->conn->prepare("SELECT * FROM relationships WHERE id = ?");
    $stmt->bind_param("i", $this->id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $this->id = $row["id"];
      $this->parentID = $row["parent_id"];
      $this->childID = $row["child_id"];
      $this->type = $row["type"];

      $this->hasDetails = true;
      return true;
    }
    else {
      $this->hasDetails = false;
      throw new AppException("Cannot load relationship", AppException::RELATIONSHIP_ERROR);
    }
  }

  public function add_relationship($parentID, $childID, $type) {
    $this->validate_details($parentID, $childID, $type);

    $stmt = $this->conn->prepare("INSERT INTO relationships(parent_id, child_id, type) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $this->parentID, $this->childID, $this->type);
    $result = $stmt->execute();
    if(!$result)
      throw new AppException("Cannot add relationship to DB", AppException::RELATIONSHIP_ERROR);
    return true;
  }

  public function delete_relationship() {
    if($this->has_details()) {
      $stmt = $this->conn->prepare("DELETE FROM relationships WHERE id = ?");
      $stmt->bind_param("i", $this->id);
      $result = $stmt->execute();
      if(!$result)
        throw new AppException("Cannot delete relationship", AppException::RELATIONSHIP_ERROR);
      return true;
    }
  }
}
?>
