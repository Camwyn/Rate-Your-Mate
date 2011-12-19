<?php //do NOT put anything above this line!
    error_reporting(-1);
    $_GET['page']=$page='View Evaluations'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $currclass=(isset($session->currclass))?$session->currclass:null;
    $classes=$database->getClasses($session->UID);
?>
<div class='m-b-1em'>
    <label for='classes'>Class:</label><select id='classes' name='classes'>
        <option>Choose one...</option>
        <?php
            foreach($classes as $class){
                $selected=($class['id']==$currclass)?"selected='selected'":'';
                echo "<option value='{$class['id']}' $selected>{$class['name']}</option>";
            }
        ?>
    </select>
    <?php
        $currproj=(isset($session->currproj)&&$session->currproj!="Choose one...")?$session->currproj:null;//pass me a project
        $projects=$database->getProjects($session->UID);
    ?>
    <label for='projects'>Project:</label><select id='projects' name='projects'>
        <option>Choose one...</option>
        <?php
            if(!is_null($currclass)){
                foreach($projects as $project){
                    $selected=($project['PID']==$currproj)?"selected='selected'":'';
                    echo "<option value='{$project['PID']}' $selected>{$project['pname']}</option>";
                }
            }else{
                echo"<option selected='selected'>Please choose a class.</option>";
            }
        ?>
    </select>
    <label for='evals'>Evaluation:</label><select id='evals' name='evals'>
        <option>Choose one...</option>
        <?php
            if(!is_null($currproj)){
                //get evals for selected project
                try{
                    $pth=$database->connection->prepare("SELECT * FROM Evals WHERE PID=:pid AND EID IN (SELECT EID FROM Eval_Grades WHERE UID=:uid)");
                    $pth->execute(array(":pid"=>$currproj,":uid"=>$session->UID));
                    $evals=array();
                    while($row=$pth->fetch(PDO::FETCH_ASSOC)){
                        $evals[$row['EID']]=$row['cdate'];
                    }
                }catch(Exception $e){
                    echo $e;
                }
                if(!empty($evals)){
                    foreach($evals as $eid=>$date){
                        $selected=(strtotime($date)<time())?"selected='selected'":'';
                        if($selected!=''){$eval=$eid;}
                        echo "<option value='$eid' $selected>$date</option>";
                    }
                }else{
                    echo"<option selected='selected'>This project has no evals to grade.</option>";
                }

            }else{
                echo"<option selected='selected'>Please choose a project.</option>";
            }

        ?>
    </select>
</div>
<?php
$maxpoints='';
$gid='';
    if(isset($currproj)){
        $mth=$database->connection->prepare("SELECT maxpoints FROM Projects WHERE PID=:pid");
        $mth->execute(array(":pid"=>$currproj));
        while($row=$mth->fetch(PDO::FETCH_ASSOC)){
            $maxpoints=$row['maxpoints'];
        }
        $grps=$database->getGroups($currproj,$session->UID);
        $gid=count($database->groupRoster($grps[0]['id']));
    }
    echo"<input type='hidden' id='maxpoints' value='$maxpoints'/>";
    echo"<input type='hidden' id='members' value='$gid'/>";
?>
<div id='roles' class='m-b-1em'>
    <label>Role:</label>
    <input type='radio' id='subject' name='role' value='subject' checked='checked'/><label for='subject'>Subject</label>
    <input type='radio' id='judge' name='role' value='judge'/><label for='judge'>Judge</label>
</div>
<div id='main'><div id='pie' class='half' ></div>
    <?php

        //echo"<pre>";print_r($grps);echo"</pre>";
        //get scores for chosen eval
        $eval=(!empty($eval))?$eval:null;
        if(!is_null($eval)){
            $_GET['eval']=$eval;
            include('../includes/viewevals.php');
        }else{
            echo"No evaluation chosen.";
        }
    ?>
</div>
<script>
    $(document).ready(function(){
        $("input:submit, button, #reset").button();
        $("#roles").buttonset();

        $("#classes").change(function(){
            var classChange=$(this).val();
            $.ajax({  
                type:"POST",  
                url: "../jx/setclass.php?v="+jQuery.Guid.New(),  
                data: "class="+classChange+"&sid="+jQuery.Guid.New(),
                success: function(){location.reload();}
            }); 
        });
        $("#projects").change(function(){
            var projChange=$(this).val();
            $.ajax({  
                type:"POST",  
                url: "../jx/setproj.php?v="+jQuery.Guid.New(),  
                data: "proj="+projChange+"&sid="+jQuery.Guid.New(),
                success: function(){location.reload();} 
            }); 
        });


        $("#evals").change(function(){
            var eval=$(this).val();
            var role=$('input[type=radio]:checked').val();
            var proj=$("#projects").val();
            $.ajax({  
                type:"POST",  
                url: "../includes/viewevals.php?v="+jQuery.Guid.New(),  
                data: "eval="+eval+"&role="+role+"&proj="+proj+"&sid="+jQuery.Guid.New(),
                success: function(data){
                    $("#main").html(data);
                }
            });
        });


        $("[name=role]").click(function(){
            var eval=$("#evals").val();
            var role=$('input[type=radio]:checked').val();
            var proj=$("#projects").val();
            $.ajax({  
                type:"POST",  
                url: "../includes/viewevals.php?v="+jQuery.Guid.New(),  
                data: "eval="+eval+"&role="+role+"&proj="+proj+"&sid="+jQuery.Guid.New(),
                success: function(data){
                    $("#main").html(data);
                }
            });
        });
    });
</script>
</body>
</html>