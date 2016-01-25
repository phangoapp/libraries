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

class Emailer {

    /**
    * Simple method for send emails using SwiftMailer.
    *
    * A method used for send_mail using a smtp server. You can config this method with contants called SMTP_HOST, SMTP_USER and SMTP_PASS
    * You can send lists using bcc features, html text, attachments, etc...
    *
    * 
    * @param const  SMTP_HOST the host used for send the email
    * @param string $sender The email address used for send the email
    * @param string $email The email adress where the message is sended
    * @param string $subject The subject of email
    * @param string $message The content of email
    * @param string $content_type The type of email, values can be plain or html
    * @param array $arr_bcc An Array with emails sended using BCC
    * @param array $attachments A list of files to be sended with the email
    */

    static public function send_mail($sender, $email, $subject, $message, $content_type='plain', $arr_bcc=array(), $attachments=array())
    {

            
        if( defined('SMTP_HOST') && defined('SMTP_USER') && defined('SMTP_PASS') )
        {
        
            if(!defined('SMTP_PORT'))
            {
            
                define('SMTP_PORT', 25);
            
            }
        
            $transport = \Swift_SmtpTransport::newInstance(SMTP_HOST, SMTP_PORT)->setUsername(SMTP_USER)->setPassword(SMTP_PASS);
            
            if(defined('SMTP_ENCRIPTION'))
            {
            
                $transport->setEncryption(SMTP_ENCRIPTION);
            
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
        
        if(!defined('SMTP_SENDER'))
        {
        
            define('SMTP_SENDER', $sender);
        
        }
        
        $mail_set->setFrom(array(SMTP_SENDER => $sender));
        // Set the To addresses with an associative array
        $mail_set->setTo(array($email));
        // Give it a body
        $mail_set->setBody($message);
        
        $mail_set->setContentType('text/'.$content_type);
        
        if(count($arr_bcc)>0)
        {
        
            $mail_set->setBcc($arr_bcc);
            
        }
        
        $mail_set->setReplyTo(array(SMTP_SENDER));
        
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
        catch(Exception $e)
        {
            
            return 0;
        
        }
        
        foreach($failures as $email_fail)
        {
        
            if($email_fail==$email)
            {
            
                return 0;
            
            }
        
        }
        
        return 1;

    }
    
}

?>

