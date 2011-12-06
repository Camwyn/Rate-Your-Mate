<?
    /**
    * Process.php
    * 
    * The Process class is meant to simplify the task of processing
    * user submitted forms, redirecting the user to the correct
    * pages if errors are found, or if form is successful, either
    * way. Also handles the logout procedure.
    * 
    */
    include("includes/session.php");

    class Process{
        // Class constructor
        function Process(){
            global $session;
            // User submitted login form
            if(isset($_POST['sublogin'])){
                $this->procLogin();
            }
            // User submitted registration form
            else if(isset($_POST['subjoin'])){
                    $this->procRegister();
                }else if(isset($_POST['subforgot'])){
                        // User submitted forgot password form
                        $this->procForgotPass();
                    }else if(isset($_POST['subedit'])){
                            // User submitted edit account form      
                            $this->procEditAccount();
                        }else if($session->logged_in){
                                // The only other reason user should be directed here is if he wants to logout, which means user is logged in currently.      
                                $this->procLogout();
                            }else{
                                // Should not get here, which means user is viewing this page by mistake and therefore is redirected.
                                header("Location: index.php");
            }
        }

        /**
        * procLogin - Processes the user submitted login form, if errors
        * are found, the user is redirected to correct the information,
        * if not, the user is effectively logged in to the system.
        */
        function procLogin(){
            global $session, $form;
            /* Login attempt */
            $retval = $session->login($_POST['user'], $_POST['pass'], isset($_POST['remember']));

            /* Login successful */
            if($retval){
                header("Location: activity.php");
            }else{ /* Login failed */
                $_SESSION['value_array'] = $_POST;
                $_SESSION['error_array'] = $form->getErrorArray();
                header("Location: ".$session->referrer);
            }
        }

        /**
        * procLogout - Simply attempts to log the user out of the system
        * given that there is no logout form to process.
        */
        function procLogout(){
            global $session;
            $retval = $session->logout();
            header("Location: index.php");
        }

        /**
        * procRegister - Processes the user submitted registration form,
        * if errors are found, the user is redirected to correct the
        * information, if not, the user is effectively registered with
        * the system and an email is (optionally) sent to the newly
        * created user.
        */
        function procRegister(){
            global $session, $form;
            /* Convert username to all lowercase (by option) */
            if(ALL_LOWERCASE){
                $_POST['user'] = strtolower($_POST['user']);
            }
            /* Registration attempt */
            $retval = $session->register($_POST['user'], $_POST['pass'], $_POST['email']);

            /* Registration Successful */
            if($retval == 0){
                $_SESSION['reguname'] = $_POST['user'];
                $_SESSION['regsuccess'] = true;
                header("Location: ".$session->referrer);
            }
            /* Error found with form */
            else if($retval == 1){
                    $_SESSION['value_array'] = $_POST;
                    $_SESSION['error_array'] = $form->getErrorArray();
                    header("Location: ".$session->referrer);
                }
                /* Registration attempt failed */
                else if($retval == 2){
                        $_SESSION['reguname'] = $_POST['user'];
                        $_SESSION['regsuccess'] = false;
                        header("Location: ".$session->referrer);
                    }
        }

        /**
        * procForgotPass - Validates the given username then if
        * everything is fine, a new password is generated and
        * emailed to the address the user gave on sign up.
        */
        function procForgotPass(){
            global $database, $session, $mailer, $form;
            /* Username error checking */
            $subuser = $_POST['user'];
            $field = "user";  //Use field name for username
            if(!$subuser || strlen($subuser = trim($subuser)) == 0){
                $form->setError($field, "* Username not entered<br>");
            }else{
                /* Make sure username is in database */
                $subuser = stripslashes($subuser);
                if(strlen($subuser) < 5 || strlen($subuser) > 30 ||
                !preg_match("/^([0-9a-z])+$/i", $subuser) ||
                (!$database->usernameTaken($subuser))){
                    $form->setError($field, "* Username does not exist<br>");
                }
            }      
            /* Errors exist, have user correct them */
            if($form->num_errors > 0){
                $_SESSION['value_array'] = $_POST;
                $_SESSION['error_array'] = $form->getErrorArray();
            }else{
                /* Generate new password */
                $newpass = $session->generateRandStr(8);         
                /* Get email of user */
                $usrinf = $database->getUserInfo($subuser);
                $email  = $usrinf['email'];         
                /* Attempt to send the email with new password */
                if($mailer->sendNewPass($subuser,$email,$newpass)){
                    /* Email sent, update database */
                    // Create a 256 bit (64 characters) long random salt Let's add 'something random' and the username to the salt as well for added security
                    $salt=hash('sha256',uniqid(mt_rand(),true).'something random'.strtolower($subuser));
                    // Prefix the password with the salt
                    $hash=$salt.$newpass;
                    // Hash the salted password a bunch of times
                    for($i=0;$i<10000;$i++){$hash=hash('sha256',$hash);}
                    // Prefix the hash with the salt so we can find it back later
                    $hash=$salt.$hash;
                    //store in database:
                    try{
                        $sth = $database->connection->prepare("UPDATE ".TBL_USERS." SET password=:hash WHERE username=:uname");
                        $sth->execute(array(':uname'=>$subuser,':hash'=>$hash));
                        $_SESSION['forgotpass'] = true;
                    }catch(Exception $e){
                        $_SESSION['forgotpass'] = false;//dB update failed
                    }
                }else{/* Email failure, do not change password */
                    $_SESSION['forgotpass'] = false;
                }
            }
            header("Location: ".$session->referrer);
        }

        /**
        * procEditAccount - Attempts to edit the user's account
        * information, including the password, which must be verified
        * before a change is made.
        */
        function procEditAccount(){
            global $session, $form;
            /* Account edit attempt */
            $retval = $session->editAccount($_POST['curpass'], $_POST['newpass'], $_POST['email']);

            /* Account edit successful */
            if($retval){
                $_SESSION['useredit'] = true;
                header("Location: ".$session->referrer);
            }
            /* Error found with form */
            else{
                $_SESSION['value_array'] = $_POST;
                $_SESSION['error_array'] = $form->getErrorArray();
                header("Location: ".$session->referrer);
            }
        }
    };

    /* Initialize process */
    $process = new Process;

?>
