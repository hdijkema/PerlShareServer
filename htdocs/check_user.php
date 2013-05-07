<?php
include "auth";

$share = $_POST['share'];
$user  = $_POST['user'];
$pass  = $_POST['pass'];

if (try_login_normal_user($user, $pass)) {
  print "OKOKOK\n";
} else {
  print "$user not authenticated\n";
}
?>
