<?php
    if(!isset($session->UID)){include("session.php");}
    if(isset($session->currproj)||isset($_SESSION['currproj'])){$project=$session->currproj=$_SESSION['currproj'];}elseif(isset($_GET['project'])){$project=$_GET['project'];}else{$project=null;}
    if(!is_null($project)&&$project!='Chose one...'){
        $path=($session->isInstructor())?"instructor/evaluator.php":"student/evaluation.php";
        $instructor=($session->isInstructor())?$session->UID:$database->getInstructor($session->currproj);
        echo"<ul>";
        if($session->isInstructor()){//for instructors/admins
            $count=0;
            /**
            * Project Arrow - current if no project
            */
            $pimg='currentProject.png';
            try{
                $pth=$database->connection->prepare("SELECT count(class) FROM Projects WHERE PID=:pid");
                $pth->execute(array(":pid"=>$project));
                while($row=$pth->fetch(PDO::FETCH_ASSOC)){
                    $pimg='pastProject.png';
                }
            }catch(Exception $e){
                echo $e;
            }
            echo"<li><a href='../instructor/project.php'><img src='../img/InstructorArrows/$pimg'/></a></li>";

            /**
            *  Contract Arrow - Links to student/contract.php 
            *  Current if contract is accepted by entire group but
            *  not by instructor. 
            * 
            * Should it also be current if contract overdue?
            */
            try{
                $sth=$database->connection->prepare("SELECT count(UID) AS count FROM Users WHERE UID IN (SELECT instructor FROM Projects WHERE PID=:pid AND PID IN (SELECT DISTINCT PID FROM Groups WHERE GID=:gid AND GID IN (SELECT GID FROM Contracts WHERE CID NOT IN (SELECT CID FROM Contract_Flags WHERE Flag=1 AND UID=:uid))))");
                $sth->execute(array(":uid"=>$instructor,":gid"=>$session->currgroup,":pid"=>$project));
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $count=$row['count'];
                }
            }catch(Exception $e){
                echo $e;
            }
            $cimg=($count>0)?'currentContracts.png':'pastContracts.png';
            echo"<li><a href='../student/contract.php'><img src='../img/InstructorArrows/$cimg'/></a></li>";
            /**
            * Evaluator Arrows - Links to instructor/evaluator.php 
            * Current when there are evaluator reports to grade.
            * Future wedge for upcoming Evals. Past wedge for completed ones.
            */
            try{
                $sth=$database->connection->prepare("SELECT * FROM Evals WHERE PID=:pid");
                $sth->execute(array(":pid"=>$project));
                $evals=array();
                $x=0;
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $evals[$x]['id']=$row['EID'];
                    $evals[$x]['odate']=$row['odate'];
                    $evals[$x]['cdate']=$row['cdate'];
                    $x++;
                }
            }catch(Exception $e){
                echo $e;
            }
            for($i=0;$i<count($evals);$i++){
                $past=0;$cur=0;
                $now=time();
                if(strtotime($evals[$i]['odate']) > $now && strtotime($evals[$i]['cdate']) < $now){
                    try{
                        $sth=$database->connection->prepare("SELECT count(Flag) AS count FROM Review_Flags WHERE Flag=1 AND Graded=0 AND role='judge' AND RID IN(SELECT RID FROM Reviews WHERE EID IN (SELECT EID FROM Evals WHERE PID=:project))");
                        $sth->execute(array(":project"=>$project));
                        while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                            if($row['count']>0){$cur++;}
                        }
                    }catch(Exception $e){
                        echo $e;
                    }
                }elseif(strtotime($evals[$i]['cdate']) > $now){
                    $past++;
                    if(strtotime($evals[$i+1]['odate']) < $now){
                        $cur++;
                    }
                }
            }
            $eimg='upcomingEvaluator.png';
            if(max($cur,$past)==$cur){
                $eimg='currentEvaluator.png';
            }elseif(max($cur,$past)==$past){
                $eimg='pastEvaluator.png';
            }
            echo"<li><a href='../$path'><img src='../img/InstructorArrows/$eimg'/></a></li>";
            /**
            * Evaluatee Arrows - Links to instructor/evaluatee.php
            * Current when there are evaluatee reports to grade
            * Future wedge for upcoming Evals. Past wedge for completed ones.
            */
            try{
                $sth=$database->connection->prepare("SELECT * FROM Evals WHERE PID=:pid");
                $sth->execute(array(":pid"=>$project));
                $evals=array();
                $x=0;
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $evals[$x]['id']=$row['EID'];
                    $evals[$x]['odate']=$row['odate'];
                    $evals[$x]['cdate']=$row['cdate'];
                    $x++;
                }
            }catch(Exception $e){
                echo $e;
            }
            for($i=0;$i<count($evals);$i++){
                $past=0;$cur=0;
                $now=time();
                if(strtotime($evals[$i]['odate']) > $now && strtotime($evals[$i]['cdate']) < $now){
                    try{
                        $sth=$database->connection->prepare("SELECT count(Flag) AS count FROM Review_Flags WHERE Flag=1 AND Graded=0 AND role='subject' AND RID IN(SELECT RID FROM Reviews WHERE EID IN (SELECT EID FROM Evals WHERE PID=:project))");
                        $sth->execute(array(":project"=>$project));
                        while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                            if($row['count']>0){$cur++;}
                        }
                    }catch(Exception $e){
                        echo $e;
                    }
                }elseif(strtotime($evals[$i]['cdate']) > $now){
                    $past++;
                    if(strtotime($evals[$i+1]['odate']) < $now){
                        $cur++;
                    }
                }
            }
            $eimg='upcomingEvaluatee.png';
            if(max($cur,$past)==$cur){
                $eimg='currentEvaluatee.png';
            }elseif(max($cur,$past)==$past){
                $eimg='pastEvaluatee.png';
            }
            echo"<li><a href='../$path'><img src='../img/InstructorArrows/$eimg'/></a></li>";

            /**
            * Grade Arrow - Links to instructor/grades.php
            * Current when?
            */
            echo"<li><a href='../instructor/grades.php'><img src='../img/InstructorArrows/upcomingFinalGrades.png'/></a></li>";
        }else{// for students
            $count=0;
            /**
            *  Contract Arrow - Links to student/contract.php 
            *  Current if contract not accepted by current user.
            */
            try{
                $sth=$database->connection->prepare("SELECT count(UID) AS count FROM Users WHERE UID=:uid AND UID IN (SELECT DISTINCT UID FROM Groups WHERE GID=:gid AND GID IN (SELECT GID FROM Contracts WHERE CID NOT IN (SELECT CID FROM Contract_Flags WHERE Flag=1)))");
                $sth->execute(array(":gid"=>$session->currgroup,":uid"=>$session->UID));
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $count=$row['count'];
                }
            }catch(Exception $e){
                echo $e;
            }
            $cimg=($count>0)?'currentContract.png':'pastContract.png';
            echo"<li><a href='../student/contract.php'><img src='../img/StudentArrows/$cimg'/></a></li>";
            /**
            * Evaluation Arrows - Links to student/evaluation.php
            * Current when there are incomplete evaluation(s) due
            * Future wedge for upcoming Evals. Past wedge for completed ones.
            */
            try{
                $sth=$database->connection->prepare("SELECT * FROM Evals WHERE PID=:pid");
                $sth->execute(array(":pid"=>$project));
                $evals=array();
                $x=0;
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $evals[$x]['id']=$row['EID'];
                    $evals[$x]['odate']=$row['odate'];
                    $evals[$x]['cdate']=$row['cdate'];
                    $x++;
                }
            }catch(Exception $e){
                echo $e;
            }
            for($i=0;$i<count($evals);$i++){
                $eimg='upcomingWedge.png';
                $now=time();
                if($i==0 && strtotime($evals[$i]['odate']) > $now){
                    $eimg='upcomingEvaluations.png';
                }elseif(strtotime($evals[$i]['odate']) > $now && strtotime($evals[$i]['cdate']) < $now){
                    $eimg='currentEvaluations.png';
                }elseif(strtotime($evals[$i]['cdate']) > $now){
                    $eimg='pastWedge.png';
                    if(strtotime($evals[$i+1]['odate']) < $now){
                        $eimg='currentEvaluations.png';
                    }
                }
                echo"<li><a href='../$path'><img src='../img/StudentArrows/$eimg'/></a></li>";
            }
            /**
            * View Grades Arrow - Links to student/vieweval.php
            * Current if they have recent (how do we define recent?)
            * Evals graded and ready for viewing.
            * Should there be wedges for each eval?
            */
            echo"<li><a href='../student/evaluatee.php'><img src='../img/StudentArrows/upcomingViewGrades.png'/></a></li>";
            /**
            * Final Grade Arrow - Links to student/grades.php
            * Current when instructor has submitted a project
            * grade for current user.
            */
            echo"<li><a href='../student/grades.php'><img src='../img/StudentArrows/upcomingFinal.png'/></a></li>";
        }


        echo"</ul>";
    }else{
        echo"No current project to track, thus no navbar.$project";
    }
?>