<?php
$sid=htmlentities($_POST["sid"],ENT_QUOTES,'iso-8859-1');
if(!isset($_GET['v'])||$sid=NULL){die;}//tests for dummy data added for security
include("../includes/session.php");
$i=0;
foreach($_POST as $name=>$val){
    $postsub=substr($name,0,4);
    if($postsub=='sval'){//'sval-ef1d7288-f37c-11e0-863b-003048965058'=>'8'
        $svalues[$i]['subject']=substr($name,5);
        $svalues[$i]['score']=$val;
    }else if($postsub=='comm'){//'comment-e6039216-1223-11e1-afe7-000c29964cd2.ef1d7288-f37c-11e0-863b-003048965058'=>'Richard is a rock star!'
            $comments[$i]['behavior']=substr($name,8,36);
            $comments[$i]['subject']=substr($name,45);
            $comments[$i]['comment']=$val;
        }
        $i++;
}
$id=$_POST['id'];
$pid=(isset($_SESSION['currproj']))?$_SESSION['currproj']:$session->currproj;
$eid=$database->getEID($pid);
$rGUID=(isset($_POST['RID'])&&$_POST['RID']!='')?$_POST['RID']:$database->getGuid();
foreach($comments as $comm){
    if($comm['comment']){//if no comment, no dB hit...and no null value error
        try{
            $sth = $database->connection->prepare("INSERT INTO Reviews (RID,subject,judge,BID,EID,scomm) VALUES (:RID,:subject,:judge,:BID,:EID,:scomm) ON DUPLICATE KEY UPDATE scomm=:scomm;");
            $sth->execute(array(":RID"=>$rGUID,":subject"=>$comm['subject'],":BID"=>$comm['behavior'],":EID"=>$eid,":scomm"=>$comm['comment'],":judge"=>$id));
        }catch(Exception $e){
            echo $e;
        }
    }
}
foreach($svalues as $sval){
    if($sval['score']){//if no score, no dB hit...and no null value error
        try{
            $sth = $database->connection->prepare("INSERT INTO Scores (EID,judge,subject,score) VALUES (:EID,:judge,:subject,:score) ON DUPLICATE KEY UPDATE score=:score");
            $sth->execute(array(":EID"=>$eid,":judge"=>$id,":subject"=>$sval['subject'],":score"=>$sval['score']));
        }catch(Exception $e){
            echo $e;
        }
    }
}
if($_POST['method']=='save'){
    $database->setFlag($id,0,'judge',$rGUID,null);// Unlock me (set flag as judge).
    foreach($comments as $comm){// Unlock subjects
        $database->setFlag($comm['subject'],0,'subject',$rGUID,null);
    }
    try{
        $sth = $database->connection->prepare("SELECT cdate FROM Evals WHERE EID=:EID");
        $sth->execute(array(":EID"=>$eid));
        while($row=$sth->fetch(PDO::FETCH_ASSOC)){
            $cdate=$row['cdate'];
        }
    }catch(Exception $e){
        echo $e;
    }
    $message="You have saved your review for ".$database->getProjName($pid).". Don't forget to <a href='".DOC_ROOT."'>log in</a> and finish the review before $cdate!";
    $mailer->sendMail($session->userinfo['fname'],$session->userinfo['email'],$message);
}elseif($_POST['method']=='accept'){
    $database->setFlag($id,1,'judge',$rGUID,null);// Lock me (set flag as judge).
        foreach($comments as $comm){// Lock subjects
        $database->setFlag($comm['subject'],1,'subject',$rGUID,null);
    }
    $message=$session->realname." from ".$database->getProjName($pid)." has submitted their review. Please <a href='".DOC_ROOT."'>log in</a> and grade it.";
    $instructor=$database->getInstructor($pid);
    $mailer->sendMail($instructor['fname']." ".$instructor['lname'],'stephenjpage@gmail.com',$message);// $instructor['email']
}
