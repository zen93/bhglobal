<?php
  class Paginator {
    const PAGES_SHOWN = 10;
    const PAGES_SKIP = 5;

    private $currentPage;
    private $totalCount;
    private $resultsPerPage;

    public function __construct($currentPage, $totalCount, $resultsPerPage) {
      $this->currentPage = sanitize_input($currentPage);
      $this->totalCount = sanitize_input($totalCount);
      $this->resultsPerPage = sanitize_input($resultsPerPage);
    }

    private function get_number_of_pages() {
      return ceil($this->totalCount/$this->resultsPerPage);
    }

    private function get_page_url($pageName, $queryString) {
      $queryURL = "";
      $pageName = $pageName . "?";
      $queryParams = explode("&", $queryString);
      if(empty($queryString) || empty($queryParams)) return $pageName;
      $flag = false;
      foreach($queryParams as $param) {
        if(empty($param))
          continue;
        $pairs = explode("=", $param);
        if(strcmp($pairs[0], "page") == 0) {
          continue;
        }
        if(count($pairs) < 2) {
          //Does not contain =
          if($flag)
            $queryURL .= "&";
          $queryURL .= $pairs[0];
          $flag = true;
          continue;
        }
        $key = $pairs[0];
        $value = $pairs[1];
        if($flag) {
          $queryURL .= "&";
        }
        $queryURL .= $key . "=" . $value;
        $flag = true;
      }
      return ($pageName . $queryURL . "&");
    }

    public function get_pages_div($pageName, $queryString) {
      if($this->totalCount <= 0)
        return null;
      $pagesDiv = null;
      $start = $end = 0;
      $next = $prev = 0;
      $prevTen = $nextTen = 0;
      $showPrev = $showNext = false;
      $totalPages = $this->get_number_of_pages();

      if($this->currentPage < 8) {
        $start = 1;
        if($totalPages <= self::PAGES_SHOWN) {
          $end = $totalPages;
          $next = $end;
          $nextTen = $end;
        }
        else {
          $end = self::PAGES_SHOWN;
          $nextTen = $end + 1;
          $showNext = true;
        }
        $prevTen = 1;
      }
      else {
        if($this->currentPage > $totalPages) {
          $this->currentPage = $totalPages;
        }
        if($this->currentPage > 8) {
          $start = $this->currentPage - self::PAGES_SKIP;
          $prevTen = $start - 1;
          $showPrev = true;
        }
        else {
          $start = 1;
          $prevTen = 1;
        }
        if($totalPages <= ($this->currentPage + self::PAGES_SKIP)) {
          $end = $totalPages;
          $nextTen = $end;
        }
        else {
          $end = $this->currentPage + self::PAGES_SKIP;
          $nextTen = $end + 1;
          $showNext = true;
        }
      }

      $queryURL = $this->get_page_url($pageName, $queryString);
      $pagesDiv = "<div id='pagenumbers'>";
      // $numberOfPages = $this->get_number_of_pages();
      if($showPrev) {
        $pagesDiv .= "<a class='page' href='" . $queryURL . "page=" . $prevTen . "'><<</a>";
        $pagesDiv .= "<a class='page' href='" . $queryURL . "page=" . ($this->currentPage - 1) ."'><</a>";
      }
      for($i=$start;$i<=$end;$i++) {
        if($this->currentPage == $i)
          $pagesDiv .= "<a class='page' href='" . $queryURL . "page=" . $i ."'><b>" . $i . "</b></a>";
        else
          $pagesDiv .= "<a class='page' href='" . $queryURL . "page=" . $i ."'>" . $i . "</a>";
      }
      if($showNext) {
        $pagesDiv .= "<a class='page' href='" . $queryURL . "page=" . ($this->currentPage + 1) . "'>></a>";
        $pagesDiv .= "<a class='page' href='" . $queryURL . "page=". $nextTen ."'>>></a>";
      }
      $pagesDiv .= "</div>";
      return $pagesDiv;
    }
  }
 ?>
