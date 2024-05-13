<?php
require_once('../../../../classPhp.php'); 
$data = array(     
    "empid"                         => '',
    "effectivedate"                 => '',
    "actiontaken"                	=> "Lateral Transfer",
    "empname"                       => '',
    "currentdeptname"               => '',
    "newdeptname"                   => '',
    "currentdeptmanager"            => '',
    "newdeptmanager"                => '',
    "currentimmediatesupervisor"    => '',
    "newimmediatesupervisor"        => '',
    "currentsection"                => '',
    "newsection"                    => '',
    "currentempstatus"              => '',
    "newempstatus"                  => '',
    "currentjobcode"                => '',
    "newjobcode"                    => '',
    "currentjoblevel"               => '',
    "newjoblevel"                   => '',
    "currentpositiontitle"          => '',
    "newpositiontitle"              => '',
    "currentpaygroup"               => '',
    "newpaygroup"                   => '',
    "currentlabortype"              => '',
    "newlabortype"                  => '',
    "remarks"                       => '',
    "doc_job_desc"					=> '',
    "doc_perf_appr"					=> '',
    "doc_promotion"					=> '',
	"picFile"						=> array()
);
$return = json_encode($data);
print $return;
?>