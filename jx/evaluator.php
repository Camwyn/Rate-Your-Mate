<?php
$sid=htmlentities($_POST["sid"],ENT_QUOTES,'iso-8859-1');
if(!isset($_GET['v'])||!isset($_POST['student'])||$sid=NULL){die;}//tests for dummy data added for security
include("../includes/session.php");
$i=0;
foreach($_POST as $name=>$val){
    $postsub=substr($name,0,3);
    if($postsub=='ico'){//'icomm.bfb171aa-1223-11e1-afe7-000c29964cd2.aa6e4e22-f2e2-11e0-863b-003048965058'=>'blah'
        list($title,$behavior,$judge)=explode(".", $name);
        $icoms[$i]['behavior']=$behavior;
        $icoms[$i]['judge']=$judge;
        $icoms[$i]['comment']=$val;
    }else if($postsub=='com'){//'comment.d2c4c3aa-1223-11e1-afe7-000c29964cd2.aa6e4e22-f2e2-11e0-863b-003048965058'=>'Richard is a rock star!'
            list($title,$behavior,$judge)=explode(".", $name);
            $comments[$i]['behavior']=$behavior;
            $comments[$i]['judge']=$judge;
            $comments[$i]['comment']=$val;
        }
        $i++;
}
$id=$_POST['student'];
$eid=$_POST['EID'];
$grade=$_POST['grade'];
$instructor=$_SESSION['UID'];
$addcomments=$_POST['iaddcomm'];
//we'll put the comments and all in the dB first
foreach($comments as $comm){
    if($comm['comment']){//if no comment, no dB hit...and no null value error
        foreach($icoms as $icom){//let's match up the instructor comment
            if($icom['EID']==$comm['EID']&&$icom['behavior']==$comm['behavior']&&$icom['judge']==$comm['judge']){//these three (plus the subject) make up the primary key of our table...there better not be any duplicates!
                $comm['icomm']=$icom['comment'];
            }
        }
        try{//and do our update to the review table
            $sth = $database->connection->prepare("UPDATE Reviews SET scomm=:scomm, icomm=:icomm WHERE EID=:EID AND BID=:BID AND subject=:subject AND judge=:judge");
            $sth->execute(array(":subject"=>$id,":BID"=>$comm['behavior'],":EID"=>$eid,":scomm"=>$comm['comment'],":icomm"=>$comm['icomm'],":judge"=>$comm['judge']));
        }catch(Exception $e){
            echo $e;
        }
    }
}
try{//now we need to insert/update the additional comments table
    $sth = $database->connection->prepare("INSERT INTO Add_Comments (subject,instructor,comments,EID) VALUES (:subject,:instructor,:comments,:EID) ON DUPLICATE KEY UPDATE comments=:comments");
    $sth->execute(array(":subject"=>$id,":instructor"=>$instructor,":comments"=>$addcomments,":EID"=>$eid));
}catch(Exception $e){
    echo $e;
}
try{//now we need to insert/update the grade table
    $sth = $database->connection->prepare("INSERT INTO Eval_Grades (UID,EID,role,grade) VALUES (:UID,:EID,'subject',:grade) ON DUPLICATE KEY UPDATE grade=:grade");
    $sth->execute(array(":UID"=>$id,":EID"=>$eid,":grade"=>$grade));
}catch(Exception $e){
    echo $e;
}
//then test flags
if($_POST['method']=='save'){//save for later - graded=0
    try{//now we need to insert/update the grade table
        $sth = $database->connection->prepare("INSERT INTO Review_Flags (UID,RID,graded) VALUES (:UID,:EID,0) ON DUPLICATE KEY UPDATE graded=0");
        $sth->execute(array(":UID"=>$id,":RID"=>$eid));
    }catch(Exception $e){
        echo $e;
    }
    $message="You have saved your evaluatee grade form for {$database->getUserName($id)}. Don't forget to <a href='".DOC_ROOT."'>log in</a> and finish it!";
    $mailer->sendMail($session->userinfo['fname'],$session->userinfo['email'],$message);
}else{//finalize - graded=1
    try{//now we need to insert/update the grade table
        $sth = $database->connection->prepare("INSERT INTO Review_Flags (UID,RID,graded) VALUES (:UID,:EID,1) ON DUPLICATE KEY UPDATE graded=1");
        $sth->execute(array(":UID"=>$id,":RID"=>$eid));
    }catch(Exception $e){
        echo $e;
    }
    $message="Your instructor has graded you based on your evaluations. <a href='".DOC_ROOT."'>Log in</a> to see your grade.";
    $mailer->sendMail($database->getUserName($id),$database->getUserEmail($id),$message);
}