<?php 

    require 'phpmailer/PHPMailerAutoload.php';

    $mail = new PHPMailer;

    // $mail->SMTPDebug = 3;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = '<Specify main and backup SMTP servers>';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = '<SMTP username>';                 // SMTP username
    $mail->Password = '<SMTP password>';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = <The port to connect to>;                                    // TCP port to connect to

    $mail->From = '';	//Email that will send this message to your email';
    $mail->FromName = 'Mail service';//$_POST['name'];
    $mail->addAddress('<email you want to receive message>', '<Name of the email account>');     // Add a recipient      // Name is optional
    $mail->addReplyTo($_POST['email'], $_POST['name']);

    $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
    $mail->isHTML(true);                                  // Set email format to HTML

	//Set the subject of the email. Here is an example to table booking
    $mail->Subject = $_POST['name'] . ' sent you a message';
    $mail->Body    = '<p>' . $_POST['name'] . '</p>';
    $mail->Body    .= '<table>';
    $mail->Body    .= '<tbody>';

    $mail->Body    .= '<tr><td>';
    $mail->Body    .= 'Name: ' . $_POST['name'];
    $mail->Body    .= '</tr></td>';

    $mail->Body    .= '<tr><td>';
    $mail->Body    .= 'Email: ' . $_POST['email'];
    $mail->Body    .= '</tr></td>';
    
    $mail->Body    .= '<tr><td>';
    $mail->Body    .= 'Telephone: ' . $_POST['telephone'];
    $mail->Body    .= '</tr></td>';

    $mail->Body    .= '<tr><td>';
    $mail->Body    .= 'Message: ' . $_POST['message'];
    $mail->Body    .= '</tr></td>';

    $mail->Body    .= '</tbody>';
    $mail->Body    .= '</table>';
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if(!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'OK';
    }
?>
