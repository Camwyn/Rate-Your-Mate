<?php
    /**
    * Database.php
    * 
    * This Database class is meant to simplify the task of accessing information from the website's database.
    */
    error_reporting(-1);
    include("constants.php");
    class MySQLDB{
        var $connection;         //The MySQL database connection
        var $num_active_users;   //Number of active users viewing site
        var $num_members;        //Number of signed-up users

        /* Class constructor */
        function MySQLDB(){
            /* Make connection to database */
            try{
                $this->connection= new PDO(DB_DSN,DB_USER,DB_PASS);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }catch(Exception $e){echo DB_ERR;}
        }

        /**
        * confirmUserPass - Checks whether or not the given
        * username is in the database, if so it checks if the
        * given password is the same password in the database
        * for that user. If the user doesn't exist or if the
        * passwords don't match up, it returns an error code
        * (1 or 2). On success it returns 0.
        */
        function confirmUserPass($username, $password){
            /* Verify that user is in database */
            try{
                /* Verify that we're passed a username and not an email address */
                if(strpos($username,'@')){
                    $sth=$this->connection->prepare("SELECT password FROM ".TBL_USERS." WHERE email=:email");
                    $sth->bindParam(':email', $username, PDO::PARAM_STR);
                }else{
                    $sth=$this->connection->prepare("SELECT password FROM ".TBL_USERS." WHERE username=:uname");
                    $sth->bindParam(':uname', $username, PDO::PARAM_STR);
                }
                $sth->execute();
                $dbarray=$sth->fetch(PDO::FETCH_ASSOC);
                $count=$sth->rowCount();
            }catch(Exception $e){
                echo DB_ERR;
            }
            if(!$count || ($count < 1)){return 1;} //Indicates username failure

            /* Validate that password is correct */
            $hasher=$dbarray['password'];
            // The first 64 characters of the hash is the salt
            $salt=substr($hasher,0,64); 
            $hash=$salt.$password; 
            // Hash the password as we did before
            for($i=0;$i<10000;$i++){$hash=hash('sha256', $hash);} 
            $hash=$salt.$hash; 
            $sth=null;
            if($hash==$hasher){
                return 0; //Success! Username and password confirmed
            }else{
                return 2; //Indicates password failure
            }
        }

        /**
        * confirmUserID - Checks whether or not the given
        * username is in the database, if so it checks if the
        * given userid is the same userid in the database
        * for that user. If the user doesn't exist or if the
        * userids don't match up, it returns an error code
        * (1 or 2). On success it returns 0.
        */
        function confirmUserID($username, $userid){
            /* Verify that user is in database */
            try{
                /* Verify that we're passed a username and not an email address */
                if(strpos($username,'@')){
                    $sth=$this->connection->prepare("SELECT UID FROM ".TBL_USERS." WHERE email=:email");
                    $sth->bindParam(':email', $username, PDO::PARAM_STR);
                }else{
                    $sth=$this->connection->prepare("SELECT UID FROM ".TBL_USERS." WHERE username=:uname");
                    $sth->bindParam(':uname', $username, PDO::PARAM_STR);
                }

                $sth->execute();
                $dbarray=$sth->fetch(PDO::FETCH_ASSOC);
                $count=$sth->rowCount();
            }catch(Exception $e){
                echo DB_ERR;
            }
            if(!$count || ($count < 1)){return 1;} //Indicates username failure

            /* Retrieve userid from result, strip slashes */
            $dbarray['UID']=stripslashes($dbarray['UID']);
            $userid=stripslashes($userid);
            $sth=null;
            /* Validate that userid is correct */
            if($userid == $dbarray['UID']){
                return 0; //Success! Username and userid confirmed
            }else{
                return 2; //Indicates userid invalid
            }
        }

        /**
        * addNewUser - Inserts the given (username, password, email)
        * info into the database. Appropriate user level is set.
        * Returns true on success, false otherwise.
        */
        function addNewUser($username, $password, $email){
            $time=time();
            /* If admin sign up, give admin user level */
            if(strcasecmp($username, ADMIN_NAME) == 0){
                $ulevel=ADMIN_LEVEL;
            }else{
                $ulevel=USER_LEVEL;
            }
            try{
                $sth=$this->connection->prepare("INSERT INTO ".TBL_USERS." VALUES (:uname, :password, '0', :ulevel, :email, :time)");
                $sth->bindParam(':uname', $username, PDO::PARAM_STR);
                $sth->bindParam(':password', $password, PDO::PARAM_STR);
                $sth->bindParam(':ulevel', $ulevel, PDO::PARAM_INT);
                $sth->bindParam(':email', $email, PDO::PARAM_STR);
                $sth->bindParam(':time', $time, PDO::PARAM_STR);
                return $sth->execute();
            }catch(Exception $e){
                echo DB_ERR;
            }
            $q="INSERT INTO ".TBL_USERS." ";
            $sth=null;
        }

        /**
        * usernameTaken - Returns true if the username has
        * been taken by another user, false otherwise.
        */
        function usernameTaken($username){
            if(!get_magic_quotes_gpc()){
                $username=addslashes($username);
            }
            try{
                $sth=$this->connection->prepare("SELECT username FROM ".TBL_USERS." WHERE username=:uname");
                $sth->bindParam(':uname', $username, PDO::PARAM_STR);
                return $sth->execute();
                $count=$sth->rowCount();
                return ($count > 0);
            }catch(Exception $e){
                echo DB_ERR;
            }
            $sth=null;
        }

        /**
        * updateUserField - Updates a field, specified by the field
        * parameter, in the user's row of the database.
        */
        function updateUserField($UID, $field, $value){
            try{
                $sth=$this->connection->prepare("UPDATE ".TBL_USERS." SET ".$field."=:value WHERE UID=:uid");
                $sth->bindParam(':uid', $UID, PDO::PARAM_STR);
                if(is_int($value))
                    $param=PDO::PARAM_INT;
                elseif(is_bool($value))
                    $param=PDO::PARAM_BOOL;
                elseif(is_null($value))
                    $param=PDO::PARAM_NULL;
                elseif(is_string($value))
                    $param=PDO::PARAM_STR;
                else
                    $param=FALSE;                   
                if($param){
                    $sth->bindValue(":value",$value,$param);
                }
                return $sth->execute();
            }catch(Exception $e){
                echo $e;
                $sth=null;
            }
        }

        /**
        * getUserInfo - Returns the result array from a mysql
        * query asking for all information stored regarding
        * the given username. If query fails, NULL is returned.
        */
        function getUserInfo($username){
            try{
                /* Verify that we're passed a username and not an email address */
                if(strpos($username,'@')){
                    $sth=$this->connection->prepare("SELECT * FROM ".TBL_USERS." WHERE email=:email");
                    $sth->bindParam(':email', $username, PDO::PARAM_STR);
                }else{
                    $sth=$this->connection->prepare("SELECT * FROM ".TBL_USERS." WHERE username=:uname");
                    $sth->bindParam(':uname', $username, PDO::PARAM_STR);
                }
                $sth->execute();
                $dbarray=$sth->fetch(PDO::FETCH_ASSOC);
                $count=$sth->rowCount();
            }catch(Exception $e){
                echo DB_ERR;
            }
            $sth=null;
            /* Error occurred, return given name by default */
            if(!$count || ($count < 1)){
                return NULL;
            }
            /* Return result array */ 
            return $dbarray;
        }


        /**
        * getNumMembers - Returns the number of signed-up users
        * of the website, banned members not included. The first
        * time the function is called on page load, the database
        * is queried, on subsequent calls, the stored result
        * is returned. This is to improve efficiency, effectively
        * not querying the database when no call is made.
        */
        function getNumMembers(){// Calculate number of site members
            if($this->num_members < 0){
                try{  
                    $sth=$this->connection->prepare("SELECT * FROM ".TBL_USERS);
                    $sth->execute();
                    $count=$sth->rowCount();
                }catch(Exception $e){
                    echo DB_ERR;
                }
                $this->num_members=$count;
            }
            $sth=null;
            return $this->num_members;
        }

        /**
        * calcNumActiveUsers - Finds out how many active users
        * are viewing site and sets class variable accordingly.
        */
        function calcNumActiveUsers(){// Calculate number of users at site
            if($this->num_members < 0){
                try{  
                    $sth=$this->connection->prepare("SELECT * FROM ".TBL_ACTIVE);
                    $sth->execute();
                    $count=$sth->rowCount();
                }catch(Exception $e){
                    echo DB_ERR;
                }
                $this->num_active_users=$count;
                $sth=null;
            }
        }

        /**
        * addActiveUser - Updates username's last active timestamp
        * in the database, and also adds him to the table of
        * active users, or updates timestamp if already there.
        */
        function addActiveUser($username,$time){
            try{  
                $sth=$this->connection->prepare("UPDATE ".TBL_ACTIVE." SET timestamp=:time WHERE username=:uname");
                $sth->bindParam(':uname', $username, PDO::PARAM_STR);
                $sth->bindParam(':time', $time, PDO::PARAM_STR);
                $sth->execute();
            }catch(Exception $e){
                echo DB_ERR;
            }

            if(!TRACK_VISITORS) return;
            try{  
                $sth=$this->connection->prepare("REPLACE INTO ".TBL_ACTIVE." VALUES (:uname,:time)");
                $sth->bindParam(':uname', $username, PDO::PARAM_STR);
                $sth->bindParam(':time', $time, PDO::PARAM_STR);
                $sth->execute();
            }catch(Exception $e){
                echo DB_ERR;
            }
            $this->calcNumActiveUsers();
            $sth=null;
        }

        /* removeActiveUser */
        function removeActiveUser($username){
            if(!TRACK_VISITORS) return;
            try{  
                $sth=$this->connection->prepare("DELETE FROM ".TBL_ACTIVE." WHERE username=:uname");
                $sth->bindParam(':uname', $username, PDO::PARAM_STR);
                $sth->execute();
            }catch(Exception $e){
                echo DB_ERR;
            }
            $this->calcNumActiveUsers();
            $sth=null;
        }

        /* removeInactiveUsers */
        function removeInactiveUsers(){
            if(!TRACK_VISITORS) return;
            $timeout=time()-USER_TIMEOUT*60;
            try{  
                $sth=$this->connection->prepare("DELETE FROM ".TBL_ACTIVE." WHERE timestamp=:timeout");
                $sth->bindParam(':timeout', $timeout, PDO::PARAM_STR);
                $sth->execute();
            }catch(Exception $e){
                echo DB_ERR;
            }
            $this->calcNumActiveUsers();
            $sth=null;
        }

        /*Creates a GUID for dB use */
        function getGuid(){
            return $guid=(function_exists('com_create_guid') === true)? trim(com_create_guid(),'{}'):sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',mt_rand(0,65535),mt_rand(0,65535),mt_rand(0,65535),mt_rand(16384,20479),mt_rand(32768,49151),mt_rand(0,65535),mt_rand(0,65535),mt_rand(0,65535));
        }

        /**
        * query - Performs the given query on the database and
        * returns the result, which may be false, true or a
        * resource identifier.
        */
        function query($query){
            try{  
                $sth=$this->connection->prepare(":query");
                $sth->bindParam(':query', $query, PDO::PARAM_STR);
                return $sth->execute();
            }catch(Exception $e){
                echo $e;
            }
            $sth=null;
        }

        /**
        * getProjects - returns an array of projects for the given ID
        */
        function getProjects($id){
            try{
                $sth=$this->connection->prepare("SELECT ulevel FROM Users WHERE UID=:id");
                $sth->execute(array(':id'=>$id));
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $lvl=$row['ulevel'];
                }
            }catch(Exception $e){
                echo $e;
            }
            try{
                if($lvl>1){
                    $sth=$this->connection->prepare("SELECT * FROM Users LEFT JOIN Projects ON instructor=UID WHERE UID =:uid");
                    $sth->bindParam(':uid', $id, PDO::PARAM_STR); 
                }else{
                    $sth=$this->connection->prepare("SELECT * FROM Projects LEFT JOIN Groups ON Groups.PID=Projects.PID WHERE Groups.UID =:uid");
                    $sth->bindParam(':uid', $id, PDO::PARAM_STR);   
                }
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $projects[]=array('pid'=>$row['PID'],'pname'=>$row['pname']);
                }
            }catch(Exception $e){
                echo $e;
            }
            return $projects;
        }                                  

        /**
        * getStudents - returns an array of all students with associated IDs
        */
        function getStudents(){
            $students=array();
            try{
                $sth=$this->connection->prepare("SELECT fname,lname,UID FROM Users WHERE ulevel=1 ORDER BY lname ASC, fname ASC");   
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $students[]=array('id'=>$row['UID'],'fname'=>$row['fname'],'lname'=>$row['lname']);
                }
            }catch(Exception $e){
                echo $e;
            }
            return $students;
        }

        /**
        * getUserName - returns 'real name' of user, given ID
        */
        function getUserName($id){
            try{
                $sth=$this->connection->prepare("SELECT fname,lname FROM Users WHERE UID=:id");
                $sth->bindParam(':id', $id, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $name=$row['fname']." ".$row['lname'];
                }
            }catch(Exception $e){
                echo $e;
            }
            return $name;
        }

        /**
        * getGroupName - returns name of a group, given ID
        */
        function getGroupName($gid){
            try{
                $sth=$this->connection->prepare("SELECT name FROM Groups WHERE GID=:gid");
                $sth->bindParam(':gid', $gid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $name=$row['name'];
                }
            }catch(Exception $e){
                echo $e;
            }
            return $name;
        }

        /**
        * getProjName - returns name of a project, given ID
        */
        function getProjName($pid){
            try{
                $sth=$this->connection->prepare("SELECT pname FROM Projects WHERE PID=:pid");
                $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $name=$row['pname'];
                }
            }catch(Exception $e){
                echo $e;
            }
            return $name;
        }

        /**
        * getInstructor - returns array of instructor info for given class
        */
        function getInstructor($pid){
            $instructor=array();
            try{
                $sth=$this->connection->prepare("SELECT fname, lname, UID, email FROM Users WHERE UID IN (SELECT instructor FROM Projects WHERE PID=:pid)");
                $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $instructor['fname']=$row['fname'];
                    $instructor['lname']=$row['lname'];
                    $instructor['UID']=$row['UID'];
                    $instructor['email']=$row['email'];
                }
            }catch(Exception $e){
                echo $e;
            }
            return $instructor;
        }

        /**
        * getClasses - returns an array of all classes for a provided user
        * needs to know if the person is a student or an instructor
        */
        function getClasses($id){
            try{
                $sth=$this->connection->prepare("SELECT ulevel FROM Users WHERE UID=:id");
                $sth->execute(array(':id'=>$id));
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $ulevel=$row['ulevel'];
                }
            }catch(Exception $e){
                echo $e;
            }
            $classes=array();

            try{
                if($ulevel <= 1){ //user is a student
                    $sth=$this->connection->prepare("SELECT cname, CLID FROM Classes, Enrollment WHERE Classes.CLID=Enrollment.class AND Enrollment.user=:id");
                    $sth->execute(array(':id'=>$id));
                }else{ // user is an instructor or admin
                    $sth=$this->connection->prepare("SELECT DISTINCT cname, CLID FROM Classes WHERE instructor=:id");
                    $sth->execute(array(':id'=>$id));
                }

                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $classes[]=array('name'=>$row['cname'],'id'=>$row['CLID']);
                }
            }catch(Exception $e){
                echo $e;
            }
            return $classes;
        }


        /**
        * getRoster - returns an array of all students in provided class, along with associated IDs
        */
        function getRoster($class){
            $roster=array();
            try{
                $sth=$this->connection->prepare("SELECT fname,lname,UID FROM Users, Enrollment WHERE Users.UID=Enrollment.user AND Enrollment.class=:class ORDER BY lname ASC, fname ASC");
                $sth->bindParam(':class', $class, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $roster[]=array('id'=>$row['UID'],'fname'=>$row['fname'],'lname'=>$row['lname']);
                }
            }catch(Exception $e){
                echo $e;
            }
            return $roster;
        }

        /**
        * getGroupID - returns GID given user and project
        */
        function getGroupID($project,$user){
            $gid;
            try{
                $sth=$this->connection->prepare("SELECT GID FROM Groups WHERE PID=:project AND UID=:user");
                $sth->bindParam(':project', $project, PDO::PARAM_STR);
                $sth->bindParam(':user', $user, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $gid=$row['GID'];
                }
            }catch(Exception $e){
                echo $e;
            }
            return $gid;
        }

        /**
        * getContract - returns contract and behavior info for a given group.
        * This returns an array contaiing two arrays. The first is the contract
        *  data, the second is the behavior data
        */
        function getContract($group){
            $contdata=array();
            try{
                $sth=$this->connection->prepare("SELECT *,C.CID AS CCID, C.timestamp AS cstamp, B.timestamp AS bstamp, C.changedby AS cchange, B.changedby AS bchange FROM Contracts C  JOIN Behaviors B ON C.CID=B.CID WHERE C.GID=:group");
                $sth->bindParam(':group', $group, PDO::PARAM_STR);
                $sth->execute();
                if($sth->rowCount()>0){//test for empty results to save time...
                    $i=0;
                    while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                        if($i==0){
                            /*
                            * Since we're going to get duplicates of the contract data for each behavior, we create
                            * an iterator and only grab the contract data the first time through we stuff each 
                            * type of data in it's own array and stuff them in the original array. We'll have to
                            * disassemble the arrays on the other side,but still easier than the alternatives
                            */
                            $contract[]=array('CID'=>$row['CCID'],'goals'=>$row['goals'],'comments'=>$row['comments'],'timestamp'=>$row['cstamp'],'changedby'=>$row['cchange'],);   
                        }
                        $behaviors[]=array('title'=>$row['title'],'notes'=>$row['notes'],'BID'=>$row['BID'],'changedby'=>$row['bchange'],'timestamp'=>$row['bstamp']);
                        $i++;
                    }
                    $contdata=array("contract"=>$contract,'behaviors'=>$behaviors);
                }
            }catch(Exception $e){
                echo $e;
            }
            return $contdata;
        }

        /**
        * getFlag - returns flag given user and eval or contract
        * since you only need one of them, pass null for the other
        */
        function getFlag($user,$eval=null,$contract=null){
            $flag=false;
            if($eval!=null){
                $sth=$this->connection->prepare("SELECT Flag FROM Review_Flags WHERE RID=:eval AND UID=:user");
                $sth->bindParam(':eval', $eval, PDO::PARAM_STR);
                $sth->bindParam(':user', $user, PDO::PARAM_STR);
            }elseif($contract!=null){
                $sth=$this->connection->prepare("SELECT Flag FROM Contract_Flags WHERE CID=:contract AND UID=:user");
                $sth->bindParam(':contract', $contract, PDO::PARAM_STR);
                $sth->bindParam(':user', $user, PDO::PARAM_STR);
            }else{
                return false;//something went wrong - most likely a variable wasn't passed, and we got no results.
            }
            if(!$flag){
                try{
                    $sth->execute();
                    while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                        $flag=$row['Flag'];
                    }
                }catch(Exception $e){
                    echo $e;
                }
            }
            return $flag;
        }

        /**
        * setFlag - sets a flag given user and eval or contract
        * since you only need one of them, pass null for the other
        */
        function setFlag($user,$flag,$eval=null,$contract=null){
            if($eval!=null){
                $sth=$this->connection->prepare("INSERT INTO Review_Flags (RID,UID,Flag) VALUES (:eval,:user,:flag) ON DUPLICATE KEY UPDATE flag=:flag");
                $sth->bindParam(':eval', $eval, PDO::PARAM_STR);
                $sth->bindParam(':flag', $flag, PDO::PARAM_BOOL);//don't forget, true=locked
                $sth->bindParam(':user', $user, PDO::PARAM_STR);
                $sth->execute();
            }elseif($contract!=null){
                $sth=$this->connection->prepare("INSERT INTO Contract_Flags (CID,UID,Flag) VALUES (:contract,:user,:flag) ON DUPLICATE KEY UPDATE flag=:flag");
                $sth->bindParam(':contract', $contract, PDO::PARAM_STR);
                $sth->bindParam(':flag', $flag, PDO::PARAM_BOOL);//don't forget, true=locked
                $sth->bindParam(':user', $user, PDO::PARAM_STR);

            }else{
                return false;//something went wrong - most likely a variable wasn't passed, and we got no results.
            }
            $sth->execute();
        }
        
        /**
        * getRID - gets a review/eval ID given user and eval or contract
        * since you only need one of them, pass null for the other
        */
        function getRID($user,$flag,$eval=null,$contract=null){
            if($eval!=null){
                $sth=$this->connection->prepare("INSERT INTO Review_Flags (RID,UID,Flag) VALUES (:eval,:user,:flag) ON DUPLICATE KEY UPDATE flag=:flag");
                $sth->bindParam(':eval', $eval, PDO::PARAM_STR);
                $sth->bindParam(':flag', $flag, PDO::PARAM_BOOL);//don't forget, true=locked
                $sth->bindParam(':user', $user, PDO::PARAM_STR);
                $sth->execute();
            }elseif($contract!=null){
                $sth=$this->connection->prepare("INSERT INTO Contract_Flags (CID,UID,Flag) VALUES (:contract,:user,:flag) ON DUPLICATE KEY UPDATE flag=:flag");
                $sth->bindParam(':contract', $contract, PDO::PARAM_STR);
                $sth->bindParam(':flag', $flag, PDO::PARAM_BOOL);//don't forget, true=locked
                $sth->bindParam(':user', $user, PDO::PARAM_STR);

            }else{
                return false;//something went wrong - most likely a variable wasn't passed, and we got no results.
            }
            $sth->execute();
        }

        /**
        * getReviewFlags - returns an array of 'locked' ids for a contract -
        *  to check for sending to the instructor. Note we've excluded the user's UID 
        * because if they are editing the contract it's obviously not flagged for them.
        */
        function getReviewFlags($contract,$uid){
            $flags=array();
            $sth=$this->connection->prepare("SELECT Flag, UID FROM Review_Flags WHERE RID=:eval AND UID != :uid");
            $sth->bindParam(':eval', $eval, PDO::PARAM_STR);
            $sth->bindParam(':uid', $uid, PDO::PARAM_STR);
            $sth->execute();
            while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                if($row['Flag']==1){
                    $flags[]=$row['UID'];
                }
            }

            return $flags;
        }

        /**
        * checkLocks - Checks to see if all group members have accepted.Returns a boolean.
        */
        function checkLocks($contract){
            $lock=true;//all have accepted
            $sth=$this->connection->prepare("SELECT Flag FROM Contract_Flags WHERE CID=:contract");
            $sth->bindParam(':contract', $contract, PDO::PARAM_STR);
            $sth->execute();
            while($row=$sth->fetch(PDO::FETCH_ASSOC)){
                if($row['Flag']==false){
                    $lock=false;
                }
            }

            return $lock;
        }

        /**
        * groupRoster - returns an array of all students in provided group,
        *  except for current user, along with associated IDs and emails
        */
        function groupRoster($gid,$user){
            $groster=array();
            try{
                $sth=$this->connection->prepare("SELECT fname,lname,email,Users.UID AS UID FROM Users JOIN Groups ON Users.UID=Groups.UID AND GID=:gid");
                $sth->bindParam(':gid', $gid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    if($row['UID']!=$user){
                        $groster[]=array('id'=>$row['UID'],'fname'=>$row['fname'],'lname'=>$row['lname'],'email'=>$row['email']);
                    }
                }
            }catch(Exception $e){
                echo $e;
            }
            return $groster;
        }

        /**
        * getGroups - returns an array of all groups in provided class, along with associated GIDs
        */
        function getGroups($class){
            $groups=array();
            try{
                $sth=$this->connection->prepare("SELECT GID, name FROM Groups WHERE PID=:class");
                $sth->bindParam(':class', $class, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $groups[]=array('id'=>$row['UID'],'fname'=>$row['fname'],'lname'=>$row['lname']);
                }
            }catch(Exception $e){
                echo $e;
            }
            return $groups;
        }

        /**
        * getMembers - returns an array of all groups in provided class, along with associated GIDs
        */
        function getMembers($class,$group){
            $members=array();
            try{
                $sth=$this->connection->prepare("SELECT fname,lname,UID FROM Users, Enrollment WHERE Users.UID=Enrollment.user AND Enrollment.class=:class ORDER BY lname ASC, fname ASC");
                $sth->bindParam(':class', $class, PDO::PARAM_STR);
                $sth->bindParam(':group', $group, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $members[]=array('id'=>$row['UID'],'fname'=>$row['fname'],'lname'=>$row['lname']);
                }
            }catch(Exception $e){
                echo $e;
            }
            return $members;
        }

        /**
        * getBehaviors - returns an array of all behaviors for a provided group
        */
        function getBehaviors($cid){
            $behaviors=array();
            try{
                $sth=$this->connection->prepare("SELECT title,notes,BID,timestamp FROM Behaviors WHERE CID=:cid");
                $sth->bindParam(':cid', $cid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $behaviors[]=array('title'=>$row['title'],'notes'=>$row['notes'],'id'=>$row['BID'],'time'=>$row['timestamp']);
                }
            }catch(Exception $e){
                echo $e;
            }
            return $behaviors;
        }

        /**
        * getMaxPoints - returns the max allowed points for the provided project
        */
        function getMaxPoints($pid){
            $maxpoints=0;// Default value
            try{
                $sth=$this->connection->prepare("SELECT maxpoints FROM Projects WHERE PID=:pid");
                $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $maxpoints=$row['maxpoints'];
                }
            }catch(Exception $e){
                echo $e;
            }
            return $maxpoints;
        }

        /**
        * getEID - returns the EID for the current eval
        * returns false if there isn't one currently open
        */
        function getEID($pid){
            $eid=false;//default 'there is no current project'
            try{
                $sth=$this->connection->prepare("SELECT EID FROM Evals WHERE PID=:pid AND (CURDATE() BETWEEN odate AND cdate)");
                $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $eid=$row['EID'];
                }
            }catch(Exception $e){
                echo $e;
            }
            return $eid;
        }

        /**
        * getChanged - returns an array of items (projects, behaviors, contracts, reviews) and who changed them if they changed within the last 14 days. Requires an associated $pid (project id)
        */
        function getChanged($pid){
            $items=array();
            try{
                //Projects - empty unless the instructor has made a change
                $sth=$this->connection->prepare("SELECT PID AS item FROM Projects WHERE timestamp BETWEEN ((CURDATE() - INTERVAL 10 DAY) AND CURDATE()) AND PID=:pid");
                $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $items['projects'][]=array('id'=>$row['item']);
                }
                
                //Contracts
                $sth=$this->connection->prepare("SELECT CID AS item, C.timestamp, C.changedby, U.fname, U.lname FROM Contracts AS C, Users AS U WHERE C.timestamp BETWEEN ((CURDATE() - INTERVAL 11 DAY) AND CURDATE()) AND C.changedby = U.UID AND C.GID IN (SELECT GID FROM Groups WHERE PID=:pid)");
                $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $items['contracts'][$row['item']]=array('timestamp'=>$row['timestamp'],'changedby'=>$row['fname']." ".$row['lname']);
                }
                
                //Behaviors
                $sth=$this->connection->prepare("SELECT DISTINCT B.BID as item, B.title, B.timestamp, B.changedby, U.lname,U.fname FROM Users AS U, Behaviors AS B WHERE U.UID=B.changedby AND B.timestamp BETWEEN (CURDATE() - INTERVAL 12 DAY) AND CURDATE() AND B.CID IN (SELECT DISTINCT CID FROM Contracts WHERE GID IN (SELECT DISTINCT GID FROM Groups WHERE PID=:pid))");
                $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $items['behaviors'][$row['item']]=array('title'=>$row['title'], 'timestamp'=>$row['timestamp'],'changedby'=>$row['fname']." ".$row['lname']);
                }
                
                //Reviews
                $sth=$this->connection->prepare("SELECT RID AS item, R.timestamp, R.judge, U.fname, U.lname FROM Reviews AS R, Users AS U WHERE R.timestamp BETWEEN ((CURDATE() - INTERVAL 13 DAY) AND CURDATE()) AND R.judge = U.UID AND R.EID IN (SELECT EID FROM Evals WHERE PID=:pid)");
                $sth->bindParam(':pid', $pid, PDO::PARAM_STR);
                $sth->execute();
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $items['reviews'][$row['item']]=array('timestamp'=>$row['timestamp'],'changedby'=>$row['fname']." ".$row['lname']);
                }

            }catch(Exception $e){
                echo $e;
            }
            return $items;
        }

    };//end MySQLDB

    /* Create database connection */
    $database=new MySQLDB;
?>
