<?php
    $_GET['page']=$page='Welcome'; //Variable to set up the page title - feeds header.php
    include('includes/header.php');
    $classes=$database->getClasses($session->UID);
    $projects=$database->getProjects($session->UID);
?>
<div class='roundall' style='width:50em;margin:auto auto;'>
    <?php
        echo "<h2>Welcome ".$session->realname."!</h2>";
        echo "You last visited: ".$session->userinfo['timestamp'];
        $clen=count($classes);
        $cp=($clen>1)? "s" : "";
        if ($clen>0){
            echo "<h3 style='margin-bottom:.25em'>You currently have $clen classe$cp:</h3>";
            echo"<form method='POST' action='instructor/activity.php' id='go2form' style='display:inline'>"
            ."<select id='classsel' name='classsel'>";
            foreach ($classes as $class){
                echo"<option value='".$class['id']."'>".$class['name']."</option>";
            }
            echo"</select>&nbsp;&nbsp; <input type='submit' id='go2class' name='go2class' value='choose this class' /></form>";
        }else{
            echo"<span style='font-size:1.6em;'>&nbsp;<strong>You have no current classes</strong></span>";
        }
        if($session->isInstructor()||$session->isAdmin()){//allows admin to be an instructor.
            echo"<span style='font-size:1.6em;'><strong>, or, start a &nbsp;&nbsp;</strong></span>"
            ."<form method='POST' action='instructor/add_class.php' id='newcform' style='display:inline'>"
            ."<input type='submit' id='newclass' name='newclass' value='new class'/>"
            ."<input name='currclass' type='hidden'/>"
            ."</form>";
        }
        $plen=count($projects);
        $pp=($plen>1)? "s" : "";
        if ($plen>0){
            echo "<h3 style='margin-bottom:.25em'>You currently have $plen project$pp:</h3>";
            echo"<form method='POST' action='instructor/activity.php' id='go2form' style='display:inline;'>"
            ."<select id='projsel' name='projsel'>";
            foreach($projects as $project){
                echo"<option value='".$project['PID']."'>".$project['pname']."</option>";
            }
            echo"</select>&nbsp;&nbsp; <input type='submit' id='go2proj' name='go2proj' value='choose this project' />"
            ."<input name='currproj' type='hidden'/>"
            ."</form>";
        }else{
            echo"<span style='font-size:1.6em;'>&nbsp;<strong>You have no current projects. </span></strong>";
        }
        if($session->isInstructor()||$session->isAdmin()){//allows admin to be an instructor.
            echo"<span style='font-size:1.6em;'><strong> or, start a &nbsp;&nbsp;</strong></span>"
            ."<form method='POST' action='instructor/project.php' id='newpform' style='display:inline'>"
            ."<input type='submit' id='newproj' name='newproj' value='new project'/></form>";
        }
        if($session->isAdmin()){//link to admin panel only visible to admins.
            echo"<h3>Go to <a href='admin/admin.php'>Admin panel</a></h3>";
        }
        $sth=null;//clear/kill connection.
    ?>
</div>
<div id='dialog'>dialog placholder</div>
<script>
    $(document).ready(function(){
        $("input:submit, button, #reset").button();//styles buttons
        $( "#dialog" ).dialog({//sets up dialog
                    autoOpen:false,
                    buttons: {
                        Ok: function(){$( this ).dialog( "close" );}
                    }
                }); 
        $('#go2proj').click(function(){
            $.ajax({  
                        type:"POST",  
                        url: "../jx/setproj.php?v="+jQuery.Guid.New(),  
                        data: "proj="+$('#projsel').val()+"&sid="+jQuery.Guid.New(),
                        success: function(){
                            window.location.href = "activity.php";
                        },
                        error:function(){
                            $("#dialog").text("There was an error setting the project, please try again.");
                            $("#dialog").dialog("open");
                        }  
                    });
                    return false;  
        })
        
        $('#go2class').click(function(){
            $.ajax({  
                        type:"POST",  
                        url: "../jx/setclass.php?v="+jQuery.Guid.New(),  
                        data: "class="+$('#classsel').val()+"&sid="+jQuery.Guid.New(),
                        success: function(){
                            window.location.href = "activity.php";
                        },
                        error:function(){
                            $("#dialog").text("There was an error setting the class, please try again.");
                            $("#dialog").dialog("open");
                        }  
                    });
                    return false;  
        })
    });
</script>

