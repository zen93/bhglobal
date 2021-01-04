<?php
  require_once("includes/routines.php");
  const PAGE_ACCESS_LEVEL = AccessLevel::PAGE_CUSTOMERS;
  require_once("includes/validate_session.php");

  if(isset($_GET[Search::COMPANY_PARAM]) || isset($_GET[Search::PERSON_PARAM]) || isset($_GET[Search::TENDER_ENQUIRY_PARAM]) || isset($_GET[Search::TENDER_DESCRIPTION_PARAM])) {
    $count = 0;
    $page = 1;
    $companySearch = $personSearch = $selectionSearch = $tenderEnquiry = $tenderDescription = false;

    if(isset($_GET[Search::SELECTION_PARAM])) {
      $selectionSearch = true;
      $baseCompanyID = $_GET[Search::SELECTION_PARAM];
    }
    if(isset($_GET[Search::COMPANY_PARAM])) {
      $companySearch = true;
      $query = $_GET[Search::COMPANY_PARAM];
      $count++;
    }
    if(isset($_GET[Search::TENDER_ENQUIRY_PARAM])) {
      $tenderEnquiry = true;
      $query = $_GET[Search::TENDER_ENQUIRY_PARAM];
      $count++;
    }
    if(isset($_GET[Search::TENDER_DESCRIPTION_PARAM])) {
      $tenderDescription = true;
      $query = $_GET[Search::TENDER_DESCRIPTION_PARAM];
      $count++;
    }
    if(isset($_GET[Search::PERSON_PARAM])) {
      $personSearch = true;
      $query = $_GET[Search::PERSON_PARAM];
      $count++;
    }
    if(isset($_GET[Search::OPTIONS_PARAM])) {
      $options = $_GET[Search::OPTIONS_PARAM];
    }
    else {
      $options = Search::BOTH;
    }
    if($count > 1){
      die("Error: Cannot set multiple params!");
    }
    if(isset($_GET[Search::PAGE_PARAM])) {
      $page = $_GET[Search::PAGE_PARAM];
    }
    $search = new Search();
    if($companySearch) {
      $result = $search->search_company($query, $options, $page, Search::DEFAULT_RESULTS_PER_PAGE);
      $resultCount = $search->get_count();
    }
    elseif($personSearch) {
      $result = $search->search_person($query, $options, $page, Search::DEFAULT_RESULTS_PER_PAGE);
      $resultCount = $search->get_count();
    }
    elseif($tenderEnquiry) {
      $result = $search->search_enquiry($query, $options, $page, Search::DEFAULT_RESULTS_PER_PAGE);
      $resultCount = $search->get_count();
    }
    elseif($tenderDescription) {
      $result = $search->search_description($query, $options, $page, Search::DEFAULT_RESULTS_PER_PAGE);
      $resultCount = $search->get_count();
    }
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Search | BHI</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css" />
    <link rel="stylesheet" type="text/css" href="css/search.css" />
    <script src="scripts/search.js"></script>
    <style>
    #pagenumbers {
      text-align: center;
      margin: 20px;
    }
    .page {
      padding-left: 3px;
      padding-right: 3px;
    }
    </style>
  </head>
  <body>
    <div id="wrapper">
      <img id="logo" src="img/BHI Logo.jpg" />
      <?php require_once("includes/menu.php"); ?>
      <div id="main">
        <h2>Search Results</h2>
        <h3>Search</h3>
        <input type="text" id="searchbox" />
        <input type="button" id="searchbutton" />
        <div id="livesearch"></div>
        <br>
        <?php if($tenderEnquiry || $tenderDescription) { ?>
          <input type="radio" name="searchtype" value="<?php echo Search::TENDER_ENQUIRY_PARAM; ?>" checked /> Enquiry Number
        <input type="radio" name="searchtype" value="<?php echo Search::TENDER_DESCRIPTION_PARAM; ?>" /> Description
        <?php } else { ?>
        <input type="radio" name="searchtype" value="company" checked /> Company
        <input type="radio" name="searchtype" value="person" /> Person
        <?php } ?>
        <br>
        <hr>
          <?php
            $rcount = 0;
            if(isset($result))
              $rcount = count($result);
            if($rcount == 0) {
              echo "No results";
            }
            else {
              if($companySearch) {
                echo "<table><tr>"
                . "<th>ID</th><th>Name</th><th>City</th><th>Country</th><th>Phone</th><th>Website</th>"
                . "</tr>";
                $company = new Company();
                foreach ($result as $key => $value) {
                  echo "<tr>";
                  $company->load_id($value);
                  $address = $company->get_address();
                  echo "<td>" . $company->get_id() . "</td>";
                  if($selectionSearch)
                    echo "<td><a href='/customers.php?type=relationship&add=".$baseCompanyID ."&selection=";
                  else
                    echo "<td><a href='/customers.php?type=company&view=";
                  echo $company->get_id() . "'>" . $company->get_name() . "</a></td>";
                  echo "<td>" . $address->get_address2() . "</td>";
                  echo "<td>" . $address->get_country() . "</td>";
                  echo "<td>" . $company->get_phone() . "</td>";
                  echo "<td>" . $company->get_website() . "</td>";
                  echo "</tr>";
                }
              }
              elseif($personSearch) {
                echo "<table><tr>"
                . "<th>ID</th><th>Name</th><th>Designation</th><th>Role</th><th>Phone</th><th>Email</th><th>Skype</th>"
                . "</tr>";
                $company = new Company();
                $person = new Person();
                foreach ($result as $key => $value) {
                  echo "<tr>";
                  $person->load_id($value);
                  echo "<td>" . $person->get_id() . "</td>";
                  echo "<td><a href='/customers.php?type=person&view=" . $person->get_id() . "'>" . $person->get_name() . "</a></td>";
                  echo "<td>" . $person->get_designation() . "</td>";
                  echo "<td>" . (new PersonRole)->get_name($person->get_role())  . "</td>";
                  echo "<td>" . $person->get_phone() . "</td>";
                  echo "<td>" . $person->get_email() . "</td>";
                  echo "<td>" . $person->get_skype() . "</td>";
                  echo "</tr>";
                }
              }
              elseif($tenderEnquiry || $tenderDescription) {
                echo "<table><tr>"
                . "<th>ID</th><th>Enquiry Number</th><th>Description</th><th>Posted On</th><th>Deadline</th>"
                . "</tr>";
                $tender = new Tender();
                foreach($result as $key => $value) {
                  echo "<tr>";
                  $tender->load_id($value);
                  echo "<td>" . $tender->get_id() . "</td>";
                  echo "<td>" . get_truncated_string($tender->get_enquiry_num(), Tender::ENQUIRY_STRING_LENGTH) . "</td>";
                  echo "<td><a href='/tenders.php?view=" . $tender->get_id() . "'>" . get_truncated_string($tender->get_description(), Tender::DESCRIPTION_STRING_LENGTH) . "</a></td>";
                  echo "<td>" . $tender->get_posted_on() . "</td>";
                  echo "<td>" . $tender->get_deadline() . "</td>";
                  echo "</tr>";
                }
              }
            }
          ?>
        </table>
        <?php
          if($tenderEnquiry || $tenderDescription || $companySearch || $personSearch) {
            $pageName = "search.php";
            $paginator = new Paginator($page, $resultCount, Search::DEFAULT_RESULTS_PER_PAGE);
            echo $paginator->get_pages_div($pageName, $_SERVER["QUERY_STRING"]);
          }
        ?>
      </div>
    </div>
  </body>
</html>
