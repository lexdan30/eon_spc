<?php
$data  = array(
			"alias"		=> '',
			"emp"		=> array('0','0'),
			"wdate"		=> array('0','0'),
			"wtime"		=> array('0','0'),
			"atype"		=> array('0','0'),
			"ain"		=> 'C/I',
			"bout"		=> 'C/1',
			"bin"		=> 'C/2',
			"aout"		=> 'C/O',
			"descript"	=> '',
			"note"		=> ''
		);

$return = json_encode($data);
print $return;
?>