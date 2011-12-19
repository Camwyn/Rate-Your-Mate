<?php
/* Sets the current class in the session */
if(!isset($_GET['v'])||!isset($_POST['class'])||!isset($_POST['sid'])){
    die;
}else{ //Enrollment (class,user)
    include('../includes/session.php');
    if($_POST['class']=='null'){
        $session->currclass=null;
        $_SESSION['currclass']=null;
    }else{
        $session->currclass=$_POST['class'];
        $_SESSION['currclass']=$_POST['class'];
        try{
            $sth=$database->connection->prepare("SELECT PID FROM Projects WHERE class=:class LIMIT 1");
            $sth->bindParam(':class', $_POST['class'], PDO::PARAM_STR);
            $sth->execute();
            while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                $session->currproj=$row['PID'];
                $_SESSION['currproj']=$row['PID'];
            }
        }catch(Exception $e){
            echo $e;
        }
    }
}