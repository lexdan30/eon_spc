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

	//$name23 = array();
	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id > 0 AND (approved_lateral_transfer_ctr > 0 OR approved_wage_increase_ctr > 0 OR approved_promotion_ctr > 0) ".$search;
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			$movement       = getData($con,$row['id']);

            $str = '';
            $str1 = '';
            $str2 = '';
            $str3 = '';
            $str4 = '';
			$ctr = 1;

			if($movement){
				foreach($movement as $val){
                    if($val['actiontaken']=='Lateral Transfer'){
                        $str=$str . $val['actiontaken']."\n";
                        $str1=$str1 . $val['effectivedate']."\n";
                        if($val['currentdeptname']!=$val['newdeptname']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Department Name" ."\n";
                            $str3=$str3 . $val['currentdeptname'] ."\n";
                            $str4=$str4 . $val['newdeptname'] ."\n";
                        }
                        if($val['currentdeptmanager']!=$val['newdeptmanager']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Department Manager" ."\n";
                            $str3=$str3 . $val['currentdeptmanager'] ."\n";
                            $str4=$str4 . $val['newdeptmanager'] ."\n";
                        }
                        if($val['currentsection']!=$val['newsection']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Section" ."\n";
                            $str3=$str3 . $val['currentsection'] ."\n";
                            $str4=$str4 . $val['newsection'] ."\n";
                        }
                        if($val['currentimmediatesupervisor']!=$val['newimmediatesupervisor']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Department Manager" ."\n";
                            $str3=$str3 . $val['currentimmediatesupervisor'] ."\n";
                            $str4=$str4 . $val['newimmediatesupervisor'] ."\n";
                        }
                        if($val['currentjobcode']!=$val['newjobcode']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Job Code" ."\n";
                            $str3=$str3 . $val['currentjobcode'] ."\n";
                            $str4=$str4 . $val['newjobcode'] ."\n";
                        }
                        if($val['currentpositiontitle']!=$val['newpositiontitle']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Position Title" ."\n";
                            $str3=$str3 . $val['currentpositiontitle'] ."\n";
                            $str4=$str4 . $val['newpositiontitle'] ."\n";
                        }
                        if($val['currentjoblevel']!=$val['newjoblevel']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Job Level" ."\n";
                            $str3=$str3 . $val['currentjoblevel'] ."\n";
                            $str4=$str4 . $val['newjoblevel'] ."\n";
                        }
                        if($val['currentempstatus']!=$val['newempstatus']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Employment Status" ."\n";
                            $str3=$str3 . $val['currentempstatus'] ."\n";
                            $str4=$str4 . $val['newempstatus'] ."\n";
                        }
                        if($val['currentpaygroup']!=$val['newpaygroup']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Pay Group" ."\n";
                            $str3=$str3 . $val['currentpaygroup'] ."\n";
                            $str4=$str4 . $val['newpaygroup'] ."\n";
                        }
                        if($val['currentlabortype']!=$val['newlabortype']){
                            $str2=$str2 . "Labor Type" ."\n";
                            $str3=$str3 . $val['currentlabortype'] ."\n";
                            $str4=$str4 . $val['newlabortype'] ."\n";
                        }
                    }
                    if($val['actiontaken']=='Wage Increase'){
                        $str=$str . $val['actiontaken']."\n";
                        $str1=$str1 . $val['effectivedate']."\n";
                        if($val['currentbasepay']!=$val['newbasepay']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Base Pay" ."\n";
                            $str3=$str3 . $val['currentbasepay'] ."\n";
                            $str4=$str4 . $val['newbasepay'] ."\n";
                        }
                        if($val['currentriceallowance']!=$val['newriceallowance']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Rice Allowance" ."\n";
                            $str3=$str3 . $val['currentriceallowance'] ."\n";
                            $str4=$str4 . $val['newriceallowance'] ."\n";
                        }
                        if($val['currentclothingallowance']!=$val['newclothingallowance']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Clothing Allowance" ."\n";
                            $str3=$str3 . $val['currentclothingallowance'] ."\n";
                            $str4=$str4 . $val['newclothingallowance'] ."\n";
                        }
                        if($val['currentlaundryallowance']!=$val['newlaundryallowance']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Laundry Allowance" ."\n";
                            $str3=$str3 . $val['currentlaundryallowance'] ."\n";
                            $str4=$str4 . $val['newlaundryallowance'] ."\n";
                        }
                        if($val['currenttotalcashcomp']!=$val['newtotalcashcomp']){
                            $str2=$str2 . "Total Cash Compensation" ."\n";
                            $str3=$str3 . $val['currenttotalcashcomp'] ."\n";
                            $str4=$str4 . $val['newtotalcashcomp'] ."\n";
                        }
                    }
                    if($val['actiontaken']=='Promotion'){
                        $str=$str . $val['actiontaken']."\n";
                        $str1=$str1 . $val['effectivedate']."\n";
                        if($val['currentdeptname']!=$val['newdeptname']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Department Name" ."\n";
                            $str3=$str3 . $val['currentdeptname'] ."\n";
                            $str4=$str4 . $val['newdeptname'] ."\n";
                        }
                        if($val['currentdeptmanager']!=$val['newdeptmanager']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Department Manager" ."\n";
                            $str3=$str3 . $val['currentdeptmanager'] ."\n";
                            $str4=$str4 . $val['newdeptmanager'] ."\n";
                        }
                        if($val['currentsection']!=$val['newsection']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Section" ."\n";
                            $str3=$str3 . $val['currentsection'] ."\n";
                            $str4=$str4 . $val['newsection'] ."\n";
                        }
                        if($val['currentimmediatesupervisor']!=$val['newimmediatesupervisor']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Department Manager" ."\n";
                            $str3=$str3 . $val['currentimmediatesupervisor'] ."\n";
                            $str4=$str4 . $val['newimmediatesupervisor'] ."\n";
                        }
                        if($val['currentjobcode']!=$val['newjobcode']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Job Code" ."\n";
                            $str3=$str3 . $val['currentjobcode'] ."\n";
                            $str4=$str4 . $val['newjobcode'] ."\n";
                        }
                        if($val['currentpositiontitle']!=$val['newpositiontitle']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Position Title" ."\n";
                            $str3=$str3 . $val['currentpositiontitle'] ."\n";
                            $str4=$str4 . $val['newpositiontitle'] ."\n";
                        }
                        if($val['currentjoblevel']!=$val['newjoblevel']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Job Level" ."\n";
                            $str3=$str3 . $val['currentjoblevel'] ."\n";
                            $str4=$str4 . $val['newjoblevel'] ."\n";
                        }
                        if($val['currentempstatus']!=$val['newempstatus']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Employment Status" ."\n";
                            $str3=$str3 . $val['currentempstatus'] ."\n";
                            $str4=$str4 . $val['newempstatus'] ."\n";
                        }
                        if($val['currentpaygroup']!=$val['newpaygroup']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Pay Group" ."\n";
                            $str3=$str3 . $val['currentpaygroup'] ."\n";
                            $str4=$str4 . $val['newpaygroup'] ."\n";
                        }
                        if($val['currentlabortype']!=$val['newlabortype']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Labor Type" ."\n";
                            $str3=$str3 . $val['currentlabortype'] ."\n";
                            $str4=$str4 . $val['newlabortype'] ."\n";
                        }
                        if($val['currentbasepay']!=$val['newbasepay']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Base Pay" ."\n";
                            $str3=$str3 . $val['currentbasepay'] ."\n";
                            $str4=$str4 . $val['newbasepay'] ."\n";
                        }
                        if($val['currentriceallowance']!=$val['newriceallowance']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Rice Allowance" ."\n";
                            $str3=$str3 . $val['currentriceallowance'] ."\n";
                            $str4=$str4 . $val['newriceallowance'] ."\n";
                        }
                        if($val['currentclothingallowance']!=$val['newclothingallowance']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Clothing Allowance" ."\n";
                            $str3=$str3 . $val['currentclothingallowance'] ."\n";
                            $str4=$str4 . $val['newclothingallowance'] ."\n";
                        }
                        if($val['currentlaundryallowance']!=$val['newlaundryallowance']){
                            $str=$str . "\n";
                            $str1=$str1 . "\n";
                            $str2=$str2 . "Laundry Allowance" ."\n";
                            $str3=$str3 . $val['currentlaundryallowance'] ."\n";
                            $str4=$str4 . $val['newlaundryallowance'] ."\n";
                        }
                        if($val['currenttotalcashcomp']!=$val['newtotalcashcomp']){
                            $str2=$str2 . "Total Cash Compensation" ."\n";
                            $str3=$str3 . $val['currenttotalcashcomp'] ."\n";
                            $str4=$str4 . $val['newtotalcashcomp'] ."\n";
                        }
                    }
					$ctr++;
				}
			}

			$data[] = array(
							"empid"			        => $row['empid'],
							"empname" 		        => $row['empname'],
                            "action_taken"          => $str,
                            "effective_date"        => $str1,
                            "details"               => $str2,
                            "old"                   => $str3,
                            "new"                   => $str4,
                    );
		}
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
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
	
print $return;	
mysqli_close($con);


?>