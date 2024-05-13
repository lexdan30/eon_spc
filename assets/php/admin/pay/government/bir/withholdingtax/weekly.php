<?php
require_once('../../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblbirweekly";
$Qry->selected  = "*";
$Qry->fields = "id>0 ";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            'id'		=> $row['id'],
			'description' => $row['description'],
			'br1'		=> $row['br1'],
            'br2'   	=> $row['br2'],
			'br3'       => $row['br3'],
            'br4'       => $row['br4'],
            'br5'       => $row['br5'],
            'br6'       => $row['br6']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>

            