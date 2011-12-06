<?php
    error_reporting(-1);
    include('header.php');
    $user='a692064b3294c09624d055a92ca0c038';
    $class='79d44de0-f371-11e0-863b-003048965058';
    try{
                $sth=$database->connection->prepare("SELECT ulevel FROM Users WHERE UID=:id");
                $sth->execute(array(':id'=>$user));
                while ($row=$sth->fetch(PDO::FETCH_ASSOC)){
                    $lvl=$row['ulevel'];
                }
            }catch(Exception $e){
                echo $e;
            }
            $items=array();
            try{
                //Projects
                $pth=$database->connection->prepare("SELECT PID, pname FROM Projects WHERE class=:class");
                $pth->bindParam(':class', $class, PDO::PARAM_STR);
                $pth->execute();
                while ($prow=$pth->fetch(PDO::FETCH_ASSOC)){
                    $items['projects'][$prow['PID']]=array('PID'=>$prow['PID'],'name'=>$prow['pname']);
                    if($lvl>1){
                        $gth=$database->connection->prepare("SELECT DISTINCT GID, name FROM Groups WHERE PID=:pid");
                        $gth->bindParam(':pid', $prow['PID'], PDO::PARAM_STR);
                    }else{
                        $gth=$database->connection->prepare("SELECT GID, name FROM Groups WHERE PID=:pid AND UID=:uid");
                        $gth->bindParam(':pid', $prow['PID'], PDO::PARAM_STR);
                        $gth->bindParam(':uid', $user, PDO::PARAM_STR);
                    }
                    $gth->execute();
                    while ($grow=$gth->fetch(PDO::FETCH_ASSOC)){
                        $items['projects'][$prow['PID']]['groups'][$grow['GID']]=array('GID'=>$grow['GID'],'name'=>$grow['name']);
                        $cth=$database->connection->prepare("SELECT CID, C.timestamp, C.changedby, U.fname, U.lname FROM Contracts AS C, Users AS U WHERE C.timestamp >= (CURDATE() - INTERVAL 14 DAY)  AND C.changedby = U.UID AND C.GID=:gid");
                        $cth->bindParam(':gid', $grow['GID'], PDO::PARAM_STR);
                        $cth->execute();
                        while ($crow=$cth->fetch(PDO::FETCH_ASSOC)){
                            $items['projects'][$prow['PID']]['groups'][$grow['GID']]['contract']=array('CID'=>$crow['CID'],'timestamp'=>$crow['timestamp'],'changedby'=>$crow['fname']." ".$crow['lname'],'changeid'=>$crow['changedby']);
                            $bth=$database->connection->prepare("SELECT B.BID, B.title, B.timestamp, B.changedby, U.lname,U.fname FROM Users AS U, Behaviors AS B WHERE U.UID=B.changedby AND B.timestamp >= (CURDATE() - INTERVAL 14 DAY) AND B.CID IN (SELECT DISTINCT CID FROM Contracts WHERE GID=:gid)");
                            $bth->bindParam(':gid', $grow['GID'], PDO::PARAM_STR);
                            $bth->execute();
                            while ($brow=$bth->fetch(PDO::FETCH_ASSOC)){
                                $items['projects'][$prow['PID']]['groups'][$grow['GID']]['contract']['behaviors'][$brow['BID']] = array('BID'=>$brow['BID'],'title'=>$brow['title'], 'timestamp'=>$brow['timestamp'],'changedby'=>$brow['fname']." ".$brow['lname'],'changeid'=>$brow['changedby']);
                            }
                            $rth=$database->connection->prepare("SELECT R.RID, R.timestamp, R.judge, U.fname, U.lname, F.Flag FROM Reviews AS R, Users AS U, Review_Flags AS F WHERE R.timestamp >= (CURDATE() - INTERVAL 14 DAY)  AND R.judge = U.UID AND R.EID IN (SELECT EID FROM Evals WHERE PID=:pid) AND F.RID=R.RID");
                            $rth->bindParam(':pid', $prow['PID'], PDO::PARAM_STR);
                            $rth->execute();
                            while ($rrow=$rth->fetch(PDO::FETCH_ASSOC)){
                                $items['projects'][$prow['PID']]['groups'][$grow['GID']]['contract']['reviews'][$rrow['RID']]=array('timestamp'=>$rrow['timestamp'],'flag'=>$rrow['Flag'],'changedby'=>$rrow['fname']." ".$rrow['lname'],'changeid'=>$rrow['judge']);
                            }
                        }
                    }
                }
        echo"<pre>";
        print_r($items);
        echo"</pre>";

    }catch(Exception $e){
        echo $e;
    }
    return $items;
?>