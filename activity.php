<?php //do NOT put anything above this line!
    $_GET['page']=$page='Recent Activity'; //Variable to set up the page title - feeds header.php
    include('includes/header.php');//this include file has all the paths for the stylesheets and javascript in it.
    $classes=$database->getClasses($session->UID);
?>

<body>
    <div id='tabs' class='half'>
        <!-- tab list-item for each class -->
        <ul>
            <?php foreach($classes as $class){echo"<li><a href='#class-".$class['id']."'>".$class['name']."</a></li>";}?>
        </ul>
        <!-- tab div for each class -->
        <?php
            foreach($classes as $class){
                echo"<div id='class-".$class['id']."'>";//tab div start
                $changed=array();
                $behaviors=array();
                $contracts=array();
                $reviews=array();
                $project=false;
                try{
                    $sth=$database->connection->prepare("SELECT PID FROM Projects WHERE class=:class");
                    $sth->bindParam(':class', $class['id'], PDO::PARAM_STR);   
                    $sth->execute();
                    while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                        $project=$row['PID'];
                    }
                }catch(Exception $e){
                    echo DB_ERR;
                }

                if($project){
                    echo"<div class='accordion'>"; //accordion div start
                    $changed=$database->getChanged($project);
                    $behaviors=(isset($changed['behaviors']))?$changed['behaviors']:false;
                    $contracts=(isset($changed['contracts']))?$changed['contracts']:false;
                    $reviews=(isset($changed['reviews']))?$changed['reviews']:false;

                    echo "<h3 style='padding-left:2em;'>Contracts</h3><div>";
                    if($contracts){                        
                        echo"<ul style='list-style:none;line-height:2em;'>";
                        foreach($contracts as $contract){
                            echo "<li>"."The contract was changed by ".$contract['changedby']." on ".$contract['timestamp']."</li>";
                        }
                        echo "</ul>";
                    }else{
                        echo "No contract changes.";
                    }
                    echo "</div>";
                    echo "<h3 style='padding-left:2em;'>Behaviors</h3><div>";
                    if($behaviors){                        
                        echo"<ul style='list-style:none;line-height:2em;'>";
                        foreach($behaviors as $behavior){
                            echo "<li>".$behavior['title']." was changed by ".$behavior['changedby']." on ".$behavior['timestamp']."</li>";
                        }
                        echo "</ul>";
                    }else{
                        echo "No behavior changes.";
                    }
                    echo"</div>";
                    echo "<h3 style='padding-left:2em;'>Evals</h3><div>";
                    if($reviews){                        
                        echo"<ul style='list-style:none;line-height:2em;'>";
                        foreach($reviews as $review){
                            echo "<li>Review was changed by ".$review['fname']." ".$review['lname']." on ".$review['timestamp']."</li>";
                        }
                        echo "</ul>";
                    }else{
                        echo "No review changes.";
                    }
                    echo"</div>";
                    echo"</div>"; //accordion div end
                }else{
                    echo"There is no project for this class yet.";
                }
                echo "</div>"; // tab div end
            }
        ?>
    </div>
    <script>
        $(document).ready(function(){
            $( "div.accordion" ).accordion();
            $('#tabs').tabs();

        });

    </script>
</body>
</html>