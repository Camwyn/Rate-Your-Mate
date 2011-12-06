
<div class='ui-widget ui-widget-content ui-corner-all' style='width:450px;position:fixed;top:50%;left:50%;margin-left:-200px;margin-top:-120px;' id='login'>
<img src="img/rymLogo.png"/><br /><br />
    <div class="ui-widget-header ui-corner-top">Rate-Your-Mate: Login</div>
    <?php
        /* User not logged in, display the login form. If errors occurred, they will be displayed. */          
        if($form->num_errors > 0){
            echo "<font size='2' color='#ff0000'>".$form->num_errors." error(s) found</font>";
        }
    ?>
    <form action="process.php" method="POST" style='width:95%;margin:auto auto;margin-top:.75em;' >
        <div  border="0" cellspacing="0" cellpadding="3">
            <div>
                <div title="Alphanumeric (0-9 &amp; a-z) characters and underscores(_) emails allow '@' and '.'. Minimum 5 characters." >
                    <b>Username or email:</b>

                    <input style='margin-left:.245em' type="text" name="user" required="required" pattern="[a-z0-9A-Z_@.]{5,}" minlength='5' maxlength="30" value="<?php echo $form->value("user"); ?>">
                </div>
                <div>
                    <?php echo $form->error("user"); ?>
                </div>
            </div>
            <div>
                <div title='Alphanumeric (0-9 &amp; a-z) characters and underscores(_). 8-15 characters.' >
                    <b>Password:</b>

                    <input style='margin-left:5em' type="password" required="required" pattern="[0-9a-zA-Z_]{8,}" minlength='8' name="pass" maxlength="15" value="<?php echo $form->value("pass"); ?>">
                </div>
                <div>
                    <?php echo $form->error("pass"); ?>
                </div>
            </div>
            <div>
                <input type="submit" value="Login" style='float:right'>
                <div style='font-size: .8em;'><input type="checkbox" name="remember" checked='checked'>Remember me!&nbsp;
                    <input type="hidden" name="sublogin" value="1">
                    <br/><a href="forgotpass.php" id='fgtpass' style="font-size: .75em;" title='Have a new password emailed to you.'>Forgot Password?</a>
                </div>
                <p style='font-size:1.2em;display:none'>
                    Not registered? <a href="register.php" id='reglink' title='Click here to create an account and get started!'>Sign-Up!(disabled for now)</a>
                </p>
            </div>
        </div>
    </form>                    
</div>
<script>
$(function(){
    $('#fgtpass').click(function(){
        $('#login').load("./forgotpass.php")
        evt.preventDefault();
    });
    $('#reglink').click(function(){
        $('#login').load("./register.php")
        evt.preventDefault();
    });
});