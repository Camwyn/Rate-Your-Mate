<?php //do NOT put anything above this line!
    $_GET['page']=$page='Contract Creation'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $project=$session->currproj;//pass me a project
    $gid=$database->getGroupID($project,$session->UID);
    $contdata=$database->getContract($gid);//grab all the info in one fell swoop
    $behaviors=$contdata['behaviors'];//separate out the behaviors for easier access
    $contract=$contdata['contract'][0];//separate out the contract for easier access
    $cid=$contract['CID'];
    $flag=$database->getFlag($session->UID,null,$cid);//second and third arguments are optional, we're looking for contract, so we pass null for eval.

?>
<!-- Originally made by Richard Frederick edited by Jon Linden -->
<body class='two-thirds' style='min-width:105em;'>
    <h1 style='margin-bottom:0px;'><?php echo $page;?><img src='../img/help.png' title='help'/></h1>
    <h3 style='font-style: italic'>Last changed by <?php echo ($contract['changedby']==$session->UID)? 'you':$database->getUserName($contract['changedby']);echo" on ".$contract['timestamp'];?></h3>
    <form name="contract" id=contract action="contract.php" method="post">       
        <div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em half' >
            <div class='ui-corner-top ui-widget-header m-b-05em'><h3 style='margin-bottom:.2em;margin-top:.2em'>Group Goals</h3></div>
            <textarea rows="10" cols="50" name="goals" style='width:99%;margin-left:.5em;margin-right:.5em;border:none'><?php echo $contract['goals'];?></textarea>
        </div>
        <?php if(count($behaviors>0)){
                foreach($behaviors as $behave){
                    $title=$behave['title'];
                    $bid=$behave['BID'];
                    $notes=$behave['notes'];
                    $change=($contract['changedby']==$session->UID)? 'you':$database->getUserName($behave['changedby']);
                    $time=$behave['timestamp'];
                    echo"<div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em half'>"
                    ."<div class='ui-corner-top ui-widget-header m-b-05em'><input name='title-$bid' id='id-$bid' value='$title' style='width:18em'><i> Last changed by $change on $time.</i></div>"
                    ."<textarea rows='5' cols='50' name='notes-$bid' id='notes-$bid' style='width:99%;border:none'>$notes</textarea></div>";
                }
            }else{
                for($x=0;$x<3;$x++){
                    echo"<div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em half'>"
                    ."<div class='ui-corner-top ui-widget-header m-b-05em'><input name='title-$x' id='id-$x' style='width:18em'></div>"
                    ."<textarea rows='5' cols='50' name='notes-$x' id='notes-$x' style='width:99%;border:none'></textarea></div>";

                }
            }
        ?>
        <div class='ui-corner-all ui-tabs ui-widget ui-widget-content m-b-1em half' >
            <div class='ui-corner-top ui-widget-header m-b-05em'><h3 style='margin-bottom:.2em;margin-top:.2em'>Additional Comments</h3></div>
            <textarea rows="10" cols="50" name="comments" style='width:99%;margin-left:.5em;margin-right:.5em;border:none'><?php echo $contract['comments'];?></textarea>
        </div>
        <input type='hidden' name='ID' id='id' value='<?php echo $session->UID;?>'/>
        <input type='hidden' name='CID' id='cid' value='<?php echo $cid;?>'/>
        <input type='hidden' name='GID' id='gid' value='<?php echo $gid;?>'/>
        <input type='hidden' name='PID' id='pid' value='<?php echo $project;?>'/>
        <input type='submit' name='save' id='save' value='Save Changes' style='color:#E17009;font-size:1.5em;'>
        <?php if($session->isInstructor()||$session->isAdmin()){ ?>
            <input type='submit' name='finalize' id='finalize' value='Finalize Contract' style='color:#E17009;font-size:1.5em;'>
            <?php }else{ ?>
            <input type='submit' name='accept' id='accept' value='Accept Contract' style='color:#E17009;font-size:1.5em;'>
            <?php } ?>
        <input type='reset' name='reset' id='reset' value='Cancel (undo changes)' style='color:#E17009;font-size:1.5em;'>

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
            $("#accept, #save").click(function(){
                var method="&method="+$(this).attr('id');
                $.ajax({  
                    type:"POST",  
                    url: "../jx/behaviors.php?v="+jQuery.Guid.New(),  
                    data: $("#contract").serialize()+method+"&sid="+jQuery.Guid.New(),
                    success:function(){
                        $("#dialog").text("Your contract updated and saved.");
                        $("#dialog").dialog("open");
                    },
                    error:function(){
                        $("#dialog").text("There was an error, please try again.");
                        $("#dialog").dialog("open");
                    }  
                });
                return false;
            });
        })
    </script>
    </body>
</html>
