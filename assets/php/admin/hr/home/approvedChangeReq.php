<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    // $param 	= $_POST;
    $param = json_decode(file_get_contents('php://input'));
    $date 	= SysDate();
    $time 	= SysTime();
    $return = null;


    if(!empty($param->accountid)){
        
        // Filter for Dependent
        $getdepts_new = array();
        $var = 0;
        foreach($param->approve->getdepts_new as $key => $value){
            if(!empty($param->approve->getdepts_new[$key]->name) || !empty($param->approve->getdepts_new[$key]->bdate)){
                array_push($getdepts_new,$key);
                $var++;
            }
        }

        foreach($getdepts_new as $valxxx){
            $param->approve->getdepts_new[$valxxx]->name          =   (str_replace("'","",$param->approve->getdepts_new[$valxxx]->name ));
            $param->approve->getdepts_new[$valxxx]->bdate         =   (str_replace("'","",$param->approve->getdepts_new[$valxxx]->bdate ));
        }
		

        $Qry 			= new Query();	
        $Qry->table 	= "tblaccount";  
        $Qry->selected 	= "idcomp = 1";
                        if( !empty( $param->approve->new_fname ) ){
                                $Qry->selected 	= $Qry->selected . ", fname='".$param->approve->new_fname."'";			
                        }
                        if( !empty( $param->approve->new_mname ) ){
                            $Qry->selected 	= $Qry->selected . ", mname='".$param->approve->new_mname."'";			
                        }
                        if( !empty( $param->approve->new_lname ) ){
                            $Qry->selected 	= $Qry->selected . ", lname='".$param->approve->new_lname."'";			
                        }
                        if( !empty( $param->approve->new_suffix ) ){
                            $Qry->selected 	= $Qry->selected . ", suffix='".$param->approve->new_suffix."'";			
                        }
                        if( !empty( $param->approve->new_nickname ) ){
                            $Qry->selected 	= $Qry->selected . ", nickname='".$param->approve->new_nickname."'";			
                        }
                        if( !empty( $param->approve->new_mari_stat ) ){
                            $Qry->selected 	= $Qry->selected . ", civilstat='".$param->approve->new_mari_stat."'";			
                        }
                        if( !empty( $param->approve->new_emer_name ) ){
                            $Qry->selected 	= $Qry->selected . ", emergency_name='".$param->approve->new_emer_name."'";			
                        }
                        if( !empty( $param->approve->new_emer_cont ) ){
                            $Qry->selected 	= $Qry->selected . ", emergency_number='".$param->approve->new_emer_cont."'";			
                        }
						if( !empty( $param->approve->new_add_st ) ){
                            $Qry->selected 	= $Qry->selected . ", addr_st='".$param->approve->new_add_st."'";			
                        }
                        if( !empty( $param->approve->new_add_area ) ){
                            $Qry->selected 	= $Qry->selected . ", addr_area='".$param->approve->new_add_area."'";			
                        }
                        if( !empty( $param->approve->new_add_city ) ){
                            $Qry->selected 	= $Qry->selected . ", addr_city='".$param->approve->new_add_city."'";			
                        }
                        if( !empty( $param->approve->new_add_prov ) ){
                            $Qry->selected 	= $Qry->selected . ", addr_prov='".$param->approve->new_add_prov."'";			
                        }
                        if( !empty( $param->approve->new_add_code ) ){
                            $Qry->selected 	= $Qry->selected . ", addr_code='".$param->approve->new_add_code."'";			
                        }
                        if( !empty( $param->approve->new_pnum ) ){
                            $Qry->selected 	= $Qry->selected . ", pnumber='".$param->approve->new_pnum."'";			
                        }
                        if( !empty( $param->approve->new_fax_num ) ){
                            $Qry->selected 	= $Qry->selected . ", fnumber='".$param->approve->new_fax_num."'";			
                        }
                        if( !empty( $param->approve->new_mnum ) ){
                            $Qry->selected 	= $Qry->selected . ", cnumber='".$param->approve->new_mnum."'";			
                        }

        $Qry->fields 	= "id = '".$param->approve->idacct."'";
        $checkentry 	= $Qry->exe_UPDATE($con); 
        if($checkentry){
			
			
			
			if( count( $getdepts_new ) > 0 ){
				$total_deps 	= count( $param->approve->getdepts ) + count( $getdepts_new );
				if( (int)$total_deps > 10 ){ $total_deps = 10; }
				$Qry1 			= new Query();	
				$Qry1->table 	= "tblaccountjob";  
				$Qry1->fields 	= "idacct = '".$param->approve->idacct."'";
				$Qry1->selected = "dependent = '".(int)$total_deps."'";
				$checkQry1 		= $Qry1->exe_UPDATE($con);
			}
			
			
            $Qry 			= new Query();	
            $Qry->table 	= "tblchangereq";  
            $Qry->selected 	= "id_status = 1";
            $Qry->fields 	= "id = '".$param->approve->id."'";
            $checkentry 	= $Qry->exe_UPDATE($con);

            // Insert Dependent
            foreach($getdepts_new as $valz){
                    // Insert to dependent table
					if( countDependents($con,$param->approve->idacct) < 10 ){
						$QryA            = new Query();
						$QryA->table     = "tblacctdependent";
						$QryA->selected  = "idacct,
										name,
										birthday";
						$QryA->fields    = "'".$param->approve->idacct."',
										'".$param->approve->getdepts_new[$valz]->name."',
										'".$param->approve->getdepts_new[$valz]->bdate."'";
						$rs             = $QryA->exe_INSERT($con);
					}
            }

            $return = json_encode(array("status"=>"success"));
        }else{
            $return = json_encode(array('status'=>'error', "w"=>$Qry->selected,"err"=>mysqli_error($con)));
        }
		// if( $checkentry ){
            

        //     // Insert Dependent
        //     foreach($new_dependent as $valz){
        //             // Insert to dependent table
        //             $Qry            = new Query();
        //             $Qry->table     = "tbldependent";
        //             $Qry->selected  = "idacct,
        //                             name,
        //                             birthday,
        //                             status";
        //             $Qry->fields    = "'".$param->accountid."',
        //                             '".$param->approve->new_dependent->new_dependent_name[$valz]."',
        //                             '".$param->approve->new_dependent->new_dependent_bdate[$valz]."',
        //                             '3'";
        //             $rs             = $Qry->exe_INSERT($con);
                
        //     }
      

		// 	$return = json_encode(array("status"=>"success", "refno"=>"$ticketNumber" ));
		// }else{
		// 	$return = json_encode(array('status'=>'empty', "err"=>mysqli_error($con)));
		// }
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);

function countDependents($con,$accountid){
	$Qry=new Query();
    $Qry->table="tblacctdependent";
    $Qry->selected="*";
    $Qry->fields="idacct='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
		return mysqli_num_rows($rs);
	}else{
		return 0;
	}
}

?>