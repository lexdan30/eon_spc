<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$data  = array();
$date  = SysDateDan();
$time  = SysTime();


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id > 0 AND dfrom IS NOT NULL AND dto IS NOT NULL ";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        //Format date for display
        $hired_date_format=date_create($row['hdate']);

        $getEmpHistory = getEmpHistory($con, $row['id']);

        //Get the position heirarchy 
        $heirarchy = checkHeirarchy($con, $row['idunit']);

        $org = '';
        // $division = '';
        // $department = '';
        // $section = '';
        // $subsection = '';
        // $unit = '';
        // $subunit = '';
        // $operator = '';

        foreach ($heirarchy as $key => $value) {
            if($value['stype']=='Organization'){
                $org = $value['unit'];
            }

            // if($value['stype']=='Division'){
            //     $division = $value['unit'];
            // }

            // if($value['stype']=='Department'){
            //     $department = $value['unit'];
            // }

            // if($value['stype']=='Section'){
            //     $section = $value['unit'];
            // }

            // if($value['stype']=='Sub Section'){
            //     $subsection = $value['unit'];
            // }

            // if($value['stype']=='Unit'){
            //     $unit = $value['unit'];
            // }

            // if($value['stype']=='Sub Unit'){
            //     $subunit = $value['unit'];
            // }

            // if($value['stype']=='Operator'){
            //     $operator = $value['unit'];
            // }
        }

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "post" 		            => ucwords($row['post']),
            "dfrom" 		        => $row['dfrom'],
            "dto" 		            => $row['dto'],
            "org"                   => $org,
            "hdate"                 => date_format($hired_date_format,"m/d/Y"),
            // "labor_type" 		    => ucwords($row['labor_type']),
            // // "product" 		    => ucwords($row['product']),
            // "division" 		        => $division,
            // "dept" 		            => $department,
            // "section" 		        => $section,
            // "sub_section" 		    => $subsection,
            // "unit" 		            => $unit,
            // "sub_unit" 		        => $subunit,
            // "operator" 		        => $operator,
            // "per_st" 		        => $row['per_st'],
            // "addr_st" 		        => $row['addr_st'],
            // "gender" 		        => ucwords(strtolower($row['sexstr'])),
            // "marital_stat" 		    => ucwords(strtolower($row['civil_status'])),
            // "birth_place" 		    => ucwords(strtolower($row['bplace'])),
            // "address_city" 		    => ucwords(strtolower($row['addr_city'])),
            // "address_prov" 		    => ucwords(strtolower($row['addr_prov'])),
            // "reg_date" 		        => $reg_date_format,
            // "count" 		        => ucwords(strtolower($row['count'])),
            "getEmpHistory"         => $getEmpHistory,
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time)),


			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}

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
                'date_from'	 =>date_format($date_from_format,"m/d/Y"),
                'date_to'	 =>date_format($date_to_format,"m/d/Y"),
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


print $return;
mysqli_close($con);
?>