<?php //do NOT put anything above this line!
    //error_reporting(-1);
    $disabled=null;
    $contract=null;
    $_GET['page']=$page='Contract Creation'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    if(isset($_SESSION['currproj'])){
        $project=$_SESSION['currproj'];
    }elseif(isset($session->currproj)){
        $project=$session->currproj;
    }
    
    ;//pass me a project
    $cid=null;
    if(isset($_GET['group'])){
        $gid=$_GET['group'];
    }elseif(isset($_SESSION['currgroup'])){
        $gid=$_SESSION['currgroup'];
    }elseif($session->userlevel==1){
        $gid=$database->getGroupID($project,$session->UID);
    }
    if(isset($gid)){
        $contdata=$database->getContract($gid);//grab all the info in one fell swoop
    }
    if(isset($contdata['behaviors'])){
        $behaviors=$contdata['behaviors'];//separate out the behaviors for easier access
        $contract=$contdata['contract'][0];//separate out the contract for easier access
        $cid=$contract['CID'];
        $disabled=($database->getFlag($session->UID,null,$cid))?"disabled='disabled'":"";
    }
?>
<body class='two-thirds' style='min-width:105em;'>
    <?php if(isset($contract['changedby'])){?><h3 style='font-style: italic'>Last changed by <?php echo ($contract['changedby']==$session->UID)? 'you':$database->getUserName($contract['changedby']);echo" on ".$contract['timestamp'];?></h3><?php }?>
    <form name="contract" id=contract action="contract.php" method="post">       
        <div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em half' >
            <div class='ui-corner-top ui-widget-header m-b-05em'><h3 style='margin-bottom:.2em;margin-top:.2em'>Group Goals</h3></div>
            <textarea rows="10" cols="50" name="goals" style='width:99%;margin-left:.5em;margin-right:.5em;border:none' <?php echo $disabled;?>><?php echo $contract['goals'];?></textarea>
        </div>
        <div id='behavediv' class='half'>
            <?php if(isset($behaviors)&&count($behaviors>0)){
                    foreach($behaviors as $behave){
                        $title=$behave['title'];
                        $bid=$behave['BID'];
                        $notes=$behave['notes'];
                        $change=($contract['changedby']==$session->UID)? 'you':$database->getUserName($behave['changedby']);
                        $time=$behave['timestamp'];
                        echo"<div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em behave'>";
                        echo"<div class='ui-corner-top ui-widget-header m-b-05em'><input name='title-$bid' id='id-$bid' value='$title' style='width:18em' $disabled placeholder='Enter behavior title/name here' /><i> Last changed by $change on $time.</i><a href='#' style='float:right' class='close'>X</a></div>"
                        ."<textarea rows='5' cols='50' name='notes-$bid' id='notes-$bid' style='width:99%;border:none' $disabled>".stripslashes($notes)."</textarea></div>";
                    }
                }else{
                    for($x=0;$x<3;$x++){
                        $y=$database->getGuid();
                        echo"<div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em behave'>"
                        ."<div class='ui-corner-top ui-widget-header m-b-05em'><input name='title-$y' id='id-$y' style='width:18em' placeholder='Enter behavior title/name here' /><a href='#' style='float:right' class='close'>X</a></div>"
                        ."<textarea rows='5' cols='50' name='notes-$y' id='notes-$y' style='width:99%;border:none' $disabled></textarea></div>";

                    }
                }
            ?>
            <button id='add_tab' title='Click to add another behavior field.' style='color:#AA4643;position:absolute;bottom:.2em;left:60%' <?php echo $disabled;?>>Add Behavior</button>
        </div>

        <div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em half' >
            <div class='ui-corner-top ui-widget-header m-b-05em'><h3 style='margin-bottom:.2em;margin-top:.2em'>Additional Comments</h3></div>
            <textarea rows="10" cols="50" name="comments" style='width:99%;margin-left:.5em;margin-right:.5em;border:none'<?php echo $disabled;?>><?php echo $contract['comments'];?></textarea>
        </div>
        <?php if($disabled==''){?>
            <input type='hidden' name='ID' id='id' value='<?php echo $session->UID;?>'/>
            <input type='hidden' name='CID' id='cid' value='<?php echo $cid;?>'/>
            <input type='hidden' name='GID' id='gid' value='<?php echo $gid;?>'/>
            <input type='hidden' name='PID' id='pid' value='<?php echo $project;?>'/>
            <input type='reset' name='reset' id='reset' value='Cancel (undo changes)' style='font-size:1.5em;'>
            <input type='submit' name='save' id='save' value='Save Changes' style='font-size:1.5em;'>
            <?php if($session->isInstructor()||$session->isAdmin()){ ?>
                <input type='submit' name='finalize' id='finalize' value='Finalize Contract' style='color:#AA4643;font-size:1.5em;'>
                <?php }else{ ?>
                <input type='submit' name='accept' id='accept' value='Accept Contract' style='color:#AA4643;font-size:1.5em;'>
                <?php } ?>
            <?php }?>

    </form>
    <div id='dialog'>Dialog placeholder</div>
    <script>
        $(document).ready(function(){
            $("input:submit, button, #reset").button();
            $( "#dialog" ).dialog({
                autoOpen:false,
                buttons: {
                    Ok: function(){$( this ).dialog( "close" );}
                }
            });
            $('.close').live("click",function(){
                $(this).parent().parent('.behave').remove();
                $('#add_tab').css('bottom',parseFloat($('#add_tab').css('bottom'))+143);
                return false;
            });
            $('#add_tab').click(function(){
                var blen=$(".behave").length+1;
                $("#behavediv").append("<div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em behave'><div class='ui-corner-top ui-widget-header m-b-05em'><input name='title-"+blen+"' id='id-"+blen+"' style='width:18em' placeholder='Enter behavior title/name here' /><a href='#' style='float:right' class='close'>X</a></div><textarea rows='5' cols='50' name='notes-"+blen+"' id='notes-"+blen+"' style='width:99%;border:none'></textarea></div>");
                $(this).css('bottom',parseFloat($(this).css('bottom'))-143);
                return false;
            })
            $("#accept, #save, #finalize").click(function(){
                var method=$(this).attr('id');
                $.ajax({  
                    type:"POST",  
                    url: "../jx/contract.php?v="+jQuery.Guid.New(),  
                    data: $("#contract").serialize()+"&method="+method+"&sid="+jQuery.Guid.New(),
                    success:function(){
                        if(method!='save'){
                            window.location.href='contract.php';
                        }else{
                            $("#dialog").text("Your contract updated and saved.");
                            $("#dialog").dialog("open");
                        }
                    },
                    error:function(){
                        $("#dialog").text("There was an error, please try again.");
                        $("#dialog").dialog("open");
                    }  
                });
                return false;
            });
            $("reset").click(function(){
                window.location.href='contract.php';
            });
        })
    </script>
    </body>
</html>
