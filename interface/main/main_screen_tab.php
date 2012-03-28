<?php
 include_once("../globals.php");
 require_once("$srcdir/formdata.inc.php");
$_SESSION["encounter"] = "";

 // Fetching the password expiration date
 $is_expired=false;
 if($GLOBALS['password_expiration_days'] != 0){
 $is_expired = false;
 $q=formData('authUser','P');
 $result = sqlStatement("select pwd_expiration_date from users where username = '".$q."'");
 $current_date = date("Y-m-d");
 $pwd_expires_date = $current_date;
 if($row = sqlFetchArray($result)) {
  $pwd_expires_date = $row['pwd_expiration_date'];
 }

// Displaying the password expiration message (starting from 7 days before the password gets expired)
 $pwd_alert_date = date("Y-m-d", strtotime($pwd_expires_date . "-7 days"));

 if (strtotime($pwd_alert_date) != "" && strtotime($current_date) >= strtotime($pwd_alert_date) && 
     (!isset($_SESSION['expiration_msg']) or $_SESSION['expiration_msg'] == 0)) {

  $is_expired = true;
  $_SESSION['expiration_msg'] = 1; // only show the expired message once
 }
}
 if ($GLOBALS['athletic_team']) {
  $frame1url = "../reports/players_report.php?embed=1";
 } else {
  if ($is_expired) {
   $frame1url = "pwd_expires_alert.php"; //php file which display's password expiration message.
  }
  elseif (isset($_GET['mode']) && $_GET['mode'] == "loadcalendar") {
   $frame1url = "calendar/index.php?pid=" . $_GET['pid'];
   if (isset($_GET['date'])) $frame1url .= "&date=" . $_GET['date'];
  } else {
   if ($GLOBALS['concurrent_layout']) {
    // new layout
    if ($GLOBALS['default_top_pane']) {
      $frame1url=$GLOBALS['default_top_pane'];
     } else {
     $frame1url = "main_info.php";
     }
    }
   else
    // old layout
    $frame1url = "main.php?mode=" . $_GET['mode'];
  }
 }

$nav_area_width = $GLOBALS['athletic_team'] ? '230' : '130';
if (!empty($GLOBALS['gbl_nav_area_width'])) $nav_area_width = $GLOBALS['gbl_nav_area_width'];
?>
<!DOCTYPE html>
<html>
<head>
<title>
<?php echo $openemr_name ?>
</title>
<link rel="stylesheet" type="text/css" href="../themes/iconic/iconic.css" />
<link rel="stylesheet" type="text/css" href="main_screen_tab.css" />
<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="../../library/js/jquery.touchSwipe-1.2.5.js"></script>
<script language='JavaScript'>
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script>
<script src="../../library/js/objectWatch.js"></script>
<script src="main_screen_tab.js"></script>
</head>
<body>
    <iframe src='daemon_frame.php' name='Daemon' style="display:none"></iframe>
<div id="divHeader"><iframe src='main_title.php' name='Title'></iframe></div>
<div id="divSpacer"></div>
<div id="divBottom">
    <div id="divNav">
        <iframe name="left_nav"  src="left_nav.php"></iframe>
    </div>
    
    <div id="divMain">
        <div id="navButtons">
            <?php $buttonStyle="checkbox" ?>
            <span id="butTab1" class="active"/>Calendar</span>
            <span id="butTab2"/>Patient</span>
            <span id="butTab3"/>Messages</span>
            <input id="multiTabs" type="checkbox" title="Enable Multiple Tabs"/>
        </div>
        <div id="divframes">
            <div id="divMain-1" class="main">
                <iframe name="calendar" class="main" src="main_info.php"></iframe>
            </div>
            <div id="divMain-2" class="main hidden">
                <iframe name="patient" class="main " src="../new/new.php"></iframe>
            </div>
            <div id="divMain-3" class="main hidden">
                <iframe name="messages" class="main" src="messages/messages.php"></iframe>
            </div>
        </div>
    </div>
</div>
<script>
var msgAddPat='<?php xl('You must first select or add a patient.','e') ?>';
var msgSelEnc='<?php xl('You must first select or create an encounter.','e') ?>';
var pathWebroot='<?php echo "$web_root/interface/" ?>';
setupMainScreenTabs();

</script>
</body>
</html>
