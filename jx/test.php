<?php
    error_reporting(-1);// This will display any error in the ajax response.
    $sid=htmlentities($_POST["sid"],ENT_QUOTES,'iso-8859-1');if(!isset($_GET['v'])&&$sid=NULL){die;}// This tests for dummy data added for security purposes.
    include("../includes/database.php");
    include("../includes/mailer.php");// Need this for sending notification emails.
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
    function checkDiff($contract=null,$behaves=null,$behaviors=null){ //default is null, so that if we don't pass something we still get the correct outcome
        if(!is_null($contract)){
            if(strcasecmp($contract['CID'],$_POST['CID'])==0&&strcasecmp($contract['goals'],$_POST['goals'])==0&&strcasecmp($contract['comments'],$_POST['comments'])==0){
                // Contract is ok, now check behaviors.
                $i=0;
                foreach($behaves as $behave){
                    if(strcasecmp($behave['BID'],$behaviors[$i]['BID'])!=0||strcasecmp($behave['notes'],$behaviors[$i]['notes'])!=0||strcasecmp($behave['title'],$behaviors[$i]['title'])!=0){
                        return 'true - BID is bad';// If there is any difference, we return true and make changes
                    }
                    $i++;
                }//we've looped through the behaviors and all is good
                    return 'false - BID is good';
       
            }else{
                return 'true - CID is bad'; // Contract difference
            }

        }
        return 'true - no array';// No array was passed, so we need to flag for insert.
    }

    echo checkDiff($contract,$behaves,$behaviors);



?>
