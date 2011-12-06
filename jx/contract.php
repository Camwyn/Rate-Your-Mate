<?php
    error_reporting(-1);// This will display any error in the ajax response.
    $sid=htmlentities($_POST["sid"],ENT_QUOTES,'iso-8859-1');if(!isset($_GET['v'])&&$sid=NULL){die;}// This tests for dummy data added for security purposes.
    include("../includes/session.php");
    //include("../includes/mailer.php");// Need this for sending notification emails.
    // Let's set up some variables - and possibly an array.
    $id=$_POST['ID'];// Current user
    $gid=$_POST['GID'];// Current group
    $pid=$_POST['PID'];// Current project
    if(isset($_POST['CID'])){//check for contract first, if exists - get copy.
        $cid=$_POST['CID'];
        $contdata=$database->getContract($gid);// Grab all the info in one fell swoop!
        $behaviors=$contdata['behaviors'];// Separate out the behaviors for easier access.
        $contract=$contdata['contract'][0];// Separate out the contract for easier access.
    }else{// Contract not in dB so create a new GUID
        $cid=$database->$database->getGuid();
        $contract=null;// Cast contract as a null value for our checks
    }
    $group=$database->groupRoster($gid,$id);// We'll need this to direct emails
    $method=$_POST['method']; // We need this for later comparison

    // Now we need to loop through the post variables to extract out the titles and comments...sigh...
    $behaves=array();
    foreach($_POST as $key=>$val){
        $postsub=substr($key,0,5);
        if($postsub=='notes'){
            $behaves[substr($key,6)]['BID']=substr($key,6);
            $behaves[substr($key,6)]['notes']=$val;
        }elseif($postsub=='title'){
            $behaves[substr($key,6)]['title']=$val;
        }
    }

    /**
    * checkDiff - function returns a boolean upon comparing current contract 
    * and behaviors in the database to the $_POST array.
    */
    function checkDiff($contract=null,$behaves=null,$behaviors=null){ //defaults are null, so that if we don't pass something we still get the correct outcome
        if(!is_null($contract)){
            if(strcasecmp($contract['CID'],$_POST['CID'])==0&&strcasecmp($contract['goals'],$_POST['goals'])==0&&strcasecmp($contract['comments'],$_POST['comments'])==0){
                // Contract is ok, now check behaviors. Essentially we're holding a false here, waiting for behaviors to measure up.
                $i=0;
                foreach($behaves as $behave){
                    if(strcasecmp($behave['BID'],$behaviors[$i]['BID'])!=0||strcasecmp($behave['notes'],$behaviors[$i]['notes'])!=0||strcasecmp($behave['title'],$behaviors[$i]['title'])!=0){
                        return true;// If there is any difference, we return true and make changes
                    }
                    $i++;
                }
                return false;//we've looped through the behaviors and all is good

            }else{
                return true; // Contract difference
            }
        }
        return true;// No array was passed, so we need to flag for insert.
    }

    /**
    * updateContract - because I had to use it twice and the code was getting thick.
    */
    function updateContract($database,$cid,$gid,$id){
        /* 
        * Compare current contracts to array. We need to do this on an accept,
        * in case they decide to get cute and make changes then accept 
        * Create/update contract in database. We can just set
        * this up once since we can use ON DUPLICATE KEY UPDATE
        */
        try{
            $sth = $database->connection->prepare("INSERT INTO Contracts (CID, GID, goals, comments, changedby) VALUES (:cid, :gid, :goals, :comm, :UID) ON DUPLICATE KEY UPDATE GID=:gid, goals=:goals, comments=:comm, changedby=:UID;");
            $sth->execute(array(":cid"=>$cid, ":gid"=>$gid, ":goals"=>$_POST['goals'], ":comm"=>$_POST['comments'], ":UID"=>$id));
        }catch(Exception $e){
            echo $e;
        }
    }

    /**
    * updateBehaviors - because I had to use it twice and the code was getting thick.
    */
    function updateBehaviors($database,$behaves,$cid,$id){
        /* 
        * Create/update behaviors in database. Again, we can just
        * set this up once since we can use ON DUPLICATE KEY UPDATE
        */
        foreach($behaves as $behave){
            try{
                $sth = $database->connection->prepare("INSERT INTO Behaviors (BID, CID, notes, title, changedby) VALUES (:bid, :CID, :notes, :title, :UID) ON DUPLICATE KEY UPDATE CID=:CID, notes=:notes, title=:title, changedby=:UID;");
                $bGUID= (isset($behave['BID']))? $behave['BID'] : $database->getGuid();//should already have one if we're editing, else create one
                $sth->execute(array(":bid"=>$bGUID, ":CID"=>$cid, ":notes"=>$behave['notes'], ":title"=>$behave['title'], ":UID"=>$id));
            }catch(Exception $e){
                echo $e;
            }
        }
    }

    if($method=='accept'){// They hit the accept button
        if(checkDiff($contract,$behaves,$behaviors)){// There's a difference, update
            updateContract($database,$cid,$gid,$id);
            updateBehaviors($database,$behaves,$cid,$id);
            $database->setFlag($id,1,null,null,$cid);// Lock me.
            foreach($group as $member){
                $database->setFlag($member['id'],0,null,null,$cid);// Unlock others.
                $message=$session->userinfo['fname']." ".$session->userinfo['lname']." has made changes to your group contract for ".$database->getProjName($pid).". Please <a href='".DOC_ROOT."'>log in</a> and edit or accept the current version.";
                $mailer->sendMail($member['fname']." ".$member['lname'],$member['email'],$message);
            }
        }else{// No difference - just change locks and notify as needed
            $database->setFlag($id,1,null,null,$cid);// Lock me.
            if ($database->checkLocks($cid)){// Everyone is locked (accepted)
                $message=$database->getGroupName($gid)." from ".$database->getProjName($pid)." have all accepted their group contract. Please <a href='".DOC_ROOT."'>log in</a> and edit or finalize the current version.";
                $instructor=$database->getInstructor($pid);
                $database->setFlag($instructor['UID'],0,null,null,$cid);// Unlock instructor.
                $mailer->sendMail($instructor['fname']." ".$instructor['lname'],$instructor['email'],$message);
            }else{//some have not accepted yet
                $lock=$database->getContractFlags($cid,$id);
                foreach($group as $member){
                    if(!in_array($member['id'],$lock)){
                        $message=$session->userinfo['fname']." ".$session->userinfo['lname']." has accepted your group contract for ".$database->getProjName($pid).". Please <a href='".DOC_ROOT."'>log in</a> and edit or accept the current version.";
                        $mailer->sendMail($member['fname']." ".$member['lname'],$member['email'],$message);
                    }else{
                        $message=$session->userinfo['fname']." ".$session->userinfo['lname']." has accepted your group contract for ".$database->getProjName($pid).". They made no changes.";
                        $mailer->sendMail($member['fname']." ".$member['lname'],$member['email'],$message);
                    }
                }
            }
        }
    }else{// They hit the save button
        if(checkDiff($contract,$behaves,$behaviors)){// There's a difference, update
            updateContract($database,$cid,$gid,$id);
            updateBehaviors($database,$behaves,$cid,$id);
            $database->setFlag($id,0,null,null,$cid);// Unlock me.
            foreach($group as $member){// Unlock others.
                $database->setFlag($member['id'],0,null,null,$cid);
                $message=$session->userinfo['fname']." ".$session->userinfo['lname']." has made changes to your group contract for ".$database->getProjName($pid).". Please <a href='".DOC_ROOT."'>log in</a> and edit or accept the current version.";
                $mailer->sendMail($member['fname']." ".$member['lname'],$member['email'],$message);
            }
        }else{// No difference - just change locks and notify as needed
            $database->setFlag($id,0,null,null,$cid);// Unlock me.
            $message="You have saved your contract for ".$database->getProjName($pid)." to accept later. Don't forget to <a href='".DOC_ROOT."'>log in</a> and accept the current version when you're ok with it!";
            $mailer->sendMail($session->userinfo['fname'],$session->userinfo['email'],$message);
        }
    }
