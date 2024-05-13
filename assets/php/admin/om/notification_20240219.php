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
$Qry->fields    = "id='".$param->info->id."' AND DATE_ADD(NOW(),INTERVAL -notif_graceperiod MINUTE) >= email_datetime AND email_datetime IS NOT NULL";
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

        if($param->auto_status == 'warn'){ 
            $mailSubject = "NXPERT EON SPC - WARNING";
            $mailBody = "<h4>NG Control Line Alert</h4>";
            $mailBody .= "Equipment: ".$row['machine_name'];
            $mailBody .= "<br />Remarks: It has reached the control line limit for NG value.";
            $mailBody .= "<br /><br /><span style='color: green'>The equipment is still on going a production. You may turn on the automatic turn off if you don't want the machine to continue on unattended process.</span>";
            $mailBody .= "<br /><br />See captured details below.";
            
            $mailBody .= "<br/><br/>
            <table style='border: 1px solid #000000;text-align: center'>
            <thead >
            <tr>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Utilization</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Run Time</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Abnormal Stop Count</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Abnormal Stop Time</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Progress Percent</th> 
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Production Count</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Longest Downtime</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Error</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Running</th> 
            </tr>
            </thead>
            <tbody >
            <tr>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->utilization."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->run_time."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->abnormal_stop_count."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->abnormal_stop_time."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->progress_percent."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->production_count."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->longest_downtime."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".(($param->info->error)? 'true':'false')."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".(($param->info->running)? 'true':'false')."</td> 
            </tr>
            </tbody>
            </table> ";

            $mailBody .= "<br/><br/>
            <table style='border: 1px solid #000000;text-align: center'>
            <thead >
            <tr>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Target</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Finished Goods</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>No Good</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Machines Controlline</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Auto Off</th>
            </tr>
            </thead>
            <tbody >
            <tr> 
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->target."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->finished_goods."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->no_good."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->machines_controlline."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".(($param->info->auto_off) ? 'true':'false')."</td>
            </tr>
            </tbody>
            </table> ";  

            $mailBody .="<br /><br />This is a system generated notification.<br />"; 
            $return = _EMAILDIRECT_CONTROLLINE('atcuraraton@n-pax.com',$mailSubject, $mailBody,$param->accountid); 
            //$return2 = _EMAILDIRECT_CONTROLLINE('meabucay@n-pax.com',$mailSubject, $mailBody, $param->accountid);
            //$return3 = _EMAILDIRECT_CONTROLLINE('rmmuana@n-pax.com',$mailSubject, $mailBody, $param->accountid);
            //$return4 = _EMAILDIRECT_CONTROLLINE('arbalayo@n-pax.com',$mailSubject, $mailBody, $param->accountid);  
        }else{ 
            $mailSubject = "NXPERT EON SPC - WARNING";
            $mailBody = "<h4>NG Control Line Alert</h4>";
            $mailBody .= "Equipment: ".$row['machine_name'];
            $mailBody .= "<br />Remarks: It has reached the control line limit for NG value.";
            $mailBody .= "<br /><br /><span style='color: red'>Auto shutdown has been triggered for this equipment.</span>";
            $mailBody .= "<br /><br />See captured details below.";
            
            $mailBody .= "<br/><br/>
            <table style='border: 1px solid #000000;text-align: center'>
            <thead >
            <tr>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Utilization</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Run Time</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Abnormal Stop Count</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Abnormal Stop Time</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Progress Percent</th> 
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Production Count</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Longest Downtime</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Error</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Running</th> 
            </tr>
            </thead>
            <tbody >
            <tr>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->utilization."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->run_time."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->abnormal_stop_count."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->abnormal_stop_time."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->progress_percent."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->production_count."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->longest_downtime."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".(($param->info->error)? 'true':'false')."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".(($param->info->running)? 'true':'false')."</td> 
            </tr>
            </tbody>
            </table> ";

            $mailBody .= "<br/><br/>
            <table style='border: 1px solid #000000;text-align: center'>
            <thead >
            <tr>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Target</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Finished Goods</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>No Good</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Machines Controlline</th>
                <th style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>Auto Off</th>
            </tr>
            </thead>
            <tbody >
            <tr> 
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->target."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->finished_goods."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->no_good."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".$param->info->machines_controlline."</td>
                <td style='border: 1px solid #000000;border-collapse: collapse;text-align: center'>".(($param->info->auto_off) ? 'true':'false')."</td>
            </tr>
            </tbody>
            </table> ";  

            $mailBody .="<br /><br />This is a system generated notification.<br />"; 
            $return = _EMAILDIRECT_CONTROLLINE('atcuraraton@n-pax.com',$mailSubject, $mailBody,$param->accountid); 
            //$return2 = _EMAILDIRECT_CONTROLLINE('meabucay@n-pax.com',$mailSubject, $mailBody, $param->accountid);
            //$return3 = _EMAILDIRECT_CONTROLLINE('rmmuana@n-pax.com',$mailSubject, $mailBody, $param->accountid);
            //$return4 = _EMAILDIRECT_CONTROLLINE('arbalayo@n-pax.com',$mailSubject, $mailBody, $param->accountid); 
                
       }
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