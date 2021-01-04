<?php
  require_once("includes/routines.php");

  const PAGE_ACCESS_LEVEL = AccessLevel::PAGE_DASHBOARD;
  require_once("includes/validate_session.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Dashboard | BHI</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <link rel="stylesheet" type="text/css" href="css/dashboard.css" />
    <script src="/scripts/dashboard.js"></script>
  </head>
  <body>
    <div id="wrapper">
      <img id="logo" src="img/BHI Logo.jpg" />
      <?php require_once("includes/menu.php") ?>
      <div id="main">
        <?php
          $conn = new Database();
          $stmt = $conn->prepare("SELECT * FROM dashboard WHERE did = ?");

          for($i=0; $i<4; $i++) {
            $stmt->bind_param("s", $i);
      			$stmt->execute();
      			$result = $stmt->get_result();

            if($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $item["name"][$i] = $row["name"];
              $item["value"][$i] = $row["value"];
              $item["last_modified"][$i] = $row["last_modified"];
            }
          }

          echo "<h2>Dashboard</h2>";
          echo "<h3>Exchange Rates</h3>";
          echo $item["name"][0] . ": " . $item["value"][0];
          echo "<p class='update'>Last Updated: " . $item["last_modified"][0] . "</p>";
          echo "<input type='hidden' id='" . $item["name"][1] . "' value='". $item["value"][1] . '@' .$item["last_modified"][1] ."' /> ";
          echo "<input type='hidden' id='" . $item["name"][2] . "' value='". $item["value"][2] . '@' .$item["last_modified"][2] ."' /> ";
          echo "<input type='hidden' id='" . $item["name"][3] . "' value='". $item["value"][3] . '@' .$item["last_modified"][3] ."' /> ";

        ?>
        <hr>
        <h3>Currency Exchange Calculator</h3>
        <div id="currform">
          <input type="number" id="amount" name="amount" value="1" />
          <select id="base" name="base" value="INR">
            <option value="INR">INR</option>
            <option value="USD">USD</option>
            <option value="SGD">SGD</option>
          </select>
          <span> to </span>
          <select id="foreign" name="foreign" value="USD">
            <option value="USD">USD</option>
            <option value="SGD">SGD</option>
            <option value="INR">INR</option>
          </select>
          <span id="convertedAmt">0.00</span>
          <input type="button" value="Convert" onclick="convert_currency()" />
          <?php echo "<p class='update'>Last Updated: " . $item["last_modified"][1] . "</p>"; ?>
          <hr>
          <h3>Status</h3>
          <?php
            $stmt = $conn->prepare("SELECT * FROM status WHERE id = ?");

            for($i=1; $i<=2; $i++) {
              $stmt->bind_param("s", $i);
              $stmt->execute();
              $result = $stmt->get_result();

              if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $item["name"][$i] = $row["name"];
                $item["success"][$i] = $row["success"];
                $item["last_modified"][$i] = $row["last_modified"];

                $code[$i] = "";
                $success = $item["success"][$i];
                if($success == 1)
                  $code[$i] = "success";
                elseif ($success == 2)
                  $code[$i] = "failure";
                elseif ($success == 3)
                  $code[$i] = "partial failure";
                $item["code"][$i] = $code[$i];
              }
            }
           ?>
          <span>Dashboard Status:</span>
          <?php
            $class = "fail";
            if($item["success"][1] == 1)
              $class = "ok";
            echo "<span class='" . $class . "'>" . $code[1] . "</span> <br>";
            echo "<p class='update'>Last Updated: " . $item["last_modified"][1] . "</p>";
           ?>
          <span>Tenders Status:</span>
          <?php
            $class = "fail";
            if($item["success"][2] == 1)
              $class = "ok";
            echo "<span class='" . $class . "'>" . $code[2] . "</span>";
            echo "<p class='update'>Last Updated: " . $item["last_modified"][2] . "</p>";
           ?>
           <hr>
        </div>
      </div>
    </div>
  </body>
</html>
