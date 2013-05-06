<?php
function user_display_menu() {
  ?>
  <div class="menu">
    <form action="index.php" method="post">
      <table>
      <tr>
        <td><img src="perlshare-login.png" /></td>
      </tr><tr>
        <td><button type="submit" value="display-shares" name="command" >Shares</input></td>
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

function user_display_shares($email) {
  ?>
  <div class="shares">
    <h1><?php print "$email"; ?></h1> 
    <form action="index.php" method="post">
    <table>
    <tr><th>Sharename</th><th>Kind</th><th>Share</th></tr>
    <?php
      $shares = get_shares($email);
      $count = 0;
      foreach ($shares as $share) {
        $from = "";
        if (shared_from($email, $share) != "") {
          $from = " (".shared_from($email, $share).")";
        }
          
        $count += 1;
        $cl = "white";if ($count%2 == 0) { $cl = "gray"; }
        $click = "e=document.getElementById('$share').style;".
                 "if ( e.display == 'none') { e.display = 'table-row'; } else { e.display = 'none'; };";
        ?>
          <tr class="<?php print $cl; ?>">
            <td class="name"><?php print "$share$from";?></td>
            <td class="info">
            <p class="kind" onclick="<?php print $click; ?>">
            <?php print get_kind_share($email, $share); ?>
            </p>
            </td>
            <td class="share">
            <?php
            if ($from == "") { 
              ?>
              <button type="submit" value=<?php print "\"share:$share\""; ?> name="command">
              <?php print get_kind_share($email, $share) == "Shared" ? "(un)share" : "share"; ?>
              </button>
              <?php
            }
            ?>
            </td>
          </tr>
          <tr class="shareinfo" id="<?php print "$share"; ?>" style="display:none;">
          <td colspan="3">
            <h3>Shared with:</h3>
            <ul>
            <?php 
              foreach (get_sharees($email, $share) as $sharee) {
                print "<li>$sharee</li>";
              }
            ?>
            </ul>
          </td>
          </tr>
        <?php
      }
    ?>
    </table>
    </form>
  <?php
}

function user_change_password() {
}

function user_share_share($email, $share) {
  ?>
  <div class="shares">
    <h1><?php print "$email - $share";?></h1>
    <form action="index.php" method="post">
    <input type="hidden" name="share" value="<?php print "$share";?>" />
    <table>
    <tr><th colspan="3">Share with</th></tr>
    <?php
      $sharees = get_sharees($email, $share);
      $holders = get_share_holders($email);
      $count = 0;
      foreach ($holders as $holder) {
        $yes = in_array($holder, $sharees, true); 
        if ($yes != false) { $checked = "checked=\"checked\""; } else { $checked = ""; }
        $count += 1;
        $cl = "white";if ($count%2 == 0) { $cl = "gray"; }
        ?>
        <tr class="<?php print $cl; ?>">
          <td colspan="3" class="holder">
          <input type="checkbox" name="sharee:<?php print "$holder";?>" value="sharee:<?php print "$holder";?>" <?php print "$checked";?> />
          <?php print "$holder";?>
          </td>
        </tr>
      <?php 
      }
      ?>
    <td class="name" />
    <td class="holder" ><button type="submit" name="command" value="display-shares">cancel</button></td>
    <td><button type="submit" name="command" value="set-sharees:<?php print "$share";?>">share</button></td>
    </table>
    </form>
  </div>
  <?php
}

function user_set_sharees($email, $share) {
  $sharees = array();
  #print "email=$email, share=$share<br>";
  foreach ($_POST as $key) {
    #print "$key<br>";
    if (substr($key, 0, 7) == "sharee:") {
      $email_sharee = substr($key, 7);
      array_push($sharees, $email_sharee);
    }
  }
  set_sharees($email, $share, $sharees);
}

function user_logout() {
  logout();
}

function user_main() {
  $cmd = isset($_POST['command']) ? $_POST['command'] : 'display-shares';
  if (substr($cmd, 0, 6) == "share:") {
    set_cmd_share(substr($cmd, 6));
    $cmd = "share";
  } else if (substr($cmd, 0, 12) == "set-sharees:") {
    set_cmd_share(substr($cmd, 12));
    $cmd = "set-sharees";
  }
  $email = account();
  $share = isset($_POST['share']) ? $_POST['share'] : '';
  
  if ($cmd == "set-sharees") {
    user_set_sharees($email, $share);
    $cmd = "display-shares";
  }

  user_display_menu();
  if ($cmd == "display-shares") {
    user_display_shares($email);
  } else if ($cmd == "change-password") {
    user_change_password();
  } else if ($cmd == "share") {
    user_share_share($email, get_cmd_share());
  } else if ($cmd == "logout") {
    user_logout();
  }
}

?>
