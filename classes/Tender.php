<?php
class Tender {
  const DESCRIPTION_STRING_LENGTH = 40;
  const ENQUIRY_STRING_LENGTH = 30;

  const ACTIVE = 1;
  const INACTIVE = 0;
  const IGNORE = -1;

  private $id;
  private $urlid;
  private $enquiryNum;
  private $description;
  private $postedOn;
  private $deadline;
  private $active;
  private $link;
  private $count;

  private $archive;
  private $conn;
  private $hasDetails;

  function __construct() {
    $this->conn = new Database();
    $this->hasDetails = false;
    $this->archive = false;
  }

  private function has_details() {
    if($this->hasDetails)
      return true;
    else {
      throw new AppException("Tender not loaded", AppException::TENDER_ERROR);
    }
  }

  public function get_id() {
    if($this->has_details())
      return $this->id;
  }
  public function get_url_id() {
    if($this->has_details())
      return $this->urlid;
  }
  public function get_enquiry_num() {
    if($this->has_details())
      return $this->enquiryNum;
  }
  public function get_description() {
    if($this->has_details())
      return $this->description;
  }
  public function get_posted_on() {
    if($this->has_details())
      return $this->postedOn;
  }
  public function get_deadline() {
    if($this->has_details())
      return $this->deadline;
  }
  public function get_active() {
    if($this->has_details())
      return $this->active;
  }
  public function get_link() {
    if($this->has_details())
      return $this->link;
  }
  public function get_count() {
    if(!isset($this->count))
      $this->load_count();
    return $this->count;
  }

  public function set_archive($archive) {
    $this->archive = $archive;
  }

  private function load_link($urlid) {
    $urlid = sanitize_input($urlid);

    $stmt = $this->conn->prepare("SELECT * FROM tender_url WHERE id = ?");
    $stmt->bind_param("s", $urlid);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      $this->link["id"] = $row["id"];
      $this->link["companyName"] = $row["company_name"];
      $this->link["url"] = $row["url"];
      $this->link["lastModified"] = $row["last_modified"];
    }
    else
      throw new AppException("Invalid url id", AppException::TENDER_ERROR);
  }

  private function load_count() {
    if($this->archive)
      $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tenders WHERE active = " . Tender::INACTIVE);
    else
      $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tenders WHERE active = " . Tender::ACTIVE);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $this->count = $row["COUNT(*)"];
    }
    else {
      throw new AppException("Cannot count rows in tenders", AppException::TENDER_ERROR);

    }
  }

  public function load_id($id) {
    $this->id = sanitize_input($id);

    $stmt = $this->conn->prepare("SELECT * FROM tenders WHERE id = ?");
    $stmt->bind_param("s", $this->id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      $this->id = $row["id"];
      $this->urlid = $row["url_id"];
      $this->enquiryNum = $row["enquiry_number"];
      $this->description = $row["description"];
      $this->postedOn = $row["posted_on"];
      $this->deadline = $row["deadline"];
      $this->active = $row["active"];
      $this->load_link($this->urlid);

      $this->hasDetails = true;
    }
    else
      throw new AppException("Cannot load tender", AppException::TENDER_ERROR);
  }

  public function get_all_ids() {
    $stmt = $this->conn->prepare("SELECT id FROM tenders ORDER BY posted_on DESC");
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
      throw new AppException("No tenders found", AppException::TENDER_ERROR);
    }
  }

  public function get_page_ids($page, $resultsPerPage) {
    $page = sanitize_input($page);
    $resultsPerPage = sanitize_input($resultsPerPage);
    $startRow = ($page - 1) * $resultsPerPage;

    if($this->archive)
      $stmt = $this->conn->prepare("SELECT id FROM tenders WHERE active = '". Tender::INACTIVE . "' ORDER BY posted_on DESC LIMIT ? , ?");
    else
      $stmt = $this->conn->prepare("SELECT id FROM tenders WHERE active = '" . Tender::ACTIVE . "' ORDER BY posted_on DESC LIMIT ? , ?");
    $stmt->bind_param("ii", $startRow, $resultsPerPage);
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
      throw new AppException("No tenders found", AppException::TENDER_ERROR);
    }
  }

  public function set_active($active) {
    $this->active = sanitize_input($active);

    if($this->has_details()) {
      if($this->active >= Tender::IGNORE and $this->active <= Tender::ACTIVE) {
        $stmt = $this->conn->prepare("UPDATE tenders SET active = ? WHERE id = ?");
        $stmt->bind_param("ss", $active, $this->get_id());
        $result = $stmt->execute();

        if(!$result)
          throw new AppException("Could not update tender mode", AppException::TENDER_ERROR);
        else {
          return true;
        }
      }
      else {
        throw new AppException("Invalid active type", AppException::TENDER_ERROR);
      }
    }
  }
}
 ?>
