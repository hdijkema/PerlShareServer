<?php session_start(); ?>
<html>
<head>
<title>PerlShare file sharing</title>
</head>
<body>
<?php
  include "auth.php";
  if (logged_in()) {
  } else {
    login();
  }
?>
</body>
</html>
