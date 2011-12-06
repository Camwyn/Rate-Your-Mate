<?php
/* Sets the current project in the session */
if(!isset($_GET['v'])||!isset($_POST['proj'])||!isset($_POST['sid'])){
    die;
}else{ //Enrollment (class,user)
    include('../includes/session.php');
    $session->currproj=$_POST['proj'];
    $_SESSION['currproj']=$_POST['proj'];
    try{
        $sth=$database->connection->prepare("SELECT class FROM Projects WHERE PID=:pid LIMIT 1");
        $sth->bindParam(':pid', $_POST['proj'], PDO::PARAM_STR);
        $sth->execute();
        while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
            $session->currclass=$row['class'];
            $_SESSION['currclass']=$row['class'];
        }
    }catch(Exception $e){
        echo $e;
    }
}