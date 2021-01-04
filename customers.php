<?php
require_once("includes/routines.php");

const PAGE_ACCESS_LEVEL = AccessLevel::PAGE_CUSTOMERS;
require_once("includes/validate_session.php");

require_once("classes/Company.php");
require_once("classes/Person.php");
require_once("classes/Relationship.php");

const TYPE_PARAM = "type";
const ADD_PARAM = "add";
const EDIT_PARAM = "edit";
const DELETE_PARAM = "delete";
const VIEW_PARAM = "view";

const COMPANY_NAME_PARAM = "cname";
const COMPANY_TYPE_PARAM = "ctype";
// const COMPANY_ROLE_PARAM = "crole";
const COMPANY_SECTOR_PARAM = "csector";
const COMPANY_OWNERSHIP_PARAM = "cownership";
const COMPANY_PHONE_PARAM = "cphone";
const COMPANY_WEBSITE_PARAM = "cwebsite";

const PERSON_COMPANY_ID_PARAM = "pcompanyid";
const PERSON_NAME_PARAM = "pname";
const PERSON_ROLE_PARAM = "prole";
const PERSON_PHONE_PARAM = "pphone";
const PERSON_DESIGNATION_PARAM = "pdesignation" ;
const PERSON_EMAIL_PARAM = "pemail";
const PERSON_SKYPE_PARAM = "pskype";
const PERSON_NOTES_PARAM = "pnotes";
const PERSON_CONTACT_PARAM = "pcontact";
const PERSON_UPDATE_PARAM = "pupdate";

const HIERARCHY_PARAM = "hierarchy";
const RELATIONSHIP_TYPE_PARAM = "rtype";
const SELECTION_PARAM = "selection";

const ADD1_PARAM = "address1";
const ADD2_PARAM = "address2";
const ADD3_PARAM = "address3";
const COUNTRY_PARAM = "country";
const PINCODE_PARAM = "pincode";

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Customers | BHI</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css" />
    <link rel="stylesheet" type="text/css" href="css/search.css" />
    <script src="scripts/search.js"></script>
    <style>
      select {
        margin: 5px 3px;
      }
      fieldset {
        margin-bottom: 10px;
      }
      table.halfwidth {
        margin-right: 10px;
        max-width: 50%;
      }
      table.boldcolumn tr td:first-child {
        font-weight: bold;
      }
      td {
        text-align: left;
      }
      .success {
        color: green;
        font: 1.2em bold;
      }
      .errorMsg {
        color: red;
        font: 1.2em bold;
      }
      .icon {
        width: 16px;
        height: 16px;
        padding: 0;
        margin: 0;
        margin-right: 3px;
      }
      .labelblock {
        min-width: 170px;
        display: inline-block;
        margin: auto;
      }
    </style>
  </head>
  <body>
    <div id="wrapper">
      <img id="logo" src="img/BHI Logo.jpg" />
      <?php require_once("includes/menu.php") ?>
      <div id="main">
        <h2>Customers</h2>
        <?php
        if(isset($_GET[ADD_PARAM]) || isset($_GET[EDIT_PARAM]) || isset($_GET[DELETE_PARAM]) || isset($_GET[VIEW_PARAM])) {
          if(!isset($_GET[TYPE_PARAM])) {
            die("<p class='errorMsg'>Type must be set!</p>"
            . "<p>Go back to <a href='/customers.php'>Customers</a></p>");
          }
          $isCompany = $isPerson = false;
          $type = $_GET[TYPE_PARAM];
          if(strcmp($type, "company") == 0) {
            $isCompany = true;
          }
          elseif(strcmp($type, "person") == 0) {
            $isPerson = true;
          }
          elseif(strcmp($type, "relationship") == 0) {
            $isRelationship = true;
          }
          else {
            die("<p class='errorMsg'>Type is invalid!</p>"
            . "<p>Go back to <a href='/customers.php'>Customers</a></p>");
          }
          $addCompany = $editCompany = $deleteCompany = $viewCompany = false;
          $addPerson = $editPerson = $deletePerson = $viewPerson = false;
          $addRelationship = $deleteRelationship = false;
          $count = 0;
          if(isset($_GET[ADD_PARAM])) {
            if($isCompany)
              $addCompany = true;
            elseif($isPerson) {
              $addID = $_GET[ADD_PARAM];
              $addPerson = true;
            }
            elseif($isRelationship) {
              $addID = $_GET[ADD_PARAM];
              $addRelationship = true;
              if(isset($_GET[SELECTION_PARAM]))
                $selectedID = $_GET[SELECTION_PARAM];
            }
            $count++;
          }
          if(isset($_GET[EDIT_PARAM])) {
            $editID = $_GET[EDIT_PARAM];
            if($isCompany)
              $editCompany = true;
            else
              $editPerson = true;
            $count++;
          }
          if(isset($_GET[DELETE_PARAM])) {
            $deleteID = $_GET[DELETE_PARAM];
            if($isCompany)
              $deleteCompany = true;
            elseif($isPerson)
              $deletePerson = true;
            elseif($isRelationship) {
              $deleteRelationship = true;
            }
            $count++;
          }
          if(isset($_GET[VIEW_PARAM])) {
            $viewID = $_GET[VIEW_PARAM];
            if($isCompany)
              $viewCompany = true;
            else
              $viewPerson = true;
            $count++;
          }
          if($count > 1) {
            die("<p class='errorMsg'>Error: Cannot set multiple params</p>"
            . "<p>Go back to <a href='/customers.php'>Customers</a></p>");
          }
          $uerror = $eerror = $error = "";
          $successMsg = "";
          $success = false;

          if($_SERVER["REQUEST_METHOD"] == "POST") {
            //Do the stuff here
            if($addCompany || $editCompany) {
              $name = $_POST[COMPANY_NAME_PARAM];
              $type = $_POST[COMPANY_TYPE_PARAM];
              // $role = $_POST[COMPANY_ROLE_PARAM];
              $sector = $_POST[COMPANY_SECTOR_PARAM];
              $ownership = $_POST[COMPANY_OWNERSHIP_PARAM];
              $phone = $_POST[COMPANY_PHONE_PARAM];
              $website = $_POST[COMPANY_WEBSITE_PARAM];

              $address1 = $_POST[ADD1_PARAM];
              $address2 = $_POST[ADD2_PARAM];
              $address3 = $_POST[ADD3_PARAM];
              $country = $_POST[COUNTRY_PARAM];
              $pincode = $_POST[PINCODE_PARAM];
            }
            elseif($addPerson || $editPerson) {
              //$id = $_POST[PERSON_NAME_PARAM];
              // $companyID = $_POST[PERSON_COMPANY_ID_PARAM];
              $name = $_POST[PERSON_NAME_PARAM];
              $designation = $_POST[PERSON_DESIGNATION_PARAM];
              $role = $_POST[PERSON_ROLE_PARAM];
              $phone = $_POST[PERSON_PHONE_PARAM];
              $email = $_POST[PERSON_EMAIL_PARAM];
              $skype = $_POST[PERSON_SKYPE_PARAM];
              $notes = $_POST[PERSON_NOTES_PARAM];
              $contact = $_POST[PERSON_CONTACT_PARAM];
              $updates = $_POST[PERSON_UPDATE_PARAM];
            }
            elseif($addRelationship) {
              $hierarchy = $_POST[HIERARCHY_PARAM];
              $rtype = $_POST[RELATIONSHIP_TYPE_PARAM];
            }

            try {
              if($addCompany) {
                $company = new Company();
                if($company->add_company($name, $type, $sector, $ownership, $phone, $website, $address1, $address2, $address3, $country, $pincode)) {
                  $success = true;
                  $successMsg = "Company successfully added!";
                }
              }
              elseif($editCompany) {
                $company = new Company();
                if($company->load_id($editID)) {
                  if($company->edit_company($name, $type, $sector, $ownership, $phone, $website, $address1, $address2, $address3, $country, $pincode)) {
                    $success = true;
                    $successMsg = "Company edited successfully!";
                  }
                }
                else
                  throw new AppException("No such company exists", AppException::COMPANY_ERROR);
              }
              elseif($deleteCompany) {
                $company = new Company();
                if($company->load_id($deleteID)) {
                  if($company->delete_company()) {
                    $success = true;
                    $successMsg = "Company deleted successfully!";
                  }
                }
                else
                  throw new AppException("No such company exists", AppException::COMPANY_ERROR);
              }
              elseif($addPerson) {
                $person = new Person();
                $company = new Company();

                if($company->load_id($addID)) {
                  if($person->add_person($company->get_id(), $name, $designation, $role, $phone, $email, $skype, $notes, $contact, $updates)) {
                    $success = true;
                    $successMsg = "Person added successfully";
                  }
                }
                else {
                  throw new AppException("Invalid company ID!", AppException::COMPANY_ERROR);
                }
              }
              elseif($editPerson) {
                $person = new Person();
                if($person->load_id($editID)) {
                  if($person->edit_person($name, $designation, $role, $phone, $email, $skype, $notes, $contact, $updates)) {
                    $success = true;
                    $successMsg = "Person edited successfully!";
                  }
                }
                else
                  throw new AppException("No such person exists", AppException::COMPANY_ERROR);
              }
              elseif($deletePerson) {
                $person = new Person();
                if($person->load_id($deleteID)) {
                  if($person->delete_person()) {
                    $success = true;
                    $successMsg = "Person deleted successfully!";
                  }
                }
                else
                  throw new AppException("No such person exists", AppException::COMPANY_ERROR);
              }
              elseif($addRelationship) {
                if(strcmp($hierarchy, "parent") == 0 ) {
                  $parentID = $selectedID;
                  $childID = $addID;
                }
                elseif(strcmp($hierarchy, "child") == 0 ) {
                  $parentID = $addID;
                  $childID = $selectedID;
                }
                else {
                  throw new AppException("Incorrect hierarchy parameter", AppException::RELATIONSHIP_ERROR);
                }
                $relationship = new Relationship();
                // echo $parentID . $childID . $rtype;
                if($relationship->add_relationship($parentID, $childID, $rtype)) {
                  $success = true;
                  $successMsg = "Relationship added successfully";
                }
              }
              elseif($deleteRelationship) {
                $relationship = new Relationship();
                if($relationship->load_id($deleteID)) {
                  if($relationship->delete_relationship()) {
                    $success = true;
                    $successMsg = "Relationship deleted!";
                  }
                }
                else
                  throw new AppException("No such relationship exists", AppException::RELATIONSHIP_ERROR);
              }
            }
            catch(AppException $e) {
              $success = false;
              $error = $e->message();
            }
          }
          if(!$success) {
            $actionURL = "customers.php";
            $legend = "";
            $placeholder = "";
            if($isCompany) {
              $actionURL .= "?type=company";
              $placeholder = "company";
            }
            elseif($isPerson) {
              $actionURL .= "?type=person";
              $placeholder = "person";
            }
            elseif($isRelationship) {
              $actionURL .= "?type=relationship";
              $placeholder = "relationship";
            }

            if($addCompany || $addPerson){
              if($addCompany)
                $actionURL .= "&add=true";
              elseif($addPerson)
                $actionURL .= "&add=" . $addID;
              $legend = "Add New " . $placeholder;
            }
            elseif($editCompany || $editPerson) {
              $actionURL .= '&edit=' . $editID;
              $legend = "Edit Existing " . $placeholder;

              if($editCompany) {
                $company = new Company();
                if($company->load_id($editID)) {
                  $name = $company->get_name();
                  $type = $company->get_type();
                  $sector = $company->get_sector();
                  $ownership = $company->get_ownership();
                  $phone = $company->get_phone();
                  $website = $company->get_website();

                  $address = $company->get_address();
                  $loadedValues = true;
                }
                else {
                  $loadedValues = false;
                  $error = "No such company exists";
                }
              }
              elseif($editPerson) {
                $person = new Person();
                if($person->load_id($editID)) {
                  $companyID = $person->get_companyid();
                  $name = $person->get_name();
                  $designation = $person->get_designation();
                  $role = $person->get_role();
                  $phone = $person->get_phone();
                  $email = $person->get_email();
                  $skype = $person->get_skype();
                  $notes = $person->get_notes();
                  $contact = $person->get_contact();
                  $updates = $person->get_updates();

                  $loadedValues = true;
                }
                else {
                  $loadedValues = false;
                  $error = "No such person exists";
                }
              }
            }
            elseif($deleteCompany || $deletePerson || $deleteRelationship) {
              $actionURL .= '&delete=' . $deleteID;
              $legend = "Delete " . $placeholder;
            }
            elseif($viewCompany || $viewPerson) {
              $legend = "View " . $placeholder . " details";
            }
            elseif($addRelationship) {
              $actionURL .= "&add=" . $addID;
              if(isset($selectedID))
                $actionURL .= "&selection=" . $selectedID;
              $legend = "Add " . $placeholder;
            }

            //open the form here
            if(!$viewCompany && !$viewPerson) {
              echo "<form action=\"" . $actionURL . "\" method='post'>";
              echo "<h3>" . $legend . "</h3>";
            }

            if($addRelationship) {
              $company = new Company();
              if($company->load_id($addID)) {
                echo "Add Relationship to: <a href='customers.php?type=company&view=" . $company->get_id() . "'>";
                echo $company->get_name() . "</a>";
                echo "<input type='hidden' id='basecompanyid' value='" . $company->get_id() . "' />";
                if(isset($_GET[SELECTION_PARAM])) {
                  if($company->load_id($selectedID)) {
                    echo "<br><br>Selected Company: <a href='customers.php?type=company&view=" . $company->get_id() . "'>";
                    echo $company->get_name() . "</a>";
                    echo "<br>Type: ";
                    echo "<select name='rtype'>";
                    $i=RelationshipType::BRANCH;
                    $relationshipType = new RelationshipType();
                    while($relationshipType->is_valid($i)) {
                      echo "<option value='" . $i . "'";
                      if($i == 0)
                        echo " selected ";
                      echo ">" . $relationshipType->get_name($i) . "</option>";
                      $i++;
                    }
                    echo "</select>";
                    echo "<br>Hierarchy: ";
                    echo "<input type='radio' name='hierarchy' value='child' checked /> Child ";
                    echo "<input type='radio' name='hierarchy' value='parent' /> Parent ";
                    echo "<br><input type='submit' value='Add Relationship' />";
                    echo "</form>";
                  }
                }
                else {
                ?>
                <p><b>Select Related Company</b></p>
                <input type="text" id="searchbox" name="relationship" />
                <input type="button" id="searchbutton" />
                <div id="livesearch"></div>
                <br>
                <input type="radio" name="searchtype" value="company" checked /> Company
                <br>
                <div id="selectionresults"></div>
                <?php
                }
              }
              else {
                die("<p class='errorMsg'>Invalid company ID</p>"
                    . "Go back to <a href='customers.php'>Customers</a>");
              }
            }
            elseif($viewCompany) {
              //Handle views
              try {
                $company = new Company();
                if($company->load_id($viewID)) {
                  $address = new Address();
                  if($address->load_company_id($company->get_id())) {
                    echo "<input type='button' onclick='window.location.href=\"customers.php?type=person&add=" . $company->get_id() . "\"' value='Add Employee'/>";
                    echo "<input style='margin-left: 10px;' type='button' onclick='window.location.href=\"customers.php?type=company&edit=" . $company->get_id() . "\"' value='Edit Company'/>";
                    echo "<input style='margin-left: 10px;' type='button' onclick='window.location.href=\"customers.php?type=company&delete=" . $company->get_id() . "\"' value='Delete Company'/>";
                    echo "<input style='margin-left: 10px;' type='button' onclick='window.location.href=\"customers.php?type=relationship&add=" . $company->get_id() . "\"' value='Add Relationship'/>";
                    echo "<br> <hr>";
                    echo "<table class='halfwidth boldcolumn' class='boldcolumn' style='display: inline-block;'>";
                    echo "<th colspan='2'>Company Details</th><tr>";
                    echo "<td>Name</td>";
                    echo "<td>" . $company->get_name() . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Address</td>";
                    echo "<td>";
                    $add1 = $address->get_address1();
                    $add2 = $address->get_address2();
                    $add3 = $address->get_address3();
                    $country = $address->get_country();
                    $pincode = $address->get_pincode();
                    if(!empty($add1))
                      echo $add1 . "<br>";
                    if(!empty($add2))
                      echo $add2 . "<br>";
                    if(!empty($add3))
                      echo $add3 . "<br>";
                    if(!empty($country))
                      echo $country . "<br>";
                    if(!empty($pincode))
                      echo $pincode . "<br>";
                    echo "</tr><tr>";
                    echo "<td>Type</td>";
                    echo "<td>" . (new CompanyType())->get_name($company->get_type()) . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Sector</td>";
                    echo "<td>" . (new CompanySector())->get_name($company->get_sector()) . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Ownership</td>";
                    echo "<td>" . (new CompanyOwnership())->get_name($company->get_ownership()) . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Phone</td>";
                    echo "<td>" . $company->get_phone() ."</td>";
                    echo "</tr><tr>";
                    echo "<td>Website</td>";
                    echo "<td><a href='" . $company->get_website() . "'>" . $company->get_website() . "</a></td>";
                    echo "</tr></table>";
                  }
                  else {
                    throw new AppException("Address not found!", AppException::COMPANY_ERROR);
                  }

                  echo "<table class='halfwidth'  style='display: inline-block; vertical-align: top;'>";
                  echo "<th colspan='2'>Employee Details</th><tr>";
                  echo "<th>Name</th>";
                  echo "<th>Phone</th>";
                  echo "</tr>";
                  try {
                    $person_ids = $company->get_all_person_ids();
                    $person = new Person();
                    for($i=0;$i < count($person_ids); $i++) {
                      if($person->load_id($person_ids[$i])) {
                        echo "<tr>";
                        echo "<td><a href='customers.php?type=person&view=" . $person->get_id() . "'>" . $person->get_name() . "</a></td>";
                        echo "<td>" . $person->get_phone() . "</td>";
                        echo "</tr>";
                      }
                      else {
                        throw new AppException("Employee not found", AppException::PERSON_ERROR);
                      }
                    }
                  }
                  catch(AppException $e) {
                    echo "<tr><td colspan='2'>" . $e->message() . "</td></tr>";
                  }

                  echo "</table>";

                  echo "<table class='halfwidth' style='margin-top: 10px;'>";
                  echo "<th colspan='4'>Related Companies</th>";
                  echo "<tr><th>Name</th> <th>Hierarchy</th> <th>Relation Type</th> <th>Options</th></tr>";
                  echo "<tr><td style='text-align: center;' colspan='4'><b style='text-align: center;'>Parent Companies</b></td> </tr>";
                  try {
                    $related = new Relationship();
                    $parent_ids = $related->get_all_parent($company->get_id());
                    $rcompany = new Company();
                    for($i=0; $i < count($parent_ids); $i++) {
                      if($related->load_id($parent_ids[$i])) {
                        if($rcompany->load_id($related->get_parent_id()))
                        {
                          echo "<tr>";
                          echo "<td><a href='customers.php?type=company&view=" . $rcompany->get_id() . "'>" . $rcompany->get_name() . "</a></td>";
                          echo "<td>Parent</td>";
                          echo "<td>". (new RelationshipType)->get_name($related->get_type()) ."</td>";
                          echo "<td><a href='customers.php?type=relationship&delete=" . $related->get_id() . "'><img style='display: block; margin: auto' class='icon' src='img/dustbin.png' alt='Delete Relationship' title='Delete Relationship'/></a></td>";
                          echo "</tr>";
                        }
                      }
                    }
                  }
                  catch(AppException $e) {
                    echo "<tr><td colspan='4'>" . $e->message() . "</td></tr>";
                  }
                  echo "<tr><td style='text-align: center;' colspan='4'><b>Child Companies</b></td> </tr>";
                  try {
                    $related = new Relationship();
                    $child_ids = $related->get_all_children($company->get_id());
                    $rcompany = new Company();

                    for($i=0; $i < count($child_ids); $i++) {
                      if($related->load_id($child_ids[$i])) {
                        if($rcompany->load_id($related->get_child_id()))
                        {
                          echo "<tr>";
                          echo "<td><a href='customers.php?type=company&view=" . $rcompany->get_id() . "'>" . $rcompany->get_name() . "</a></td>";
                          echo "<td>Child</td>";
                          echo "<td>". (new RelationshipType)->get_name($related->get_type()) ."</td>";
                          echo "<td><a href='customers.php?type=relationship&delete=" . $related->get_id() . "'><img style='display: block; margin: auto' class='icon' src='img/dustbin.png' alt='Delete Relationship' title='Delete Relationship'/></a></td>";
                          echo "</tr>";
                        }
                      }
                    }
                  }
                  catch(AppException $e) {
                    echo "<tr><td colspan='4'>" . $e->message() . "</td></tr>";
                  }
                  echo "</table>";
                }
                else {
                  throw new AppException("Cannot find company!", AppException::COMPANY_ERROR);
                }
              }
              catch(AppException $e) {
                die("<p class='errorMsg'>" . $e->message() . "</p>"
                    . "<a href='customers.php'>Click here to go back to customers</a>");
              }
            }
            elseif($viewPerson) {
              try {
                $person = new Person();
                if($person->load_id($viewID)) {
                  $company = new Company();
                  if($company->load_id($person->get_companyid())) {
                    echo "<table>";
                    echo "<th colspan='2'>Person Details</th><tr>";
                    echo "<td>Options</td><td>";
                    echo "<input type='button' onclick='window.location.href=\"customers.php?type=person&edit=" . $person->get_id() . "\"' value='Edit Person'/>";
                    echo "<input style='margin-left: 10px;' type='button' onclick='window.location.href=\"customers.php?type=person&delete=" . $person->get_id() . "\"' value='Delete Person'/>";
                    echo "</td></tr><tr>";
                    echo "<td>Parent Company</td>";
                    echo "<td><a href='customers.php?type=company&view=" . $company->get_id() . "'>" . $company->get_name() . "</a></td>";
                    echo "</tr><tr>";
                    echo "<td>Name</td>";
                    echo "<td>" . $person->get_name() . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Designation</td>";
                    echo "<td>" . $person->get_designation() . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Role</td>";
                    echo "<td>" . (new PersonRole())->get_name($person->get_role()) . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Phone</td>";
                    echo "<td>" . $person->get_phone() . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Email</td>";
                    echo "<td><a href='mailto:" .$person->get_email() . "'>" . $person->get_email() ."</a></td>";
                    echo "</tr><tr>";
                    echo "<td>Skype</td>";
                    echo "<td><a href='skype:" . $person->get_skype() . "'>" . $person->get_skype() . "</a></td>";
                    echo "</tr><tr>";
                    echo "<td>Notes</td>";
                    echo "<td>" . $person->get_notes() . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Contact</td>";
                    echo "<td>" . $person->get_contact() . "</td>";
                    echo "</tr><tr>";
                    echo "<td>Updates</td>";
                    echo "<td>" . $person->get_updates() . "</td>";
                    echo "</tr></table>";
                  }
                  else {
                    throw new AppException("Parent company id not found!", AppException::COMPANY_ERROR);
                  }
                }
                else {
                  throw new AppException("Cannot find person!", AppException::PERSON_ERROR);
                }
              }
              catch(AppException $e) {
                die("<p class='errorMsg'>" . $e->message() . "</p>"
                    . "<a href='customers.php'>Click here to go back to customers</a>");
              }
            }
            elseif($addCompany || $editCompany) {
              //add company form
              //<input type="submit" value="Submit" />
              ?>
              <fieldset>
                <legend>Company Details</legend>
              <label class="labelblock" for="cname">Name</label>
              <input type="text" name="cname" id="cname" placeholder="Name" <?php if($editCompany && $loadedValues) echo "value='" . $name . "'"; ?> required /> *
              <br>
              <?php
                echo "<label class='labelblock' for='ctype'>Company Type</label>";
                echo "<select name='ctype' id='ctype'>";
                $i = CompanyType::SHIP_BUILDER;
                $cType = new CompanyType();
                while($cType->is_valid($i)) {
                  if($editCompany && $loadedValues){
                    if($i == $type){
                      echo "<option value='" . $i ."' selected>" . $cType->get_name($i) ."</option>";
                      $i++;
                      continue;
                    }
                  }
                  echo "<option value='" . $i ."'>" . $cType->get_name($i) ."</option>";
                  $i++;
                }
                echo "</select> *<br>";

                echo "<label class='labelblock' for='csector'>Company Sector</label>";
                echo "<select name='csector' id='csector'>";
                $i = CompanySector::DEFENCE;
                $cSector = new CompanySector();
                while($cSector->is_valid($i)) {
                  if($editCompany && $loadedValues){
                    if($i == $sector){
                      echo "<option value='" . $i ."' selected>" . $cSector->get_name($i) ."</option>";
                      $i++;
                      continue;
                    }
                  }
                  echo "<option value='" . $i ."'>" . $cSector->get_name($i) ."</option>";
                  $i++;
                }
                echo "</select> * <br>";

                echo "<label class='labelblock' for='cownership'>Company Ownership</label>";
                echo "<select name='cownership' id='cownership'>";
                $i = CompanyOwnership::PRIVATE_COMPANY;
                $cOwnership = new CompanyOwnership();
                while($cOwnership->is_valid($i)) {
                  if($editCompany && $loadedValues){
                    if($i == $ownership){
                      echo "<option value='" . $i ."' selected>" . $cOwnership->get_name($i) ."</option>";
                      $i++;
                      continue;
                    }
                  }
                  echo "<option value='" . $i ."'>" . $cOwnership->get_name($i) ."</option>";
                  $i++;
                }
                echo "</select> * <br>";
              ?>
              <?php echo "<p class='error'>" . $uerror . "</p>" ; ?>
              <label class="labelblock" for="cphone">Phone</label>
              <input type="tel" name="cphone" id="cphone" placeholder="Phone" <?php if($editCompany && $loadedValues) echo "value='" . $phone . "'"; ?> required /> * <br>
              <label class="labelblock" for="cwebsite">Website</label>
              <input type="url" name="cwebsite" id="cwebsite" placeholder="http://www.example.com" <?php if($editCompany && $loadedValues) echo "value='" . $website . "'"; ?> required /> *
              <?php echo "<p class='error'>" . $eerror . "</p>" ; ?>
              </fieldset>

              <fieldset>
                <legend>Address Details</legend>
                <label class="labelblock" for="address1">Street Address</label>
                <input type="text" name="address1" id="address1" placeholder="Address Line 1" <?php if($editCompany && $loadedValues) echo "value='" . $address->get_address1() . "'"; ?> required /> * <br>
                <label class="labelblock" for="address2">City/ Town</label>
                <input type="text" name="address2" id="address2" placeholder="Address Line 2" <?php if($editCompany && $loadedValues) echo "value='" . $address->get_address2() . "'"; ?> required /> * <br>
                <label class="labelblock" for="address3">State/ Province/ Region</label>
                <input type="text" name="address3" id="address3" placeholder="Address Line 3" <?php if($editCompany && $loadedValues) echo "value='" . $address->get_address3() . "'"; ?> /> <br>
                <label class="labelblock" for="country">Country</label>
                <input type="text" name="country" id="country" placeholder="Country" <?php if($editCompany && $loadedValues) echo "value='" . $address->get_country() . "'"; ?> required /> *<br>
                <label class="labelblock" for="pincode">Zipcode/ Postal Code</label>
                <input type="text" name="pincode" id="pincode" placeholder="Pincode" <?php if($editCompany && $loadedValues) echo "value='" . $address->get_pincode() . "'"; ?> /> <br>
              </fieldset>

              <input type="submit" value="Submit" />
              <?php
            }
            elseif($addPerson || $editPerson) {
              ?>
              <fieldset>
                <legend>Person Details</legend>

              <?php
              $comp = new Company();
              $cID = -1;
              if($addPerson) {
                $cID = $addID;
              }
              elseif($editPerson && $loadedValues)  {
                $cID = $companyID;
              }
              try{
                if($comp->load_id($cID)) {
                  echo "<label class='labelblock' for='pcompanyid'>";
                  echo "Parent Company</label>";
                  echo "<a href='customers.php?type=company&view=" . $comp->get_id() . "'>";
                  echo $comp->get_name();
                  echo "</a>";
                }
                else {
                  throw new AppException("Invalid company ID", AppException::COMPANY_ERROR);
                }
              }
              catch(AppException $e) {
                die("<p class='errorMsg'>" . $e->message() . '</p>'
                    . "<a href='customers.php'>Go back to customers</a>");
              }
              ?>
              <br>
              <!-- <input type="text" name="pcompanyid" id="pcompanyid" placeholder="Name" <?php //if($editPerson && $loadedValues) echo "value='" . $companyID . "'"; ?> required /> * <br> -->
              <label class="labelblock" for="pname">Name</label>
              <input type="text" name="pname" id="pname" placeholder="Name" <?php if($editPerson && $loadedValues) echo "value='" . $name . "'"; ?> required /> *
              <br>
              <?php
                echo "<label class='labelblock' for='prole'>Role</label>";
                echo "<select name='prole' id='prole'>";
                $i = PersonRole::TECHNICAL;
                $pRole = new PersonRole();
                while($pRole->is_valid($i)) {
                  if($editPerson && $loadedValues){
                    if($i == $role){
                      echo "<option value='" . $i ."' selected>" . $pRole->get_name($i) ."</option>";
                      $i++;
                      continue;
                    }
                  }
                  echo "<option value='" . $i ."'>" . $pRole->get_name($i) ."</option>";
                  $i++;
                }
                echo "</select> *<br>";
              ?>
              <?php echo "<p class='error'>" . $uerror . "</p>" ; ?>
              <label class="labelblock" for="pdesignation">Designation</label>
              <input type="text" name="pdesignation" id="pdesignation" placeholder="Designation" <?php if($editPerson && $loadedValues) echo "value='" . $designation . "'"; ?> required /> * <br>
              <label class="labelblock" for="pphone">Phone</label>
              <input type="tel" name="pphone" id="pphone" placeholder="Phone" <?php if($editPerson && $loadedValues) echo "value='" . $phone . "'"; ?> required /> * <br>
              <label class="labelblock" for="pemail">Email</label>
              <input type="email" name="pemail" id="pemail" placeholder="Email" <?php if($editPerson && $loadedValues) echo "value='" . $email . "'"; ?> required /> * <br>
              <label class="labelblock" for="pskype">Skype Username</label>
              <input type="text" name="pskype" id="pskype" placeholder="Skype" <?php if($editPerson && $loadedValues) echo "value='" . $skype . "'"; ?> required /> * <br>
              <label class="labelblock" for="pnotes">Notes</label>
              <textarea name="pnotes" id="pnotes"><?php if($editPerson && $loadedValues) echo $notes; ?></textarea>  <br>
              <label class="labelblock" for="pcontact">Contact</label>
              <textarea name="pcontact" id="pcontact"><?php if($editPerson && $loadedValues) echo $contact; ?></textarea> <br>
              <label class="labelblock" for="pupdate">Updates</label>
              <textarea name="pupdate" id="pupdate"><?php if($editPerson && $loadedValues) echo $updates; ?></textarea>

              <?php echo "<p class='error'>" . $eerror . "</p>" ; ?>
              </fieldset>
              <input type="submit" value="Submit" />
              <?php

            }
            elseif($deleteCompany) {
              $company = new Company();
              if($company->load_id($deleteID)) {
                echo "<fieldset>";
                echo "<input type='hidden' name='confirmDeletion' value='true' />";
                echo "Are you sure you want to delete company, " . $company->get_name() . "? <br>";
                ?>
                <input type="button" onclick="window.location.href='customers.php'" value="Cancel" />
                <input type="submit" value="Yes" />
              </fieldset>
                <?php
              }
              else {
                  $error = "No such company exists!";
              }
            }
            elseif($deletePerson) {
              $person = new Person();
              if($person->load_id($deleteID)) {
                echo "<fieldset>";
                echo "<input type='hidden' name='confirmDeletion' value='true' />";
                echo "Are you sure you want to delete person, " . $person->get_name() . "? <br>";
                ?>
                <input type="button" onclick="window.location.href='customers.php'" value="Cancel" />
                <input type="submit" value="Yes" />
                </fieldset>
                <?php
              }
              else {
                  $error = "No such person exists!";
              }
            }
            elseif($deleteRelationship) {
              $relationship = new Relationship();
              if($relationship->load_id($deleteID)) {
                echo "<fieldset>";
                echo "<input type='hidden' name='confirmDeletion' value='true' />";
                echo "Are you sure you want to delete this relationship? <br>";
                ?>
                <input type="button" onclick="window.location.href='customers.php'" value="Cancel" />
                <input type="submit" value="Yes" />
                </fieldset>
                <?php
              }
              else {
                  $error = "No such relation exists!";
              }
            }

            echo "<p class='error'>" . $error . "</p>" ;
            if(!$viewCompany && !$viewPerson)
              echo "</form>";
          }
          elseif($success) {
            echo "<fieldset>";
            echo "<p class='success'>" . $successMsg . "</p>";
						echo "<p> Go back to <a href='/customers.php'>Customers Page.</a></p>";
            echo "</fieldset>";
          }
        } else {
         ?>
       <h3>Search</h3>
       <input type="text" id="searchbox" autocomplete="off" />
       <input type="button" id="searchbutton" />
       <div id="livesearch"></div>
       <br>
       <input type="radio" name="searchtype" value="company" checked /> Company
       <input type="radio" name="searchtype" value="person" /> Person
       <br>
        <hr>
        <input type="button" onclick="window.location.href='customers.php?add=true&type=company'" value="Add Company"/>
        <br> <hr>
        <?php
          try {
            echo "<h3>Companies</h3>";
            $company = new Company();
            $ids = $company->get_all_ids();
            $companyCount = count($ids);
            echo "<table><th>Options</th><th>ID</th> <th>Name</th> <th>Phone</th> <th>Website</th>";
            for($i=0; $i < $companyCount; $i++) {
              $company->load_id($ids[$i]);
              echo "<tr>";
              echo "<td> <a href='/customers.php?type=company&edit=" . $company->get_id() . "'><img class='icon' src='img/setting-tool.png' alt='Edit Company' title='Edit Company'/></a>" .
                   "<a href='/customers.php?type=company&delete=". $company->get_id() . "'><img class='icon' src='img/dustbin.png' alt='Delete Company' title='Delete Company'/></a>";
              echo " <td>" . $company->get_id() ."</td>" .
              " <td><a href='customers.php?type=company&view=" .$company->get_id() . "'>" . $company->get_name() . "</a></td>".
              " <td>" . $company->get_phone() . "</td>".
              " <td><a href='" . $company->get_website() . "'>" . $company->get_website() . "</a></td>".
              " </tr>";
            }
          }
          catch(AppException $e) {
            echo "<p>" . $e->message() . "</p>";
          }
        }
         ?>
      </div>
    </div>
  </body>
</html>
