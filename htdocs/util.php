<?php

function get_home($email) {
  if (isset($_SESSION["homedir:$email"])) {
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
  sort($dirs);
  return $dirs;
}

function get_share_dir($email, $share) {
  $homedir = get_home($email);
  return "$homedir/$share";
}

function get_kind_share($email, $share)
{
  $dir = get_share_dir($email, $share);
  $shared_with = "$dir/.shared_with";
  if (is_file($shared_with)) {
    $fh = fopen($shared_with, "r");
    $line = fgets($fh);
    $line = trim($line);
    fclose($fh);
    if ($line) {
      return "Shared";
    } else {
      return "Folder";
    }
  } else {
    return "Folder";
  }
}

function shared_from($email, $share) {
  $dir = get_share_dir($email, $share);
  if (is_link($dir)) {
    $d = readlink($dir);
    $parts = preg_split("%[/]%", $d);
    $from = $parts[3];
    return $from;
  } else {
    return "";
  }
}

function get_sharees($email, $share)
{
  if (get_kind_share($email, $share) == "Shared") {
    $sharees = array();
    $dir = get_share_dir($email, $share);
    $shared_with = "$dir/.shared_with";
    $fh = fopen($shared_with, "r");
    while ($line = fgets($fh)) {
      $email = trim($line);
      if (!in_array($email, $sharees)) {
        array_push($sharees, $email);
      }
    }
    fclose($fh);
    return $sharees;
  } else {
    return array();
  }
}

function get_cmd_dir() {
  $cfg = parse_ini_file("/etc/perlshare/config.ini", true);
  $dir = $cfg['locations']['commands'];
  return $dir;
}

function create_user($email, $password) {
  $file = get_cmd_dir()."/create $email $password";
  $fh = fopen($file, "w");
  fclose($fh);
  while (!exists_user($email)) {
    sleep(1);
  }
}

function exists_user($email) {
  $file = get_cmd_dir()."/exists $email";
  $fh = fopen($file, "w");
  fclose($fh);
  while (file_exists($file)) {
    error_log("$file exists");
    sleep(1);
  }
  $file_ok = get_cmd_dir()."/result exists ok";
  $file_nok = get_cmd_dir()."/result exists nok";
  error_log("checking $file_ok and $file_nok");
  while (!file_exists($file_ok) && !file_exists($file_nok)) {
    sleep(1);
  }
  $ok = true;
  if (file_exists($file_nok)) {
    $ok = false;
  }
  error_log("ok = $ok");
  unlink($file_ok);
  unlink($file_nok);
  return $ok;
}

function set_sharees($email, $share, $sharees) {
  $current = get_sharees($email, $share);
  foreach ($sharees as $sharee) {
    $file = get_cmd_dir()."/share $email $sharee $share";
    $fh = fopen($file ,"w");
    fclose($fh);
    while (file_exists($file)) {
      sleep(1);
    }
  }
  foreach ($current as $c) {
    if (!in_array($c, $sharees)) {
      $file = get_cmd_dir()."/unshare $email $c $share";
      $fh = fopen($file ,"w");
      fclose($fh);  
      while (file_exists($file)) {
        sleep(1);
      }
    }
  }
}

function get_share_holders($email) {
  $fh = fopen("/etc/passwd","r");
  $share_holders = array();
  while ($line = fgets($fh)) {
    $line = trim($line);
    $parts = preg_split("/:/", $line);
    $user = $parts[0];
    $homedir = $parts[5];
    if (preg_match("%^[/]home[/]perlshare%", $homedir)) {
      if ($user != "perlshare") {
        if ($user != $email) {
          array_push($share_holders, $user);
        }
      }
    }
  }
  fclose($fh);
  sort($share_holders);
  return $share_holders;
}

function get_users() {
  return get_share_holders("");
}

function get_cmd_share() {
  return $_SESSION['share'];
}

function set_cmd_share($share) {
  $_SESSION['share'] = $share;
}

?>
