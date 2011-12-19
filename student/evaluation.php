<?php //do NOT put anything above this line!
    error_reporting(-1);
    $_GET['page']=$page='Peer Evaluation'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $project=$_SESSION['currproj'];//pass me a project
    // is there an open eval for this project?
    $eid=(isset($_GET['eval']))?$_GET['eval']:$database->getEID($project);
    try{
        $sth=$database->connection->prepare("SELECT cdate FROM Evals WHERE EID=:eid");
        $sth->bindParam(':eid', $eid, PDO::PARAM_STR);
        $sth->execute();
        while($row=$sth->fetch(PDO::FETCH_ASSOC)){
            $duedate=$row['cdate'];
        }
    }catch(Exception $e){
        echo $e;
    }
?>
<body style='min-width:105em;'>
    <h3 style='font-style:italic;'><?php if(isset($duedate)){echo"This evaluation is due on ".date('D M jS, Y',strtotime($duedate))." by ".date('g:i a',strtotime($duedate));}else{echo"Your group does not have an accepted <a href='contract.php'>contract</a>!";}?> </h3>
    <?php
        if($eid){
            $maxpoints=$database->getMaxPoints($project);
            $flag=$database->getFlag($session->UID,$eid,null);//second and third arguments are optional, we're looking for a review, so we pass null for contract.
            $gid=(isset($_SESSION['currgroup']))?$_SESSION['currgroup']:$database->getGroupID($project,$session->UID);
            $contdata=$database->getContract($gid);// Grab all the info in one fell swoop!
            $behaviors=$contdata['behaviors'];// But we only need behaviors, so separate them out for easier access.
            $team=$database->groupRoster($gid,$session->UID);//get list of group members from database, minus current user
            $numstudents=count($team);
            try{
                $sth=$database->connection->prepare("SELECT R.subject AS subject, R.BID AS BID,R.RID AS RID, R.scomm AS comments, S.score AS score FROM Reviews AS R JOIN Scores AS S ON (R.subject=S.subject AND R.EID=S.EID AND R.judge=S.judge) WHERE R.judge=:judge AND R.EID=:eid");
                $rid=null;
                $sth->execute(array(':judge'=>$session->UID,':eid'=>$eid));
                $evals=array();
                while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $rid=$row['RID'];
                    $scores[$row['subject']]=$row['score'];
                    $evals[$row['BID']][$row['subject']]=$row['comments'];
                }
            }catch(Exception $e){
                echo $e;
            }
            $disabled=($flag)?"disabled='disabled'":'';
        ?>
        <form id='behaveform' method='post'>
            <input type='hidden' name='id' id='id' value='<?php echo $session->UID;?>'/>
            <input type='hidden' name='EID' id='EID' value='<?php echo $eid;?>'/>
            <input type='hidden' name='RID' id='RID' value='<?php echo $rid;?>'/>
            <input type='hidden' name='maxpoints' id='maxpoints' value='<?php echo $maxpoints;?>'/>
            <div class='half' >
                <?php foreach($behaviors as $behave){
                        $title=$behave['title'];
                        $bid=$behave['BID'];
                        echo"<h2><a class='behavetitle' href='#' title='".$behave['notes']."'>".$behave['title']."</a></h2>";
                        $c=0;
                        foreach($team as $teammate){
                            $id=$teammate['id'];
                            $name=$teammate['fname']." ".$teammate['lname'];
                            echo"<div class='ui-corner-all' style='width:54.3em;border:1px solid #A6C9E2;border-left:2em solid ".$colors[$c].";'>"
                            ."<textarea rows='5' cols='10' name='comment-$bid.$id' id='comment-$bid.$id'"
                            ."style='border:none;overflow:auto;resize:vertical;width:50em' $disabled placeholder='Enter Comments for $name Here...'>";
                            if(isset($evals[$bid])){echo stripslashes($evals[$bid][$id]);}
                            echo"</textarea></div>";
                            ($c<6)?$c++:$c=0;//for looping through our rainbow ;)
                        }
                }?>

            </div>
            <div class='third' style='position:fixed;margin-left:65em;top:0px;width:200px;'>
                <div class='half' style='display:inline;margin-right:5%;margin-left:5%'>
                    <div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em whole'>
                        <div class='ui-corner-top ui-widget-header m-b-1em'>Student Overall Scores</div>
                        <div id='pie' style='width:95%;margin:auto auto;'>Wheel goes here</div>
                        <div id='eq'>

                            <ul id='slidelist' style='list-style: none;padding-left:0px;'>
                                <?php
                                    $c=0;//not sure I need to keep resetting this to 0, but better safe than sorry!
                                    foreach($team as $teammate){
                                        $id=$teammate['id'];
                                        $name=$teammate['lname'].", ".$teammate['fname'];
                                        echo"<li value='$id' title='$name' style='padding:.2em;background-color:".$colors[$c]."'>"
                                        ."<div id='slider-$id' class='slider' style='width:65%;margin:.5em;background-color:".$colors[$c].";color:".$colors[$c].";'></div>"
                                        ."<div><div id='name-$id' style='float:left;margin-top:.5em;margin-left:1em;font-weight:bold;color:#FFF'>$name</div>";
                                        $scoreid=(isset($scores[$id]))?$scores[$id]:0;
                                        echo"<input id='sval-$id' title='$name' class='slideval' style='text-align:right;float:right;width:2.5em;font-weight:bold;margin-top:.2em;margin-right:.2em;' value='$scoreid'/></div>"
                                        ."<input type='hidden' id='slidval-$id' value='$scoreid'/>"
                                        ."<div class='clear'></div></li>";
                                        ($c<6)?$c++:$c=0;//for looping through our rainbow ;)
                                    }
                                ?>
                            </ul>
                            <i>If chart collapses, click on a slider to refresh.</i>

                        </div>
                    </div>
                </div>

                <div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em' style='min-width:200px;width:200px;'>
                    <div class='ui-corner-top ui-widget-header m-b-1em'>Behavior Description:</div>
                    <p id='explanation' >Click on a behavior name to get the description of the behavior.</p>
                </div>
            </div>
            <br/>
            <button type="reset" name='reset' id='reset' onClick='history.go(0)'  style='font-size:1.5em;'>Cancel</button>
            <button type="submit" name='save' id='save'  style='font-size:1.5em;'>Save Changes</button>
            <button type="submit" name='accept' id='accept'  style='font-size:1.5em;color:#AA4643'>Submit</button>
        </form>
        <div id='dialog'>Dialog placeholder</div>
        <div id='modialog'>You must fill in all comments before scoring.</div>
        <script type='text/javascript' src='../js/jquery.linkedsliders.min.js'></script>
        <script>
            $(document).ready(function(){
                nicEditors.allTextAreas();
                
                var maxPoints=parseInt($('#maxpoints').val());
                
                var numStudents=parseInt(<?php echo $numstudents;?>);
                
                var maxIndPoints=parseInt(maxPoints-$('#slidelist>li').length+1);
                
                $("input:submit, button, #reset").button();
                
                $( "#dialog" ).dialog({
                    autoOpen:false,
                    buttons: {
                        Ok: function(){$( this ).dialog( "close" );}
                    }
                });
                
                $( "#modialog" ).dialog({
                    modal: true,
                    autoOpen:false,
                    buttons: {
                        Ok: function(){$( this ).dialog( "close" );}
                    }
                });

                
                $('.behavetitle').click(function(){
                    $('#explanation').text($(this).attr('title'));
                    return false;
                });
                
                //pie chart stuff:        
                var optionsPie = {// set up 'blank' chart
                    chart: {
                        height: 200,
                        renderTo: 'pie',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        defaultSeriesType: 'pie'
                    },
                    plotOptions:{
                        pie: {
                            dataLabels: {
                                enabled: false
                            }
                        }
                    },
                    title: {
                        text: ''
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ this.y ;
                        }
                    },
                    series: []
                };
                var seriesPie = {
                    data: []      
                };

                $('input[id^=slidval-]').each(function(){//fill data values
                    var seriesPieItem = new Array();
                    var vid=$(this).attr('id').substr(5);
                    var nam=$("#name-"+vid).text();
                    var val=($(this).val()>0)?$(this).val():(maxPoints/numStudents);
                    $(this).val(parseInt(val));
                    $("#slider-"+vid).slider( "option", "value", val);
                    seriesPieItem.push(nam);                    
                    seriesPieItem.push(parseFloat(val));
                    seriesPie.data.push(seriesPieItem);                    
                });

                optionsPie.series.push(seriesPie);

                // Create the chart
                var chart = new Highcharts.Chart(optionsPie);

                //slider stuff:

                function distribVals(i){
                    if(i==null){i=0;}
                    $('.slider').slider().each(function(i){                            
                        var sval = $(this).slider( "option", "value" );
                        var vid=$(this).attr('id').substr(7);
                        var nam=$("#name-"+vid).text();
                        chart.series[0].data[i].update([nam,sval]);
                        $('#sval-'+vid).val(sval);
                        i++;
                    });

                }

                $('.slider').slider({
                    max: maxIndPoints,
                    slide:function(){distribVals();}
                }).linkedSliders({
                    total:maxPoints,
                    policy:'next'
                });

                $('.slider').slider().each(function(i){
                    var vid=$(this).attr('id').substr(7);
                    var val=$('#sval-'+vid).val();
                    var nam=$("#name-"+vid).text();
                    $('#slider-'+vid).slider( "option", "value", val);
                    //chart.series[0].data[i].update([nam,val]);
                    i++;
                });

                $('.slideval').keyup(function(i){
                    var val=$(this).val();
                    var vid=$(this).attr('id').substr(5);
                    $('#slider-'+vid).slider( "option", "value", val);
                    var sindex=$(this).index('.slideval');
                    var nam=$("#name-"+vid).text();
                    //chart.series[0].data[sindex].update([nam,val]);
                });

                $('.ui-slider-handle').mousedown(function(){
                    var comments=false;
                    $('.nicEdit-main').each(function(){
                        if($(this).text().length<=0){comments=true;}
                    });
                    if(comments==true){
                        $('#modialog').dialog("open");
                    }else{
                        $( ".slider" ).slider( "option", "disabled", false );
                    }
                });
                
                //form submission
                $("#accept, #save").click(function(){
                    var scorenums='';
                    var twit=true;
                    $("input[id^=sval-]").each(function(){
                        if($(this).val()==''){
                            $("#dialog").text("You have not entered scores for your teammates!");
                            $("#dialog").dialog("open");
                            twit=false;
                            return false;
                        }
                    });
                    if(twit){
                        $("input[id^=sval-]").each(function(){
                            scorenums+="&"+$(this).attr('id')+"="+$(this).val();
                        });
                        $("textarea[name^=comment-]").each(function(){
                            var textValue=nicEditors.findEditor($(this).attr('id')).getContent();
                            textValue=textValue.replace(/\u00a0/g, " ");
                            scorenums+="&"+$(this).attr('id')+"="+textValue;
                        });
                        var id="&id="+$('#id').val();
                        var EID="&EID="+$("#EID").val();
                        var RID="&RID="+$("#RID").val();
                        var method="method="+$(this).attr('id');
                        $.ajax({  
                            type:"POST",  
                            url: "../jx/review.php?v="+jQuery.Guid.New(),  
                            data: method+id+EID+RID+scorenums+"&sid="+jQuery.Guid.New(),
                            success:function(data){
                                $("#RID").val(data);
                                $("#dialog").text("Your scoring has been submitted.");
                                $("#dialog").dialog("open");
                            },
                            error:function(){
                                $("#dialog").text("There was an error, please try again.");
                                $("#dialog").dialog("open");
                            }  
                        });
                    }
                    return false;  
                });
                
            });
        </script>
        <?php }else{
            if(isset($duedate)){echo"<h3>No evaulation due at this time.</h3>";}
    } ?>
    </body>
</html>
