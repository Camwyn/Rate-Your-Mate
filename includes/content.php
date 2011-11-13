
<div class='roundall' style='width:50em;margin:auto auto;'>
<?php
    echo "<h2>Welcome ".$session->realname."!</h2>";
    echo "You last visited: ".$session->userinfo['timestamp'];
    $classes=$database->getClasses($session->UID);
    $projects=$database->getProjects($session->UID);

    $clen=count($classes);
    $cplural=($clen>1)? "s" : "";
    if ($clen>0){
        echo "<h3 style='margin-bottom:.25em'>You currently have $clen classe$cplural:</h3>";
        echo"<form method='POST' action='instructor/activity.php' id='go2form' style='display:inline'>"
        ."<select id='classsel' name='classsel'>";
        foreach ($classes as $class){
            echo"<option value='".$class['CLID']."'>".$class['cname']."</option>";
        }
        echo"</select>&nbsp;&nbsp; <input type='submit' id='go2class' name='go2class' value='go to class page' disabled='disabled'/></form>"
        ."<span style='font-size:1.6em;'>&nbsp;<strong> or, ";
    }else{
        echo"<span style='font-size:1.6em;'>&nbsp;<strong>You have no current classes, ";
    }
    echo"start a &nbsp;&nbsp;</strong></span>"
    ."<form method='POST' action='instructor/add_class.php' id='newcform' style='display:inline'>"
    ."<input type='submit' id='newclass' name='newclass' value='new class'/></form>";


    $plen=count($projects);
    $pplural=($plen>1)? "s" : "";
    if ($plen>0){
        echo "<h3 style='margin-bottom:.25em'>You currently have $plen project$pplural:</h3>";
        echo"<form method='POST' action='instructor/activity.php' id='go2form' style='display:inline;'>"
        ."<select id='projsel' name='projsel'>";
        foreach($projects as $project){

            echo"<option value='".$project['PID']."'>".$project['pname']."</option>";
        }
        echo"</select>&nbsp;&nbsp; <input type='submit' id='go2proj' name='go2proj' value='go to project' disabled='disabled'/></form>"
        ."<span style='font-size:1.6em;'>&nbsp;<strong> or, ";
    }else{
        echo"<span style='font-size:1.6em;'>&nbsp;<strong>You have no current projects. ";
    }
    if($session->isInstructor()||$session->isAdmin()){//allows admin to be an instructor.
        echo"start a &nbsp;&nbsp;</strong></span>"
        ."<form method='POST' action='instructor/project.php' id='newpform' style='display:inline'>"
        ."<input type='submit' id='newproj' name='newproj' value='new project'/></form>";
    }
    echo"<br/><h3>Future Link to <a href='activity.php'>Activity Page</a></h3>";
    if($session->isAdmin()){//link to admin panel only visible to admins.
        echo"<h3>Go to <a href='admin/admin.php'>Admin panel</a></h3>";
    }

    $sth=null;//clear/kill connection.
    echo"</div>";
?>
<script>
    $(document).ready(function(){
        $("input:submit, button, #reset").button(); 
    });
</script>

