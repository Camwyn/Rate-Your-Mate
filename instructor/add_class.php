<?php //do NOT put anything above this line!
    $_GET['page']=$page='Add Class Form'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $students=$database->getStudents();
?>
<!-- Class Creation this will access the class table and student table to get a list of students
-->
<html>
    <body>
        <h1><?php echo $page;?></h1>
        <div class='left half ui-widget-content ui-tabs ui-corner-all'>
            <div class='ui-corner-top ui-widget-header m-b-1em'>Class:</div>
            <form name="className" method="post">
                <!-- input -->
                Class Name: <input type="text" name='cname' id='cname'>
                <div class='ui-state-error ui-corner-all' style='display:none;font-style:italic;padding:.1em;width:210px;float:right' id='classname'>
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
		<!-- Java script that allows you to drag and drop students from a list into a class
                Class list will be on left and student list will be on right-->
        <script>
            $(document).ready(function(){
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
                        data: "classname="+$('#cname').val()+"&sid="+jQuery.Guid.New(),
                        success:function(data){
                            (data=='1')? ($("#cname").css('backgroundColor','#F0B5B5'),$("#classname").show()) : ($("#pid").css('backgroundColor','#FFF'),$("#classname").hide());
                        }
                    });
                }

                $('li>a').live('click', function(){
                    var id=$(this).parent().attr('id');
                    $(this).parent().remove();
                    $("#studentlist > #"+id).show();
                });

            });

        </script>
    </body>
</html>