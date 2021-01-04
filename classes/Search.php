<?php
class Search {
  const COMPANY_PARAM = "company";
  const PERSON_PARAM = "person";
  const TENDER_ENQUIRY_PARAM = "tender-enq";
  const TENDER_DESCRIPTION_PARAM = "tender-desc";
  const OPTIONS_PARAM = "options";
  const SELECTION_PARAM = "selection";
  const PAGE_PARAM = "page";
  const AJAX_LIMIT = 10;
  const DEFAULT_RESULTS_PER_PAGE = 25;
  const NONE = 0;
  const START = 1;
  const END = 2;
  const BOTH = 3;

  private $query;
  private $searchResult;
  private $count;
  private $limit;
  private $startRow;
  private $endRow;
  private $conn;

  function __construct() {
    $this->conn = new Database();
    $this->count = 0;
    $this->limit = false;
    $this->startRow = 0;
    $this->endRow = self::DEFAULT_RESULTS_PER_PAGE;
  }

  public function get_count() {
    return $this->count;
  }

  private function validate_query($query) {
    $this->query = sanitize_input($query);
    if(empty($this->query)) {
      $this->searchResult = null;
      return false;
    }
    else {
      return true;
    }
  }

  private function add_wildcards($constant) {
    switch($constant) {
      case self::NONE:
        break;
      case self::START:
        $this->query = "%" . $this->query;
        break;
      case self::END:
        $this->query = $this->query . "%";
        break;
      case self::BOTH:
        $this->query = "%" . $this->query . "%";
        break;
      default:
        $this->query = "%" . $this->query . "%";
        break;
    }
  }

  private function set_count($table, $param) {
    $stmt = $this->conn->prepare("SELECT COUNT(id) FROM " . $table . " WHERE " . $param . " LIKE ?");
    $stmt->bind_param("s", $this->query);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $this->count = $row["COUNT(id)"];
    }
    else {
      $this->count = 0;
    }
  }

  private function set_limits($page, $resultsPerPage) {
    $page = sanitize_input($page);
    $resultsPerPage = sanitize_input($resultsPerPage);
    if(!($page === null) && !($resultsPerPage === null)) {
      $this->startRow = ($page - 1) * $resultsPerPage;
      $this->endRow = $resultsPerPage;
      $this->limit = true;
    }
    else {
      $this->limit = false;
    }
  }

  public function search_company($query, $constant, $page=null, $resultsPerPage=null) {
    if($this->validate_query($query)) {
      $this->set_limits($page, $resultsPerPage);
      $this->add_wildcards($constant);
      $stmt = null;
      if($this->limit) {
        $stmt = $this->conn->prepare("SELECT * FROM company WHERE name LIKE ? LIMIT ?, ?");
        $stmt->bind_param("sii", $this->query, $this->startRow, $this->endRow);
      }
      else {
        $stmt = $this->conn->prepare("SELECT * FROM company WHERE name LIKE ?");
        $stmt->bind_param("s", $this->query);
      }
      $stmt->execute();
      $result = $stmt->get_result();
      if($result->num_rows > 0) {
        for($i=0;$i<$result->num_rows;$i++) {
          $row = $result->fetch_assoc();
          $id = $row['id'];
          $name = $row['name'];
          $companies[$name] = $id;
        }
        $this->searchResult = $companies;

        $table = "company";
        $param = "name";
        $this->set_count($table, $param);
      }
      else {
        $this->searchResult = null;
      }
    }
    return $this->searchResult;
  }
  public function search_person($query, $constant, $page=null, $resultsPerPage=null) {
    if($this->validate_query($query)) {
      $this->set_limits($page, $resultsPerPage);
      $this->add_wildcards($constant);
      $stmt = null;
      if($this->limit){
        $stmt = $this->conn->prepare("SELECT * FROM person WHERE name LIKE ? LIMIT ?, ?");
        $stmt->bind_param("sii", $this->query, $this->startRow, $this->endRow);
      }
      else {
        $stmt = $this->conn->prepare("SELECT * FROM person WHERE name LIKE ?");
        $stmt->bind_param("s", $this->query);
      }
      $stmt->execute();
      $result = $stmt->get_result();
      if($result->num_rows > 0) {
        for($i=0;$i<$result->num_rows;$i++) {
          $row = $result->fetch_assoc();
          $id = $row['id'];
          $name = $row['name'];
          $companies[$name] = $id;
        }
        $this->searchResult = $companies;

        $table = "person";
        $param = "name";
        $this->set_count($table, $param);
      }
      else {
        $this->searchResult = null;
      }
    }
    return $this->searchResult;

  }

  public function search_enquiry($query, $constant, $page=null, $resultsPerPage=null) {
    if($this->validate_query($query)) {
      $this->set_limits($page, $resultsPerPage);
      $this->add_wildcards($constant);
      $stmt = null;
      if($this->limit) {
        $stmt = $this->conn->prepare("SELECT * FROM tenders WHERE enquiry_number LIKE ? AND active != " . Tender::IGNORE . " LIMIT ?, ?");
        $stmt->bind_param("sii", $this->query, $this->startRow, $this->endRow);
      }
      else {
        $stmt = $this->conn->prepare("SELECT * FROM tenders WHERE enquiry_number LIKE ? AND active != " . Tender::IGNORE);
        $stmt->bind_param("s", $this->query);
      }
      $stmt->execute();
      $result = $stmt->get_result();
      if($result->num_rows > 0) {
        for($i=0;$i<$result->num_rows; $i++) {
          $row = $result->fetch_assoc();
          $id = $row['id'];
          $enquiryNumber = $row['enquiry_number'];
          $tenders[$enquiryNumber] = $id;
        }
        $this->searchResult = $tenders;

        $table = "tenders";
        $param = "enquiry_number";
        $this->set_count($table, $param);
      }
      else {
        $this->searchResult = null;
      }
    }
    return $this->searchResult;
  }

  public function search_description($query, $constant, $page=null, $resultsPerPage=null) {
    if($this->validate_query($query)) {
      $this->set_limits($page, $resultsPerPage);
      $this->add_wildcards($constant);
      $stmt = null;
      if($this->limit) {
        $stmt = $this->conn->prepare("SELECT * FROM tenders WHERE description LIKE ? AND active != " . Tender::IGNORE . " LIMIT ?, ?");
        $stmt->bind_param("sii", $this->query, $this->startRow, $this->endRow);
      }
      else {
        $stmt = $this->conn->prepare("SELECT * FROM tenders WHERE description LIKE ? AND active != " . Tender::IGNORE);
        $stmt->bind_param("s", $this->query);
      }
      $stmt->execute();
      $result = $stmt->get_result();
      if($result->num_rows > 0) {
        for($i=0;$i<$result->num_rows; $i++) {
          $row = $result->fetch_assoc();
          $id = $row['id'];
          $enquiryNumber = $row['description'];
          $tenders[$enquiryNumber] = $id;
        }
        $this->searchResult = $tenders;

        $table = "tenders";
        $param = "description";
        $this->set_count($table, $param);
      }
      else {
        $this->searchResult = null;
      }
    }
    return $this->searchResult;
  }

  public function get_json() {
    if($this->searchResult == null)
      return null;
    $output = '{"results":[';
    $flag = false;
    foreach ($this->searchResult as $key => $value) {
      if($flag) {
        $output .= ",";
      }
      $output .= '{"name":"' . $key . '", "id":"'. $value . '"}';
      $flag = true;
    }
    $output .= "]}";
    return $output;
  }
}

 ?>
