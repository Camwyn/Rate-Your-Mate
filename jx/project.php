<?php
$sid=htmlentities($_POST["sid"],ENT_QUOTES,'iso-8859-1');
if(!isset($_GET['v'])&&$sid=NULL){die;}//tests for dummy data added for security
include("../includes/database.php");
//grab $_post variables except for group lists  (we'll deal with them separately) and create a project
$numeval=$_POST['numeval'];
try{
    $sth = $database->connection->prepare("INSERT INTO Projects (PID,pname,odate,cdate,instructor,late,groups,evals,contract,contractdate,grades,evalgradepoints,gradepoints,class,maxpoints) VALUES (:pid,:pname,:odate,:cdate,:instructor,:late,:groups,:evals,:contract,:contractdate,:grades,:evalgradepoints,:gradepoints,:class,:maxpoints);");
    $pGUID=$database->getGuid();
    $oDate=date( 'Y-m-d H:i:s', strtotime($_POST['oDate']));
    $cDate=date( 'Y-m-d H:i:s', strtotime($_POST['cDate']));
    $sth->execute(array(":pid"=>$pGUID,":pname"=>$_POST['pid'],":odate"=>$oDate,":cdate"=>$cDate,":instructor"=>$_POST['inst'],":late"=>$_POST['late'],":groups"=>$_POST['numgroups'],":evals"=>$numeval,":contract"=>$_POST['contract'],":contractdate"=>$_POST['contractdate'],":grades"=>$_POST['grades'],":evalgradepoints"=>$_POST['evalgradepoints'],":gradepoints"=>$_POST['numpoints'],":class"=>$_POST['class'],":maxpoints"=>$_POST['points']));
}catch(Exception $e){
    echo $e;
}
//assuming no errors above,we now create the groups:    
$gCount = 1; //group counter
foreach($_POST['group'] as $group){
    $gname="Group-$gCount";
    $gGUID=$database->getGuid();
    foreach($group as $student){
        try{
            $sth = $database->connection->prepare("INSERT INTO Groups (GID,UID,PID,name) VALUES (:gid,:uid,:pid,:name);");
            $sth->execute(array(":gid"=>$gGUID,":uid"=>$student,":pid"=>$pGUID,":name"=>$gname));
        }catch(Exception $e){
            echo $e;
        }
    }
    $gCount++;
}
//and the evals:
for($i=1;$i<=$numeval;$i++){
    try{
        $sth=$database->connection->prepare("INSERT INTO Evals (EID,PID,odate,cdate) VALUES (:EID,:PID,:odate,:cdate)");
        $eGUID=$database->getGUID();
        $open="e".$i."oDate";
        $close="e".$i."cDate";
        $odate=date( 'Y-m-d H:i:s', strtotime($_POST[$open]));
        $cdate=date( 'Y-m-d H:i:s', strtotime($_POST[$close]));
        $sth->execute(array(":EID"=>$eGUID,":PID"=>$pGUID,":odate"=>$odate,":cdate"=>$cdate));
    }catch(Exception $e){
        echo $e;
    }
}
    $sth=null;