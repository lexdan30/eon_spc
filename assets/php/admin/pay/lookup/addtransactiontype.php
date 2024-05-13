<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tbltransactiontype";
$Qry->selected = "`seq`,
                    `code`,
                    `pay_item`,
                    `flags`,
                    `abbrevation`, 
                    `classid`,
                    `adjusting`,
                    `ytdgroupings`,
                    `credit`, 
                    `debit`";
$Qry->fields   = "'".$param->add->seq."',
                    '".$param->add->code."', 
                    '".$param->add->payitem ."', 
                    '".$param->add->flags ."', 
                    '".$param->add->abbrevation ."', 
                    '".$param->add->class."',
                    '".$param->add->adjusting ."', 
                    '".$param->add->ytdgrouping ."', 
                    '".$param->add->credit."',
                    '".$param->add->debit ."'";                        
$rs = $Qry->exe_INSERT($con);

mysqli_close($con);
?>