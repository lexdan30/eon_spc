<?php 
require_once('../../../activation.php');
require_once('../../../classPhp.php');
$conn = new connector();
$con = $conn->connect();
$param = json_decode(file_get_contents('php://input'));
$date=SysDate();
$search='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like '%".$param->empid."%' "; }
    if( !empty( $param->equi_tools ) ){ $search=$search." AND equi_tools like '%".$param->equi_tools."%' "; }
    if( !empty( $param->ser_num ) ){ $search=$search." AND serial like '%".$param->ser_num."%' "; }
    
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
$Qry->fields    = "id>0 AND (awards_title IS NOT NULL) ".$search;
$where = $Qry->fields;	
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $getPropertyAccountability = getPropertyAccountability($con, $row['id']);
        $str  = '';
        $str1 = '';
        $str2 = '';
        $str3 = '';
        $str4 = '';
        $ctr  = 1;
        
        if($getPropertyAccountability){
            foreach($getPropertyAccountability as $val){
                $str=$str . $ctr . ". " . $val['equi_tools']."\n";
                $str1=$str1 . $ctr . ". " . $val['serial']."\n";
                $str2=$str2 . $ctr . ". " . $val['quantity']."\n";
                $str3=$str3 . $ctr . ". " . $val['date_issued']."\n";
                $str4=$str4 . $ctr . ". " . $val['date_returned']."\n";
                $ctr++;
            }
        }

        $data[] = array(    
            
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "equipment/tools"       => $str,
            "serial"                => $str1,
            "quantity"              => $str2,
            "date issued"           => $str3,
            "date returned"         => $str4,

                           
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('status'=>'empty'));
}

function getPropertyAccountability($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountpropacc";
    $Qry->selected="*";
    $Qry->fields="id>0 AND idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

        //Format date for display
        $date_issued_format=date_create($row['date_issued']);
        
        
        if(!empty($row['date_returned'])){
            $date_returned_format=date_create($row['date_returned']);
            $date_returned_format=date_format($date_returned_format,"m/d/Y ");
        }else{
            $date_returned_format = '';
        }

            $data[] = array(
                'equi_tools' => $row['equi_tools'],
                'serial'	 => $row['serial'],
                'quantity'	 => $row['quantity'],
                'date_issued'=> date_format($date_issued_format,"m/d/Y"),
                'date_returned'=> $date_returned_format
            );
        }
        return $data;
    }
    return null;
}






print $return;	
mysqli_close($con);


?>