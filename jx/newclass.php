<?php
session_start();
if(!isset($_GET['v'])||!isset($_POST['cname'])){
    die;
}else{
    include('../includes/database.php');
    $students=$_POST['id'];
    $cid=$database->getGuid();//generate a new class id #
    try{
        $sth = $database->connection->prepare("INSERT INTO Classes (CLID,cname,instructor) VALUES (:class,:cname,:user)");
        $sth->bindParam(':class', $cid, PDO::PARAM_STR);
        $sth->bindParam(':cname', $_POST['cname'], PDO::PARAM_STR);
        $sth->bindParam(':user', $_POST['instructor'], PDO::PARAM_STR);
        $sth->execute();
    }catch(Exception $e){
        echo $e;
    }
    foreach( $students as $student){
        try{
            $sth = $database->connection->prepare("INSERT INTO Enrollment (class,user) VALUES (:class,:user)");
            $sth->bindParam(':class', $cid, PDO::PARAM_STR);   
            $sth->bindParam(':user', $student, PDO::PARAM_STR);   
            $sth->execute();
        }catch(Exception $e){
            echo $e;
        } 
    }
}