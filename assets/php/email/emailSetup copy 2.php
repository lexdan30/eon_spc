<?php
////activation email
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Host       = 'smtp.gmail.com'; 
$mail->Port       = 587;
//$mail->SMTPSecure = 'tls';
$mail->SMTPAuth   = true;
$mail->Username   = 'npax.iiot@gmail.com'; 
$mail->Password   = 'xdby kiom gdxf ctnu';//"iiot@npax2023"; //
$mail->SetFrom('npax.iiot@gmail.com');
//$mail->AddReplyTo('Support@N-Pax.com');


?>