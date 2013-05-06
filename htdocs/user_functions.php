<?php
#require "auth.php";

function user_display_menu() {
  ?>
  <div class="menu">
    <form action="index.php" method="post">
      <table>
      <tr>
        <td><img src="perlshare-login.png" /></td>
      </tr><tr>
        <td><button type="submit" value="change-password" name="command" >Change Password</input></td>
      </tr><tr>
        <td><button type="submit" value="logout" name="command" >Logout</button></td>
      </tr>
    </form>
  </div>
  <?php
}

function user_display_shares($email) {
  ?>
  <div class="shares">
    <table>
  <?php
    $shares = get_shares($email);
    foreach ($shares as $share) {
      ?>
      <tr><td><?php print "$share";?></td><td>info</td><td>share</td></tr>
      <?php
    }
  ?>
  <?php
}

function user_change_password() {
}

function user_logout() {
  logout();
}

function user_main() {
  user_display_menu();
  
  var_dump($_POST);
  $cmd = isset($_POST['command']) ? $_POST['command'] : 'display-shares';
  
  $email = $_SESSION['email'];
  
  if ($cmd == "display-shares") {
    user_display_shares($email);
  } else if ($cmd == "change-password") {
    user_change_password();
  } else if ($cmd == "logout") {
    user_logout();
  }
}


?>
