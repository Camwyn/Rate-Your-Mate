<?php
/* Returns student list as list-items for a draggable list */
if(!isset($_GET['v'])||!isset($_POST['class'])){
    die;
}else{ //Enrollment (class,user)
    include('../includes/database.php');
    $roster=$database->getRoster($_POST['class']);
    if($roster!=null){
        $classlist="<div class='ui-corner-top ui-widget-header m-b-1em'>Students:</div><i>Drag names to the group tabs to add students to a group.</i><ul id='studentlist' style='list-style:none'>";    
        foreach($roster as $student){
            $classlist.="<li id='".$student['id']."'>".$student['lname'].", ".$student['fname']."</li>";
        }
        $classlist.="</ul>";
    }else{
        $classlist="There are no students in that class!";
    }
    echo $classlist;
}