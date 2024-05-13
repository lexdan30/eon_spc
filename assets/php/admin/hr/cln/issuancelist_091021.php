<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry 			= new Query();	
$Qry->table     = "vw_clnissuance";
$Qry->selected  = "*";
$Qry->fields    = "1";
$rs 			= $Qry->exe_SELECT($con);
$return = '';

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[]  = array( 
            "id"            => $row['id'],
            "fnamecreator"  => $row['fnamecreator'],
            "lnamecreator"	=> $row['lnamecreator'],
            "template"	    => $row['template'],
            "date_created"	=> $row['date_created'],
            "date_issued"	=> $row['date_issued'],
            "content"	    => $row['content'],
            "fnamea1"	    => $row['fnamea1'],
            "lnamea1"	    => $row['lnamea1'],
            "mnamea1"	    => $row['mnamea1'],
            "fnamea2"	    => $row['fnamea2'],
            "lnamea2"	    => $row['lnamea2'],
            "mnamea2"	    => $row['mnamea2'],
            "fnamea3"	    => $row['fnamea3'],
            "lnamea3"	    => $row['lnamea3'],
            "mnamea3"	    => $row['mnamea3'],

            "a1pos"	        => $row['a1pos'],
            "a2pos"	        => $row['a2pos'],
            "a3pos"	        => $row['a3pos'],

            "department"	    => $row['department'],
            "salutation"	    => $row['salutation'],
			"firstname"	    => $row['firstname'],
            "lastname"	    => $row['lastname'],
            "middlename"	=> $row['middlename'],
            "middleinitial" => $row['middleinitial'],
            "suffix"	    => $row['suffix'],
            "SheorHe"	    => $row['HeorShe'],
            "annualpay"	    => $row['annualpay'],
            "hireddate"	    => $row['hireddate'],
            "position"	    => $row['jobposition'],
            "depid"	        => $row['depid'],
            "company"	    => $row['company']
        );
    }
    $return = json_encode($data);
}

print $return;
mysqli_close($con);
?>