<?php
require_once('../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../classPhp.php'); 
require_once('../../email/emailFunction.php');

$param = json_decode(file_get_contents('php://input'));
$date = date('Y-m-d');

$data = array();
$endcontract_notif = array();
$high_temp = array();

$Qry = new Query();	
$Qry->table     = "tblmachine_master";
$Qry->selected  = "*";
$Qry->fields    = "DATE_ADD(NOW(),INTERVAL -10 MINUTE) >= email_datetime AND email_datetime IS NOT NULL";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data = array( 
            "id" 			=> $row['id'],
            "machine_code"  => $row['machine_code'],
            "machine_name" 	=> $row['machine_name'], 
            "description" 	=> $row['description'], 
            "locator_code" 	=> $row['locator_code'], 
            "location" 	    => $row['location'], 
            "auto_off" 	    => $row['auto_off'], 
            "stats" 	    => $row['stats'], 
            "email_count" 	=> $row['email_count'],
            "email_datetime" 	=> $row['email_datetime']
        );
        update_machine_report($con,$row['id']);

        $mailSubject = "NXPERT EON SPC - WARNING";
        $mailBody = "<h4>NG Control Line Alert</h4>";
        $mailBody .= "Equipment: ".$row['machine_name'];
        $mailBody .= "<br />Remarks: It has reached the control line limit for NG value.";
        $mailBody .= "<br /><br />Auto shutdown has been triggered for this equipment.";
        $mailBody .= "<br /><br />See captured details below.";
        
        $mailBody .= "<br/><br/>
        <table style='border: 1px solid #000000;align-text: center'>
        <thead>
        <tr>
            <th>utilization</th>
            <th>run_time</th>
            <th>abnormal_stop_count</th>
            <th>abnormal_stop_time</th>
            <th>progress_percent</th> 
            <th>production_count</th>
            <th>longest_downtime</th>
            <th>target</th>
            <th>finished_goods</th>
            <th>no_good</th>
            <th>error</th>
            <th>running</th>
            <th>machines_controlline</th>
            <th>auto_off</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>".$param->info->utilization."</td>
            <td>".$param->info->run_time."</td>
            <td>".$param->info->abnormal_stop_count."</td>
            <td>".$param->info->abnormal_stop_time."</td>
            <td>".$param->info->progress_percent."</td>
            <td>".$param->info->production_count."</td>
            <td>".$param->info->longest_downtime."</td>
            <td>".$param->info->target."</td>
            <td>".$param->info->finished_goods."</td>
            <td>".$param->info->no_good."</td>
            <td>".$param->info->error."</td>
            <td>".$param->info->running."</td>
            <td>".$param->info->machines_controlline."</td>
            <td>".$param->info->auto_off."</td>
        </tr>
        </tbody>
        </table> ";

        $mailBody .= "<br/><br/>
        <table style='border: 1px solid #000000;align-text: center'>
        <thead>
        <tr style='border: 1px solid #000000;align-text: center'>
            <th>target</th>
            <th>finished_goods</th>
            <th>no_good</th>
            <th>machines_controlline</th>
            <th>auto_off</th>
        </tr>
        </thead>
        <tbody>
        <tr style='border: 1px solid #000000;align-text: center'> 
            <td>".$param->info->target."</td>
            <td>".$param->info->finished_goods."</td>
            <td>".$param->info->no_good."</td>
            <td>".$param->info->machines_controlline."</td>
            <td>".$param->info->auto_off."</td>
        </tr>
        </tbody>
        </table> ";
        
        // $mailBody .= "<br /><br />utilization: ".$param->info->utilization;
        // $mailBody .= "<br />run_time: ".$param->info->run_time;
        // $mailBody .= "<br />abnormal_stop_count: ".$param->info->abnormal_stop_count;
        // $mailBody .= "<br />abnormal_stop_time: ".$param->info->abnormal_stop_time;
        // $mailBody .= "<br />progress_percent: ".$param->info->progress_percent;
        // $mailBody .= "<br />production_count: ".$param->info->production_count;
        // $mailBody .= "<br />longest_downtime: ".$param->info->longest_downtime;
        // $mailBody .= "<br />target: ".$param->info->target;
        // $mailBody .= "<br />finished_goods: ".$param->info->finished_goods;
        // $mailBody .= "<br />no_good: ".$param->info->no_good;
        // $mailBody .= "<br />error: ". (($param->info->error) ? "true": "false");
        // $mailBody .= "<br />running: ". (($param->info->running) ? "true": "false");
        // $mailBody .= "<br />machines_controlline: ".$param->info->machines_controlline;
        // $mailBody .= "<br />auto_off: ".$param->info->auto_off; 

        $mailBody .="<br /><br />This is a system generated notification.<br />"; 
        $return = _EMAILDIRECT_CONTROLLINE('atcuraraton@n-pax.com',$mailSubject, $mailBody,$param->accountid); 
        // $return2 = _EMAILDIRECT_CONTROLLINE('meabucay@n-pax.com',$mailSubject, $mailBody, $param->accountid);
        // $return3 = _EMAILDIRECT_CONTROLLINE('rmmuana@n-pax.com',$mailSubject, $mailBody, $param->accountid);
        // $return4 = _EMAILDIRECT_CONTROLLINE('arbalayo@n-pax.com',$mailSubject, $mailBody, $param->accountid); 
    }    
}

$myData = array('status' => 'success', 'result' => $data);
$return = json_encode($myData);

print $return;
mysqli_close($con);

function update_machine_report($con,$id){
    $Qry = new Query();	
    $Qry->table ="tblmachine_master";	
    $Qry->selected = "email_datetime = NOW(),email_count = email_count + 1";
    $Qry->fields = "id='".$id."'";                        
    $Qry->exe_UPDATE($con);
} 

?>