<?php
include "auth.php";

$share = $_POST['share'];
$user  = $_POST['email'];
$pass  = $_POST['pass'];
$key   = $_POST['key'];

if (try_login_normal_user($user, $pass)) {
  $dir = 
  fopen(
  print "OKOKOK\n";
} else {
  print "$user not authenticated\n";
}
?>
