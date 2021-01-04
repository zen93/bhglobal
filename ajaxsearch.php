<?php
  require_once("includes/routines.php");
  const PAGE_ACCESS_LEVEL = AccessLevel::PAGE_CUSTOMERS;
  require_once("includes/validate_session.php");

  if(isset($_GET[Search::COMPANY_PARAM]) || isset($_GET[Search::PERSON_PARAM]) || isset($_GET[Search::TENDER_ENQUIRY_PARAM]) || isset($_GET[Search::TENDER_DESCRIPTION_PARAM])) {
    $count = 0;
    $companySearch = $personSearch = $tenderEnquiry = $tenderDescription = false;

    if(isset($_GET[Search::COMPANY_PARAM])) {
      $companySearch = true;
      $query = $_GET[Search::COMPANY_PARAM];
      $count++;
    }
    if(isset($_GET[Search::PERSON_PARAM])) {
      $personSearch = true;
      $query = $_GET[Search::PERSON_PARAM];
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
    if(isset($_GET[Search::OPTIONS_PARAM])) {
      $options = $_GET[Search::OPTIONS_PARAM];
    }
    else {
      $options = Search::BOTH;
    }
    if($count > 1){
      die("Error: Cannot set multiple params!");
    }
    $search = new Search();
    if($companySearch) {
      $search->search_company($query, $options, 1, Search::AJAX_LIMIT);
      echo $search->get_json();
    }
    elseif($personSearch) {
      $search->search_person($query, $options, 1, Search::AJAX_LIMIT);
      echo $search->get_json();
    }
    elseif($tenderEnquiry) {
      $result = $search->search_enquiry($query, $options, 1, Search::AJAX_LIMIT);
      echo $search->get_json();
    }
    elseif($tenderDescription) {
      $result = $search->search_description($query, $options, 1, Search::AJAX_LIMIT);
      echo $search->get_json();
    }
  }
?>
