<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$date  = SysDateDan();
$time  = SysTime();

    $search ='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    if( !empty( $param->move_type ) && $param->move_type=='1' ){ $search=$search." AND approved_lateral_transfer_ctr > 0 "; }
    if( !empty( $param->move_type ) && $param->move_type=='2' ){ $search=$search." AND approved_wage_increase_ctr > 0 "; }
    if( !empty( $param->move_type ) && $param->move_type=='3' ){ $search=$search." AND approved_promotion_ctr > 0 "; }
    
    //Search Department
    if( !empty( $param->department ) ){
        $arr_id = array();
        $arr 	= getHierarchy($con,$param->department);
        array_push( $arr_id, $param->department );
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
        $search.=" AND idunit in (".$ids.") "; 
    }

    $search.=" ORDER BY empname ASC";

    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "*";
    $Qry->fields    = "id > 0 AND (approved_lateral_transfer_ctr > 0 OR approved_wage_increase_ctr > 0 OR approved_promotion_ctr > 0) ".$search;
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
    
            $movement       = getData($con,$row['id']);

            $data[] = array( 
                "id"        	        => $row['id'],
                "empid"			        => $row['empid'],
                "empname" 		        => $row['empname'],
                "movement"              => $movement,
                "date"                  => $date,
                "time"                  => date ("H:i:s A",strtotime($time))
            );
            $return = json_encode($data);
        }
    }else{
        $return = json_encode(array('status'=>'error'));
    }

    function getData($con, $idacct){
        $Qry=new Query();
        $Qry->table="vw_personnelaction_forms";
        $Qry->selected="*";
        $Qry->fields="requestor='".$idacct."' AND idstatus='1' ORDER BY effectivedate ASC";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_array($rs)){
                $data[] = array(
                    'currentdeptname'               =>$row['currentdeptname'],
                    'newdeptname'                   =>$row['newdeptname'],
                    'currentdeptmanager'            =>$row['currentdeptmanager'],
                    'newdeptmanager'                =>$row['newdeptmanager'],
                    'currentimmediatesupervisor'    =>$row['currentimmediatesupervisor'],
                    'newimmediatesupervisor'        =>$row['newimmediatesupervisor'],
                    'currentsection'                =>$row['currentsection'],
                    'newsection'                    =>$row['newsection'],
                    'currentempstatus'              =>$row['currentempstatus'],
                    'newempstatus'                  =>$row['newempstatus'],
                    'currentjobcode'                =>$row['currentjobcode'],
                    'newjobcode'                    =>$row['newjobcode'],
                    'currentjoblevel'               =>$row['currentjoblevel'],
                    'newjoblevel'                   =>$row['newjoblevel'],
                    'currentpositiontitle'          =>$row['currentpositiontitle'],
                    'newpositiontitle'              =>$row['newpositiontitle'],
                    'currentpaygroup'               =>$row['currentpaygroup'],
                    'newpaygroup'                   =>$row['newpaygroup'],
                    'currentlabortype'              =>$row['currentlabortype'],
                    'newlabortype'                  =>$row['newlabortype'],
                    'currentbasepay'               =>$row['currentbasepay'],
                    'newbasepay'                   =>$row['newbasepay'],
                    'currentriceallowance'         =>$row['currentriceallowance'],
                    'newriceallowance'             =>$row['newriceallowance'],
                    'currentclothingallowance'     =>$row['currentclothingallowance'],
                    'newclothingallowance'         =>$row['newclothingallowance'],
                    'currentlaundryallowance'      =>$row['currentlaundryallowance'],
                    'newlaundryallowance'          =>$row['newlaundryallowance'],
                    'currenttotalcashcomp'         =>$row['currenttotalcashcomp'],
                    'newtotalcashcomp'             =>$row['newtotalcashcomp'],
                    'effectivedate'                 =>$row['effectivedate'],
                    'actiontaken'                   =>$row['empactiontaken']
                );
            }
            return $data;
        }
        return null;
    }

$return = json_encode($data);
print $return;
mysqli_close($con);
?>