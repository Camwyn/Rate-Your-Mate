<?php //do NOT put anything above this line!
    $_GET['page']='Student Final Report'; //Variable to set up the page title - feeds header.php
    include('../includes/header.php');//this include file has all the paths for the stylsheets and javascript in it.
?>
    <body>
        <form action="Insert_PHP" method="post">

            <select name="Project ID">
                <option value="0">Choose one...</option>
                <?php
                    /*for given student, get all projects they are in and loop:
                    * $projects = array();
                    * foreach ($projects as $project){
                    *   echo" <option value='$project['id']'>$project['name']</option>"
                    * }
                    */

                ?>
                <option value="Project 1">Project 1</option>
                <option value="Project 2">Project 2</option>
                <option value="Project 3">Project 3</option>
                <option value="Project 4">Project 4</option>
        </select> </form>

        <h2>Mean Score:</h2>
        <div id='pie-chart' style='border: 1px solid #000;height:10em;width:10em;'>this is where the image will go</div>
        <h2>Grade:<span id='grade'>105%</span></h2>
        <!-- get behaviors from database (based on project and group) and loop through them, adding appropriate student comments. --> 
        <?php           
            /*
            * grab behaviors 
            */        
        ?>
        <h2 style="border: 1px solid #000;border-bottom:1px solid #FFF;width:10em;margin-bottom:-20px;">Behavior 1</h2>
        <div style="border:1px solid #000;">
            <p><h3>Student Comments:</h3>
                Comments go here!
            </p>
            <p><h3>Instructor Comments:</h3>
                Comments go here!
            </p>
        </div>

        <button value="OK">OK</button>
        <script>
            $(document).ready(function(){
                $("input:submit, button").button();
            });
        </script>
    </body>
</html>
