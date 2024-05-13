<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblbonuses";
$Qry->selected = "description,docnum,period,totalmonthcovered,accountid,rule,unitamount,mode,exemption,remarks,start,end,applyto,releasedate,type";
$Qry->fields   = "'13th Month', '13th Month', '', '', '0', '', '0', '', '', '',
                '".$param->data->start."',
                  '".$param->data->end."',
                  '".$param->data->paygroup."',
                  '".$param->data->pds."',
                    '13'
                ";   

$Qry->exe_INSERT($con);
$bonusid = mysqli_insert_id($con);
echo mysqli_error($con);
if($bonusid){
    if($param->data->paygroup == 'all'){
        $Qry1 = new Query();	
        $Qry1->table     = "tblpayreg";
        $Qry1->selected  = "idacct,(SUM(salary) / 12) * (COUNT(idpayperiod)/2)
                             AS amount";
        $Qry1->fields    = "idpayperiod 
                            IN (SELECT id
                                FROM `tblpayperiod`
                                WHERE period_start BETWEEN '".$param->data->start."' AND '".$param->data->end."' 
                                AND period_end BETWEEN '".$param->data->start."' AND '".$param->data->end."'
                            ) GROUP BY idacct";
        $rs             = $Qry1->exe_SELECT($con);
        
        if(mysqli_num_rows($rs)>= 1){
            while($row=mysqli_fetch_assoc($rs)){
                $row['amount'] = $row['amount'] - retro($con,$row['idacct'],$param);

                $Qry2           = new Query();
                $Qry2->table    = "tblbonusesdetails";
                $Qry2->selected = "bonusid,idacct,amount,taxable,nontaxable";
                $Qry2->fields   = "'".$bonusid."',
                                    '".$row['idacct']."',
                                    '".$row['amount']."',
                                    '0',
                                    '".$row['amount']."'
                                "; 
                $checke = $Qry2->exe_INSERT($con);
            }
        }
   }else{
        $checke = '';  
   }
}

if($checke){
    $return = json_encode(array("status"=>"success","data"=>dataReturn($con,$bonusid)));
}else{
    $return = json_encode(array("status"=>"error"));
}
                 
print $return;
mysqli_close($con);

function retro($con,$idacct,$param){
    $amount = 0;
    $Qry             = new Query();	
    $Qry->table      = "( SELECT MIN(work_date) AS 'work_date',salary,daysmonth FROM vw_timesheetfinal WHERE tid = '".$idacct."') AS tmp";
    $Qry->selected   = "(CASE
                            WHEN DATE_FORMAT(work_date,'%d') BETWEEN DATE_FORMAT('2021-01-01','%d') AND DATE_FORMAT('2021-01-15','%d') THEN ((DATE_FORMAT(work_date,'%d') - DATE_FORMAT('2021-01-01','%d')) * (salary/daysmonth) /12)
                            ELSE ((DATE_FORMAT(work_date,'%d') - DATE_FORMAT('2021-01-15','%d')) * (salary/daysmonth) /12)
                        END) AS amount";
    $Qry->fields     = "work_date BETWEEN '".$param->data->start."' AND '".$param->data->end."'";
    $rs              = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $amount = $row['amount'];
        }
    }
    
    return $amount;
}


function dataReturn($con,$bonusid){
    $Qry             = new Query();	
    $Qry->table      = "`tblbonusesdetails` as b LEFT JOIN tblaccount as a ON b.idacct = a.id";
    $Qry->selected   = " b.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`,a.empid";
    $Qry->fields     = "bonusid = '".$bonusid."' ORDER BY empname";
    $rs              = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $data[] = $row;
        }
    }
    
    return $data;
}
?>