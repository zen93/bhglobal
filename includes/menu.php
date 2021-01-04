<?php
  $access_level = new AccessLevel();
?>
<div id="sidebar">
  <ul id="sidemenu">
    <?php
      if($access_level->has_privilege($accessLevel, AccessLevel::PAGE_DASHBOARD))
      echo '<a href="/dashboard.php"><li>Dashboard</li></a>';
      if($access_level->has_privilege($accessLevel, AccessLevel::PAGE_USERS))
      echo '<a href="/users.php"><li>Users</li></a>';
      if($access_level->has_privilege($accessLevel, AccessLevel::PAGE_CUSTOMERS))
      echo '<a href="/customers.php"><li>Customers</li></a>';
      if($access_level->has_privilege($accessLevel, AccessLevel::PAGE_TENDERS))
      echo '<a href="/tenders.php"><li>Tender Search</li></a>';
      if($access_level->has_privilege($accessLevel, AccessLevel::PAGE_SETTINGS))
      echo '<a href="/settings.php"><li>Settings</li></a>';
      echo '<a href="/logout.php"><li>Logout</li></a>';
    ?>
  </ul>
</div>
