<?php
    error_reporting(-1);
    include('session.php'); //includes sessions file, which includes the others needed
	$pagetitle=(isset($_GET['page']))?"Rate Your Mate | ".$_GET['page']:"Rate Your Mate";
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pagetitle;?></title>
    <!-- css stylesheets -->
    <link href='../css/styles.css' rel='stylesheet'/>
    <link href='../css/ui.spinner.css' rel='stylesheet'/>
    <link href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/redmond/jquery-ui.css' rel='stylesheet'/>
    <!-- javascript files -->
    <script type="text/javascript" src="../js/modernizer.js"></script>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js'></script>
    <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js'></script>
    <script type="text/javascript" src="../js/jquery-ui-timepicker.js"></script>
    <script type="text/javascript" src="../js/jquery.guid.js"></script>
    <script type="text/javascript" src="../js/ui.spinner.min.js"></script>
    <script type="text/javascript" src="../js/highcharts.js"></script>
    </head>
    <?php
    $greeting=($session->username!='')? "You are logged in as ".$session->realname." <a href='".DOC_ROOT."/logout.php'>Logout</a>" : "You are not logged in! <a href='".DOC_ROOT."/index.php'>Log in</a>";
    echo"<body><div class='right'>$greeting</div>";
?>
    
    