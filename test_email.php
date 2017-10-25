<?php
require __DIR__.'/include/pageboot.php';
$smtp = array(
      'name'              => 'florae.it',
      'host'              => 'mail.florae.it',
      'port'              => 587, // Notice port change for TLS is 587
      'connection_class'  => 'login',
      'connection_config' => array(
          'username' => 'florae.it',
          'password' => 'kaeteece',
          'ssl'      => 'tls'
      )
    );

$mail = new \PHPMailer\PHPMailer\PHPMailer();
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->isSMTP();
$mail->SMTPDebug = 4;

$mail->Debugoutput = 'html';

$mail->Host = $smtp['host'];
$mail->Port = $smtp['port'];
$mail->SMTPSecure = $smtp['connection_config']['ssl'];

$mail->SMTPAuth = $smtp['connection_class']=='login';

$mail->Username = $smtp['connection_config']['username'];
$mail->Password = $smtp['connection_config']['password'];

$mail->setFrom('info@florae.it', 'Florae');
$mail->addAddress('info@florae.it', 'Florae');

$mail->Subject = 'Test';

$mail->msgHTML('Test');

$mail->AltBody = 'This is Test Email';

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}