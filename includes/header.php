<?php
    error_reporting(-1);
    include('session.php'); //includes sessions file, which includes the others needed
    if($session->logged_in){
        if($_SERVER['SCRIPT_NAME']=='/index.php'){        
            header('Location: activity.php');
        }
        $pagetitle=(isset($_GET['page'])&&$_GET['page']!='')?"Rate Your Mate | ".$_GET['page']:"Rate Your Mate";
        if(isset($_SESSION['currclass'])){$session->currclass=$_SESSION['currclass'];}

        if(isset($_GET['proj'])){$session->currproj=$_GET['proj'];}elseif(isset($_SESSION['currproj'])){$session->currproj=$_SESSION['currproj'];}
        if(isset($_GET['group'])){$session->currgroup=$_GET['group'];}elseif(isset($_SESSION['currgroup'])){$session->currgroup=$_SESSION['currgroup'];}
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo $pagetitle;?></title>
        <!-- css stylesheets -->
        <link href='../css/styles.css' rel='stylesheet'/>
        <link href='../css/ui.spinner.css' rel='stylesheet'/>
        <link href='../js/jquery-ui/css/custom-theme/jquery-ui-1.8.16.custom.css' rel='stylesheet'/>
        <!-- javascript files -->
        <script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js'></script>
        <script type='text/javascript' src='../js/jquery-ui/js/jquery-ui-1.8.16.custom.min.js'></script>
        <script type='text/javascript' src='../js/jquery.qtip-1.0.0-rc3.min.js'></script>
        <script type="text/javascript" src="../js/jquery-ui-timepicker.js"></script>
        <script type="text/javascript" src="../js/jquery.guid.js"></script>
        <script type="text/javascript" src="../js/ui.spinner.min.js"></script>
        <script type="text/javascript" src="../js/highcharts.js"></script>
        <!-- other stuff -->
        <link rel="shortcut icon" href="../RYM_favicon2.ico" />
        <script>
            $(document).ready(function(){
                $("#classid").change(function(){
                    var classChange=$(this).val();
                    $.ajax({  
                        type:"POST",  
                        url: "../jx/setclass.php?v="+jQuery.Guid.New(),  
                        data: "class="+classChange+"&sid="+jQuery.Guid.New(),
                        success: function(){location.reload();}
                    }); 
                });
                $("#projid").change(function(){
                    var projChange=$(this).val();
                    $.ajax({  
                        type:"POST",  
                        url: "../jx/setproj.php?v="+jQuery.Guid.New(),  
                        data: "proj="+projChange+"&sid="+jQuery.Guid.New(),
                        success: function(){location.reload();} 
                    }); 
                });
                $("#groupid").change(function(){
                    var groupChange=$(this).val();
                    $.ajax({  
                        type:"POST",  
                        url: "../jx/setgroup.php?v="+jQuery.Guid.New(),  
                        data: "group="+groupChange+"&sid="+jQuery.Guid.New(),
                        success: function(){location.reload();} 
                    }); 
                });
            });
        </script>
    </head>
    <body>
    <div id='header' style='width: 100%;'>
    <div class='left'>
        <img src='../img/rymLogo.png' />
        <h1><?php echo $_GET['page'];?><img src='../img/help.png' title='help'/></h1>
        <div id='arrownav'>
            <?php include('nav.php');?>
        </div>
    </div>
    <?php
        $greeting='';
        if($session->logged_in){
            $greeting="You are logged in as ".$session->realname." <a href='".DOC_ROOT."/logout.php'>Logout</a><br/>";
            $classes=$database->getClasses($session->UID);
            $projects=$database->getProjects($session->UID);
            echo"<div class='right' style='width:250px;'>$greeting";
            echo"<br/>Choose Class: <select id='classid'><option>Choose one...</option>";
            $ses=(!is_null($session->currclass))?$session->currclass:$_SESSION['currclass'];
            foreach($classes as $class){
                $sel=($class['id']==$_SESSION['currclass'])?"selected='selected'":"";
                echo"<option value='".$class['id']."' $sel>".$class['name']."</option>";
            }
            echo"</select>";
            if(isset($_SESSION['currclass'])){
                echo"<br/>Choose Project: <select id='projid'><option>Choose one...</option>";
                foreach($projects as $project){
                    $sel=($project['PID']==$_SESSION['currproj'])?"selected='selected'":"";
                    echo"<option value='".$project['PID']."' $sel>".$project['pname']."</option>";
                }
                echo"</select>";
            }
            if(isset($_SESSION['currproj'])){
                echo"<br/>Choose Group: <select id='groupid'><option>Choose one...</option>";
                $groups=$database->getGroups($_SESSION['currproj']);
                foreach($groups as $group){
                    $sel=($group['id']==$_SESSION['currgroup'])?"selected='selected'":"";
                    echo"<option value='".$group['id']."' $sel>".$group['name']."</option>";
                }
                echo"</select>";
            }
        ?>
        <div id='navblock' class='three-quarters'>
        <!--- Nav links --->
        <ul id='nav'>
        <li style='font-weight:bold'>Testing Links:</li>
        <li><a href="<?php echo DOC_ROOT;?>/activity.php">Activity</a></li>
        <!--- Teacher-only links --->
        <?php if ($session->isInstructor()||$session->isAdmin()){ ?>
            <li><a href="<?php echo DOC_ROOT;?>/instructor/add_class.php">Add A Class</a></li>
            <li><a href="<?php echo DOC_ROOT;?>/instructor/evaluatee.php">Evaluatee Report</a></li>
            <li><a href="<?php echo DOC_ROOT;?>/instructor/evaluator.php">Evaluator Report</a></li>
            <li><a href="<?php echo DOC_ROOT;?>/student/contract.php">Student Contract</a></li>
            <li><a href="<?php echo DOC_ROOT;?>/instructor/override.php">Overrides</a></li>
            <li><a href="<?php echo DOC_ROOT;?>/instructor/project.php">New Project</a></li>
            <li><a href="#">Submit Grades</a></li>
            <!--- Student-only links --->
            <?php }else{ ?>
            <li><a href="<?php echo DOC_ROOT;?>/student/contract.php">Contract</a></li>
            <li><a href="<?php echo DOC_ROOT;?>/student/evaluation.php">Do an Evaluation</a></li>
            <li><a href="<?php echo DOC_ROOT;?>">View Your Evaluations (is this needed?)</a></li>
            <li><a href="<?php echo DOC_ROOT;?>/student/student-final-report.php">Final Report</a></li>
            <?php }?>
        </ul> 
        </div>
        <?php
            echo"</div>";
        }elseif($_SERVER['SCRIPT_NAME']!='/index.php'){        
            header('Location:'.DOC_ROOT);
        }
    }else{
        if($_SERVER['SCRIPT_NAME']!='/index.php'){        
            header('Location:'.DOC_ROOT);
        }

    }
?>
<div class='clear'></div>
</div>