<?php
require_once('../../../../classPhp.php'); 

$data  = array(
			"acct"			=> '',			
			"otdate"		=> '',
			"otsdate"		=> '',
			"start_time"	=> '',
			"otfdate"		=> '',
			"end_time"		=> '',
			"remarks"		=> ''
		);

$return = json_encode($data);
print $return;
?>