<?php
require_once('../../../../classPhp.php'); 

$data  = array(
			"acct"			=> '',
			"idleave"		=> '',
			"datefrom"		=> '',
			"dateto"		=> '',
			"remarks"		=> '',
			"picFile"		=> array(),
			"leave_dates"	=> array()
		);

$return = json_encode($data);
print $return;
?>