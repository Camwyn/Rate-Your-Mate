<?php //do NOT put anything above this line!
    $_GET['page']=$page='Add Class Form'; //Variable to set up the page title - feeds header.php
    if($session->lvl==1){
        header('Location:'.DOC_ROOT);
    }
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $students=$database->getStudents();
?>
<!-- Class Creation this will access the class table and student table to get a list of students
-->
<html>
    <body>
        <div class='left half ui-widget-content ui-tabs ui-corner-all'>
            <div class='ui-corner-top ui-widget-header m-b-1em'>Class:</div>
            <form name="className" id="className" method="post">
                <!-- input -->
                <input type='hidden' name='instructor' id='instructor' value='<?php echo $session->UID;?>'/>
                Class Name: <input type="text" name='cname' id='cname'>
                <div class='ui-state-error ui-corner-all' style='display:none;font-style:italic;padding:.1em;float:right' id='classname'>
                    <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>Class name already in use!
                </div>
                <ul id='classlist' style='min-height:2em;list-style:none'>
                    <li class='placeholder' style='font-style:italic;font-weight:normal'>Add students here</li>
                </ul>
                <input type = "submit" value = "Add Class" />

            </form>
        </div>

        <div id='studentbox' class='right half ui-widget-content ui-tabs ui-corner-all' style='min-height:9.2em;'>
            <div class='ui-corner-top ui-widget-header m-b-1em'>Students:</div>
            <ul style='list-style:none;' id='studentlist'>
                <?php
                    foreach($students as $student){
                        echo"<li id='".$student['id']."'>".$student['lname'].", ".$student['fname']."</li>";
                    }
                ?>
            </ul>
        </div>
        <div id='dialog'>Dialog placeholder</div>
        <!-- Java script that allows you to drag and drop students from a list into a class
        Class list will be on left and student list will be on right-->
        <script>
            $(document).ready(function(){
                $("input:submit, button").button();
                $( "#dialog" ).dialog({
                autoOpen:false,
                buttons: {
                    Ok: function(){$( this ).dialog( "close" );}
                }
            });
                $("#classlist").droppable({
                    activeClass: "ui-state-highlight",
                    hoverClass: "ui-state-hover",
                    accept: ":not(.ui-sortable-helper)",
                    drop: function(event,ui){
                        $(this).find(".placeholder").remove();
                        $("<li class='ui-state-highlight student' id='"+ui.draggable.attr('id')+"'>"+ui.draggable.html()+"&nbsp;<a href='#'>[x]</a></li>").appendTo(this);
                        ui.helper.remove();
                        ui.draggable.css({display:'none'});
                    }
                });

                $("#studentlist li").draggable({appendTo: "body",helper: "clone",cursor: "move",revert: "invalid"});

                /* tests project names aainst the database to prevent duplicates. */
                $("#cname").keyup(function(){check_availability();});

                function check_availability(){//function to check project name availability  
                    $.ajax({
                        type:"POST",  
                        url: "../jx/classname.php?v="+jQuery.Guid.New(),  
                        data: "cname="+$('#cname').val()+"&sid="+jQuery.Guid.New(),
                        success:function(data){
                            (data=='1')? ($("#cname").css('backgroundColor','#F0B5B5'),$("#classname").show()) : ($("#pid").css('backgroundColor','#FFF'),$("#classname").hide());
                        }
                    });
                }

                $("#className").submit(function(){					
                    var cntr =0;
                    var strng='';
                    $("#classlist").children().each(function(){
                        strng+='&id['+cntr+']='+$(this).attr('id');
                        cntr++;
                    });
                    if($("#cname").val()==''){
                    $("#dialog").text("You must enter a class name.");
                    $("#dialog").dialog("open");
                    return false;
                    }
                    $.ajax({
                        type:"POST",  
                        url: "../jx/newclass.php?v="+jQuery.Guid.New(),  
                        data: "instructor="+$('#instructor').val()+"&cname="+$('#cname').val()+strng+"&sid="+jQuery.Guid.New(),
                        success:function(data){
                            $("#dialog").text("Your class ("+$('#cname').val()+") has been created.");
                            $("#dialog").dialog("open");
                        }
                    });
                    return false;

                }); 

                $('li>a').live('click', function(){
                    var id=$(this).parent().attr('id');
                    $(this).parent().remove();
                    $("#studentlist > #"+id).show();
                });

            });

        </script>
    </body>
</html>
