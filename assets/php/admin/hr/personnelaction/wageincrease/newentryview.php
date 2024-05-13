<?php
require_once('../../../../classPhp.php'); 
$data = array(     
    "empid"                         => '',
    "effectivedate"                 => '',
    "actiontaken"                	=> "Wage Increase",
    "empname"                       => '',
    "currentdeptname"               => '',
    "currentdeptmanager"            => '',
    "currentimmediatesupervisor"    => '',
    "currentsection"                => '',
    "currentempstatus"              => '',
    "currentjobcode"                => '',
    "currentjoblevel"               => '',
    "currentpositiontitle"          => '',
    "currentpaygroup"               => '',
    "currentlabortype"              => '',
    "currentbasepay"                => '',
    "newbasepay"                    => '',
    "currentriceallowance"          => '',
    "newriceallowance"              => '',
    "currentclothingallowance"      => '',
    "newclothingallowance"          => '',
    "currentlaundryallowance"       => '',
    "newlaundryallowance"           => '',
    "currenttotalcashcomp"          => '',
    "newtotalcashcomp"              => '',
    "remarks"                       => '',
    "doc_job_desc"					=> '',
    "doc_perf_appr"					=> '',
    "doc_promotion"					=> '',
	"picFile"						=> array()
);
$return = json_encode($data);
print $return;
?>