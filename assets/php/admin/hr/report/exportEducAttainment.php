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
    if( !empty( $param->attainment ) ){ $search=$search." AND attainment like 	'%".$param->attainment."%' "; }
    if( !empty( $param->school ) ){ $search=$search." AND school like 	'%".$param->school."%' "; }
    
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
	$Qry->fields    = "id>0 AND attainment is not null AND school is not null ".$search;
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			$educ = getEducAttainment($con,$row['id']);

            $str = '';
            $str1 = '';
            $str2 = '';
            $str3 = '';
            $ctr = 1;

			if($educ){
				foreach($educ as $val){
                    $str=$str . $ctr. ". " . $val['attainment']."\n";
                    $str1=$str1 . $ctr. ". " . $val['school']."\n";
                    $str2=$str2 . $ctr. ". " . $val['from']."\n";
                    $str3=$str3 . $ctr. ". " . $val['to']."\n";
                    $ctr++;
				}
			}

			$data[] = array(
							"empid"			        => $row['empid'],
							"empname" 		        => $row['empname'],
                            "attainment"            => $str,
                            "school"                => $str1,
                            "dfrom"                 => $str2,
                            "dto"                   => $str3
                    );
		}
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
	}

    function getEducAttainment($con, $idacct){
        $Qry=new Query();
        $Qry->table="tblaccountedubg";
        $Qry->selected="*";
        $Qry->fields="id>0 AND idacct='".$idacct."'";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_array($rs)){
                $data[] = array(
					'schooldetails' => '('.$row['attainment'].' - '.$row['school'].' From '.$row['dfrom'].' To '.$row['dto'].')',
                    'attainment'    =>$row['attainment'],
                    'school'	    =>$row['school'],
                    'from'	        =>$row['dfrom'],
                    'to'	        =>$row['dto']
                );
            }
            return $data;
        }
        return null;
    }
	
print $return;	
mysqli_close($con);


?>