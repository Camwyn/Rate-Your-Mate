<?php
    $sid=htmlentities($_POST["sid"],ENT_QUOTES,'iso-8859-1');
    if(!isset($_GET['v'])&&$sid=NULL){die;}//tests for dummy data added for security
    include("../includes/session.php");
    $i=0;
    foreach($_POST as $name=>$val){
        $postsub=substr($name,0,4);
        if($postsub=='sval'){
            $svalues['subject']=substr($name,5);
            $svalues['score']=$val;
        }else if($postsub=='comm'){
                $comments[$i]['behavior']=substr($name,8,36);
                $comments[$i]['subject']=substr($name,45);
                $comments[$i]['comment']=$val;
            }
            $i++;
    }
    $id=$_POST['id'];
    $eid=$_POST['EID'];
    $rGUID=(isset($_POST['rid']))?$_POST['rid']:$database->getGuid();
    foreach($comments as $comm){
        if($comm['comment']){//if no comment, not dB hit...and no null value error
            try{
                $sth = $database->connection->prepare("INSERT INTO Reviews (RID,subject,judge,BID,EID,scomm) VALUES (:RID,:subject,:judge,:BID,:EID,:scomm) ON DUPLICATE KEY UPDATE scomm=:scomm;");
                $sth->execute(array(":RID"=>$rGUID,":subject"=>$comm['subject'],":BID"=>$comm['behavior'],":EID"=>$eid,":scomm"=>$comm['comment'],":judge"=>$id));
            }catch(Exception $e){
                echo $e;
            }
        }
    }
    foreach($svalue as $sval){
        if($val['score']){//if no score, not dB hit...and no null value error
            try{
                $sth = $database->connection->prepare("INSERT INTO Scores (EID,judge,subject,score) VALUES (:EID,:judge,:subject,:score) ON DUPLICATE KEY UPDATE score=:score");
                $sth->execute(array(":EID"=>$eid,":judge"=>$id,":subject"=>$sval['subject'],":score"=>$sval['score']));
            }catch(Exception $e){
                echo $e;
            }
        }
    }
    if($_POST['method'=='save']){
         $database->setFlag($id,0,null,$cid);// Unlock me.
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
    }elseif($_POST['method'=='accept']){
        $database->setFlag($id,1,null,$cid);// Lock me.
        
    }

?>