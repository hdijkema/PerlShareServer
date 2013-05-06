<?php
# auth.php

function logged_in() {
  return $_SESSION['logged_in'] == 1;
}

function account() {
  return $_SESSION['account']; 
}

function usertype() {
  return $_SESSION['usertype'];
}

function login() {
  $logged_in = 0;
  if ($_POST[email] != "") {
    $email = trim($_POST[email]);
    $passwd = trim($_POST[passwd]);
    
    # Users need to have a home directory in /home/perlshare
    $user_ok = 0;
    $fh = fopen("/etc/passwd","r");
    $go_on = 1;
    while ($go_on && $line = fgets($fh)) {
      $line = trim($line);
      $parts = preg_split("/:/", $line);
      $user = $parts[0];
      $homedir = $parts[5];
      if ($user == $email) {
        $go_on = 0;
        if (preg_match("^[/]home[/]perlshare", $homedir)) {
          $user_ok = 1;
        }
      }
    }
    fclose($fh);
    
    if ($user_ok) {
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
    } else {
      ?>
      <div class="error">
      <p>Authorization for '<?php echo $email; ?>' failed</p>
      <?php
    }
  } 

  if ($logged_in == 0) {
    ?>
    <div class="login">
    <img src="perlshare_logo.png" />
    <form action="index.php" method="post">
      <table><tr>
      <td>eMail:</td><td><input type="text" name="email" /></td>
      </tr><tr>
      <td>Password:</td><td><input type="password" name="passwd" /></td>
      </tr><tr>
      <td></td><td><input type="submit" value="login" /></td>
      </tr>
      </table>
    </form>
    </div>
    <?php
  }
}

?>
