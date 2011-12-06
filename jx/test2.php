<?php
    error_reporting(-1);// This will display any error in the ajax response.
    define('TEST','foo',true);
    var_dump(TEST);
    echo "<br />";
    define('TEST','bar');
    var_dump(TEST);
    echo "<br />";
    print_r(TEST);
?>
