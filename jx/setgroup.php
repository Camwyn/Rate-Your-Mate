<?php
/* Sets the current class in the session */
if(!isset($_GET['v'])||!isset($_POST['group'])||!isset($_POST['sid'])){
    die;
}else{ //Enrollment (class,user)
    include('../includes/session.php');
    $_SESSION['currgroup']=$_POST['group'];       
    $session->currgroup=$_POST['group'];
    try{
        $sth=$database->connection->prepare("SELECT PID FROM Groups WHERE GID=:gid");
        $sth->execute(array(":gid"=>$_POST['group']));
        while($row=$sth->fetch(PDO::FETCH_ASSOC)){
            $proj=$row['PID'];
            $_SESSION['currproj']=$proj;
            $session->currproj=$proj;
        }
    }catch(Exception $e){
        echo $e;
    }
    echo $proj;
}