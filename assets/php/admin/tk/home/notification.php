<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 
require_once('../../../email/emailFunction.php');

$param = json_decode(file_get_contents('php://input'));
$date = date('Y-m-d');
//$date = '2020-01-10';
$data = array();
$endcontract_notif = array();
$high_temp = array();
$Qry = new Query();	
$Qry->table     = "tblforms01";
$Qry->selected  = "empid, empname, empactiontaken";
$Qry->fields    = "idstatus = 3";

$rs = $Qry->exe_SELECT($con);

while($row=mysqli_fetch_array($rs)){
    $data[] = array( 
        "id" 			    => $row['empid'],
        "name" 			    => $row['empname'],
        "empactiontaken" 	=> 'Pending ' . $row['empactiontaken'], 
    );
}

$Qry1 = new Query();	
$Qry1->table     = "tblforms02";
$Qry1->selected  = "empid, empname, empactiontaken";
$Qry1->fields    = "idstatus = 3";

$rs1 = $Qry1->exe_SELECT($con);

while($row=mysqli_fetch_array($rs1)){
    $data[] = array( 
        "id" 			    => $row['empid'],
        "name" 			    => $row['empname'],
        "empactiontaken" 	=> 'Pending ' . $row['empactiontaken'],
    );
}

$Qry2 = new Query();	
$Qry2->table     = "tblforms03";
$Qry2->selected  = "empid, empname, empactiontaken";
$Qry2->fields    = "idstatus = 3";

$rs2 = $Qry2->exe_SELECT($con);

while($row=mysqli_fetch_array($rs2)){
    $data[] = array( 
        "id" 			    => $row['empid'],
        "name" 			    => $row['empname'],
        "empactiontaken" 	=> 'Pending ' . $row['empactiontaken'],
    );
}


$Qry7 = new Query();	
$Qry7->table     = "vw_dataemployees vd LEFT JOIN vw_data_timesheet vt ON vt.idacct = vd.id";
$Qry7->selected  = "vd.empname,vd.empid,vt.temp";
$Qry7->fields    = "vt.work_date = CURDATE() ";
$rs7 = $Qry7->exe_SELECT($con);

while($row=mysqli_fetch_array($rs7)){
    if($row['temp'] > 37.9){
        $data[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "empactiontaken" 	=> '<p style="color : red">High Temperature : ' . $row['temp'] .  ' &#8451;</p>',
        );
        $high_temp[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "empactiontaken" 	=> '<p style="color : red">High Temperature : ' . $row['temp'] .  ' &#8451;</p>',
        );
    }
}

$Qry8 = new Query();	
$Qry8->table     = "tblaccountjob AS a LEFT JOIN tblaccount AS b ON b.id = a.idacct";
$Qry8->selected  = "b.id, a.empstat, b.empid, CONCAT(b.lname,', ',b.fname,' ',SUBSTR(`b`.`mname`,1,1),'. ') AS empname, a.hdate, a.contract_fdate, DATE_SUB(DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY, INTERVAL 45 DAY) AS startdate, DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY AS regdate";
$Qry8->fields    = "'".$date."' BETWEEN DATE_SUB(DATE_ADD(hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY, INTERVAL 45 DAY) AND DATE_ADD(hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY ";
$rs8 = $Qry8->exe_SELECT($con);

while($row=mysqli_fetch_array($rs8)){
    if($row['empstat'] != '5' && $row['empstat'] != '4' ){
        $data[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "empactiontaken" 	=> 'Contract is about to end.',
        );
        $endcontract_notif[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'], 
            "empactiontaken" 	=> 'Contract is about to end.',
        );
    }
}

$Qry9 = new Query();	
$Qry9->table     = "tblaccountjob AS a LEFT JOIN tblaccount AS b ON b.id = a.idacct";
$Qry9->selected  = "b.id, b.empid, CONCAT(b.lname,', ',b.fname,' ',SUBSTR(`b`.`mname`,1,1),'. ') AS empname,a.empstat, a.hdate, a.contract_fdate, DATE_SUB(DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY, INTERVAL 45 DAY) AS startdate, DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY AS regdate";
// $Qry9->fields    = "'".$date."' BETWEEN DATE_SUB(DATE_ADD(hdate, INTERVAL 5 MONTH) - INTERVAL 1 DAY, INTERVAL 45 DAY) AND DATE_ADD(hdate, INTERVAL 5 MONTH) - INTERVAL 1 DAY AND empstat = 8";
$Qry9->fields    = "'".$date."' BETWEEN DATE_ADD(a.contract_fdate,INTERVAL -45 DAY) AND DATE_ADD(a.contract_fdate,INTERVAL 1 DAY) AND a.contract_fdate IS NOT NULL";
$rs9 = $Qry9->exe_SELECT($con);

while($row=mysqli_fetch_array($rs9)){
    if($row['empstat'] != '5'){
        $data[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "empactiontaken" 	=> 'Project based contract is about to end.',
        );
        $endcontract_notif[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "empactiontaken" 	=> 'Contract is about to end.',
        );
    }
}

if(!empty($endcontract_notif)){
    $email = '';
    $Qry12 = new Query();	
    $Qry12->table ="tblpreference";	
    $Qry12->selected ="value";
    $Qry12->fields ="alias='D-APPR'"; // Administrative Assistant
    $rs12 = $Qry12->exe_SELECT($con);
    if(mysqli_num_rows($rs12)>= 1){
        if($row12=mysqli_fetch_array($rs12)){
            $posi = $row12['value']; 
            //================================
            $Qry13 = new Query();	
            $Qry13->table ="vw_dataemployees";	
            $Qry13->selected ="id"; 
            $Qry13->fields ="post='".$posi."'"; 
            $rs13 = $Qry13->exe_SELECT($con);
            if(mysqli_num_rows($rs13)>= 1){
                if($row13=mysqli_fetch_array($rs13)){
                    $sender = getEmail($con,$row13['id']); // get the Administrative Assistant id
                    if(!empty($sender)){
                        $email = $sender;
                    }else{
                        $email = 'magnolia@kajima.com.ph'; // set by default incase no Admin found 
                    }
                }}
            //================================
        }
    }
    
    foreach($endcontract_notif as $key => $val){
        if(emailEndofContract($con,$date,$val['id'])){
            $mailSubject = "Kajima HRIS 2.0 - End of Contract";
            $mailBody = "<h4>Employees - End of Contract</h4>";
            $mailBody .= "Employee: ".$val['name'];
            $mailBody .= "<br />Remarks: ".$val['empactiontaken'];
    
            $mailBody .="<br /><br />This is a system generated notification.<br />";
            $return = _EMAILDIRECT_ENDCONTRACT($email,$mailSubject, $mailBody,$val['id']);
            $return2 = _EMAILDIRECT_ENDCONTRACT('jahllyza@kajima.com.ph',$mailSubject, $mailBody,$val['id']); //add on request - no more modification
        }
    }
}
// Administrative Assistant Only
// if(!empty($high_temp)){
//     $emailtemp = '';
//     $Qry12temp = new Query();	 
//     $Qry12temp->table ="tblpreference";	
//     $Qry12temp->selected ="value";
//     $Qry12temp->fields ="alias='D-APPR'"; 
//     $rs12temp = $Qry12temp->exe_SELECT($con);
//     if(mysqli_num_rows($rs12temp)>= 1){
//         if($row12temp=mysqli_fetch_array($rs12temp)){
//             $positemp = $row12temp['value'];  
//             $Qry13temp = new Query();	
//             $Qry13temp->table ="vw_dataemployees";	
//             $Qry13temp->selected ="id"; 
//             $Qry13temp->fields ="post='".$positemp."'"; 
//             $rs13temp = $Qry13temp->exe_SELECT($con);
//             if(mysqli_num_rows($rs13temp)>= 1){
//                 if($row13temp=mysqli_fetch_array($rs13temp)){
//                     $sendertemp = getEmail($con,$row13temp['id']); 
//                     if(!empty($sendertemp)){
//                         $emailtemp = $sendertemp;
//                     }else{
//                         $emailtemp = 'magnolia@kajima.com.ph'; 
//                     }
//                 }}
//         }
//     }
    
//     foreach($high_temp as $key => $val){
//         if(emailHighTemp($con,$date,$val['id'])){
//             $mailSubjecttemp = "Kajima HRIS 2.0 - High Temperature";
//             $mailBodytemp = "<h4>Employees - High Temperature</h4>";
//             $mailBodytemp .= "Employee: ".$val['name'];
//             $mailBodytemp .= "<br />Remarks: ".$val['empactiontaken'];
    
//             $mailBodytemp .="<br /><br />This is a system generated notification.<br />";
//             $return = _EMAILDIRECT_TEMP($emailtemp,$mailSubjecttemp, $mailBodytemp,$val['id']);
//         }
//     }
// }

// Email Receipients
if(!empty($high_temp)){
    $emailtemp = '';
    $Qry12temp = new Query();	 
    $Qry12temp->table ="tblpreference";	
    $Qry12temp->selected ="value";
    $Qry12temp->fields ="alias='HTEMP-ER'"; 
    $rs12temp = $Qry12temp->exe_SELECT($con);
    if(mysqli_num_rows($rs12temp)>= 1){
        if($row12temp=mysqli_fetch_array($rs12temp)){
            $emailtemp = getEmails( $con,$row12temp['value']);
        }
    }
    
    foreach($high_temp as $key => $val){
        if(emailHighTemp($con,$date,$val['id'])){
            $mailSubjecttemp = "Kajima HRIS 2.0 - High Temperature";
            $mailBodytemp = "<h4>Employees - High Temperature</h4>";
            $mailBodytemp .= "Employee: ".$val['name'];
            $mailBodytemp .= "<br />Remarks: ".$val['empactiontaken'];
    
            $mailBodytemp .="<br /><br />This is a system generated notification.<br />";
            $return = _EMAILDIRECT_TEMPTOMANY($emailtemp,$mailSubjecttemp, $mailBodytemp,$val['id']);
        }
    }
}

$return = json_encode($data);

print $return;
mysqli_close($con);

function getEmail($con,$idacct){
    $Qry = new Query();	
    $Qry->table ="tblaccount";	
    $Qry->selected ="email";
    $Qry->fields ="id='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['email'];
        }
    }
    return '';
}

function emailHighTemp($con,$date,$id){
    $Qry = new Query();	
    $Qry->table ="tblemailer";	
    $Qry->selected ="*";
    $Qry->fields ="date='".$date."' AND high_temp='1' AND name='HIGH TEMP' AND idacct='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return false;
        }
    }else{
        $Qry2           = new Query();
        $Qry2->table    = "tblemailer";
        $Qry2->selected = "date,name,high_temp,idacct";
        $Qry2->fields   = "'".$date."', 'HIGH TEMP', '1','".$id."'";                        
        $rs2 = $Qry2->exe_INSERT($con);
    }
    return true;
}


function emailEndofContract($con,$date,$id){
    $Qry = new Query();	
    $Qry->table ="tblemailer";	
    $Qry->selected ="*";
    $Qry->fields ="idacct='".$id."' AND end_contract='1' AND name='END CONTRACT'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return false;
        }
    }else{
        $Qry2           = new Query();
        $Qry2->table    = "tblemailer";
        $Qry2->selected = "date,name,end_contract,idacct";
        $Qry2->fields   = "'".$date."', 'END CONTRACT', '1','".$id."'";                        
        $rs2 = $Qry2->exe_INSERT($con);
    }
    return true;
}

function getEmails($con,$idacct){
	$data = array();
    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "email";
	$Qry->fields    = "id IN (".$idacct.")";
	  $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
			$data[] = array(
				"email"		=> $row['email']
			);
		}
		return $data;
    }
    return '';
}

?>