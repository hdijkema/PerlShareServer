<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>PerlShare file sharing</title>
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
</head>
<body>
<?php
  include "auth.php";
  include "admin_functions.php";
  include "user_functions.php";
  
  if (logged_in()) {
    if (usertype() == "user") {
      user_main();
    } else if (usertype() == "admin") {
      admin_main();
    } else {
      # nothing
    }
  } else {
    login();
  }
?>
</body>
</html>

