<?php
    /*
    * This file is to hold all the constants - 'variables' that we use over and over and never change.
    */

    ini_set("display_errors", true);//turn off for live!
    error_reporting(-1);//turn off for live!
    date_default_timezone_set ("America/New_York");
    define('DOC_ROOT',"http://".$_SERVER['SERVER_NAME']);
    //databse info
    define( "DB_DSN", "mysql:host=localhost;dbname=wp4" );
    define('DB_USER', 'sjpage');
    define('DB_PASS', 'konkmove');
    define('DB_ERR', 'We appear to be having database issues, please bear with us and try again.');

    /**
    * Database Table Constants - these constants
    * hold the names of all the database tables used
    * in the script.
    */
    define('TBL_USERS', 'Users');
    define('TBL_BEHAVE', 'Behaviors');
    define('TBL_CLASS', 'Classes');
    define('TBL_CONTRACT', 'Contracts');
    define('TBL_ENROLL', 'Enrollment');
    define('TBL_EVAL', 'Evals');
    define('TBL_GRADE', 'Grades');
    define('TBL_GROUP', 'Groups');
    define('TBL_OVER', 'Overrides');
    define('TBL_PROJ', 'Projects');
    define('TBL_SCORE', 'Scores');
    define('TBL_ACTIVE', 'ActiveUsers');

    /**
    * Special Names and Level Constants - the intructor
    * page will only be accessible to  those users at the
    * instructor user level. Feel free to change the names
    * and level constants as you see fit, you may
    * also add additional level specifications.
    * Levels must be digits between 0-9.
    */
    define("ADMIN_NAME", 'Admin');
    define("INSTRUCTOR_NAME", 'Instructor');
    define("STUDENT_NAME",  'Student');
    define("ADMIN_LEVEL",  9);
    define("INSTRUCTOR_LEVEL", 5);
    define("STUDENT_LEVEL",  1);

    /**
    * This boolean constant controls whether or
    * not the script keeps track of active users
    * and active guests who are visiting the site.
    */
    define("TRACK_VISITORS", false);

    /**
    * Timeout Constants - these constants refer to
    * the maximum amount of time (in minutes) after
    * their last page fresh that a user and guest
    * are still considered active visitors.
    */
    define("USER_TIMEOUT", 10);

    /**
    * Cookie Constants - these are the parameters
    * to the setcookie function call, change them
    * if necessary to fit your website. If you need
    * help, visit www.php.net for more info.
    * <http://www.php.net/manual/en/function.setcookie.php>
    */
    define("COOKIE_EXPIRE", 60*60*24*100);  //100 days by default
    define("COOKIE_PATH", "/");  //Avaible in whole domain

    /**
    * Email Constants - these specify what goes in
    * the from field in the emails that the script
    * sends to users, and whether to send a
    * welcome email to newly registered users.
    */
    define("EMAIL_FROM_NAME", "Rate-Your-Mate System");
    define("EMAIL_FROM_ADDR", "sjpage@plymouth.edu");
    define("EMAIL_WELCOME", false);

    /**
    * This constant forces all users to have
    * lowercase usernames, capital letters are
    * converted automatically.
    */
    define("ALL_LOWERCASE", false);

    //persistant connection?
    try {
        $dbh = new PDO(DB_DSN,DB_USER,DB_PASS);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        die("Unable to connect. $e");
    }
    //not actually a constant, but since we'll use this often, this seemed a good place to put it
    $colors=array("#4572A7","#AA4643","#559A49","#80699B","#3D96AE","#DB843D","#92A8CD","#A47D7C","#B5CA92");
?>