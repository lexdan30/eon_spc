<?php 
 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
    $con = $conn->connect();
	// $param = $_GET;
	$param = json_decode(file_get_contents('php://input'));
	$date=SysDate();

	$search='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    if( !empty( $param->emplyract ) ){ $search=$search." AND commendation_employeraction like 	'%".$param->emplyract."%' "; }
    
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

	//$name23 = array();
	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 AND commendation_employeraction is not null AND commendation_incentive is not null ".$search;
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			$commends = getCommends($con,$row['id']);

			$str = '';
			$str1 = '';
			$ctr = 1;

			if($commends){
				foreach($commends as $val){
					$str=$str . $ctr. ". " . $val['employeraction']."\n";
					$str1=$str1 . $ctr. ". " . $val['incentive']."\n";
					$ctr++;
				}
			}

			$data[] = array(
							"empid"			        => $row['empid'],
							"empname" 		        => $row['empname'],
							"employer_action"       => $str,
							"incentive"             => $str1
                    );
		}
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
	}

    function getCommends($con, $idacct){
        $Qry=new Query();
        $Qry->table="tblaccountcom";
        $Qry->selected="*";
        $Qry->fields="id>0 AND idacct='".$idacct."'";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_array($rs)){
                $data[] = array(
                    'employeraction'    =>$row['employeraction'],
                    'incentive'	        =>$row['incentive']
                );
            }
            return $data;
        }
        return null;
    }
	
print $return;	
mysqli_close($con);


?>