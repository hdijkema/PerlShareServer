<?php
#include "auth.php";

function admin_display_menu() {
  ?>
  <div class="menu">
    <form action="index.php" method="post">
      <table>
      <tr>
        <td><img src="perlshare-login.png" /></td>
      </tr><tr>
        <td><button type="submit" value="display-users" name="command" >Users</input></td>
      </tr><tr>
        <td><button type="submit" value="add-user" name="command" >Add user</input></td>
      </tr><tr>
        <td><button type="submit" value="change-password" name="command" >Change Password</input></td>
      </tr><tr>
        <td><button type="submit" value="logout" name="command" >Logout</button></td>
      </tr>
      </table>
    </form>
  </div>
  <?php
}

function admin_display_users() {
  ?>
  <div class="shares">
    <table>
    <tr>
      <th>User</th><th colspan="2">Action</th>
    </tr>
    <?php
      $users = get_users();
      foreach ($users as $user) {
        ?>
        <tr class="<?php print "$cl";?>">
        <td class="name"><?php print "$user"?></td>
        <td class="action">
          <button type="submit" value="delete:<?php print "$user";?>" name="command">delete</button>
        </td>
        <td>
          <button type="submit" value="reset-passwd:<?php print "$user";?>" name="command">reset</button>
        </td>
        </tr>
        <?php
      }
    ?>
  </div>
  <?php
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
  
  $cmd = isset($_POST['command']) ? $_POST['command'] : 'display-users';
  
  ?>
    <h1 class="admin">Administration</h1>
  <?php
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
