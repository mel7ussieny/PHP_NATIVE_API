<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function

include_once 'ActiveLetter.php';





use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

//Create an instance; passing `true` enables exceptions

function sendMail($email,$name, $token){
    
    $mail = new PHPMailer(true);


    try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.sendgrid.net';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'apikey';                     //SMTP username
        $mail->Password   = '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';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->SMTPSecure = 'tls';
        // $mail->SMTPDebug = 2;
    
    
        //Recipients
        $mail->setFrom('admin@webika.org', 'Webika');   
        $mail->addAddress($email);
    
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Verify Email Address for Webika';
        $mail->Body    = letter($name, $token);
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    
        $mail->send();
    
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}






?>