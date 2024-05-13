<?php 
 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
    $con = $conn->connect();
	// $param = $_GET;
	$param = json_decode(file_get_contents('php://input'));
	$date=SysDate();

	$search='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like '%".$param->empid."%' "; }	

    //FROM/TO SEARCH
    if( !empty($param->search_from) && empty($param->search_to)){
        $search=$search." AND dfrom LIKE '%".$param->search_from."%' ";
    }

    if( empty($param->search_from) && !empty($param->search_to)){
        $search=$search." AND dto LIKE '%".$param->search_to."%' ";
    }
    
    if( !empty($param->search_from) && !empty($param->search_to) ){
        $search=$search." AND dfrom LIKE '%".$param->search_from."%' AND dto LIKE '%".$param->search_to."%' ";
    }
	
//$name23 = array();
$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id>0 AND dfrom IS NOT NULL AND dto IS NOT NULL ".$search;
$where = $Qry->fields;	
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        //Format date for display
		$hired_date_format=date_create($row['hdate']);
        
        $heirarchy = checkHeirarchy($con, $row['idunit']);
        $org = '';
        foreach ($heirarchy as $key => $value) {
            if($value['stype']=='Organization'){
                $org = $value['unit'];
            }
        }

        $getEmpHistory = getEmpHistory($con, $row['id']);
        $str = '';
        $str1 = '';
        $str2 = '';
        $str3 = '';
        $str4 = '';
        $ctr = 1;

        if($getEmpHistory){
            $str = $str . $ctr . ". ". $org."\n";
            $str1 = $str1 . $ctr . ". ". $row['post']."\n";
            $str2 = $str2 . $ctr . ". ". $row['hdate']."\n";
            $str3 = $str3 . $ctr . ". ". "Present"."\n";
            //Concat
            // $str=$str . $row['post'].' in ' .$org.' From '.$row['hdate'].' To '.' Present '."\n";
            foreach($getEmpHistory as $val){
                $ctr++;
                $str = $str . $ctr . ". ". $val['company']."\n";
                $str1 = $str1 . $ctr . ". ". $val['position']."\n";
                $str2 = $str2 . $ctr . ". ". $val['date_from']."\n";
                $str3 = $str3 . $ctr . ". ". $val['date_to']."\n";
                
            }
            
        }

        $data[] = array(    
            
            "empid"		   => $row['empid'],
            "empname" 	   => $row['empname'],            
            "company"      => $str,
            "position"     => $str1,
            "date from"    => $str2,
            "date to"      => $str3
                           
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('status'=>'empty'));
}


print $return;	
mysqli_close($con);



function getEmpHistory($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountemphis";
    $Qry->selected="*";
    $Qry->fields="id>0 AND idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

        //Format date for display
        $date_from_format=date_create($row['dfrom']);
        $date_to_format=date_create($row['dto']);

            $data[] = array(
                'company'    =>$row['company'],
                'position'	 =>$row['position'],
                'date_from'	 =>$row['dfrom'],
                'date_to'	 =>$row['dto'],


            );
        }
        return $data;
    }
    return null;
}

function checkHeirarchy( $con, $idunit ){
    $Qry 			= new Query();	
    $Qry->table     = "vw_databusinessunits";
    $Qry->selected  = "*";
    $Qry->fields    = "id='".$idunit."' AND unittype <> 6 ORDER BY unittype DESC";
    $rs = $Qry->exe_SELECT($con);
    $arr_id = array();
    $data = array();
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $unittype = $row['unittype'];
            
            $idunder  = $row['idunder'];
            $data[0] = array(
                'id' 			=> $row['id'],
                'unit'			=> $row['name'],
                'alias'			=> $row['alias'],
                'idhead'		=> $row['idhead'],
                'idunder'		=> $row['idunder'],
                'unittype'		=> $row['unittype'],
                'isactive'		=> $row['isactive'],
                'stype'			=> $row['stype'],
                'shead'			=> $row['shead'],
                'stat'			=> $row['stat']
            );
            $ndex	  = 1;
            $x = true;
            if( empty($idunder) ){
                $x = false;
            }
            if( !empty( $idunder ) ){
                do{
                    $data[$ndex] = getapprover2( $con, $idunder );
                    $idunder		 = $data[ $ndex ]['idunder'];
                    $unittype		 = $data[ $ndex ]['unittype'];
                    $ndex++;
                    $x = true;
                    if( empty($idunder) ){
                        $x = false;
                    }
                }while( $x == true );
            }
        }
    }
    return $data;
}


?>