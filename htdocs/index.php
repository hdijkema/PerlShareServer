<html>
<head>
<title>PerlShare file sharing</title>
</head>
<body>
<?php
  echo "<p>HI!</p>";
  var_dump (pam_auth('hans@oesterholt.net', 'rotop2' ,&$error));
  print $error;
?>
</body>
</html>
