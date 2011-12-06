<?php
	//Class Creation created by Jon Linden Modified by Stephen Page ;)
	$_GET['page']='Override Form'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
    $classes=$database->getClasses($session->UID);//function from the database.php file - returns an array of all classes for the provided instructor
?>

<body>
    <div class='left half'>
    <form name = "className" action = "link" method = "post">
        <!-- This will be pulled from database -->
		<div  class='m-b-1em'>
        <label for="class">Class</label>: <select name='class' id='class'>
            <option selected='selected'>Choose one...</option>
            <?php
                foreach($classes as $class){
                    $id=$class['id'];
                    $name=$class['name'];
                    echo"<option value='$id'>$name</option>";
                }
            ?>            
        </select>
        </div>
		<div  class='m-b-1em'>
        <!-- this is dependent on class and will be updated after class is selected -->
        <label for="studentsel">Student Name</label>:
        <select id='studentsel'>
            <option>Choose a class above...</option>
        </select>
        </div>
		<div  class='m-b-1em'>
        
        <label for="oldDate">Old Date and Time</label>: <input type='date' name='oldDate' id='oldDate' value='October 26, 2011' />

        <!-- Pull in old date from data base and put in label -->
		</div>
		<div  class='m-b-1em'>
        <label for="newDate">New Date and Time</label>: <input type='date' name='newDate' id='newDate' value='October 26, 2011' />
        <!-- use fancy java script that Steven is using on the orginal creation page -->
        </div>
        <input type = "submit" value = "Update" />
    </form>
	</div>
	<script>
	$(document).ready(function(){
		$("input:submit, button").button();
		
		$('#oldDate, #newDate').datetimepicker({timeFormat: 'hh:mm:ss',ampm: false});
		
		$("#class").change(function(){
            var value=$(this).val();
            $.ajax({  
                type: "POST",  
                url: "../jx/roster2.php?v="+jQuery.Guid.New(),  
                data: "class="+value,  
                success: function(data){
                    var insert=data;
                    $("#studentsel").html(insert);
                }  
            });
        });
	});
	</script>
</body>
</html>