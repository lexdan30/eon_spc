<?php

////activation email
$mail = new PHPMailer();
$mail->IsSMTP();
////Enable SMTP debugging
//// 0 = off (for production use)
//// 1 = client messages
//// 2 = client and server messages
////$mail->SMTPDebug  = 2;
////$mail->Debugoutput = 'html';
$mail->Host       = 'mail.N-Pax.com';
$mail->Port       = 366;
////$mail->SMTPSecure = 'tls';
$mail->SMTPAuth   = true;
$mail->Username   = "system.msg@N-Pax.com";
$mail->Password   = "Dels@n2022";
$mail->SetFrom('system.msg@N-Pax.com');
////$mail->AddReplyTo('Support@N-Pax.com');
?>

