<?php
    if(isset($_GET['eval'])){$eval=$_GET['eval'];}
    if(isset($_POST['eval'])||isset($eval)){
        if(isset($_POST['eval'])){include('session.php');$eval=$_POST['eval'];}
        $role=(isset($_POST['role']))?$_POST['role']:'subject';
        $student=$session->UID;
        if(isset($_POST['proj'])){
            $pid=$_POST['proj'];
        }elseif(isset($session->currproj)){
            $pid=$session->currproj;
        }else{
            $pid=null;
        }
        if(!is_null($pid)){$instructor=$database->getInstructor($pid);}
        //we're going to need the gid, so let's get it

        $groups=$database->getGroups($pid,$student);
        foreach($groups as $group){
            $gid=$group['id'];
        }
        $contdata=$database->getContract($gid);// Grab all the info in one fell swoop!
        $behaviors=(isset($contdata['behaviors']))?$contdata['behaviors']:null;// But we only need behaviors, so separate them out for easier access.
        if($role=='judge'){
            try{
                $sth=$database->connection->prepare("SELECT R.subject AS subject, R.BID AS BID,R.RID as RID, R.scomm AS comments, R.icomm AS icomm, U.fname AS fname, U.lname AS lname, AVG(S.score) AS score FROM Users AS U, Reviews AS R JOIN Scores AS S ON (R.subject=S.subject AND R.EID=S.EID AND R.judge=S.judge) WHERE R.judge=:judge AND R.EID=:eid AND U.UID=R.subject");
                $sth->execute(array(':judge'=>$student,':eid'=>$eval));
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $score=round($row['score'],0);
                    $evals[$row['BID']][$row['subject']]=$row['comments'];
                    $evals[$row['BID']]['icomm']=$row['icomm'];
                }
            }catch(Exception $e){
                echo $e;
            }
            echo"<h3>Average score given: $score</h3>";
            try{
                $gth=$database->connection->prepare("SELECT grade FROM Eval_Grades WHERE EID=:eid AND UID=:uid AND role='judge'");
                $gth->execute(array(':eid'=>$eval,':uid'=>$session->UID));
                while($row=$gth->fetch(PDO::FETCH_ASSOC)){
                    $grade=$row['grade'];
                }
            }catch(Exception $e){
                echo $e;
            }
            if(isset($grade)){echo"<h3>Grade received: $grade</h3>";}
        ?>
        <div class='half'>            
            <?php
                $team=$database->groupRoster($gid,$session->UID);//get list of group members from database, minus current user
                $c=0;
                foreach($behaviors as $behave){
                    $title=$behave['title'];
                    $bid=$behave['BID'];
                    echo"<h2><a class='behavetitle' href='#' title='".$behave['notes']."'>".$behave['title']."</a></h2>";
                    echo"<div class='ui-corner-all' style='border:1px solid #A6C9E2;border-left:2em solid ".$colors[$c].";'><p>";
                    echo"<h4>Student Comments:</h4>";
                    foreach($team as $teammate){
                        $id=$teammate['id'];
                        if(isset($evals[$bid][$id])){echo stripslashes(trim($evals[$bid][$id]))." ";}
                    }
                    echo"</p>";
                    echo"<p style='margin-bottom:.25em;'>";
                    echo"<h4>Instructor Comments:</h4>";
                    if(isset($evals[$bid]['icomm'])){echo stripslashes(trim($evals[$bid]['icomm']))." ";}
                    echo"</p>"
                    ."</div>";
                    ($c<6)?$c++:$c=0;//for looping through our rainbow ;)

                }
            ?>
        </div>
        <?php
        }else{//role=subject

            try{
                $sth=$database->connection->prepare("SELECT R.judge AS judge,R.BID AS BID,R.RID AS RID,R.scomm AS comments,R.icomm AS icomm,U.fname AS fname,U.lname AS lname,S.score AS score FROM Users AS U, Reviews AS R JOIN Scores AS S ON (R.subject=S.subject AND R.EID=S.EID) WHERE R.subject=:subject AND R.EID=:eid AND U.UID=R.judge");
                $sth->execute(array(':subject'=>$student,':eid'=>$eval));
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $evals[$row['BID']][$row['judge']]['comments']=$row['comments'];
                    $evals[$row['BID']][$row['judge']]['icomm']=$row['icomm'];
                    $evals[$row['BID']][$row['judge']]['RID']=$row['RID'];
                }
            }catch(Exception $e){
                echo $e;
            }
            try{
                $sth=$database->connection->prepare("SELECT AVG(score) AS score FROM Scores WHERE subject=:uid AND EID=:eid");
                $sth->execute(array(':eid'=>$eval,':uid'=>$session->UID));
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $score=round($row['score'],0);
                }
            }catch(Exception $e){
                echo $e;
            }

            echo"<input type='hidden' id='score' name='score' value='$score'/>";
            echo"<h3>Average score received: $score</h3>";
            try{
                $gth=$database->connection->prepare("SELECT grade FROM Eval_Grades WHERE EID=:eid AND UID=:uid AND role='subject'");
                $gth->execute(array(':eid'=>$eval,':uid'=>$session->UID));
                while($row=$gth->fetch(PDO::FETCH_ASSOC)){
                    $grade=$row['grade'];
                }
            }catch(Exception $e){
                echo $e;
            }
            if(isset($grade)){echo"<h3>Grade received: $grade</h3>";}
        ?>
        <div class='half'>
            <?php
                $team=$database->groupRoster($gid,$student);//get list of group members from database, minus current user
                $c=0;
                foreach($behaviors as $behave){
                    $title=$behave['title'];
                    $bid=$behave['BID'];
                    echo"<h2><a class='behavetitle' href='#' title='".$behave['notes']."'>".$behave['title']."</a></h2>";
                    echo"<div class='ui-corner-all' style='padding-left:.5em;border:1px solid #A6C9E2;border-left:2em solid ".$colors[$c].";'><p>";
                    echo"<h4>Student Comments:</h4>";
                    foreach($team as $teammate){
                        $id=$teammate['id'];
                        if(isset($evals[$bid][$id])){echo stripslashes($evals[$bid][$id]['comments'])." ";}
                    }
                    echo"</p><p style='margin-bottom:.25em;'>";
                    echo"<h4>Instructor Comments:</h4>";
                    foreach($team as $teammate){
                        if(isset($evals[$bid][$id]['icomm'])){echo stripslashes($evals[$bid][$id]['icomm'])." ";}
                    }
                    echo"</p></div>";
                    ($c<6)?$c++:$c=0;//for looping through our rainbow ;)
                }

            ?>
            <br />
            <h3>Additional Instructor Comments:</h3>
            <div class='ui-corner-all' style='border:1px solid #A6C9E2;border-left:2em solid <?php echo $colors[$c+1];?>;'>
                <p>
                    <?php

                        try{
                            $ath=$database->connection->prepare("SELECT comments FROM Add_Comments WHERE subject=:subject AND instructor=:instructor AND EID=:EID");
                            $ath->execute(array(":subject"=>$student,":instructor"=>$instructor['UID'],":EID"=>$eval,));
                            while($row=$ath->fetch(PDO::FETCH_ASSOC)){
                                echo stripslashes(trim($row['comments']));
                            }
                        }catch(Exception $e){
                            echo $e;
                        }
                ?></p>
            </div>
            <?php }?>
    </div>
    <?php   }else{
        echo"No evaluation chosen.";
    }
?>