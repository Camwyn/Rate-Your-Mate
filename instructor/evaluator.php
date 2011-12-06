<?php //do NOT put anything above this line!
    error_reporting(-1);
    $_GET['page']='Evaluator Report'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    if(isset($session->currproj)){
        $project=$session->currproj;
    }elseif(isset($_SESSION['project'])){
        $project=$_SESSION['project'];
    }elseif(isset($_GET['project'])){
        $project=$_GET['project'];
    }else{
        $project=null;
    }

    if(is_null($project)){
        echo"Please choose a project to the right first.";
    }else{
        $groups=$database->getGroups($project);// Get array of groups
        $eid=(isset($_GET['EID']))?$_GET['EID']:$database->getEID($project);//This should be set to a passed variable.
        if(isset($_GET['group'])){
            $contdata=$database->getContract($_GET['group']);// Grab all the info in one fell swoop!
            $behaviors=(isset($contdata['behaviors']))?$contdata['behaviors']:null;// But we only need behaviors, so separate them out for easier access.
        }
        if(isset($_GET['student'])){$student=$_GET['student'];
            try{
                $sth=$database->connection->prepare("SELECT R.subject AS subject, R.BID AS BID, R.scomm AS comments, R.icomm AS icomm, U.fname AS fname, U.lname AS lname, S.score AS score FROM Users AS U, Reviews AS R JOIN Scores AS S ON (R.subject=S.subject AND R.EID=S.EID AND R.judge=S.judge) WHERE R.judge=:judge AND R.EID=:eid AND U.UID=R.subject");
                $sth->execute(array(':judge'=>$_GET['student'],':eid'=>$eid));
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $scores[$row['subject']]['id']=$row['subject'];
                    $scores[$row['subject']]['score']=$row['score'];
                    $scores[$row['subject']]['name']=$row['lname']." ".$row['fname'];
                    $evals[$row['BID']][$row['subject']]=$row['comments'];
                    $evals[$row['BID']][$session->UID]=$row['icomm'];// This assumes that the instructor is the viewer!
                }
            }catch(Exception $e){
                echo $e;
            }
            try{
                $sth=$database->connection->prepare("SELECT EID, odate, cdate FROM Evals where PID=:pid ORDER BY odate ASC");
                $sth->execute(array(':pid'=>$project));
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $preval[$row['EID']]['cdate']=date('m-d-Y',strtotime($row['cdate']));
                    $preval[$row['EID']]['EID']=$row['EID'];
                    $preval[$row['EID']]['odate']=date('m-d-Y',strtotime($row['odate']));
                }
            }catch(Exception $e){
                echo $e;
            }
        }
        echo"Groups: <select id='groups' name='groups'><option>Choose one...</option>";
        foreach($groups as $group){//iterate through group array
            $selected=($group['id']==$_GET['group'])?"selected='selected'":'';
            echo"<option value='".$group['id']."' $selected>".$group['name']."</option>";
        }
        echo"</select>";
        if(isset($_GET['group'])){
            $members=$database->groupRoster($_GET['group'],'');//get array of group members
        }
        echo "Students: <select id='students' name='students'><option>Choose one...</option>";
        if(isset($members)){
            foreach($members as $member){//iterate through group members array
                $selected=($member['id']==$_GET['student'])?"selected='selected'":'';
                echo"<option value='".$member['id']."' $selected>".$member['lname'].", ".$member['fname']."</option>";
            }
        }
        echo"</select>";
    ?>

    <div id='main'>
    <?php
        if(isset($evals)&&isset($preval)&&!is_null($behaviors)){
            $evalnum=0;
            $i=1;
            foreach($preval as $pre){
                if($pre['EID']==$eid){
                    $evalnum=$i;
                }
                $i++;
            }
        ?>
        <div class='half'>

            <h2>Scores Given by Evaluator for Evaluation <?php echo $evalnum." of ".count($preval);?>.</h2>

            <?php
                if(isset($scores)){
                    foreach($scores as $score){
                        echo"<input type='hidden' name='score-".$score['id']."' alt='".$score['name']."' value='".$score['score']."'/>";
                    }
                }
            ?>
            <div id='pie'>
                <h3 style='font-style: italic;'>No evaluations submitted yet for this eval period.</h3>
            </div>
            <div id='evalnav'>
                <?php if(count($preval)>0){
                        $i=1;
                        $tag=false;
                        foreach($preval as $pre){
                            if($pre['EID']==$eid){
                                echo "<span style='font-size:1.25em'> {$pre['cdate']} </span>";
                                $tag=true;
                                continue;
                            }else{
                                if($tag){
                                    $img="<img src='../img/pastWedge.png' style='height:1em' alt='previous'/>";
                                    $arrow=($i==count($preval)-1)?"$img$img ":"$img ";
                                    $pretitle=($i==count($preval)-1)?'last':'next';
                                }else{
                                    $img="<img src='../img/backWedge.png' style='height:1em' alt='previous'/>";
                                    $arrow=($i==1)?" $img$img":" $img";
                                    $pretitle=($i==1)?'first':'previous';
                                }
                                echo"<a href='{$pre['EID']}' title='$pretitle review' style='text-decoration:none' alt='$i'>$arrow</a>";
                            }
                            $i++;
                        }
                    }else{
                        echo"No reviews are scheduled for this project!";
                    }

                ?>
            </div>
            <?php if(isset($evals)){?>
                <form id='evaluatorForm'>
                    <div id='grade' style='margin-top:.5em'>
                        <span  style='font-size: 1.5em;float:left;margin-right:2em;margin-top:-.2em'>Evaluator grade for :</span>
                        <div class='slider' style='width:20em;float:left'></div>
                        <?php
                            try{
                                $mth=$database->connection->prepare("SELECT evalgradepoints FROM Projects WHERE PID=:pid");
                                $mth->execute(array(':pid'=>$_SESSION['currproj']));
                                while($row=$mth->fetch(PDO::FETCH_ASSOC)){
                                    $max=$row['evalgradepoints'];
                                }
                            }catch(Exception $e){
                                echo $e;
                            }
                        ?>
                        <input id='slideval' name='grade' max=<?php echo $max;?> value='0' style='border:none;font-weight:bold;float:left;margin-left:2em;margin-top:-.2em'/>
                        <div class='clear'></div>
                    </div>

                    <?php 
                        $team=$database->groupRoster($_GET['group'],$_GET['student']);//get list of group members from database, minus current user
                        foreach($behaviors as $behave){
                            $title=$behave['title'];
                            $bid=$behave['BID'];
                            echo"<h2><a class='behavetitle' href='#' title='".$behave['notes']."'>".$behave['title']."</a></h2>";
                            $c=0;
                            foreach($team as $teammate){
                                $id=$teammate['id'];
                                $name=$teammate['fname']." ".$teammate['lname'];
                                echo "<h3>$name</h3>"
                                ."<div class='ui-corner-all' style='border:1px solid #A6C9E2;border-left:2em solid ".$colors[$c].";'>"
                                ."<textarea rows='5' cols='10' name='comment-$bid.$id'"
                                ."style='border:none;overflow:auto;resize:vertical;width:100%' placeholder='Enter comments for $name here...'>";
                                if(isset($evals[$bid][$id])){echo $evals[$bid][$id];}
                                echo"</textarea>"
                                ."<textarea rows='5' cols='10' name='icomm-$bid.$id'"
                                ."style='border:none;border-top:1px solid #A6C9E2;overflow:auto;resize:vertical;width:100%' placeholder='Enter instructor comments for $name here...'>";
                                if(isset($evals[$bid][$session->UID])){echo $evals[$bid][$session->UID];}
                                echo"</textarea>"
                                ."</div>";
                                ($c<6)?$c++:$c=0;//for looping through our rainbow ;)
                            }
                        }
                    ?>
                    <br />            
                    <button type="reset" name='reset' id='reset' onClick='history.go(0)' style='font-size:1.5em;'>Cancel</button>
                    <button type="submit" name='save' id='save' style='font-size:1.5em;'>Save Changes</button>
                    <button type="submit" class='ui-state-active' name='accept' id='accept' style='font-size:1.5em;'>Submit</button>
                    <?php }?>
            </form>
            <?php }else{echo"Contract not set for this group yet!";}?>
        <div id='dialog'>Dialog placeholder</div>
    </div>
    <?php } ?>
<script>
    $(document).ready(function(){
        $('h2 a[href]').qtip();
        $("input:submit, button, #reset").button();
        $( "#dialog" ).dialog({
            autoOpen:false
        });
        $('.slider').slider({
            max:$("#slideval").attr('max'),
            min:0,
            slide: function( event, ui ) {
                $("#slideval").val(ui.value);
            }
        });

        if($('input[name^=score-]').length>0){
            //pie chart stuff:        
            var optionsPie = {// set up 'blank' chart
                chart: {
                    renderTo: 'pie',
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    defaultSeriesType: 'pie'
                },
                title: {
                    text: ''
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.y ;
                    }
                },
                xAxis: {
                    categories: []
                },
                series: []
            };
            var seriesPie = {
                data: []      
            };
            var seriesPieItem = new Array(); // eg:  ['name, value]

            $('input[name^=score-]').each(function(){//fill data values
                seriesPieItem = new Array();
                var nam=$(this).attr('alt');
                seriesPieItem.push(nam);
                var val=$(this).val();
                seriesPieItem.push(parseFloat(val));
                seriesPie.data.push(seriesPieItem);
            });

            optionsPie.series.push(seriesPie);

            // Create the chart

            var chart = new Highcharts.Chart(optionsPie);
        }
        $("#groups").change(function(){
            $.ajax({  
                type:"POST",  
                url: "../jx/grouproster.php?v="+jQuery.Guid.New(),  
                data: "group="+$("#groups").val()+"&sid="+jQuery.Guid.New(),
                success: function(data){
                    $("#students").html(data);
                }
            });	
        });

        $("#students").change(function(){
            window.location.href = "evaluator.php?project=<?php echo $project;?>&group="+$("#groups").val()+"&student="+$(this).val();

        });
        $('#evalnav>a').click(function(){
            window.location.href = "evaluator.php?project=<?php echo $project;?>&group="+$("#groups").val()+"&student="+$('#students').val()+"&EID="+$(this).attr('href');
            return false;
        });

        $("#accept, #save").click(function(){
            var method="method="+$(this).attr('id');
            $.ajax({  
                type:"POST",  
                url: "../jx/gradejudge.php?v="+jQuery.Guid.New(),  
                data: $("#evaluatorForm").serialize()+"&sid="+jQuery.Guid.New(),
                success:function(){
                    $("#dialog").text("Your grading has been submitted.");
                    $("#dialog").dialog("open");
                },
                error:function(){
                    $("#dialog").text("There was an error, please try again.");
                    $("#dialog").dialog("open");
                }  
            });
            return false;  
        });


    });
</script>
</body>
</html>
