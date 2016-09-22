<?php

/**
*
* @author  Antonio de la Rosa <webmaster@web-t-sys.com>
* @file
* @package PhaLibs
*
*
*/

namespace PhangoApp\PhaLibs;

//Now, we use swiftmailer.

/**
* Simple class for send emails
*
*/

class SendMail {
    
    public $txt_error='';
    
    public $smtp_sender='';
    
    public $smtp_host='';
    
    public $smtp_user='';
    
    public $smtp_pass='';
    
    public $smtp_port=25;
    
    public $smtp_encryption='';
    
    public function __construct($sender, $smtp_host='', $smtp_user='', $smtp_pass='', $smtp_port=25, $smtp_encryption='')
    {
        
        $this->sender=$sender;
        $this->smtp_host=$smtp_host;
        $this->smtp_user=$smtp_user;
        $this->smtp_pass=$smtp_pass;
        $this->smtp_port=$smtp_port;
        $this->smtp_encryption=$smtp_encryption;
        
    }

    /**
    * Simple method for send emails using SwiftMailer.
    *
    * A method used for send_mail using a smtp server. You can config this method with contants called $this->smtp_host, $this->smtp_user and SMTP_PASS
    * You can send lists using bcc features, html text, attachments, etc...
    *
    * 
    * @param const  $this->smtp_host the host used for send the email
    * @param string $sender The email address used for send the email
    * @param string $email The email adress where the message is sended
    * @param string $subject The subject of email
    * @param string $message The content of email
    * @param string $content_type The type of email, values can be plain or html
    * @param array $arr_bcc An Array with emails sended using BCC
    * @param array $attachments A list of files to be sended with the email
    */

    public function send($email, $subject, $message, $content_type='plain', $arr_bcc=array(), $attachments=array())
    {

        /*
        $mail = new PHPMailer;

        //$mail->SMTPDebug = 3;                               // Enable verbose debug output
        
        $mail->isSMTP();                                      // Set mailer to use SMTP
        
        $mail->CharSet = "UTF-8";
        
        if( defined('$this->smtp_host') && defined('$this->smtp_user') && defined('SMTP_PASS') )
        {
            
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
        
            $mail->Host = $this->smtp_host;  // Specify main and backup SMTP servers
        
            $mail->Username = $this->smtp_user;                 // SMTP username
            $mail->Password = SMTP_PASS;                           // SMTP password
            
            if(defined('SMTP_ENCRIPTION'))
            {
            
                $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            
            }
            
        }
        
        if(!defined('$this->smtp_port'))
        {
            
            define('$this->smtp_port', 25);
            
        }

        $mail->Port = $this->smtp_port;                                    // TCP port to connect to

        if(!defined('SMTP_SENDER'))
        {
        
            define('SMTP_SENDER', $sender);
        
        }

        $mail->setFrom(SMTP_SENDER);
        $mail->addAddress($email);     // Add a recipient
        
        
        foreach($arr_bcc as $email_bcc)
        {
        
            $mail->addBCC($email_bcc);

        }

        foreach($attachments as $attachment)
        {

            $mail->addAttachment($attachment);         // Add attachments
        
        }
        
        switch($content_type)
        {
        
            default:
        
                $mail->isHTML(false);                                  // Set email format to HTML
                
            break;
            
            case 'html':
            
                $mail->isHTML($true);                                  // Set email format to HTML
            
            break;
            
        }

        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = $message;

        if(!$mail->send()) 
        {
            
            Emailer::$txt_error=$mail->ErrorInfo;
            
            return false;
            
        } 
        else 
        {
            return true;
        }
        */
        
        if( $this->smtp_host!='' && $this->smtp_user!='' && $this->smtp_pass!='' )
        {
        
            $transport = \Swift_SmtpTransport::newInstance($this->smtp_host, $this->smtp_port)->setUsername($this->smtp_user)->setPassword($this->smtp_pass);
            
            if($this->smtp_encryption)
            {
            
                $transport->setEncryption($this->smtp_encryption);
            
            }
            
        }
        else
        {
        
            $transport = \Swift_SmtpTransport::newInstance();
        
        }
        
        //mailer
        
        $mailer = \Swift_Mailer::newInstance($transport);
        
        //message
        
        $mail_set = \Swift_Message::newInstance();
        
        $mail_set->setSubject($subject);
        // Set the From address with an associative array
        
        if(!defined($this->smtp_sender))
        {
        
            define($this->smtp_sender, $this->sender);
        
        }
        
        $mail_set->setFrom(array($this->sender));
        // Set the To addresses with an associative array
        $mail_set->setTo(array($email));
        // Give it a body
        $mail_set->setBody($message);
        
        $mail_set->setContentType('text/'.$content_type);
        
        if(count($arr_bcc)>0)
        {
        
            $mail_set->setBcc($arr_bcc);
            
        }
        
        $mail_set->setReplyTo(array($this->sender));
        
        // Optionally add any attachments
        
        foreach($attachments as $attachment)
        {
        
            $mail_set->attach(\Swift_Attachment::fromPath($attachment));
            
        }

        
        //echo $mailer->send($mail_set);
        
        $failures=array();
        
        try {
        
            $mailer->send($mail_set, $failures);
            
            return 1;
        }
        catch(\Exception $e)
        {
            
            Emailer::$txt_error=$e->getMessage();
            
            return 0;
        
        }
        
        foreach($failures as $email_fail)
        {
        
            if($email_fail==$email)
            {
            
                return 0;
            
            }
        
        }
        
        //Reset transport
        
        $transport->reset();
        
        return 1;

    }
    
}

?>

