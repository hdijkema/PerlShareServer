<?php
include "auth.php";
include "util.php";

$share = $_POST['share'];
$user  = $_POST['email'];
$pass  = $_POST['pass'];
$key   = $_POST['key'];

if (try_login_normal_user($user, $pass)) {

  $file = get_cmd_dir()."/public_key $email $share";
  $fh = fopen($file, "w");
  fputs($fh, $key);
  fclose($fh);
  while (file_exists($file)) {
    sleep(1);
  }
  
  print "OKOKOK\n";
} else {
  print "$user not authenticated\n";
}
?>
