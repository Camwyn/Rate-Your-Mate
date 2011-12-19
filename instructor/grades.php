<?php //do NOT put anything above this line!
    error_reporting(-1);
    $_GET['page']=$page='Set &amp; View Final Grades'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $classes=$database->getClasses($session->UID);

?>
<label for='classes'>Class:</label><select id='classes' name='classes'>
    <option>Choose one...</option>
    <?php
        foreach($classes as $class){
            $selected=($class['id']==$session->currclass)?"selected='selected'":'';
            echo "<option value='{$class['id']}' $selected>{$class['name']}</option>";
        }
    ?>
</select>
<?php
    if(isset($session->currclass)){
        $session->currclass;
        try{
            $gth=$database->connection->prepare("SELECT U.UID, U.lname, U.fname FROM Users AS U, Enrollment AS E WHERE E.class=:clid AND E.user=U.UID ORDER BY lname ASC, fname ASC");
            $gth->execute(array(":clid"=>$session->currclass));
            $i=0;
            while($row=$gth->fetch(PDO::FETCH_ASSOC)){
                $users[$row['UID']]['UID']=$row['UID'];
                $users[$row['UID']]['lname']=$row['lname'];
                $users[$row['UID']]['fname']=$row['fname'];
                $i++;
            }
        }catch(Exception $e){
            echo $e;
        }
        foreach($users as $id){
            try{
                $gth=$database->connection->prepare("SELECT grade FROM Eval_Grades WHERE UID=:uid AND role='subject' AND EID IN(SELECT DISTINCT EID FROM Evals WHERE PID IN (SELECT PID FROM Projects WHERE class=:class))");
                $gth->execute(array(":uid"=>$id['UID'],":class"=>$session->currclass));
                $i=0;
                while($row=$gth->fetch(PDO::FETCH_ASSOC)){
                    $users[$id['UID']]['subject'][$i]=$row['grade'];
                    $i++;
                }
            }catch(Exception $e){
                echo $e;
            }
            try{
                $gth=$database->connection->prepare("SELECT grade FROM Eval_Grades WHERE UID=:uid AND role='judge'");
                $gth->execute(array(":uid"=>$id['UID']));
                $i=0;
                while($row=$gth->fetch(PDO::FETCH_ASSOC)){
                    $users[$id['UID']]['judge'][$i]=$row['grade'];
                    $i++;
                }
            }catch(Exception $e){
                echo $e;
            }
            try{
                $gth=$database->connection->prepare("SELECT grade FROM Project_Grades WHERE UID=:uid");
                $gth->execute(array(":uid"=>$id['UID']));
                $i=0;
                while($row=$gth->fetch(PDO::FETCH_ASSOC)){
                    $users[$id['UID']]['project'][$i]=$row['grade'];
                    $i++;
                }
            }catch(Exception $e){
                echo $e;
            }
        }
        echo"<table>";
        foreach($users as $user){
            echo"<tr>";
            echo"<td>{$user['UID']}</td><td>{$user['lname']}, {$user['fname']}</td>";
            if(isset($user['judge'])){
                foreach($user['judge'] as $jg){
                    echo"<td>{$jg}</td>";
                }
            }
            if(isset($user['subject'])){
                foreach($user['subject'] as $sg){
                    echo"<td>{$sg}</td>";
                }
            }
            echo"</tr>";
        }
        echo"</table>";


    }else{
        echo"Choose a class.";
    }
?>
<script>
    $(document).ready(function(){
        $("input:submit, button, #reset").button();

        $("#classes").change(function(){
            var classChange=$(this).val();
            $.ajax({  
                type:"POST",  
                url: "../jx/setclass.php?v="+jQuery.Guid.New(),  
                data: "class="+classChange+"&sid="+jQuery.Guid.New(),
                success: function(){location.reload();}
            }); 
        });
    });
    </script>
    </body>
    </html>