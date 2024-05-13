<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$date=SysDate();
$time=SysTime();
$return = null;	
$timelogs = array();

$pay_period = getPayPeriod($con);
$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brk", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brk",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brk",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brk",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brk",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brk", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brk");
					

//Validate parameters
if( empty($param["info"]["sdate"]) ){
	$return = json_encode(array('status'=>'sdate'));
	print $return;	
	mysqli_close($con);
	return;
}
if( empty($param["info"]["fdate"]) ){
	$return = json_encode(array('status'=>'fdate'));
	print $return;	
	mysqli_close($con);
	return;
}
if( empty($param["info"]["batchid"]) ){
	$return = json_encode(array('status'=>'batchid'));
	print $return;	
	mysqli_close($con);
	return;
}
if( empty($param["info"]["machid"]) ){
	$return = json_encode(array('status'=>'machid'));
	print $return;	
	mysqli_close($con);
	return;
}
if( ( strtotime( $param["info"]["sdate"] ) > strtotime( $param["info"]["fdate"]  ) ) ||
	( strtotime( $param["info"]["sdate"] ) < strtotime( $pay_period['pay_start'] ) ) ||
	( strtotime( $param["info"]["fdate"] ) > strtotime( $pay_period['pay_end']   ) )
){
	$return = json_encode(array('status'=>'invdates'));
	print $return;	
	mysqli_close($con);
	return;
}
if( checkbatchid($con,$param["info"]["batchid"],$param["info"]["machid"]) ){
	$return = json_encode(array('status'=>'dupbatchid'));
	print $return;	
	mysqli_close($con);
	return;
}

$ids=0;
if( !empty( $param["info"]["classi"] ) ){
	$dept = $param["info"]["classi"];
	if (!empty($dept)) {
		$arr_id = array();
		$arr    = getHierarchy($con, $dept);
		array_push($arr_id, $dept);
		if (!empty($arr["nodechild"])) {
			$a = getChildNode($arr_id, $arr["nodechild"]);
			if (!empty($a)) {
				foreach ($a as $v) {
					array_push($arr_id, $v);
				}
			}
		}
		if (count($arr_id) == 1) {
			$ids = $arr_id[0];
		} else {
			$ids = implode(",", $arr_id);
		}
	}
}

//upload file
if( array_key_exists('file',$_FILES) ){
	$valid_formats = array("txt");	
	$name = $_FILES['file']['name'];
	if ($_FILES['file']['error'] == 4) {
		$return = json_encode(array('status'=>'error','on'=>'img_check'));
		print $return;	
		mysqli_close($con);
		return;
	}
	if ($_FILES['file']['error'] == 0) {
		if(!in_array(pathinfo(strtolower($name), PATHINFO_EXTENSION), $valid_formats) ){
			$return = json_encode(array('status'=>'error-upload-type'));
			print $return;	
			mysqli_close($con);
			return;
		}
	}
	$folder_path 	= $param['targetPath'];	
	$t				= strtotime($date).time();	
	$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
	$save_name		= $pay_period['pay_date'].'.'.$extMove;	
	move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);	
}else{
	$return = json_encode(array('status'=>'nofile','on'=>'img_check'));
	print $return;	
	mysqli_close($con);
	return;
}

/* Machine Info*/
$machine_data	= getTimeLogsConf($con,$param["info"]["machid"]);
$emp			= explode(",",$machine_data[0]['emp']);
$wdate			= explode(",",$machine_data[0]['wdate']);
$wtime			= explode(",",$machine_data[0]['wtime']);
$atype			= explode(",",$machine_data[0]['atype']);
$ain			= $machine_data[0]['ain'];
$aout			= $machine_data[0]['aout'];


/* REPROCESS ALL */
//$truncData = truncateSheet($con);
$file = fopen($folder_path.$save_name,"r");
while(! feof($file)){
	$arr_data = array();
	$str = fgets($file);
	//$arr_data	= explode(" ",$txt_file); 
	
	array_push($arr_data, substr($str,$emp[0],$emp[1])); //Employee ID
	array_push($arr_data,date('Y-m-d',strtotime(substr($str,$wdate[0],$wdate[1])))); //Date
	array_push($arr_data,substr($str,$wtime[0],$wtime[1])); //Time
	array_push($arr_data,substr($str,$atype[0],$atype[1])); //In/Out
	
	if( !empty( $arr_data ) ){
		if( count($arr_data) == 4 ){
			$ret = insertToTimeLogs($con, $arr_data);
		}else{
			fclose($file);
			//$truncData = truncateSheet($con);
			$return = json_encode(array('status'=>'err_format'));
			print $return;	
			mysqli_close($con);
			return;
		}
	}
}
fclose($file);


//pair timelogs uploaded
$QryPair 			= new Query();	
$QryPair->table     = "tbltimelogs";
$QryPair->selected  = "acct_id";
$QryPair->fields    = "id > 0 GROUP BY acct_id ";
$rsPair				= $QryPair->exe_SELECT($con);
if(mysqli_num_rows($rsPair)>= 1){
	while($rowPair=mysqli_fetch_array($rsPair)){
		$acct_id= $rowPair['acct_id'];
		
		$idacct = getEmpAcctID($con, $rowPair['acct_id']);
		$sdate  = $param["info"]["sdate"];
		$fdate  = date('Y-m-d', strtotime("+1 day", strtotime($param["info"]["fdate"])));
		$batchid= $param["info"]["batchid"];
		$machid = $param["info"]["machid"];
		
		$Qry 			= new Query();	
		$Qry->table     = "tbltimelogs";
		$Qry->selected  = "*";
		if( $ids== 0 ){
			$Qry->fields    = "acct_id = '".$acct_id."' AND work_date BETWEEN '".$sdate."' AND '".$fdate."' ORDER BY id ASC ";
		}else{
			$Qry->fields    = "acct_id = '".$acct_id."' AND idunit in (".$ids.") AND work_date BETWEEN '".$sdate."' AND '".$fdate."' ORDER BY id ASC ";
		}
		$rs				= $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				
				$idshift = hasTimeSheetShift($con, $row['work_date'], $idacct);
				if( $idshift == 0 ){
					$shift_field= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];		
                    $shift_info = getDateShiftData( $con, $idacct, $shift_field, $row['work_date'] );
                    print_r($shift_info);
					$idshift = $shift_info[0];
				}
				
				$timelogs = array(
					"batchid"	=> $batchid,
					"machid"	=> $machid,
					"work_date"	=> $row['work_date'],
					"work_time" => $row['work_time'],
					"type"		=> $row['time_type'],
					"idshift"	=> $idshift,
					"idacct"	=> $idacct,
					"idunit"	=> $row['idunit'],
					"sdate"		=> $sdate,
					"fdate"		=> $fdate
				);
				

				/* //code if logs have 4 types
				if(!checkTimelogPairRec($con, $idacct, $row['work_date'], $batchid, $machid)){
					//INSERT record
					if( $row['time_type']==$ain ){
						insertTimelogPairRec($con, $timelogs, $ain, $aout, $bin, $bout);
					}else{
						$info = getTimelogPairRec($con, $idacct, date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $row['work_date'] ) ) )));
						if( count( $info ) == 0 ){
							insertTimelogPairRec($con, $timelogs, $ain, $aout, $bin, $bout);
						}else{
							if( $row['time_type']==$aout ){
								if( empty( $info['time_out2'] ) ){
									$fields = "date_out2='".$timelogs['work_date']."', time_out2='".$timelogs['work_time']."'";
									updateTimelogPairRec($con, $fields, $info['id']);
								}
							}elseif( $row['time_type']==$bout ){
								if( empty( $info['time_out1'] ) ){
									$fields = "date_out1='".$timelogs['work_date']."', time_out1='".$timelogs['work_time']."'";
									updateTimelogPairRec($con, $fields, $info['id']);
								}
							}elseif( $row['time_type']==$bin ){
								if( empty( $info['time_in2'] ) ){
									$fields = "date_in2='".$timelogs['work_date']."', time_in2='".$timelogs['work_time']."'";
									updateTimelogPairRec($con, $fields, $info['id']);
								}
							}
						}
					}
				}else{
					$info = getTimelogPairRec($con, $idacct, $row['work_date'], $batchid, $machid);
					if( $row['time_type']==$ain ){
						if( empty( $info['time_in1'] ) ){
							$fields = "date_in1='".$timelogs['work_date']."', time_in1='".$timelogs['work_time']."'";
							updateTimelogPairRec($con, $fields, $info['id']);
						}
					}else{
						if( $row['time_type']==$aout ){
							if( empty( $info['time_out2'] ) ){
								$fields = "date_out2='".$timelogs['work_date']."', time_out2='".$timelogs['work_time']."'";
								updateTimelogPairRec($con, $fields, $info['id']);
							}
						}elseif( $row['time_type']==$bout ){
							if( empty( $info['time_out1'] ) ){
								$fields = "date_out1='".$timelogs['work_date']."', time_out1='".$timelogs['work_time']."'";
								updateTimelogPairRec($con, $fields, $info['id']);
							}
						}elseif( $row['time_type']==$bin ){
							if( empty( $info['time_in2'] ) ){
								$fields = "date_in2='".$timelogs['work_date']."', time_in2='".$timelogs['work_time']."'";
								updateTimelogPairRec($con, $fields, $info['id']);
							}
						}
					}
				}
				
				function insertTimelogPairRec( $con, $row, $ain, $aout, $bin, $bout ){
					$Qry3           = new Query();
					$Qry3->table    = "tbltimelogpair";
					$Qry3->selected = "batchid,machid,work_date,idshift,idacct,idunit,sdate,fdate,create_stamp";
					$Qry3->fields   = "'".$row['batchid']."','".$row['machid']."','".$row['work_date']."','".$row['idshift']."', '".$row['idacct']."', '".$row['idunit']."', '".$row['sdate']."', '".$row['fdate']."', '".SysDatePadLeft()." ".SysTime()."'";
					if( $row['type'] == $ain ){
						$Qry3->selected = $Qry3->selected . ", date_in1, time_in1";
						$Qry3->fields   = $Qry3->fields   . ",'".$row['work_date']."','".$row['work_time']."'";
					}elseif( $row['type'] == $bout ){
						$Qry3->selected = $Qry3->selected . ", date_out1, time_out1";
						$Qry3->fields   = $Qry3->fields   . ",'".$row['work_date']."','".$row['work_time']."'";
					}elseif( $row['type'] == $bin ){
						$Qry3->selected = $Qry3->selected . ", date_in2, time_in2";
						$Qry3->fields   = $Qry3->fields   . ",'".$row['work_date']."','".$row['work_time']."'";
					}elseif( $row['type'] == $aout ){
						$Qry3->selected = $Qry3->selected . ", date_out2, time_out2";
						$Qry3->fields   = $Qry3->fields   . ",'".$row['work_date']."','".$row['work_time']."'";
					}
					$checke = $Qry3->exe_INSERT($con);
					return $checke;
				}
				
				//DO NOT FORGET TO ALTER vw_datatimelogs to reflect correct data
				*/
				
				//check if record exists on tbltimelogpair
				// if(!checkTimelogPairRec($con, $idacct, $row['work_date'], $batchid, $machid)){
				// 	//INSERT record
				// 	if( $row['time_type']==$ain ){
				// 		insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 	}elseif( $row['time_type']==$aout ){
				// 		$info = getTimelogPairRec($con, $idacct, date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $row['work_date'] ) ) )), $batchid, $machid);
				// 		if( count( $info ) == 0 ){
				// 			insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 		}else{
				// 			if( empty( $info["time_in1"] ) && empty( $info["time_out1"] ) ){
				// 				$fields = "date_out1='".$timelogs['work_date']."', time_out1='".$timelogs['work_time']."'";
				// 				updateTimelogPairRec($con, $fields, $info['id']);
				// 			}elseif( !empty( $info["time_in1"] ) && empty( $info["time_out1"] ) ){
				// 				$seconds =  strtotime($timelogs['work_date']." ".$timelogs['work_time']) - strtotime($info["date_in1"]." ".$info["time_in1"]);
				// 				$hours = $seconds / 60 / 60;
				// 				$fields = "date_out1='".$timelogs['work_date']."', time_out1='".$timelogs['work_time']."'";
				// 				if( $hours > 0 && $hours < 24 ){
				// 					updateTimelogPairRec($con, $fields, $info['id']);
				// 				}elseif( $hours > 24 ){
				// 					insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 				}
				// 			}elseif( empty( $info["time_in1"] ) && !empty( $info["time_out1"] ) ){
				// 				if( (empty( $info["time_in2"] ) && empty( $info["time_out2"] )) || !empty( $info["time_out2"] ) ){
				// 					insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 				}elseif(!empty( $info["time_in2"] ) && empty( $info["time_out2"] )){
				// 					$fields = "date_out2='".$timelogs['work_date']."', time_out2='".$timelogs['work_time']."'";
				// 					updateTimelogPairRec($con, $fields, $info['id']);
				// 				}
				// 			}elseif( !empty( $info["time_in1"] ) && !empty( $info["time_out1"] ) ){
				// 				if( empty( $info["time_in2"] ) && empty( $info["time_out2"] ) ){
				// 					$seconds =  strtotime($timelogs['work_date']." ".$timelogs['work_time']) - strtotime($info["date_out1"]." ".$info["time_out1"]);
				// 					$hours_out = $seconds / 60 / 60;
				// 					$seconds =  strtotime($timelogs['work_date']." ".$timelogs['work_time']) - strtotime($info["date_in1"]." ".$info["time_in1"]);
				// 					$hours_in = $seconds / 60 / 60;
				// 					$fields = "date_out2='".$timelogs['work_date']."', time_out2='".$timelogs['work_time']."'";
				// 					if( $hours_in < 24 ){
				// 						updateTimelogPairRec($con, $fields, $info['id']);
				// 					}elseif( $hours_out > 1 ){
				// 						insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 					}
				// 				}elseif( !empty( $info["time_in2"] ) && empty( $info["time_out2"] ) ){
				// 					$seconds =  strtotime($timelogs['work_date']." ".$timelogs['work_time']) - strtotime($info["date_in2"]." ".$info["time_in2"]);
				// 					$hours = $seconds / 60 / 60;
				// 					$fields = "date_out2='".$timelogs['work_date']."', time_out2='".$timelogs['work_time']."'";
				// 					if( $hours > 0 ){
				// 						updateTimelogPairRec($con, $fields, $info['id']);
				// 					}
				// 				}elseif( empty( $info["time_in2"] ) && !empty( $info["time_out2"] ) ){
				// 					insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 				}
				// 			}
				// 		}
				// 	}
				// }else{
				// 	//get tbltimelogpair data
				// 	$info = getTimelogPairRec($con, $idacct, $row['work_date'], $batchid, $machid);
				// 	//$previnfo = getTimelogPairRec($con, $idacct, date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $row['work_date'] ) ) )), $batchid, $machid);
				// 	if( $row['time_type']==$ain ){
				// 		if( empty( $info["time_in1"] ) && !empty( $info["time_out1"] ) ){
				// 			$seconds = strtotime($info["date_out1"]." ".$info["time_out1"]) - strtotime($timelogs['work_date']." ".$timelogs['work_time']);
				// 			$hours = $seconds / 60 / 60;
							
				// 			$fields ="";
				// 			if( !recExistsPrevDay( $con, $idacct, date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $row['work_date'] ) ) )), $timelogs['work_date'], $timelogs['work_time'] ) ){
				// 				if( empty($info["time_in2"]) ){
				// 					$fields = "date_in2='".$timelogs['work_date']."', time_in2='".$timelogs['work_time']."'";							
				// 				}
				// 			}else{
				// 				$fields = "date_in1='".$timelogs['work_date']."', time_in1='".$timelogs['work_time']."'";
				// 				if( $hours < 0 ){
				// 					$fields = $fields . ",date_out1=NULL,time_out1=NULL";
				// 				}
				// 			}
				// 			if( !empty($fields) ){
				// 				updateTimelogPairRec($con, $fields, $info['id']);
				// 			}
				// 		}elseif( empty( $info["time_in1"] ) && empty( $info["time_out1"] ) ){
				// 			$fields = "date_in1='".$timelogs['work_date']."', time_in1='".$timelogs['work_time']."'";
				// 			updateTimelogPairRec($con, $fields, $info['id']);
				// 		}elseif( !empty( $info["time_in1"] ) && !empty( $info["time_out1"] ) ){
				// 			if( empty( $info["time_in2"] ) && !empty( $info["time_out2"] ) ){
				// 				$seconds = strtotime($info["date_out2"]." ".$info["time_out2"]) - strtotime($timelogs['work_date']." ".$timelogs['work_time']);
				// 				$hours = $seconds / 60 / 60;
				// 				$fields = "date_in2='".$timelogs['work_date']."', time_in2='".$timelogs['work_time']."'";
				// 				if( $hours < 0 ){
				// 					$fields = $fields . ",date_out2=NULL,time_out2=NULL";
				// 				}
				// 				updateTimelogPairRec($con, $fields, $info['id']);
				// 			}elseif( empty( $info["time_in2"] ) && empty( $info["time_out2"] ) ){
				// 				$fields = "date_in2='".$timelogs['work_date']."', time_in2='".$timelogs['work_time']."'";
				// 				updateTimelogPairRec($con, $fields, $info['id']);
				// 			}
				// 		}
				// 	}elseif( $row['time_type']==$aout ){
				// 		if( empty( $info["time_in1"] ) && empty( $info["time_out1"] ) ){
				// 			$fields = "date_out1='".$timelogs['work_date']."', time_out1='".$timelogs['work_time']."'";
				// 			updateTimelogPairRec($con, $fields, $info['id']);
				// 		}elseif( !empty( $info["time_in1"] ) && empty( $info["time_out1"] ) ){
				// 			$seconds =  strtotime($timelogs['work_date']." ".$timelogs['work_time']) - strtotime($info["date_in1"]." ".$info["time_in1"]);
				// 			$hours = $seconds / 60 / 60;
				// 			$fields = "date_out1='".$timelogs['work_date']."', time_out1='".$timelogs['work_time']."'";
				// 			if( $hours > 0 && $hours < 24 ){
				// 				updateTimelogPairRec($con, $fields, $info['id']);
				// 			}elseif( $hours > 24 ){
				// 				insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 			}
				// 		}elseif( empty( $info["time_in1"] ) && !empty( $info["time_out1"] ) ){
				// 			if( (empty( $info["time_in2"] ) && empty( $info["time_out2"] )) || !empty( $info["time_out2"] ) ){
				// 				insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 			}elseif(!empty( $info["time_in2"] ) && empty( $info["time_out2"] )){
				// 				$fields = "date_out2='".$timelogs['work_date']."', time_out2='".$timelogs['work_time']."'";
				// 				if( $info["date_in2"] == $timelogs['work_date'] ){
				// 					$fields = "date_in1='".$info["date_in2"]."', time_in1='".$info["time_in2"]."', date_out1='".$timelogs['work_date']."', time_out1='".$timelogs['work_time']."'";
				// 					$fields = $fields . ",date_in2=NULL,time_in2=NULL,date_out2=NULL,time_out2=NULL";
				// 				}
				// 				updateTimelogPairRec($con, $fields, $info['id']);
				// 			}
				// 		}elseif( !empty( $info["time_in1"] ) && !empty( $info["time_out1"] ) ){
				// 			if( empty( $info["time_in2"] ) && empty( $info["time_out2"] ) ){
				// 				$seconds =  strtotime($timelogs['work_date']." ".$timelogs['work_time']) - strtotime($info["date_out1"]." ".$info["time_out1"]);
				// 				$hours_out = $seconds / 60 / 60;
				// 				$seconds =  strtotime($timelogs['work_date']." ".$timelogs['work_time']) - strtotime($info["date_in1"]." ".$info["time_in1"]);
				// 				$hours_in = $seconds / 60 / 60;
				// 				$fields = "date_out2='".$timelogs['work_date']."', time_out2='".$timelogs['work_time']."'";
				// 				if( $hours_in < 24 ){
				// 					//updateTimelogPairRec($con, $fields, $info['id']);
				// 				}elseif( $hours_out > 1 ){
				// 					//insertTimelogPairRec($con, $timelogs, $ain, $aout);
				// 				}
				// 			}elseif( !empty( $info["time_in2"] ) && empty( $info["time_out2"] ) ){
				// 				$seconds =  strtotime($timelogs['work_date']." ".$timelogs['work_time']) - strtotime($info["date_in2"]." ".$info["time_in2"]);
				// 				$hours = $seconds / 60 / 60;
				// 				$fields = "date_out2='".$timelogs['work_date']."', time_out2='".$timelogs['work_time']."'";
				// 				if( $hours > 0 ){
				// 					updateTimelogPairRec($con, $fields, $info['id']);
				// 				}
				// 			}
				// 		}
				// 	}
				// }
			}
		}
	}
}

//Delete on tbltimelogs not in specified dates
// $Qry3           = new Query();
// $Qry3->table    = "tbltimelogs";
// if( $ids== 0 ){
// 	$Qry3->fields   = "work_date NOT BETWEEN '".$sdate."' AND '".$fdate."' ";
// }else{
// 	$Qry3->fields   = "idunit NOT IN (".$ids.") OR (work_date NOT BETWEEN '".$sdate."' AND '".$fdate."') ";
// }
// $checke 		= $Qry3->exe_DELETE($con);
// if( $checke ){
// 	$Qry3           = new Query();
// 	$Qry3->table    = "tbltimelogs";
// 	$Qry3->selected = "idconf='".$machine_data[0]['id']."'";
// 	$Qry3->fields   = "id>0";
// 	$checke 		= $Qry3->exe_UPDATE($con);	
// 	$return = json_encode(array('status'=>'success','q'=>$Qry3->fields));
// }else{
// 	$return = json_encode(array('status'=>'error'));
// }

print $return;
mysqli_close($con);

function insertTimelogPairRec( $con, $row, $ain, $aout ){
	$Qry3           = new Query();
	$Qry3->table    = "tbltimelogpair";
	$Qry3->selected = "batchid,machid,work_date,idshift,idacct,idunit,sdate,fdate,create_stamp";
	$Qry3->fields   = "'".$row['batchid']."','".$row['machid']."','".$row['work_date']."','".$row['idshift']."', '".$row['idacct']."', '".$row['idunit']."', '".$row['sdate']."', '".$row['fdate']."', '".SysDatePadLeft()." ".SysTime()."'";
	if( $row['type'] == $ain ){
		$Qry3->selected = $Qry3->selected . ", date_in1, time_in1";
		$Qry3->fields   = $Qry3->fields   . ",'".$row['work_date']."','".$row['work_time']."'";
	}elseif( $row['type'] == $aout ){
		$Qry3->selected = $Qry3->selected . ", date_out1, time_out1";
		$Qry3->fields   = $Qry3->fields   . ",'".$row['work_date']."','".$row['work_time']."'";
	}
	$checke = $Qry3->exe_INSERT($con);
	return $checke;
}

function updateTimelogPairRec( $con, $fields, $id ){
	$Qry3           = new Query();
	$Qry3->table    = "tbltimelogpair";
	$Qry3->selected = $fields;
	$Qry3->fields   = "id='".$id."'";
	$checke = $Qry3->exe_UPDATE($con);
	return $checke;
}

function insertToTimeLogs($con, $arr_data){	
	$txt_acct = trim($arr_data[0]);
    $txt_date = trim($arr_data[1]);
    $txt_time = trim($arr_data[2]);
	$txt_type = trim($arr_data[3]);
	$manual_cebu = 1;
	if( !empty( $txt_acct ) ){
		$Qry3           = new Query();
		$Qry3->table    = "tbltimelogs";
		$Qry3->selected = "idconf,acct_id,idunit,work_time,time_type";
		$Qry3->fields   = "'".$manual_cebu."',
						   '".$txt_acct."',
						   '".getEmpAcctDept($con, $txt_acct)."',
						   '".$txt_time."',
						   '".$txt_type."'";
						   
		if( !empty( $txt_date ) ){
			$arr = explode("-",$txt_date);
			//$txt_date = $arr[2]."-".$arr[0]."-".$arr[1];
			$Qry3->selected = $Qry3->selected." ,work_date";
			$Qry3->fields   = $Qry3->fields  .",'".$txt_date."'";
		}				   
						   
		$checke = $Qry3->exe_INSERT($con);

		if($checke){
			return  json_encode(array("status"=>"success"));
		}else{
			return  json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
		}
	}else{
		return  json_encode(array('status'=>'error'));
	}
}

function truncateSheet($con){
	$QryTrunc      		= new Query();
	$QryTrunc->table	= "tbltimelogs";
	$checkTrucn			= $QryTrunc->exe_TRUNCATE($con);
	return $checkTrucn;
}

?>