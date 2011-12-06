<?php //do NOT put anything above this line!
    error_reporting(-1);
    $_GET['page']='Evaluatee Report'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $groups=$database->getGroups($session->currproj);// Get array of groups
    $eid=$database->getEID($session->currproj);//This should be set to a passed variable.
    $gid=(isset($_GET['group']))?$_GET['group']:false;
    if($gid){
        $contdata=$database->getContract($gid);// Grab all the info in one fell swoop!
        $behaviors=$contdata['behaviors'];// But we only need behaviors, so separate them out for easier access.
    }
    if(isset($_GET['student'])){$student=$_GET['student'];
        try{
            $sth=$database->connection->prepare("SELECT R.judge AS judge, R.BID AS BID, R.scomm AS comments, U.fname AS fname, U.lname AS lname, S.score AS score FROM Users AS U, Reviews AS R JOIN Scores AS S ON (R.subject=S.subject AND R.EID=S.EID) WHERE R.subject=:subject AND R.EID=:eid AND U.UID=R.judge");
            $sth->bindParam(':subject', $_GET['student'], PDO::PARAM_STR);
            $sth->bindParam(':eid', $eid, PDO::PARAM_STR);
            $evals=array();
            $sth->execute();
            while($row=$sth->fetch(PDO::FETCH_ASSOC)){

                $evals[$row['BID']][$row['judge']]=$row['comments'];
            }
        }catch(Exception $e){
            echo $e;
        }
        try{
            $sth=$database->connection->prepare("SELECT S.subject AS subject, AVG(S.score) AS score, U.lname AS lname, U.fname AS fname FROM Scores AS S, Users as U WHERE S.subject=U.UID AND S.EID=:eid AND U.UID IN(SELECT UID FROM Groups WHERE GID=:gid ) GROUP BY subject;");
            $sth->bindParam(':eid', $eid, PDO::PARAM_STR);
            $sth->bindParam(':gid', $gid, PDO::PARAM_STR);
            $scores=array();
            $sth->execute();
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
    }else{
        $evals=false;
    }
    echo"<h1>Evaluatee Report</h1>Groups: <select id='groups' name='groups'><option>Choose one...</option>";
    foreach($groups as $group){//iterate through group array
        $selected=($gid&&$group['id']==$gid)?"selected='selected'":'';
        echo"<option value='".$group['id']."' $selected>".$group['name']."</option>";
    }
    echo"</select>";
    $members=($gid)?$members=$database->groupRoster($gid,''):false;
    echo "Students: <select id='students' name='students'><option>Choose one...</option>";
    if($members){
        foreach($members as $member){//iterate through group members array
            $selected=($member['id']==$_GET['student'])?"selected='selected'":'';
            echo"<option value='".$member['id']."' $selected>".$member['lname'].", ".$member['fname']."</option>";
        }
    }
    echo"</select>";
?>

<div id='main'>
<?php if($evals){?>
    <h2>Average students' scores - this evaluation</h2>
    <h4>Please note that these are averaged values for each student, and may not add up to the max points for the evaluation!</h4>
    <div id='pie' class='half' >Wheel goes here</div>
    <div class='half'>
        <form>
            <?php 
                $team=$database->groupRoster($gid,$_GET['student']);//get list of group members from database, minus current user
                foreach($behaviors as $behave){
                    $title=$behave['title'];
                    $bid=$behave['BID'];
                    echo"<h2><a class='behavetitle' href='#' title='".$behave['notes']."'>".$behave['title']."</a></h2>";
                    $c=0;
                    foreach($team as $teammate){
                        $id=$teammate['id'];
                        $name=$teammate['fname']." ".$teammate['lname'];
                        echo"<br/>"
                        ."<div class='ui-corner-all' style='border:1px solid #A6C9E2;border-left:2em solid ".$colors[$c].";'>"
                        ."<div class='rotate'>$name</div>"
                        ."<textarea rows='5' cols='10' name='comment-$bid.$id'"
                        ."style='border:none;overflow:auto;resize:vertical;width:100%' placeholder='Enter comments for $name here...'>"
                        .$evals[$bid][$id]
                        ."</textarea>"
                        ."<textarea rows='5' cols='10' name='icomm-$bid.$id'"
                        ."style='border:none;border-top:1px solid #A6C9E2;overflow:auto;resize:vertical;width:100%' placeholder='Enter instructor response to comments by $name here...'>"
                        // need to pull instr comments from dB!
                        ."</textarea>"
                        ."</div>";
                        ($c<6)?$c++:$c=0;//for looping through our rainbow ;)
                    }
                }
                foreach($scores as $score){
                    echo"<input type='hidden' name='score-".$score['id']."' alt='".$score['name']."' value='".$score['score']."'/>";
                }
            ?>
            <br />
            <h3>Additional Comments:</h3>
            <div class='ui-corner-all' style='border:1px solid #A6C9E2;border-left:2em solid #B5CA92;'>
                <textarea cols='10' rows='8' name='iaddcomm' style='border:none;overflow:auto;resize:vertical;width:100%' placeholder='Enter additional instructor comments for <?php echo $studentname;?> here...'></textarea>
            </div>
            <button type="reset" name='reset' id='reset' onClick='history.go(0)'>Cancel</button>
            <button type="submit" name='save' id='save'>Save Changes</button>
            <button type="submit" name='accept' id='accept'>Submit</button>
            <?php }?>
    </form>
    <div id='dialog'>Dialog placeholder</div>
</div>
<script>
    $(document).ready(function(){
        $('h2 a[href]').qtip();
        $("input:submit, button, #reset").button();
        $( "#dialog" ).dialog({
            autoOpen:false
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
            window.location.href = "test.php?group="+$("#groups").val()+"&student="+$(this).val();

        });

    });
</script>
</body>
</html>
