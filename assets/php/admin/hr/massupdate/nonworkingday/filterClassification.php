<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $return = null;

    if(checkPayPeriodStat($con, $param->sdate)){
        $return = json_encode(array('status'=>'invalid'));
        print $return;
        mysqli_close($con);
        return;
    }

    $shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brkin,mon_brkout", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brkin,tue_brkout",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brkin,wed_brkout",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brkin,thu_brkout",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brkin,fri_brkout",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brkin,sat_brkout", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brkin,sun_brkout");

    if(!empty($param->accountid)){

        // if(checkIfValid($con,$param->edate)){

            //Search Department
            if( !empty( $param->classif ) ){
                $arr_id = array();
                $arr 	= getHierarchy($con,$param->classif);
                array_push( $arr_id, $param->classif );
                if( !empty( $arr["nodechild"] ) ){
                    $a = getChildNode($arr_id, $arr["nodechild"]);
                    if( !empty($a) ){
                        foreach( $a as $v ){
                            array_push( $arr_id, $v );
                        }
                    }
                }
                if( count($arr_id) == 1 ){
                    $ids 			= $arr_id[0];
                }else{
                    $ids 			= implode(",",$arr_id);
                }
            }

            $data			= array();
            $Qry 			= new Query();	
            $Qry->table     = "vw_data_timesheet AS a LEFT JOIN vw_dataemployees AS b ON a.empID = b.id";
            $Qry->selected  = "a.idacct,a.empID,b.lname,b.fname,b.mname,b.idunit,b.business_unit,a.idshift,a.shift_status,a.work_date";
            if($param->classif=='all'){
                $Qry->fields    = "(a.work_date BETWEEN '".$param->sdate."' AND '".$param->edate."') AND (idshift!=4 OR idshift IS NULL) ORDER BY CONCAT(a.work_date,b.lname) ASC ";
            }else{
                $Qry->fields    = "(a.work_date BETWEEN '".$param->sdate."' AND '".$param->edate."') AND (idshift!=4 OR idshift IS NULL) AND b.idunit in (".$ids.") ORDER BY CONCAT(a.work_date,b.lname) ASC ";
            }
            $rs 			= $Qry->exe_SELECT($con);
            if(mysqli_num_rows($rs)>= 1){
                while($row=mysqli_fetch_array($rs)){
					$shift_info 			=array();
                    if( empty($row['shift_status']) ){
                        $shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
						$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
						$row['idshift']			= $shift_info[0];
                        $row['shift_status']	= $shift_info[1];
                    }
					
                    if($row['shift_status']!='Rest Day'){
                        $data[] = array(
                            "idacct"                =>  $row['idacct'] ? $row['idacct'] : $row['empID'],
                            "id"                    =>  $row['empID'],
                            "lname"		            =>	$row['lname'],
                            "fname"		            =>  $row['fname'],
                            "mname"		            =>  substr($row['mname'], 0, 1),
                            "id_unit"               =>  $row['idunit'],
                            "business_unit"		    =>  $row['business_unit'],
                            "idshift"		        =>  $row['idshift'],
                            "shiftstatus"		    =>  $row['shift_status'],
                            "startdate"		        =>  $row['work_date'],
                            "enddate"		        =>  $row['work_date'],
                        );
                    }
                    
                }
            }else{
                $return = json_encode($data);
            }
            
            $return = json_encode($data);
        // }else{
        //     $return = json_encode(array('status'=>'invalid'));
        // }

    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);

function checkIfValid($con, $edate){
    $Qry=new Query();
    $Qry->table="vw_data_timesheet";
    $Qry->selected="id";
    $Qry->fields="work_date='".$edate."' AND idshift IS NOT NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return true;
        }
    }
    return false;
}

function checkPayPeriodStat($con, $date){
    $Qry=new Query();
    $Qry->table="tblpayperiod";
    $Qry->selected="*";
    $Qry->fields="stat=0 LIMIT 1";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            if($date<$row['period_start']){
                return true;
            }
        }
    }
    return false;
}


?>