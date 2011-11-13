<?php
    error_reporting(-1);// This will display any error in the ajax response.

    include("header.php");
    $classes=$database->getClasses($session->UID);
    foreach($classes as $class){
        $changed=array();
        $behaviors=array();
        $contracts=array();
        $reviews=array();
        $project=false;
        try{
            $sth=$database->connection->prepare("SELECT PID FROM Projects WHERE class=:class");
            $sth->bindParam(':class', $class['id'], PDO::PARAM_STR);   
            $sth->execute();
            while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                $project=$row['PID'];
            }
        }catch(Exception $e){
            echo $e;
        }
        if($project){
            $changed=$database->getChanged($project);
            $behaviors=(isset($changed['behaviors']))?$changed['behaviors']:false;
            $contracts=(isset($changed['contracts']))?$changed['contracts']:false;
            $reviews=(isset($changed['reviews']))?$changed['reviews']:false;
        }

        echo"B:<pre>";
        print_r($behaviors);
        echo"</pre><br/>";

        echo"C:<pre>";
        print_r($contracts);
        echo"</pre><br/>";

        echo"E:<pre>";
        print_r($reviews);
        echo"</pre><br/>";
    }
?>
