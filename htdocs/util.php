<?php

function get_home($email) {
  if (is_set($_SESSION["homedir:$email"])) {
    return $_SESSION["homedir:$email"];
  } else {
    $fh = fopen("/etc/passwd","r");
    $go_on = 1;
    $homedir = "";
    while ($go_on && $line = fgets($fh)) {
      $line = trim($line);
      $parts = preg_split("/:/", $line);
      $user = $parts[0];
      if ($user == $email) {
        $go_on = 0;
        $homedir = $parts[5];
      }
    }
    fclose($fh);
    if ($homedir != "") {
      $_SESSION["homedir:$email"] = $homedir;
    }
    return $homedir;
  }
}

function get_shares($email) {
  $homedir = get_home($email);
  $shares = glob("$homedir/*");
  $dirs = array();
  foreach ($shares as $share) {
    if (is_dir($share)) {
      $name = basename($share);
      array_push($dirs, $name);
    }
  }
  return $dirs;
}

function get_share_dir($email, $share) {
  $homedir = get_home($email);
  return "$homedir/$share";
}

?>
