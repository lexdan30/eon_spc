<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "(SELECT id, event_title, event_desc, efrom, eto, canview, atype, filename, idcreator FROM tblcompanyact) as tblc LEFT JOIN (SELECT id, fname, lname, pic FROM tblaccount) as tbla ON (idcreator = tbla.id)";
$Qry->selected  = "tblc.id, fname, lname, event_title, event_desc, efrom, eto, canview, atype, filename";
$Qry->fields    = "tblc.id = $param->id";


$rs = $Qry->exe_SELECT($con);
$data = array();
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){

        $name = $row['fname'] . " " . $row['lname'];
        $title = $row['event_title'];
        $start= ($row['efrom']);
        $end = $row['eto'];

        $file = $row['filename'];
		if( file_exists( "../../org/activity/file/".$row['id'].".pdf" ) ){
            $file = $row['id'].".pdf";
        }
        
        $pushd = array( 
            "id" 			    => $row['id'],
            "name" 			    => $name,
            "title" 			=> $title,
            "start" 			=> $start,
            "end" 		    	=> $end,
            "description" 		=> $row['event_desc'],
            "type" 	        	=> $row['atype'],
            "file"               => $file 
        );

        array_push($data, $pushd);
     
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array('status'=>'error','w'=>$Qry->fields));
	
}


print $return;
mysqli_close($con);
?>