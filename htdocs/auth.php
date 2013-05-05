# auth.php
<?php

function logged_in() {
  return $_SESSION['logged_in'] == 1;
}

function account() {
  return $_SESSION['account']; 
}

function usertype() {
  return $_SESSION['usertype'];
}

if (logged_in()) {
  # do nothing
} else {

  $logged_in = 0;
  if ($_POST[email] != "") {
    $email = trim($_POST[email]);
    $passwd = trim($_POST[passwd]);
    
    if ($email != "root" && $email != "") {
      if (pam_auth($email, $passwd, &$error)) {
        $_SESSION['logged_in'] = 1;
        $_SESSION['account'] = $email;
        if ($email == "perlshare") { # admin
          $_SESSION['usertype'] = "admin";
        } else {
          $_SESSION['usertype'] = "user";
        }
        $logged_in = 1;
      }
    }
  } 

  if ($logged_in == 0) {
    ?>
    <form class="login" action="index.php" method="post">
      <table><tr>
      <td>eMail:</td><td><input type="text" name="email" /></td>
      </tr><tr>
      <td>Password:</td><td><input type="password" name="passwd" /></td>
      </tr><tr>
      <td></td><td><input type="submit" value="login" /></td>
      </tr>
      </table>
    </form>
    <?php
  }
}

?>
