<? 
    /**
    * Mailer.php
    *
    * The Mailer class is meant to simplify the task of sending
    * emails to users. Note: this email system will not work
    * if your server is not setup to send mail.
    */

    class Mailer
    {
        /**
        * sendWelcome - Sends a welcome message to the newly
        * registered user, also supplying the username and
        * password.
        */
        function sendWelcome($username, $email, $pass){
            $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
            $subject = "Welcome to Rate-Your-Mate!";
            $body = "$username,\n\n"
            ."Welcome! You've just registered at Rate-Your-Mate "
            ."with the following information:\n\n"
            ."Username: $username\n"
            ."Password: $pass\n\n"
            ."If you ever lose or forget your password, a new "
            ."password will be generated for you and sent to this "
            ."email address, if you would like to change your "
            ."email address you can do so by going to the "
            ."My Account page after signing in.\n\n"
            ."- Rate-Your-Mate";

            return mail($email,$subject,$body,$from);
        }

        /**
        * sendNewPass - Sends the newly generated password
        * to the user's email address that was specified at
        * sign-up.
        */
        function sendNewPass($username, $email, $pass){
            $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
            $subject = "Rate-Your-Mate - Your new password";
            $body = "$username,\n\n"
            ."We've generated a new password for you at your "
            ."request, you can use this new password with your "
            ."username to log in to Rate-Your-Mate.\n\n"
            ."Username: $username\n"
            ."New Password: $pass\n\n"
            ."It is recommended that you change your password "
            ."to something that is easier to remember, which "
            ."can be done by going to the My Account page "
            ."after signing in.\n\n"
            ."- Rate-Your-Mate";

            return mail($email,$subject,$body,$from);
        }

        /**
        * sendMail - Sends the a notification to the
        * user's email address that was specified at
        * sign-up. Arguments: 
        * username: person email is going to
        * email: their email address
        * message: allows custom message to be sent when called
        */
        function sendMail($username, $email, $message){
            $from = "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
            $subject = "Rate-Your-Mate - Notification";
            $body = "$username,\n\n$message\n\n - Rate-Your-Mate";

            return mail($email,$subject,$body,$from);
        }
    };

    /* Initialize mailer object */
    $mailer = new Mailer;

?>
