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

        if($row['salutation'] == 'Mr.'){
            $genderadj  = 'His';
            $genderpronoun  = 'He';
            $genderadjsmall =  'his';
            $genderpronounsmall = 'he';
        }else{
            $genderadj  = 'Her';
            $genderpronoun  = 'She';
            $genderadjsmall =  'her';
            $genderpronounsmall = 'she';
        }


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

            "dept"	    => $row['department'],
            "idacct"            => $row['idacct'],
            "salutation"	    => $row['salutation'],
            "genderadj"	    => $genderadj,
            "genderpronoun"	    => $genderpronoun,
            "genderadjsmall"	    => $genderadjsmall,
            "genderpronounsmall"	    => $genderpronounsmall,
			"firstname"	    => $row['firstname'],
            "lastname"	    => $row['lastname'],
            "middlename"	=> $row['middlename'],
            "middleinitial" => $row['middleinitial'],
            "suffix"	    => $row['suffix'],
            "SheorHe"	    => $row['HeorShe'],
            "annualpay"	    => $row['annualpay'],
            "hireddate"	    => $row['hireddate'],
            "position"	    => $row['jobposition'],
            "salary"	    => $row['salary'],
            "salaryNum"	    => 'Php'.number_format($row['salary'], 2, '.', ',').'',
            "allowance"	    => getSumAllowance($con,$row['idacct']),
            "allowanceNum"	=> 'Php'.number_format(getSumAllowance($con,$row['idacct']), 2, '.', ',').'',
            "depid"	        => $row['depid'],
            "company"	    => $row['company']
        );
    }
    $return = json_encode($data);
}

print $return;
mysqli_close($con);

function getSumAllowance( $con, $idacct ){
	$Qry=new Query();
	$Qry->table="tblacctallowance LEFT JOIN tblallowance ON tblacctallowance.idallowance = tblallowance.id";
	$Qry->selected="SUM(tblacctallowance.amt) AS tot";
	$Qry->fields="idacct='".$idacct."'";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>0){
		if($row=mysqli_fetch_array($rs)){
			return floatval($row['tot']);
		}
	}
	return '0.00';
}


?>