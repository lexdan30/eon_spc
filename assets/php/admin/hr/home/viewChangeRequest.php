<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblchangereq";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";

$rs = $Qry->exe_SELECT($con);

while($row=mysqli_fetch_array($rs)){
	
	 $current_address = '';
		if( !empty( $row['current_add_st'] ) ){
			$current_address = $current_address .  $row['current_add_st'] . ',';
		}
		if( !empty( $row['current_add_area'] ) ){
			$current_address = $current_address .  $row['current_add_area'] . ',';
		}
		if( !empty( $row['current_add_city'] ) ){
			$current_address = $current_address .  $row['current_add_city'] . ',';
		}
		if( !empty( $row['current_add_prov'] ) ){
			$current_address = $current_address .  $row['current_add_prov'] . ',';
		}
		if( !empty( $row['current_add_code'] ) ){
			$current_address = $current_address .  $row['current_add_code'] . ',';
		}
        $current_address = substr($current_address,0, strlen($current_address)-1);
        if($current_address == false){
            $current_address ='';
        }



    $new_address = '';
		if( !empty( $row['new_add_st'] ) ){
			$new_address = $new_address .  $row['new_add_st'] . ',';
		}
		if( !empty( $row['new_add_area'] ) ){
			$new_address = $new_address .  $row['new_add_area'] . ',';
		}
		if( !empty( $row['new_add_city'] ) ){
			$new_address = $new_address .  $row['new_add_city'] . ',';
		}
		if( !empty( $row['new_add_prov'] ) ){
			$new_address = $new_address .  $row['new_add_prov'] . ',';
		}
		if( !empty( $row['new_add_code'] ) ){
			$new_address = $new_address .  $row['new_add_code'] . ',';
		}
		$new_address = substr($new_address,0, strlen($new_address)-1);
        if($new_address == false){
            $new_address ='';
        }
	
    $data[] = array( 
        "id"                    => $row['id'],
        "idacct"                => $row['idacct'],
        "current_fname" 		=> $row['current_fname'],
        "new_fname" 			=> $row['new_fname'],
        "current_mname" 		=> $row['current_mname'],
        "new_mname" 			=> $row['new_mname'],
        "current_lname" 		=> $row['current_lname'],
        "new_lname" 			=> $row['new_lname'],
        "current_suffix" 		=> $row['current_suffix'],
        "new_suffix" 			=> $row['new_suffix'],
        "current_nickname" 		=> $row['current_nickname'],
        "new_nickname" 			=> $row['new_nickname'],
        "current_mari_stat" 	=> $row['current_mari_stat'],
        "new_mari_stat" 		=> $row['new_mari_stat'],
        "current_emer_name"     => $row['current_emer_name'],
        "new_emer_name"         => $row['new_emer_name'],
        "current_emer_cont"     => $row['current_emer_cont'],
        "new_emer_cont"         => $row['new_emer_cont'],
        "current_pnum"          => $row['current_pnum'],
        "new_pnum"              => $row['new_pnum'],
        "current_fax_num"       => $row['current_fax_num'],
        "new_fax_num"           => $row['new_fax_num'],
        "current_mnum"          => $row['current_mnum'],
        "new_mnum"              => $row['new_mnum'],
        "getdepts"              => getdep($con, $row['idacct']),
		"mari_stat"				=> getCivilStatus($con),
        "getdepts_new"          => getdepNew($con, $row['idacct'], $row['ref_num']),
		"suffex"				=> getSuffix($con),
        "new_addr"           	=> $new_address,
        "current_addr"       	=> $current_address,
		"current_add_st"        => $row['current_add_st'],
        "new_add_st"            => $row['new_add_st'],
        "current_add_area"      => $row['current_add_area'],
        "new_add_area"          => $row['new_add_area'],
        "current_add_city"      => $row['current_add_city'],
        "new_add_city"          => $row['new_add_city'],
        "current_add_prov"      => $row['current_add_prov'],
        "new_add_prov"          => $row['new_add_prov'],
        "current_add_code"      => $row['current_add_code'],
        "new_add_code"          => $row['new_add_code'],
    );
}



$return = json_encode($data);

print $return;
mysqli_close($con);


function getName($con, $accountid){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="empname";
    $Qry->fields="id='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
			return $row['empname'];
        }
    }
    return '';
}

function getdep($con, $accountid){
	$data = array();
    $Qry=new Query();
    $Qry->table="tblacctdependent";
    $Qry->selected="*";
    $Qry->fields="idacct='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

            //Get the current UNIX timestamp.
            $now = time();
            
            //Get the timestamp of the person's date of birth.
            $dob = strtotime( $row['birthday'] );
            
            //Calculate the difference between the two timestamps.
            $difference = $now - $dob;
            
            //There are 31556926 seconds in a year.
            $age = floor($difference / 31556926);



            $data[] = array( 
                "id"        => $row['id'],
                "name" 		=> $row['name'],
                "bdate"	    => $row['birthday'],
                "age"       =>$age				
            );
        }
    }
    return $data;
}

function getdepNew($con, $accountid, $ref){
    $Qry=new Query();
    $data=array();
    $Qry->table="tbldependent";
    $Qry->selected="*";
    $Qry->fields="idacct='".$accountid."' AND ref_num='".$ref."' AND status=3";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

        //Get the current UNIX timestamp.
		$now = time();
		 
		//Get the timestamp of the person's date of birth.
		$dob = strtotime( $row['birthday'] );
		 
		//Calculate the difference between the two timestamps.
		$difference = $now - $dob;
		 
		//There are 31556926 seconds in a year.
		$age = floor($difference / 31556926);


            $data[] = array( 
                "id"        => $row['id'],
                "name" 		=> $row['name'],
                "bdate"	    => $row['birthday'],
                "age"       => $age				
            );
        }
    }
    return $data;
}

?>