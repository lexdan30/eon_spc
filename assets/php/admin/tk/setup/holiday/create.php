<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$municipality = !empty($param->info->municipality) ? $param->info->municipality : 0;

if(!empty($param->accountid)){
	if(!empty($param->info->name)){	
		if( !empty($param->info->date) && $param->info->date != "0000-00-00" && $param->info->date != "" && $param->info->date != " " ){	
			if(!empty($param->info->idtype)){		
				$param->info->name  = strtoupper(strtolower(str_replace("'","",$param->info->name))); 		
				if( checkHolidayName($con,$param->info->name, '',$param->info->date) ){
					$return = json_encode(array("status"=>"exists1"));
					print $return;
					mysqli_close($con);
					return;
				}
				$Qry3           = new Query();
				$Qry3->table    = "tblholidays";
				$Qry3->selected = "name,date,idtype,regcode,provcode,munid";
				$Qry3->fields   = "'".ucwords(strtolower($param->info->name),' ')."',
								   '".$param->info->date."',
								   '".$param->info->idtype."',
								  NULLIF('".$param->info->regions."',''),
								  NULLIF('".$param->info->provinces."',''),
								  NULLIF('".$municipality."','0')";
				$checke = $Qry3->exe_INSERT($con);
				if($checke){
					$return = json_encode(array("status"=>"success"));
				}else{
					$return = json_encode(array('status'=>'error'));
				}				
			}else{
				$return = json_encode(array('status'=>'idtype'));
			}
		}else{
			$return = json_encode(array('status'=>'date'));
		}
	}else{
		$return = json_encode(array('status'=>'name'));
	}
}else{
	 $return = json_encode(array('status'=>'notloggedin'));
}

// DELIMITER $$

// USE `2hrisdbic`$$

// DROP VIEW IF EXISTS `vw_dataholidays`$$

// CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_dataholidays` AS (
// SELECT
//   `a`.`id`      AS `id`,
//   `a`.`name`    AS `name`,
//   `a`.`date`    AS `date`,
//   `a`.`idtype`  AS `idtype`,
//   `c`.`regDesc` AS `idregion`,
//   `c`.`provCode` AS `idprovince`,
//   `c`.`citymunCode` AS `idmunicipality`,
//   IF((`a`.`munid` IS NOT NULL),CONCAT(`c`.`citymunDesc`,', ',`d`.`provDesc`),'National') AS `location`,
//   `b`.`type`    AS `type`,
//   `b`.`alias`   AS `ot_type`
// FROM (((`tblholidays` `a`
//     LEFT JOIN `tblmunicipality` `c`
//       ON ((`a`.`munid` = `c`.`id`)))
//     LEFT JOIN `tblprovince` `d`
// 	ON((`c`.`provCode` = `d`.`provCode`)))
//    JOIN `tblholidaytype` `b`
//      ON ((`a`.`idtype` = `b`.`id`))))$$

// DELIMITER ;

print $return;
mysqli_close($con);
?>