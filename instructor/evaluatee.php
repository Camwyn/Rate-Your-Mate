<?php //do NOT put anything above this line!
    error_reporting(-1);
    $_GET['page']='Evaluatee Report'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $groups=$database->getGroups($session->currproj);// Get array of groups
    $eid=(isset($_GET['EID']))?$_GET['EID']:$database->getEID($session->currproj);//This should be set to a passed variable.
    $gid=(isset($_GET['group']))?$_GET['group']:$_SESSION['currgroup'];
    if($gid){
        $contdata=$database->getContract($gid);// Grab all the info in one fell swoop!
        $behaviors=(isset($contdata['behaviors']))?$contdata['behaviors']:null;// But we only need behaviors, so separate them out for easier access.
    }
    if(isset($_GET['student'])){$student=$_GET['student'];
        try{
            $sth=$database->connection->prepare("SELECT R.judge AS judge,R.BID AS BID,R.scomm AS comments,R.icomm AS icomm,U.fname AS fname,U.lname AS lname,S.score AS score FROM Users AS U, Reviews AS R JOIN Scores AS S ON (R.subject=S.subject AND R.EID=S.EID) WHERE R.subject=:subject AND R.EID=:eid AND U.UID=R.judge");
            $sth->execute(array(':subject'=>$_GET['student'],':eid'=>$eid));
            while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                $evals[$row['BID']][$row['judge']]['comments']=$row['comments'];
                $evals[$row['BID']][$row['judge']]['icomm']=$row['icomm'];
            }
        }catch(Exception $e){
            echo $e;
        }
        try{
            $sth=$database->connection->prepare("SELECT S.subject AS subject, AVG(S.score) AS score, U.lname AS lname, U.fname AS fname FROM Scores AS S, Users as U WHERE S.subject=U.UID AND S.EID=:eid AND U.UID IN(SELECT UID FROM Groups WHERE GID=:gid ) GROUP BY subject;");
            $sth->execute(array(':eid'=>$eid,':gid'=>$gid));
            while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                $scores[$row['subject']]=array();
                $scores[$row['subject']]['id']=$row['subject'];
                $scores[$row['subject']]['score']=$row['score'];
                $scores[$row['subject']]['name']=$row['fname']." ".$row['lname'];
                if($student==$row['subject']){$studentname=$row['fname']." ".$row['lname'];}
            }
        }catch(Exception $e){
            echo $e;
        }
        try{
            $sth=$database->connection->prepare("SELECT EID, odate, cdate FROM Evals where PID=:pid ORDER BY odate ASC");
            $sth->execute(array(':pid'=>$_SESSION['currproj']));
            while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                $preval[$row['EID']]['cdate']=date('m-d-Y',strtotime($row['cdate']));
                $preval[$row['EID']]['EID']=$row['EID'];
                $preval[$row['EID']]['odate']=date('m-d-Y',strtotime($row['odate']));
            }
        }catch(Exception $e){
            echo $e;
        }
    }
    echo"Groups: <select id='groups' name='groups'><option>Choose one.</option>";
    foreach($groups as $group){//iterate through group array
        $selected=($gid&&$group['id']==$gid)?"selected='selected'":'';
        echo"<option value='".$group['id']."' $selected>".$group['name']."</option>";
    }
    echo"</select>";
    $members=($gid)?$members=$database->groupRoster($gid,''):false;
    echo "Students: <select id='students' name='students'><option>Choose one.</option>";
    if($members){
        foreach($members as $member){//iterate through group members array
            $selected=($member['id']==$_GET['student'])?"selected='selected'":'';
            echo"<option value='".$member['id']."' $selected>".$member['lname'].", ".$member['fname']."</option>";
        }
    }
    echo"</select>";
?>

<div id='main'>
    <?php
        if(isset($evals)){
            if(isset($scores)){
                foreach($scores as $score){
                    echo"<input type='hidden' name='score-".$score['id']."' alt='".$score['name']."' value='".$score['score']."'/>";
                }
            }
        ?>
        <h2>Average students' scores - this evaluation</h2>
        <h4 style='font-style:italic'>Please note that these are averaged values for each student, and may not add up to the max points for the evaluation!</h4>
        <div id='pie' class='half' >
            <h3>No evaluations submitted yet for this eval period.</h3>
        </div>
        <div id='evalnav'>
            <?php if(isset($preval)&&count($preval)>0){
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
            }?>
        </div>
        <form id='evaluateeForm'>
            <div id='grade' style='margin-top:.5em'>
                <span  style='font-size: 1.5em;float:left;margin-right:2em;margin-top:-.2em'>Grade:</span>
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
                <input id='slideval' name='grade' max='<?php echo $max;?>' value='0' style='border:none;font-weight:bold;float:left;margin-left:2em;margin-top:-.2em'/>
                <div class='clear'></div>
            </div>
            <div class='half'>
            <?php 
                echo"<input type='hidden' name='EID' value='$eid'/>";
                $mem=(isset($_GET['student']))?$_GET['student']:null;
                echo"<input type='hidden' name='student' value='$mem'/>";
                $team=$database->groupRoster($gid,$mem);//get list of group members from database, minus current user
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
                        ."<textarea rows='5' cols='10' name='comment.$bid.$id'"
                        ."style='border:none;overflow:auto;resize:vertical;width:100%' placeholder='Enter comments for $name here.'>";
                        if(isset($evals[$bid][$id])){echo $evals[$bid][$id]['comments'];}
                        echo"</textarea>"
                        ."<textarea rows='5' cols='10' name='icomm.$bid.$id'"
                        ."style='border:none;border-top:1px solid #A6C9E2;overflow:auto;resize:vertical;width:100%' placeholder='Enter instructor response to comments by $name here.'>";
                        if(isset($evals[$bid][$id]['icomm'])){echo $evals[$bid][$id]['icomm'];}
                        echo"</textarea></div>";
                        ($c<6)?$c++:$c=0;//for looping through our rainbow ;)
                    }
                }

            ?>
            <br />
            <h3>Additional Comments:</h3>
            <div class='ui-corner-all' style='border:1px solid #A6C9E2;border-left:2em solid <?php echo $colors[$c+1];?>;'>
                <?php $placeholder=(isset($studentname))?"Enter additional instructor comments for $studentname here.":"Please choose a student at the top of the page.";
                ?>
                <textarea cols='10' rows='8' name='iaddcomm' style='test-align:left;border:none;overflow:auto;resize:vertical;width:100%' placeholder='<?php echo $placeholder;?>'><?php
                        try{
                            $ath=$database->connection->prepare("SELECT comments FROM Add_Comments WHERE subject=:subject AND instructor=:instructor AND EID=:EID");
                            $ath->execute(array(":subject"=>$student,":instructor"=>$session->UID,":EID"=>$eid,));
                            while($row=$ath->fetch(PDO::FETCH_ASSOC)){
                                echo trim($row['comments']);
                            }
                        }catch(Exception $e){
                            echo $e;
                        }
                ?></textarea>
            </div><br />
            <button type="reset" name='reset' id='reset' onClick='history.go(0)' style='font-size:1.5em;'>Cancel</button>
            <button type="submit" name='save' id='save' style='font-size:1.5em;'>Save Changes</button>
            <button type="submit" name='accept' id='accept' class='ui-state-active' style='font-size:1.5em;'>Submit</button>
        </form>
        <?php }else{echo"Contract not set for this group yet!";}?>
    <div id='dialog'>Dialog placeholder</div>
</div>
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
                plotOptions:{
                    pie:{
                        marker:{
                            enabled:true,
                            symbol:'circle'
                        }
                    }  
                },
                tooltip: {
                    formatter: function() {
                        return this.point.name +"'s average score: <b>"+ this.y+"</b>";
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
            window.location.href = "evaluatee.php?group="+$("#groups").val()+"&student="+$(this).val();
        });
        $('#evalnav>a').click(function(){
            window.location.href = "evaluatee.php?group="+$("#groups").val()+"&student="+$('#students').val()+"&EID="+$(this).attr('href');
            return false;
        });

        $("#accept, #save").click(function(){
            var method="&method="+$(this).attr('id');
            $.ajax({  
                type:"POST",  
                url: "../jx/evaluatee.php?v="+jQuery.Guid.New(),  
                data: $("#evaluateeForm").serialize()+method+"&sid="+jQuery.Guid.New(),
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