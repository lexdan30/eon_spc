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
    if( !empty( $param->examtaken ) ){ $search=$search." AND examtaken like 	'%".$param->examtaken."%' "; }
    
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
	$Qry->fields    = "id>0 AND examtaken is not null ".$search;
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			$exams = getExamTaken($con,$row['id']);

			$str = '';
			$str1 = '';
			$str2 = '';
			$ctr = 1;

			if($exams){
				foreach($exams as $val){
					$str=$str . $ctr . ". " . $val['exam_taken']."\n";
					$str1=$str1 . $ctr . ". " . $val['location']."\n";
					$str2=$str2 . $ctr . ". " . $val['date']."\n";
					$ctr++;
				}
			}

			$data[] = array(
							"empid"			        => $row['empid'],
							"empname" 		        => $row['empname'],
							"exam_taken"            => $str,
							"location"              => $str1,
							"date"                 	=> $str2
                    );
		}
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
	}

	function getExamTaken($con, $idacct){
        $Qry=new Query();
        $Qry->table="tblaccountet";
        $Qry->selected="*";
        $Qry->fields="id>0 AND idacct='".$idacct."' AND type='exam'";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_array($rs)){
                $data[] = array(
                    'exam_taken'    =>$row['et'],
                    'location'	    =>$row['location'],
                    'date'	        =>$row['date']
                );
            }
            return $data;
        }
        return null;
	}
	
print $return;	
mysqli_close($con);


?>