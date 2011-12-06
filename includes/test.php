<?php
    error_reporting(-1);// This will display any error in the ajax response.

    include("header.php");
    $class='';

        $changed=array();
        $behaviors=array();
        $contracts=array();
        $reviews=array();
        $project=false;

        $group=$database->getChanged('79d44de0-f371-11e0-863b-003048965058','a692064b3294c09624d055a92ca0c038');



        echo"<pre>";
        print_r($group);
        echo"</pre><br/>";

      
?>
