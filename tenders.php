<?php
require_once("includes/routines.php");

const PAGE_ACCESS_LEVEL = AccessLevel::PAGE_TENDERS;
require_once("includes/validate_session.php");

const VIEW_PARAM = "view";
const DELETE_PARAM = "delete";
const ACTIVATE_PARAM = "activate";
const DEACTIVATE_PARAM = "deactivate";
const PAGE_PARAM = "page";
const ARCHIVE_PARAM = "archive";
const DEFAULT_RESULTS_PER_PAGE = 25;

$page = 1;
$archive = false;
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Tenders | BHI</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css" />
    <link rel="stylesheet" type="text/css" href="css/search.css" />
    <script src="scripts/search.js"></script>
    <style>
      #pagenumbers {
        text-align: center;
        margin: 20px;
      }
      .success {
        color: green;
        font: 1.2em bold;
      }
      .errorMsg {
        color: red;
        font: 1.2em bold;
      }
      .page {
        padding-left: 3px;
        padding-right: 3px;
      }
      .date {
        font-size: 0.9em;
        white-space: nowrap;
      }
      .description {
        font-size: 0.9em;
      }
      .enquiry {
        font-size: 0.9em;
      }
    </style>
  </head>
  <body>
    <div id="wrapper">
      <img id="logo" src="img/BHI Logo.jpg" />
      <?php require_once("includes/menu.php"); ?>
      <div id="main">
        <h2>Tenders</h2>
        <?php
          $success = false;
          $successMsg = "";
          $activateTender = $deactivateTender = false;
          if(isset($_GET[VIEW_PARAM]) || isset($_GET[DELETE_PARAM])) {
            $viewTender = $deleteTender = false;
            $count = 0;

            if(isset($_GET[VIEW_PARAM])) {
              $viewTender = true;
              $viewID = $_GET[VIEW_PARAM];
              $count++;
            }
            if(isset($_GET[DELETE_PARAM])) {
              $deleteTender = true;
              $deleteID = $_GET[DELETE_PARAM];
              $count++;
            }
            if(isset($_GET[ACTIVATE_PARAM])) {
              $count++;
            }
            if(isset($_GET[DEACTIVATE_PARAM])) {
              $count++;
            }
            if($count > 1) {
              die("<p class='errorMsg'>Error: Cannot set multiple params</p>"
              . "<p>Go back to <a href='/tenders.php'>Tenders</a></p>");
            }

            if($_SERVER["REQUEST_METHOD"] == "POST") {
              if($deleteTender) {
                try {
                  $tender = new Tender();
                  $tender->load_id($deleteID);
                  $tender->set_active(Tender::IGNORE);
                  $success = true;
                  $successMsg = "Tender successfully deleted";
                }
                catch (AppException $e) {
                  die("<p class='errorMsg'>" . $e->message() . "</p>");
                }
              }
            }

            if($viewTender) {
              try {
                $tender = new Tender();
                $tender->load_id($viewID);
                $link = $tender->get_link();
                echo "<table>";
                echo "<tr><th colspan='2'>Tender Details</th></tr>";
                echo "<tr><td>ID</td> <td>" . $tender->get_id() . "</td></tr>";
                echo "<tr><td>Enquiry Number</td> <td>" . $tender->get_enquiry_num() . "</td></tr>";
                echo "<tr><td>Description</td> <td><a href='" . $link['url'] . "'>". $tender->get_description() . "</a></td></tr>";
                echo "<tr><td>Posted On</td> <td>" . $tender->get_posted_on() . "</td></tr>";
                echo "<tr><td>Deadline</td> <td>" . $tender->get_deadline() . "</td></tr>";
                echo "</table>";
              }
              catch(AppException $e) {
                  echo "<tr><td>";
                  if($e->get_code() == AppException::TENDER_ERROR)
                    echo "<span>" . $e->message() . "</span>";
                  else
                    echo "<span class='error'>" . $e->message() . "</span>";
                  echo "</td></tr>";
              }
            }
            elseif($deleteTender && !$success) {
              echo "<h3>Delete Tender</h3>";
              $tender = new Tender();
              $tender->load_id($deleteID);
              $sucess = true;
              echo "<form method='POST'>";
              echo "Are you sure you want to delete tender with enquiry number, " . $tender->get_enquiry_num() . "? <br>";
              ?>
              <input type="button" onclick="window.location.href='tenders.php'" value="Cancel" />
              <input type="submit" value="Yes" />
              <?php
              echo "</form>";
            }
            elseif($success) {
              echo "<fieldset>";
              echo "<p class='success'>" . $successMsg . "</p>";
  						echo "<p> Go back to <a href='/tenders.php'>Tenders Page.</a></p>";
              echo "</fieldset>";
            }
          } else {
            $count = 0;
            if(isset($_GET[ACTIVATE_PARAM])) {
              $activateTender = true;
              $activateID = $_GET[ACTIVATE_PARAM];
              $count++;
            }
            if(isset($_GET[DEACTIVATE_PARAM])) {
              $deactivateTender = true;
              $deactivateID = $_GET[DEACTIVATE_PARAM];
              $count++;
            }
            if($count > 1)
              die("Cannot set multiple parameters!");

            if($activateTender) {
              try {
                $tender = new Tender();
                $tender->load_id($activateID);
                $tender->set_active(Tender::ACTIVE);
              }
              catch(AppException $e) {
                  echo "<tr><td>";
                  if($e->get_code() == AppException::TENDER_ERROR)
                    echo "<span>" . $e->message() . "</span>";
                  else
                    echo "<span class='error'>" . $e->message() . "</span>";
                  echo "</td></tr>";
              }
            }
            elseif($deactivateTender) {
              try {
                $tender = new Tender();
                $tender->load_id($deactivateID);
                $tender->set_active(Tender::INACTIVE);
              }
              catch(AppException $e) {
                  echo "<tr><td>";
                  if($e->get_code() == AppException::TENDER_ERROR)
                    echo "<span>" . $e->message() . "</span>";
                  else
                    echo "<span class='error'>" . $e->message() . "</span>";
                  echo "</td></tr>";
              }
            }

            if(isset($_GET[PAGE_PARAM])) {
              $page = $_GET[PAGE_PARAM];
            }
            if(isset($_GET[ARCHIVE_PARAM])) {
              if(strcmp($_GET[ARCHIVE_PARAM], "true") == 0)
                $archive = true;
              else
                $archive = false;
            }
         ?>
        <h3>Search</h3>
        <input type="text" id="searchbox" placeholder="Search.." autocomplete="off" />
        <input type="button" id="searchbutton" value="Search" />
        <div id="livesearch"></div>
        <br>
        <input type="radio" name="searchtype" value="<?php echo Search::TENDER_ENQUIRY_PARAM; ?>" checked /> Enquiry Number
        <input type="radio" name="searchtype" value="<?php echo Search::TENDER_DESCRIPTION_PARAM; ?>" /> Description
        <br> <hr>
        <input type="button" onclick="window.location.href='tenders.php?archive=true'" value="View Tender Archive"/> <br />
         <hr>
        <table>
          <tr>
            <th>Options</th>
            <th>Enquiry Number</th>
            <th>Description</th>
            <th>Posted On</th>
            <th>Deadline</th>
          </tr>
        <?php
          try {
            $tender = new Tender();
            if($archive)
              $tender->set_archive(true);
            $tenderIds = $tender->get_page_ids($page, DEFAULT_RESULTS_PER_PAGE);
            $tenderCount = count($tenderIds);

            for($i=0;$i<$tenderCount;$i++) {
              $tender->load_id($tenderIds[$i]);
              //display all tenders here
              echo "<tr>";
              echo "<td>" .
                   "<a href='/tenders.php?delete=". $tender->get_id() . "'><img class='icon' src='img/dustbin.png' alt='Delete Tender' title='Delete Tender'/></a>";
              if($tender->get_active() == Tender::ACTIVE) {
                echo "<a href='/tenders.php?deactivate=" . $tender->get_id() . (($archive) ? "&archive=true": "") ."'><img class='icon' src='img/small-key.png' alt='Mark tender as Inactive' title='Mark tender as Inactive'/></a> </td>";
              }
              elseif($tender->get_active() == Tender::INACTIVE) {
                echo "<a href='/tenders.php?activate=" . $tender->get_id() . (($archive) ? "&archive=true": "") ."'><img class='icon' src='img/locked-padlock.png' alt='Mark tender as Active' title='Mark tender as Active'/></a> </td>";
              }
              echo "<td><span class='enquiry' title='" .$tender->get_enquiry_num() . "'>" . get_truncated_string($tender->get_enquiry_num(), Tender::ENQUIRY_STRING_LENGTH) . "</span></td>";
              echo "<td><span class='description' title='" .$tender->get_description() . "'><a href='tenders.php?view=" . $tender->get_id() . "'>" . get_truncated_string($tender->get_description(), Tender::DESCRIPTION_STRING_LENGTH) . "</a></span></td>";
              echo "<td><span class='date'>" . (new DateTime($tender->get_posted_on()))->format('d-M-y') . "</span></td>";
              echo "<td><span class='date'>" . (new DateTime($tender->get_deadline()))->format('d-M-y') . "</span></td>";
              echo "</tr>";
            }
          }
          catch(AppException $e) {
              echo "<tr><td colspan='5'>";
              if($e->get_code() == AppException::TENDER_ERROR)
                echo "<span>" . $e->message() . "</span>";
              else
                echo "<span class='error'>" . $e->message() . "</span>";
              echo "</td></tr>";
          }
         ?>
       </table>
       <?php
         $pageName = "tenders.php";
         $tender = new Tender();
         if($archive)
          $tender->set_archive(true);
         $tenderTotalCount = $tender->get_count();

         $paginator = new Paginator($page, $tenderTotalCount, DEFAULT_RESULTS_PER_PAGE);

         echo $paginator->get_pages_div($pageName, $_SERVER["QUERY_STRING"]);
        }
        ?>
      </div>
    </div>
  </body>
</html>
