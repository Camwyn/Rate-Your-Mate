<?php
if(!isset($_GET['v'])&&$sid=NULL){die;}//tests for dummy data added for security
include("../includes/database.php");
//create contract in database
try{
    $sth = $database->connection->prepare("SELECT UID, lname, fname from Users WHERE UID IN (SELECT UID FROM Enrollment WHERE class = (SELECT class from Projects WHERE PID=:pid));");
    $sth->execute(array(":pid"=>$_POST['projectID']));
}catch(Exception $e){
    echo $e;
}
while ($row = $sth->fetch(PDO::FETCH_ASSOC)){
    echo"<option value='".$row['UID']."'>".$row['lname'].", ".$row['fname']."</option>";
}
