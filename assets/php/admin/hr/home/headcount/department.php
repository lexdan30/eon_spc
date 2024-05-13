<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry2 = new Query();	
$Qry2->table     = "vw_databusinessunits";
$Qry2->selected  = "COUNT(*) as total";
$Qry2->fields    = "stype = 'department'";

$rs2 = $Qry2->exe_SELECT($con);
if(mysqli_num_rows($rs2)>= 1){
    while($row2=mysqli_fetch_array($rs2)){
        $total = $row2['total'];
    }
}


$Qry = new Query();	
$Qry->table     = "vw_databusinessunits";
$Qry->selected  = "id,name";
$Qry->fields    = "stype = 'department'";


$rs = $Qry->exe_SELECT($con);
$loops = 0;
if(mysqli_num_rows($rs)>= 1){
    $alldata		    = array();
    
    while($row=mysqli_fetch_array($rs)){
        $loops++;
        $labels[] = $row['name']; 
      
        $count = getdepartmentchilds($con  ,$row['id']);
        array_push($count,$row['id']);

    
       $count = implode(",", $count); 
       $depdata = '';
       $depdata = array();

       for ($x = 1; $x <= $total; $x++) {
            $depdata[] = '0';
        }
    
        // echo $loops;
        // print_r($depdata);
  
        
        $Qry1 = new Query();	
        $Qry1->table     = "vw_dataemployees";
        $Qry1->selected  = "COUNT(*) as total";
        $Qry1->fields    = "idunit IN (".$count.")";
        $rs1 = $Qry1->exe_SELECT($con);
        
        if(mysqli_num_rows($rs1)>= 1){
            while($row1=mysqli_fetch_array($rs1)){

                for ($x = 1; $x <= $total; $x++) {
                   if($x == $loops){
                        $depdata[$x - 1] = $row1['total'];
                   }
                }

            }
            $data = array('label'           => $row['name'],
                            'borderColor'   => "#7f9bb6",
                            'data'          => $depdata);

                array_push($alldata,$data);
        }

    }

	$return = json_encode(array('labels' => $labels, 'datasets' => $alldata));
}else{
	$return = json_encode(array('w'=>$Qry->fields));
	
}


function getdepartmentchilds( $con, $idunit){
    $id			    = array();
    $Qry 			= new Query();	
    $Qry->table     = "tblbunits";
    $Qry->selected  = "*";
    $Qry->fields    = "idunder = '".$idunit."'";
    $rs 			= $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            array_push($id,$row['id']);
            getdepartmentchilds($con, $row['id']);
        }
    }
    return $id;
}

print $return;
mysqli_close($con);
?>