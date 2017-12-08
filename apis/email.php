<?php
require 'PHPMailer/PHPMailerAutoload.php';
require 'PHPMailer/class.smtp.php';

$mail = new PHPMailer;
//$mail->SMTPDebug = 3;                               // Enable verbose debug output
$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Mailer		= 'smtp';
$mail->Host			= 's43.cyberspace.in';  // Specify main and backup SMTP servers
$mail->SMTPAuth		= true;                               // Enable SMTP authentication
$mail->Username		= 'customersupport@bookmyhouse.com';                 // SMTP username
$mail->Password		= 'Cs_!@#';                           // SMTP password
$mail->SMTPSecure	= 'ssl';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port			= 465;                                    // TCP port to connect to
//$mail->setFrom('customersupport@bookmyhouse.com', 'BMH');
$mail->setFrom('customersupport@bookmyhouse.com');
//$mail->setFromName ('BMH');
