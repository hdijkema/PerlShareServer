<html>
<head>
<title>PerlShare file sharing</title>
</head>
<body>
<?php
  echo "<p>HI!</p>";
  var_dump (pam_auth('iconnect', 'iconnect' ,&$error,0));
  print $error;
?>
</body>
</html>
