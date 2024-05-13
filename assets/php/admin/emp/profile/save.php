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
        //pending status in hr
        if(checkPending($con, $param->accountid)){
            $return = json_encode(array('status'=>'haspending'));
            print $return;
            mysqli_close($con);
            return;
        }
        
        // Filter for Dependent
        $new_dependent = array();
        $var = 0;
        foreach($param->save->new_dependent->new_dependent_name as $key => $value){
            if(!empty($param->save->new_dependent->new_dependent_name[$key]) || !empty($param->save->new_dependent->new_dependent_bdate[$key]) || !empty($param->save->new_dependent->new_dependent_age[$key])){
                array_push($new_dependent,$key);
                $var++;
            }
        }

        // if($var == 0){
        //     $return = json_encode(array('status'=>'noentrynewdep'));
        //     print $return;
        //     mysqli_close($con);
        //     return;
        // }

        // foreach($new_dependent as $val){

        //     // Name
        //     if(empty($param->save->new_dependent->new_dependent_name[$val])){
        //         $return = json_encode(array('status'=>'noname'));
        //         print $return;
        //         mysqli_close($con);
        //         return;
        //     }
        //     //Birthdate
        //     if(empty($param->save->new_dependent->new_dependent_bdate[$val])){
        //         $return = json_encode(array('status'=>'nobdate'));
        //         print $return;
        //         mysqli_close($con);
        //         return;
        //     }
        //      //Age
        //      if(empty($param->save->new_dependent->new_dependent_age[$val])){
        //         $return = json_encode(array('status'=>'noage'));
        //         print $return;
        //         mysqli_close($con);
        //         return;
        //     }
        
        // }

        foreach($new_dependent as $valxxx){
            // $param->save->new_dependent          =   (str_replace("'","",$param->save->new_dependent ));
            $param->save->new_dependent->new_dependent_name          =   (str_replace("'","",$param->save->new_dependent->new_dependent_name ));
            $param->save->new_dependent->new_dependent_bdate         =   (str_replace("'","",$param->save->new_dependent->new_dependent_bdate ));
            $param->save->new_dependent->new_dependent_age           =   (str_replace("'","",$param->save->new_dependent->new_dependent_age ));
        }
        

        
        $linkid 		=	getReqCtr($con);
        $linkid1 		=	$linkid + 1;
        $ticketNumber 	=	"CR".str_pad($linkid1,6,"0",STR_PAD_LEFT);

        $Qry 			= new Query();	
        $Qry->table 	= "tblchangereq";
        $Qry->selected 	= "idacct,
                        empid, ref_num, 
                        current_fname, new_fname, 
                        current_mname, new_mname, 
                        current_lname, new_lname, 
                        current_suffix, new_suffix, 
                        current_nickname, new_nickname, 
                        current_mari_stat, new_mari_stat, 
                        current_emer_name, new_emer_name, 
                        current_emer_cont, new_emer_cont, 
                        
                        current_add_st, new_add_st,
                        current_add_area, new_add_area,
                        current_add_city, new_add_city,
                        current_add_prov, new_add_prov,
                        current_add_code, new_add_code,

                        current_pnum, new_pnum, 
                        current_fax_num, new_fax_num, 
                        current_mnum, new_mnum, 
                        date_created, time_created";
        $Qry->fields 	= " '".$param->accountid."',
                            '".getEmpid($con, $param->accountid)."',
							'".$ticketNumber."', 
                            '".$param->save->fname."',
                            '".$param->save->new_fname."',
                            '".$param->save->mname."',
                            '".$param->save->new_mname."',
                            '".$param->save->lname."',
                            '".$param->save->new_lname."',
							'".$param->save->suffix."',
                            '".$param->save->new_suffix."',
                            '".$param->save->nickname."',
							'".$param->save->new_nickname."',
                            '".$param->save->civil_status."',
                            '".$param->save->new_mari_stat."',
                            '".$param->save->emergency_name."',
                            '".$param->save->new_emer_name."',
							'".$param->save->emergency_number."',
                            '".$param->save->new_emer_cont."',


                            '".$param->save->addr_st."',
                            '".$param->save->new_add_st."',
                            '".$param->save->addr_area."',
                            '".$param->save->new_add_area."',
                            '".$param->save->addr_city."',
                            '".$param->save->new_add_city."',
                            '".$param->save->addr_prov."',
                            '".$param->save->new_add_prov."',
                            '".$param->save->addr_code."',
                            '".$param->save->new_add_code."',
                        
                            '".$param->save->pnumber."',
                            '".$param->save->new_pnum."',
                            '".$param->save->fnumber."',
                            '".$param->save->new_fax_num."',
                            '".$param->save->cnumber."',
                            '".$param->save->new_mnum."',
                            '".SysDate()."',
                            '".SysTime()."'";
                    // if(!empty($param->save->picFile2)){
                    //     $file = $param->accountid . '.webp';
                    //     $Qry->selected = $Qry->selected . ",attachment";
                    //     $Qry->fields = $Qry->fields . ",'".$file."'";
                    // }
        $checkentry 	= $Qry->exe_INSERT($con); 
		if( $checkentry ){
            

            // Insert Dependent
            foreach($new_dependent as $valz){
                    // Insert to dependent table
                    $Qry            = new Query();
                    $Qry->table     = "tbldependent";
                    $Qry->selected  = "idacct,
                                    ref_num,
                                    name,
                                    birthday,
                                    status";
                    $Qry->fields    = "'".$param->accountid."',
                                    '".$ticketNumber."',
                                    '".$param->save->new_dependent->new_dependent_name[$valz]."',
                                    '".$param->save->new_dependent->new_dependent_bdate[$valz]."',
                                    '3'";
                    $rs             = $Qry->exe_INSERT($con);
                
            }
      

			$return = json_encode(array("status"=>"success", "refno"=>"$ticketNumber" ));
		}else{
			$return = json_encode(array('status'=>'empty', "err"=>mysqli_error($con)));
		}
    }else{
        $return = json_encode(array('status'=>'error')); 
    }

print $return;
mysqli_close($con);



function getReqCtr($con){
    $Qry=new Query();
    $Qry->table="tblchangereq";
    $Qry->selected="count(id) as ctr";
    $Qry->fields="id>0";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['ctr'];
        }
    }
    return null;
}

function getEmpid($con, $accountid){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="empid";
    $Qry->fields="id='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
			return $row['empid'];
        }
    }
    return '';
}

function checkPending($con, $accountid){
    $Qry=new Query();
    $Qry->table="tblchangereq";
    $Qry->selected="id";
    $Qry->fields="idacct='".$accountid."' AND id_status=3";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
			return true;
        }
    }
    return false;
}

?>