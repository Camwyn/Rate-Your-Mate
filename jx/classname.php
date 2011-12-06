<?php
    if(!isset($_GET['v'])||!isset($_POST['sid'])){
        die;
    }else{ //Enrollment (class,user)
        include('../includes/database.php');
        $number_of_rows=0;
        try{
            $sth = $database->connection->prepare("SELECT count(clid) FROM Classes WHERE cname = :cnm;");
            $sth->bindParam(':cnm', $_POST['classname'], PDO::PARAM_STR);
            $sth->execute();
            if ($sth->fetchColumn() > 0) {
                $number_of_rows=1;
            }
        }catch(Exception $e){
            echo $e;
        }    
        echo $number_of_rows;
    }
