<?php //do NOT put anything above this line!
    error_reporting(-1);
    $_GET['page']=$page='Project Activity'; //Variable to set up the page title - feeds header.php
    include('includes/header.php');//this include file has all the paths for the stylesheets and javascript in it.
?>

<body>
    <div id='tabs' class='class three-quarters'>
        <ul>
            <?php foreach($classes as $class){echo"<li><a id='{$class['id']}' href='#class-{$class['id']}'>{$class['name']}</a></li>";} ?>
        </ul>
        <?php
            foreach($classes as $class){
                echo"<div id='class-{$class['id']}'>";//Class tab div start
                $changed=$database->getChanged($class['id'],$session->UID,null);
                //echo"<pre>";print_r($changed);echo"</pre>";
                if(isset($changed['projects'])){
                    foreach($changed['projects'] as $pid=>$pdata){
                        //echo"<div class='paccordion'>";
                        /* We're assuming (for now) that there will only be one project per class, but the foreach loop
                        is there just in case. However, no formatting has been done to accomodate multiple projects. */
                        echo "<h3><a href='#'>{$pdata['name']}</a></h3>"//project name 'header'
                        ."<div class='groups'><ul>";// Groups tab start
                        foreach($pdata['groups']as $group){// Groups tab list items
                            $gid=$group['GID'];
                            echo "<li><a href='#group-$gid'>{$group['name']}</a></li>";
                        }
                        echo"</ul>";
                        foreach($pdata['groups']as $group){// Groups tab divs
                            $gid=$group['GID'];
                            echo "<div id='group-$gid'>";
                            // Tab contents and accordion here! Again, we're assuming only one contract per group
                        ?>                        
                        <div class='accordion'>
                            <h3 style='padding-left:2em;'>Upcoming</h3>
                            <div>
                                <?php
                                    try{
                                        $sth=$database->connection->prepare("SELECT contractdate FROM Projects WHERE contractdate > CURDATE() AND PID=:pid");
                                        $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                                        $sth->execute();
                                        if($sth->columnCount()>0){
                                            echo"<ul style='list-style:none'>";
                                            while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                                                echo"<li>Contracts for this project are due ".date('D M jS, Y',strtotime($row['contractdate'])).".</li>";
                                            }
                                            echo"</ul>";
                                        }
                                    }catch(Exception $e){
                                        echo $e;
                                    }
                                    try{
                                        $sth=$database->connection->prepare("SELECT odate, cdate FROM Evals WHERE odate > CURDATE() AND PID=:pid");
                                        $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                                        $sth->execute();
                                        if($sth->columnCount()>0){
                                            echo"<ul style='list-style:none'>";
                                            $youhave=($session->isInstructor())?'Your class has':'You have';
                                            while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                                                echo"<li>$youhave an Evaluation beginning ".date('D M jS, Y',strtotime($row['odate']))." - due on ".date('D M jS, Y',strtotime($row['cdate'])).".</li>";
                                            }
                                            echo"</ul>";
                                        }
                                    }catch(Exception $e){
                                        echo $e;
                                    }
                                ?>
                            </div>
                            <h3 style='padding-left:2em;'>Current</h3>
                            <div>
                                <?php
                                    if($session->isInstructor()){
                                        try{
                                            $sth=$database->connection->prepare("SELECT count(DISTINCT RID) AS count FROM Review_Flags WHERE Flag=1 AND Graded=0 AND RID IN(SELECT DISTINCT RID FROM Reviews WHERE subject IN (SELECT DISTINCT UID FROM Groups WHERE GID=:gid))");
                                            $sth->bindParam(':gid',$gid,PDO::PARAM_STR);
                                            $sth->execute();
                                        }catch(Exception $e){
                                            echo $e;
                                        }
                                        if($sth->columnCount()>0){
                                            echo"<ul style='list-style:none' class='evallist'>";
                                            while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                                                if($row['count']>0){
                                                    $s=($row['count']>1)?'s':'';
                                                    echo"<li>You have ".$row['count']." recent <a href='$gid' alt='$pid'>Evaluation$s</a> to grade.</li>";
                                                }
                                            }
                                            echo"</ul>";
                                        }
                                        try{
                                            $gth=$database->connection->prepare("SELECT DISTINCT C.CID,P.contractdate FROM Contracts AS C, Projects AS P, Contract_Flags AS F WHERE P.PID IN (SELECT PID FROM Groups WHERE GID=:gid) AND C.CID=F.CID AND C.GID=:gid AND F.UID NOT IN (SELECT UID FROM Contract_Flags WHERE UID=:uid AND Flag=0)");
                                            $gth->bindParam(':gid',$group['GID'],PDO::PARAM_STR);
                                            $gth->bindParam(':uid',$session->UID,PDO::PARAM_STR);
                                            $gth->execute();
                                            if($gth->columnCount()>0){
                                                echo"<ul style='list-style:none' class='contractlist'>";
                                                while($row=$gth->fetch(PDO::FETCH_ASSOC)){
                                                    if($database->checkLocks($row['CID'])){echo"<li>Everyone in {$group['name']} accepted their <a href='$gid' alt='$pid'>contract</a> - you need to check and finalize it.</li>";}
                                                }
                                                echo"</ul></li>";
                                            }
                                        }catch(Exception $e){
                                            echo $e;
                                        }
                                    }else{
                                        echo"<ul style='list-style:none' class='evallist'>";
                                        try{
                                            $eth=$database->connection->prepare("SELECT count(Flag) as count, RID FROM Review_Flags WHERE UID=:uid AND Flag=0 AND RID IN (SELECT RID FROM Reviews WHERE EID IN (SELECT EID FROM Evals WHERE odate < CURDATE() AND cdate > CURDATE()))");
                                            $eth->bindParam(':uid',$session->UID,PDO::PARAM_STR);
                                            $eth->execute();
                                            while($row=$eth->fetch(PDO::FETCH_ASSOC)){
                                                if($row['count']>0){
                                                    echo"<li>You have an active <a href='{$row['RID']}' alt='$pid'>Evaluation</a> to complete.</li>";
                                                }
                                            }
                                        }catch(Exception $e){
                                            echo $e;
                                        }

                                        echo"</ul>";
                                        echo"<ul style='list-style:none' class='contractlist'>";
                                        try{
                                            $cth=$database->connection->prepare("SELECT count(UID) as count, CID FROM Contract_Flags WHERE Flag!=1 AND UID=:uid AND CID IN (SELECT CID FROM Contracts WHERE GID =:gid)");
                                            $cth->bindParam(':uid',$session->UID,PDO::PARAM_STR);
                                            $cth->bindParam(':gid',$group['GID'],PDO::PARAM_STR);
                                            $cth->execute();
                                            while($row=$cth->fetch(PDO::FETCH_ASSOC)){
                                                if($row['count']>0){
                                                    echo"<li>You have yet to accept your group <a href='$gid' alt='$pid'>Contract</a>.</li>";
                                                }
                                            }
                                        }catch(Exception $e){
                                            echo $e;
                                        }
                                        echo"</ul>";
                                        echo"<ul style='list-style:none' class='gradelist'>";
                                        try{
                                            $cth=$database->connection->prepare("SELECT count(UID) as count, EID FROM Eval_Grades WHERE UID=:uid AND EID IN (SELECT EID FROM Evals WHERE PID=:pid)");
                                            $cth->bindParam(':uid',$session->UID,PDO::PARAM_STR);
                                            $cth->bindParam(':pid',$pid,PDO::PARAM_STR);
                                            $cth->execute();
                                            while($row=$cth->fetch(PDO::FETCH_ASSOC)){
                                                if($row['count']>0){
                                                    echo"<li>View your current <a href='{$row['EID']}'>Eval Grades</a>.</li>";
                                                }
                                            }
                                        }catch(Exception $e){
                                            echo $e;
                                        }
                                        echo"</ul>";
                                        echo"<ul style='list-style:none' class='finallist'>";
                                        try{
                                            $cth=$database->connection->prepare("SELECT count(UID) as count FROM Project_Grades WHERE UID=:uid AND PID=:pid");
                                            $cth->bindParam(':uid',$session->UID,PDO::PARAM_STR);
                                            $cth->bindParam(':pid',$pid,PDO::PARAM_STR);
                                            $cth->execute();
                                            while($row=$cth->fetch(PDO::FETCH_ASSOC)){
                                                if($row['count']>0){
                                                    echo"<li>View your current <a href='{$row['PID']}'>Project Grade</a>.</li>";
                                                }
                                            }
                                        }catch(Exception $e){
                                            echo $e;
                                        }
                                        echo"</ul>";
                                    }
                                ?>


                            </div>
                            <h3 style='padding-left:2em;'>History</h3>
                            <div>
                                <ul style='list-style:none;line-height:2em;' class='contractlist'>
                                    <?php
                                        if(array_key_exists('contract',$group)){
                                            $changedby=($group['contract']['changeid']==$session->UID)?'you':$group['contract']['changedby'];
                                            echo"<li>The <a href='$gid'>contract</a> for {$group['name']} was changed by <i>$changedby</i> ".date('D M jS, Y',strtotime($group['contract']['timestamp'])).".</li>";
                                        ?>
                                    </ul>
                                    <ul style='list-style:none;line-height:2em;' class='behaviorlist'>
                                        <?php
                                            if(array_key_exists('behaviors',$group['contract'])){
                                                foreach($group['contract']['behaviors'] as $behavior){
                                                    $changedby=($behavior['changeid']==$session->UID)?'you':$behavior['changedby'];
                                                    echo"<li><a href='$gid'>{$behavior['title']}</a> was changed by <i>$changedby</i> ".date('D M jS, Y',strtotime($behavior['timestamp'])).".</li>";
                                                }
                                            }
                                        ?>
                                    </ul>
                                    <ul style='list-style:none;line-height:2em;' class='evallist'>
                                        <?php
                                            if(array_key_exists('reviews',$group['contract'])){
                                                foreach($group['contract']['reviews'] as $review){
                                                    $changedby= ($review['changeid']==$session->UID)?'you':$review['changedby'];
                                                    $change=($review['flag'])?'submitted':'saved';
                                                    echo "<li>Review was $change by <i>$changedby</i> on ".date('D M jS, Y',strtotime($review['timestamp']))."</li>";
                                                }
                                            }
                                        }else{
                                            echo"<li>No <a href='$gid'>contract</a> for this group yet.</li>";
                                        }
                                    ?>
                                </ul>
                            </div>
                        </div>
                        <?php
                            echo"</div>";//End each group tab
                        }
                        echo"</div>";// Groups tab div end
                    }
                }else{

                    echo"There is no project set up for this class.";
                    if($session->isInstructor()){echo"<br/><a href='../instructor/project.php'>Set one up now!</a>";}
                }
                echo "</div>"; // Class tab div end
            }
        ?>
    </div> <!-- close div id='tabs' -->
    <script>
        $(document).ready(function(){
            $( '.accordion, .paccordion').accordion();
            $('.class').tabs();
            $('.groups').tabs();
            $('.contractlist>li>a, .behaviorlist>li>a').click(function(){
                var link=$(this).attr('href');
                var proj=function(){$.get('includes/getproj.php', function(data){session=data;});};
                $.ajax({  
                    type:'POST',  
                    url: '../jx/setproj.php?v='+jQuery.Guid.New(),  
                    data: 'proj='+proj+'&sid='+jQuery.Guid.New(),
                    success: function(){
                        window.location.href = 'student/contract.php?group='+link;
                    },
                    error:function(){
                        $('#dialog').text('There was an error setting the project, please try again.');
                        $('#dialog').dialog('open');
                    }  
                });
                return false;
            });

            $('.evallist>li>a').click(function(){
                var link=$(this).attr('href');
                changeGroup(link);
                var proj=$(this).attr('alt');
                $.ajax({  
                    type:'POST',  
                    url: '../jx/setproj.php?v='+jQuery.Guid.New(),  
                    data: 'proj='+proj+'&sid='+jQuery.Guid.New(),
                    success: function(){
                        <?php if($session->isInstructor()){
                                echo "window.location.href = 'instructor/evaluator.php?group='+link;";
                            }else{
                                echo"window.location.href = 'student/evaluation.php?eval='+link;";
                        }?>
                    },
                    error:function(){
                        $('#dialog').text('There was an error setting the project, please try again.');
                        $('#dialog').dialog('open');
                    }  
                });
                return false;
            });

            $('#tabs>ul>li>a').click(function(){
                var clas=$(this).attr('href').substring(7);
                changeClass(clas);
            });

            $('.groups>ul>li>a').click(function(){
                var group=$(this).attr('href').substring(7);
                changeGroup(group);

            });

            function changeClass(clas){
                $.ajax({  
                    type:'POST',  
                    url: '../jx/setclass.php?v='+jQuery.Guid.New(),  
                    data: 'class='+clas+'&sid='+jQuery.Guid.New(),
                    success: function(){
                        $('#arrownav').fadeOut('fast').load('includes/nav.php').fadeIn('fast');
                    }
                });
            }

            function changeGroup(group){
                $.ajax({  
                    type:'POST',  
                    url: '../jx/setgroup.php?v='+jQuery.Guid.New(),  
                    data: 'group='+group+'&sid='+jQuery.Guid.New(),
                    success: function(data){
                        $('#arrownav').fadeOut('fast').load('includes/nav.php?project='+data).fadeIn('fast');
                    }
                });
            }

        });

    </script>
</body>
</html>