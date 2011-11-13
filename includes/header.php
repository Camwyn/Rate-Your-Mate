<?php
    error_reporting(-1);
    include('session.php'); //includes sessions file, which includes the others needed
    $pagetitle=(isset($_GET['page'])&&$_GET['page']!='')?"Rate Your Mate | ".$_GET['page']:"Rate Your Mate";
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
    <style>
        #nav{list-style:none;}
        #nav li{display:inline;}
    </style>
</head>
<body>
<!--- Nav links --->
<ul id='nav'>
<li><a href="<?php echo DOC_ROOT;?>/index.php">Index</a></li>
<li><a href="<?php echo DOC_ROOT;?>/includes/test.php">Test Page</a></li>
<li><a href="<?php echo DOC_ROOT;?>/activity.php">Activity</a></li>
<!--- Teacher-only links --->
<?php if ($session->isInstructor()||$session->isAdmin()){ ?>
    <li><a href="<?php echo DOC_ROOT;?>/instructor/add_class.php">Add Class</a></li>
    <li><a href="<?php echo DOC_ROOT;?>/instructor/evaluateereport.php">Evaluatee Report</a></li>
    <li><a href="<?php echo DOC_ROOT;?>/instructor/evaluatorreport.php">Evaluator Report</a></li>
    <li><a href="<?php echo DOC_ROOT;?>/student/evaluation.php">Student Contract</a></li>
    <li><a href="<?php echo DOC_ROOT;?>/instructor/override.php">Overrides</a></li>
    <li><a href="<?php echo DOC_ROOT;?>/instructor/project.php">New Project</a></li>
    <li><a href="#">Submit Grades</a></li>
    <!--- Student-only links --->
<?php }else{ ?>
    <li><a href="<?php echo DOC_ROOT;?>/student/contract.php">Contract</a></li>
    <li><a href="<?php echo DOC_ROOT;?>/student/evaluation">Do an Evaluation</a></li>
    <li><a href="<?php echo DOC_ROOT;?>">View Your Evaluations</a></li>
    <li><a href="<?php echo DOC_ROOT;?>">View Your Grades</a></li>
    <li><a href="<?php echo DOC_ROOT;?>/student/student-final-report.php">Final Report</a></li>
<?php }?>
</ul> 
<?php
if($session->logged_in){
    $greeting="You are logged in as ".$session->realname." <a href='".DOC_ROOT."/logout.php'>Logout</a><br/>"
    ."As a check, class:".$session->currclass.",<br/>project:".$session->currproj.",<br/>group:".$session->currgroup.".";
}else{
    $greeting="You are not logged in! <a href='".DOC_ROOT."/index.php'>Log in</a>";
}
echo"<div class='right'>$greeting</div>";
?>



