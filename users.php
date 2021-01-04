<?php
  require_once("includes/routines.php");

  const PAGE_ACCESS_LEVEL = AccessLevel::PAGE_USERS;
  require_once("includes/validate_session.php");

  const ADD_PARAM = "add";
  const DELETE_PARAM = "del";
  const EDIT_PARAM = "edit";
  const ACTIVATION_MAIL_PARAM = "activatemail";
  const ACTIVATE_PARAM = "activate";
  const DEACTIVATE_PARAM = "deactivate";

  const USER_PARAM = "username";
  const EMAIL_PARAM = "email";
  const ACCESS_LEVEL_PARAM = "accesslevel";
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Manage Users | BHI</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css" />
    <link rel="stylesheet" type="text/css" href="css/user.css" />
  </head>
  <body>
    <div id="wrapper">
      <img id="logo" src="img/BHI Logo.jpg" />
      <?php require_once("includes/menu.php"); ?>
      <div id="main">
        <h2>Users</h2>
        <?php
        if(isset($_GET[ADD_PARAM]) || isset($_GET[EDIT_PARAM]) || isset($_GET[DELETE_PARAM])) {
          $addUser = $editUser = $deleteUser = false;
          $count = 0;
          if(isset($_GET[ACTIVATION_MAIL_PARAM])) {
            $count++;
          }
          if(isset($_GET[ACTIVATE_PARAM])) {
            $count++;
          }
          if(isset($_GET[DEACTIVATE_PARAM])) {
            $count++;
          }
          if(isset($_GET[ADD_PARAM])) {
            $addUser = true;
            $count++;
          }
          if(isset($_GET[EDIT_PARAM])) {
            $editUID = $_GET[EDIT_PARAM];
            $editUser = true;
            $count++;
          }
          if(isset($_GET[DELETE_PARAM])) {
            $deleteUID = $_GET[DELETE_PARAM];
            $deleteUser = true;
            $count++;
          }
          if($count > 1) {
            die("<p class='errorMsg'>Error: Cannot set multiple params</p>"
            . "<p>Go back to <a href='/users.php'>Users</a></p>");
          }

          $uerror = $perror = $eerror = $aerror = "";
          $successMsg = "";
          $success = false;

          if($_SERVER["REQUEST_METHOD"] == "POST") {
            if(!$deleteUser) {
              $username = $_POST[USER_PARAM];
              $email = $_POST[EMAIL_PARAM];
              $accessLevel = $_POST[ACCESS_LEVEL_PARAM];
            }

            try {
              $user = new User();
              if($addUser) {
                //Add New User
                if($user->add_user($username, $email, $accessLevel)) {
                  $success = true;
                  $successMsg = "User added successfully!";
                }
              }
              if($editUser) {
                //Edit existing user
                if($user->load_id($editUID)) {
                  if($user->edit_user($username, $email, $accessLevel)) {
                    $success = true;
                    $successMsg = "User edited successfully!";
                  }
                }
                else {
                  throw new AppException("User does not exist", AppException::USER_ERROR);
                }
              }
              if($deleteUser) {
                if($user->load_id($deleteUID)) {
                  if($user->delete_user()) {
                    $success = true;
                    $successMsg = "User deleted successfully!";
                  }
                }
                else {
                  throw new AppException("User does not exist", AppException::USER_ERROR);
                }
              }
            }
            catch(AppException $e) {
              $success = false;
              $code = $e->get_code();
              if($code == AppException::USER_ERROR) {
                $uerror = $e->message();
              }
              elseif($code == AppException::EMAIL_ERROR) {
                $eerror = $e->message();
              }
              elseif($code == AppException::ACCESS_ERROR) {
                $aerror = $e->message();
              }
              else {
                $aerror = $e->message();
              }
            }
          }
          if(!$success) {
            //generate URL
            $actionURL = "users.php";
            $legend = "";
            if($addUser){
              $actionURL .= "?add=true";
              $legend = "Add New User";
            }
            elseif($editUser) {
              $actionURL .= '?edit=' . $editUID . '"';
              $legend = "Edit Existing User";

              $user = new User();
              if($user->load_id($editUID)) {
                $username = $user->get_username();
                $email = $user->get_email();
                $accessLevel = $user->get_access_level();
                $loadedValues = true;
              }
              else{
                $loadedValues = false;
                $aerror = "No such user exists!";
              }
            }
            elseif($deleteUser) {
              $actionURL .= '?del=' . $deleteUID . '"';
              $legend = "Delete User";
            }
        ?>
        <form action="<?php echo $actionURL; ?>" method="POST">
          <fieldset>
            <legend><?php echo $legend; ?></legend>
             <?php if(!$deleteUser) { ?>
            <label class="labelblock" for="username">Username</label>
  					<input type="text" name="username" id="username" placeholder="Username" <?php if($editUser && $loadedValues) echo "value='" . $username . "'"; ?> required />
  					<?php echo "<p class='error'>" . $uerror . "</p>" ; ?>
  					<label class="labelblock" for="email">Email</label>
  					<input type="email" name="email" id="email" placeholder="Email" <?php if($editUser && $loadedValues) echo "value='" . $email . "'"; ?> required />
  					<?php echo "<p class='error'>" . $eerror . "</p>" ; ?>
            <label class="labelblock" for="accesslevel">Access Level</label>
  					<select name="accesslevel" id="accesslevel">
              <?php
              try {
                $aLevel = new AccessLevel();
                if($addUser)
                  $accessLevel = AccessLevel::EMPLOYEE;
                for($i=0;$aLevel->is_valid_access_level($i);$i++) {
                  if($accessLevel == $i)
                    echo "<option selected='selected' value='" . $i . "'>" . $aLevel->get_name($i) . "</option>";
                  else
                    echo "<option value='" . $i . "'>" . $aLevel->get_name($i) . "</option>";
                }
              }
              catch(AppException $e) {
                if(!($e->get_code() == AppException::ACCESS_ERROR)) {
                  throw $e;
                }
              }
              ?>
            </select> <br>
            <input type="submit" value="Submit" />
            <?php
              }
              elseif($deleteUser) {
                $user = new User();
                if($user->load_id($deleteUID)) {
                  echo "<input type='hidden' name='confirmDeletion' value='true' />";
                  echo "Are you sure you want to delete user, " . $user->get_username() . "? <br>";
                  ?>
                  <input type="button" onclick="window.location.href='users.php'" value="Cancel" />
                  <input type="submit" value="Yes" />
                  <?php
                }
                else {
                    $aerror = "No such user exists!";
                  }
              }
            ?>
            <?php echo "<p class='error'>" . $aerror . "</p>" ; ?>
          </fieldset>
        </form>
        <?php

          }
          elseif($success) {
            echo "<fieldset>";
            echo "<p class='success'>" . $successMsg . "</p>";
						echo "<p> Go back to <a href='/users.php'>Users Page.</a></p>";
            echo "</fieldset>";
          }
        }
        else {
          if(isset($_GET[ACTIVATION_MAIL_PARAM])) {
            $activateMail = true;
            $activateID = sanitize_input($_GET[ACTIVATION_MAIL_PARAM]);
            if(empty($activateID))
              die("Activation mail needs a parameter");
            else {
              try{
                $user = new User();
                $user->load_id($activateID);
                $activate = new Activate($user);
                $activate->load_user($user);
                if($activate->send_activation_mail()) {
                  $activateMsg = "<span class='success'>Mail sent!</span>";
                }
              }
              catch(AppException $e) {
                $activateMsg = "<span class='error'>" . $e->message() . "</span>";
              }
            }
          }
          elseif(isset($_GET[ACTIVATE_PARAM])) {
            $activationID = sanitize_input($_GET[ACTIVATE_PARAM]);
            if(empty($activationID))
              die("Activation needs a parameter");
            else {
              try {
                $user = new User();
                $user->load_id($activationID);
                $activate = new Activate();
                $activate->load_user($user);
                $activate->activate();
              }
              catch(AppException $e) {
                $activateMsg = "<span class='error'>" . $e->message() . "</span>";
              }
            }
          }
          elseif(isset($_GET[DEACTIVATE_PARAM])) {
            $deactivateID = sanitize_input($_GET[DEACTIVATE_PARAM]);
            if(empty($deactivateID))
              die("Deactivation needs a parameter");
            else {
              try {
                $currentUserID = $sessionManager->get_user()->get_id();
                if($currentUserID == $deactivateID)
                  throw new AppException("Cannot deactivate own account!", AppException::ACTIVATION_ERROR);

                $user = new User();
                $user->load_id($deactivateID);
                $activate = new Activate();
                $activate->load_user($user);
                $activate->deactivate();
              }
              catch(AppException $e) {
                $activateMsg = "<span class='error'>" . $e->message() . "</span>";
              }
            }
          }
         ?>
        <input type="button" onclick="window.location.href='users.php?add=true'" value="Add User"/> <br />
        <?php
          try {
            $user = new User();
            $uids = $user->get_all_ids();
            $userCount = count($uids);

            echo "<table><tr> <th>Options</th> <th>UID</th> <th>Username</th>" .
                 " <th>Email</th> <th>Access Level</th> <th>Status</th>";
                  //." <th>Login Time</th><th>Logout Time</th> </tr>";

            for($i=0;$i<$userCount;$i++) {
              $user->load_id($uids[$i]);
              echo "<tr>";
              echo "<td> <a href='/users.php?edit=" . $user->get_id() . "'><img class='icon' src='img/setting-tool.png' alt='Edit User' title='Edit User'/></a>" .
                   "<a href='/users.php?del=". $user->get_id() . "'><img class='icon' src='img/dustbin.png' alt='Delete User' title='Delete User'/></a>";
              if($user->get_activated() == false){
                echo "<a href='/users.php?activate=" . $user->get_id() ."'><img class='icon' src='img/locked-padlock.png' alt='Activate User' title='Activate User'/></a> </td>";
              } else {
                echo "<a href='/users.php?deactivate=" . $user->get_id() ."'><img class='icon' src='img/small-key.png' alt='Deactivate User' title='Dectivate User'/></a> </td>";
              }
              echo "<td>" . $user->get_id() . "</td>";
              echo "<td><a href='/users.php?edit=" . $user->get_id() . "''>" . $user->get_username() . "</a></td>";
              echo "<td>" . $user->get_email() . "</td>";
              echo "<td>" . $user->get_access_level() . "</td>";
              echo "<td>";

              if($user->get_activated()) {
                if(((isset($activationID) && ($activationID == $user->get_id())) || (isset($deactivateID) && ($deactivateID == $user->get_id()))) && isset($activateMsg))
                  echo $activateMsg;
                else
                  echo "Activated";
              }
              else {
                if($user->get_password() == "") {
                  if(isset($activateID) && ($activateID == $user->get_id()) && isset($activateMsg))
                    echo $activateMsg;
                  elseif(isset($activationID) && ($activationID == $user->get_id()) && isset($activateMsg))
                    echo $activateMsg;
                  else
                    echo "<input type='button' value='Resend Activation Mail' onclick='window.location.href=\"users.php?activatemail=" . $user->get_id() . "\"' />";
                }
                else {
                  if(isset($deactivateID) && ($deactivateID == $user->get_id()) && isset($activateMsg))
                    echo $activateMsg;
                  else
                    echo "Deactivated";
                }
              }
              echo "</td>";
              //echo "<td>logout time" . "</td>";
              echo "</tr>";
            }
          }
          catch(AppException $e) {
            echo "<p class='error'>" . $e->message() . "</p>";
          }
        }
        ?>
      </div>
    </div>
  </body>
</html>
