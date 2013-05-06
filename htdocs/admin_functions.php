<?php
#include "auth.php";

function admin_display_menu() {
  
}

function admin_display_users() {
}

function admin_change_password() {
}

function admin_add_user() {
}

function admin_remove_user() {
}

function admin_logout() {
  logout();
}

function admin_main() {
  admin_display_menu();
  
  $cmd = $_POST['command'];
  
  if ($cmd == "display-users") {
    admin_display_users();
  } else if ($cmd == "change-password") {
    admin_change_password();
  } else if ($cmd == "add-user") {
    admin_add_user();
  } else if ($cmd == "remove-user") {
    admin_remove_user();
  } else if ($cmd == "logout") {
    admin_logout();
  }
}


?>
