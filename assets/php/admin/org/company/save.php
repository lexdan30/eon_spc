<?php
require_once('../../../activation.php');
$param = $_POST;
$conn = new connector();
	
if( (int)$param['conn'] == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param['conn'];
	$con = $conn->$varcon();
}

require_once('../../../classPhp.php'); 

$key = 'N-Pax';
function encrypthis($data, $key){
    $encryption_key = base64_decode($key);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc',$encryption_key,0, $iv);
    return base64_encode($encrypted . '::'. $iv);
}
function decryptthis($data, $key){
    $encryption_key = base64_decode($key);
    list($encryption_data, $iv) = array_pad(explode('::', base64_decode($data), 2),2,null);
    return openssl_decrypt($encryption_data,'aes-256-cbc', $encryption_key, 0, $iv);
}



foreach ($param['info'] as $keyzz => $valuezz) {
    if( !is_array($param['info'][$keyzz]) &&  $keyzz != 'picFile'  ){
        if( strtolower($param['info'][$keyzz]) == 'null' ){
            $param['info'][$keyzz]="";
        }
    }
}

if(!empty($param['accountid'])){
    if( array_key_exists('file',$_FILES) ){
        $valid_formats = array("jpg", "png", "jpeg");	
        if ($_FILES['file']['error'] == 4) {
			$return = json_encode(array('status'=>'error','on'=>'img_check'));
            print $return;	
            mysqli_close($con);
            return;
        }
        if ($_FILES['file']['error'] == 0) {
            if(!in_array(pathinfo(strtolower($_FILES['file']['name']), PATHINFO_EXTENSION), $valid_formats) ){
                $return = json_encode(array('status'=>'error-upload-type'));
                    print $return;	
                    mysqli_close($con);
                    return;
            }
        }
    }

    //if( !empty( $param['info']['alias'] ) ){
        if( !empty( $param['info']['name'] ) ){
            if( !empty( $param['info']['idtype'] ) ){
                if( !empty( $param['info']['cnumber'] ) ){
                    if( !empty( $param['info']['idsize'] ) ){
                        if( !empty( $param['info']['idind'] ) ){
                            if( !empty( $param['info']['idbir'] ) ){                            
                                if( !empty( $param['info']['idsss'] ) ){                            
                                    if( !empty( $param['info']['idibig'] ) ){                            
                                        if( !empty( $param['info']['idhealth'] ) ){       

                                            $param['info']['alias'] 	    = ((str_replace("'","",$param['info']['alias'])));	
                                            $param['info']['name'] 		    = ((str_replace("'","",$param['info']['name'])));	
                                            $param['info']['addr_bldg']     = ((str_replace("'","",$param['info']['addr_bldg'])));
                                            $param['info']['addr_street']   = ((str_replace("'","",$param['info']['addr_street'])));
                                            $param['info']['addr_brgy']     = ((str_replace("'","",$param['info']['addr_brgy'])));
                                            $param['info']['addr_city']     = ((str_replace("'","",$param['info']['addr_city'])));
                                            $param['info']['addr_prov']     = ((str_replace("'","",$param['info']['addr_prov'])));
                                            $param['info']['addr_code']     = ((str_replace("'","",$param['info']['addr_code'])));
                                            $param['info']['cnumber']       = ((str_replace("'","",$param['info']['cnumber'])));
                                            $param['info']['fnumber']       = ((str_replace("'","",$param['info']['fnumber'])));
                                            $param['info']['website']       = ((str_replace("'","",$param['info']['website'])));
                                            $param['info']['idbir']         = ((str_replace("'","",$param['info']['idbir'])));
                                            $param['info']['idsss']         = ((str_replace("'","",$param['info']['idsss'])));
                                            $param['info']['idibig']        = ((str_replace("'","",$param['info']['idibig'])));
                                            $param['info']['idhealth']      = ((str_replace("'","",$param['info']['idhealth'])));
                                            $param['info']['profile'] 	    = ((str_replace("'","",$param['info']['profile'])));
                                            $param['info']['mission'] 	    = ((str_replace("'","",$param['info']['mission'])));
                                            $param['info']['vision'] 	    = ((str_replace("'","",$param['info']['vision'])));

                                            $Qry            = new Query();	
                                            $Qry->table     = "tblcompany";                                            
                                            $Qry->selected  = "name='".$param['info']['name']."',alias='".$param['info']['alias']."',cnumber='".$param['info']['cnumber']."',email='". $param['info']['email']."',
                                            fnumber='".$param['info']['fnumber']."',idtype='".$param['info']['idtype']."',idind='".$param['info']['idind']."',
                                            idsize='".$param['info']['idsize']."',website='".$param['info']['website']."',idbir='".$param['info']['idbir']."',addr_bldg='".$param['info']['addr_bldg']."',
                                            idsss='".$param['info']['idsss']."',idibig='".$param['info']['idibig']."',idhealth='".$param['info']['idhealth']."',
                                            addr_street='".$param['info']['addr_street']."',addr_brgy='".$param['info']['addr_brgy']."',addr_city='".$param['info']['addr_city']."',
                                            addr_prov='".$param['info']['addr_prov']."',addr_code='".$param['info']['addr_code']."'";
                                            $Qry->fields  = "id=".$param['info']['id'];
											
											if( !empty( $param['info']['profile']  ) ){
												$Qry->selected  = $Qry->selected  . ", profile='".$param['info']['profile']."' ";
                                            }
                                            if( !empty( $param['info']['mission']  ) ){
												$Qry->selected  = $Qry->selected  . ", mission='".$param['info']['mission']."' ";
                                            }
                                            if( !empty( $param['info']['vision']  ) ){
												$Qry->selected  = $Qry->selected  . ", vision='".$param['info']['vision']."' ";
                                            }
                                            

                                            $checke = $Qry->exe_UPDATE($con);
											if($checke){
                                                if( array_key_exists('file',$_FILES) ){
                                                    $lastID = $param['info']['id'];
                                                    $folder_path = $param['targetPath'];
                                                    $type = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                                                    $name = $lastID.".webp";		
                                                    move_uploaded_file( $_FILES['file']['tmp_name'] , $folder_path.'/'.$name);                                                    
                                                    $img_prof = null;
                                                    if( !file_exists('../../../admin/org/company/'.(int)$lastID.".webp") ){
                                                        $return = json_encode(array("status"=>"error","upload"=>1));
                                                    }else{
                                                        $return = json_encode(array("status"=>"success"));
                                                    }
                                                }else{ $return = json_encode(array('status'=>'success')); }
                                            }else{
                                                $return = json_encode(array('status'=>'error'));
                                            }
                                        }else{
                                            $return = json_encode(array('status'=>'noidhealth'));
                                        }
                                    }else{
                                        $return = json_encode(array('status'=>'noidibig'));
                                    }
                                }else{
                                    $return = json_encode(array('status'=>'noidsss'));
                                }
                            }else{
                                $return = json_encode(array('status'=>'noidbir'));
                            }
                        }else{
                            $return = json_encode(array('status'=>'noidind'));
                        }
                    }else{
                        $return = json_encode(array('status'=>'noidsize'));
                    }
                }else{
                    $return = json_encode(array('status'=>'nophone'));
                }
            }else{
                $return = json_encode(array('status'=>'noidtype'));
            }
        }else{
            $return = json_encode(array('status'=>'noname'));
        }
    //}else{
    //    $return = json_encode(array('status'=>'noid'));
   // }


}else{
    $return = json_encode(array('status'=>'noacct'));
}

print $return;
mysqli_close($con);
?>