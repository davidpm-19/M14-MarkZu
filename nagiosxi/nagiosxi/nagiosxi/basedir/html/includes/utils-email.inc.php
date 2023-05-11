<?php
//
// Copyright (c) 2008-2020 Nagios Enterprises, LLC. All rights reserved.
//

require_once(dirname(__FILE__) . '/phpmailer/PHPMailerAutoload.php');


////////////////////////////////////////////////////////////////////////
// EMAIL FUNCTIONS
////////////////////////////////////////////////////////////////////////


/**
 * Sends an email (HTML or text)
 *
 * @param   array   $opts       Array including: to, subject, and message
 * @param   string  $debug      The debug output
 * @param   string  $referer    Referrer to put in php mailer log
 * @param   bool    $txt        True to send message as text
 * @return  bool                True if email was sent
 * @throws  Exception 
 * @throws  phpmailerException
 */
function send_email($opts, &$debug = null, $referer = "", $txt = false)
{
    // Timestamp for phpmailer.log message
    $timestamp = date("m-d-Y H:i:s", time());

    // Make sure we have what we need
    if (!isset($opts["to"]) || !isset($opts["subject"]) || !isset($opts["message"])) {
        return false;
    }

    // Get mail options
    $mailmethod   = get_option("mail_method");
    $fromaddress  = get_option("mail_from_address", "root@localhost");
    $smtphost     = get_option("smtp_host");
    $smtpport     = get_option("smtp_port");
    $smtpusername = get_option("smtp_username");
    $smtppassword = get_option("smtp_password");
    $smtpsecurity = get_option("smtp_security");

    // Check PHPmailer debug set_option - "on"
    $debug_logging = get_option("php_sendmail_debug");

    // Generate a new instance and set to UTF-8 encoding
    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';

    // Use global from address instead of one specified by user
    $address_parts = parse_email_address($fromaddress);
    $mail->SetFrom($address_parts[0]["email"], $address_parts[0]["name"]);

    // Add reply-to based on whether or not we are processing inbound email
    if (get_option("mail_inbound_process", 0)) {

        $replyto = get_option("mail_inbound_replyto");

        if (!empty($replyto)) {
            $replyto = parse_email_address($replyto);
            $mail->AddReplyTo($replyto[0]["email"], $replyto[0]["name"]);
        }
    } else {
        $mail->AddReplyTo($address_parts[0]["email"], $address_parts[0]["name"]);
    }

    // To address(es)
    $addresses = parse_email_address($opts["to"]);

    foreach ($addresses as $address) {
        $mail->AddAddress($address["email"], $address["name"]);
    }

    // Set newlines to <br> if sending as HTML
    if (!$txt) {
        $opts["message"] = nl2br(stripcslashes($opts["message"]));
    }

    // Convert to UTF-8 if not already done so
    $mail->Subject   = mb_convert_encoding($opts["subject"], "UTF-8", "auto");
    $opts["message"] = mb_convert_encoding($opts["message"], "UTF-8", "auto");

    // Set body of message
    if ($txt) {
        $mail->Subject = $opts["subject"];
        $mail->Body    = $opts["message"];
        $mail->IsHTML(false);
    } else {
        $mail->MsgHTML($opts["message"]);
        $mail->IsHTML(true);
    }
    
    // For most clients expecting the Priority header:
    // 1 = High, 2 = Medium, 3 = Low
    if (isset($opts["high_priority"]) && $opts["high_priority"] == 1) {
        $mail->Priority = 1;

        // MS Outlook custom header (can be set to "Urgent" or "Highest" rather than "High")
        $mail->AddCustomHeader("X-MSMail-Priority", "High");
        $mail->AddCustomHeader("Importance", "High");
    }

    // See if there is an attachment and add to the mail
    if (isset($opts["attachment"])) {
        if (is_array($opts["attachment"])) {
            foreach ($opts["attachment"] as $aopt) {
                $mail->AddAttachment($aopt[0], $aopt[1]);
            }
        } else {
            $mail->AddAttachment($opts["attachment"]);
        }
    }

    // See if there are images to add to the email
    // If you want to use this -
    // path is the local path of the image (e.g. /usr/local/myimage.png)
    // cid is the content identification (you reference this in the image tag
    //     e.g. cid=THECID  >>  <img src="cid:THECID" />
    // name is what you want to name the image is in the attachment (e.g. image.png)
    // encoding is the type of encoding (e.g. base64)
    // type is the image type (e.g. image/png)
    // disposition is how to attach it (e.g. inline)
    if (isset($opts["images"])) {
        if (is_array($opts["images"])) {
            foreach ($opts["images"] as $img) {

                // each image needs to have at least
                // a path and a cid (to be able to embed it)
                if (empty($img["path"]) || empty($img["cid"])) {
                    continue;
                }

                $path        = grab_array_var($img, "path", "");
                $cid         = grab_array_var($img, "cid", "");
                $name        = grab_array_var($img, "name", "");
                $encoding    = grab_array_var($img, "encoding", "base64");
                $type        = grab_array_var($img, "type", "");
                $disposition = grab_array_var($img, "disposition", "inline");

                $ret = $mail->AddEmbeddedImage($path, $cid, $name, $encoding, $type, $disposition);

                if ($ret == false && $debug_logging == "on") {
                    $error_message = "[$timestamp] ERROR attaching image: \n";
                    $error_message .= print_r($img, true) . "\n\n";
                    file_put_contents(get_tmp_dir() . '/phpmailer.log', $error_message, FILE_APPEND);
                }
            }
        }
    }

    // Use SMTP
    if ($mailmethod == "smtp") {

        $debuginfo = "method=smtp";

        $mail->IsSMTP();
        $mail->Host = $smtphost;
        $mail->Port = intval($smtpport);

        $smtphost = str_replace(';', ',', $smtphost);
        $debuginfo .= ";host=$smtphost";
        $debuginfo .= ";port=$smtpport";

        // Use SMTP Auth
        if (!empty($smtpusername)) {
            $debuginfo .= ";smtpauth=true";
            $mail->SMTPAuth = true;
            $mail->Username = $smtpusername;
            $mail->Password = $smtppassword;
        }

        // Optionally use TLS or SSL
        if ($smtpsecurity == "tls") {
            $mail->SMTPSecure = "tls";
            $debuginfo .= ";security=tls";
        } else if ($smtpsecurity == "ssl") {
            $mail->SMTPSecure = "ssl";
            $debuginfo .= ";security=ssl";
        } else {
            $debuginfo .= ";security=none";
        }
    } else {
        $debuginfo = "method=sendmail";
    }

    // Add referer to the 
    if (empty($referer)) {
        $refer_string = ", Referer: Unknown";
    } else {
        $refer_string = ", Referer: " . $referer;
    }

    // Send the email and log it in phpmailer log if we have debug turned on
    $sent = $mail->Send();

    if (!$sent) {

        if (!isset($opts["debug"])) {
            $debug = "[" . $timestamp . "] " . $mail->ErrorInfo . " (" . $debuginfo . ")" . $refer_string;
        }
        if ($debug_logging == "on") {
            file_put_contents(get_tmp_dir() . '/phpmailer.log', $debug . "\n", FILE_APPEND);
        }

    } else {

        if (!isset($opts["debug"])) {
            $debug = "[" . $timestamp . "] Message sent! (" . $debuginfo . ")" . $refer_string;
        }
        if ($debug_logging == "on") {
            file_put_contents(get_tmp_dir() . '/phpmailer.log', $debug . "\n", FILE_APPEND);
        }
    }

    return $sent;
}


/**
 * Parses a comma separated list of email addresses and returns
 * a formatted array.
 *
 * @param   string  $a  Comma-separated list of emails
 * @return  array       Array of emails
 */
function parse_email_address($a)
{
    $results = array();
    $addresses = explode(",", $a);

    foreach ($addresses as $address) {
        $newa = array(
            "name" => "",
            "email" => "",
        );

        $parts = explode("<", $address);

        // Just the address
        if (count($parts) == 1) {
            $newa["email"] = trim($parts[0]);
        } else {
            $newa["name"] = trim($parts[0]);
            $parts = explode(">", $parts[1]);
            $newa["email"] = trim($parts[0]);
        }

        $results[] = $newa;
    }

    return $results;
}


/**
 * Test the imap connection and give back any errors it gets
 *
 * @return  array       Array of connection errors
 */
function mail_test_settings()
{
    $conn_string = mail_create_connection_string();
    $user = get_option('mail_inbound_user');
    $pass = decrypt_data(get_option('mail_inbound_pass'));

    // Connect to IMAP or POP3
    $conn = imap_open($conn_string."INBOX", $user, $pass, 0, 1, array('DISABLE_AUTHENTICATOR' => 'GSSAPI'));
    $errors = imap_errors();
    @imap_close($conn);

    return $errors;
}


/**
 * Open mail and grab unread message
 *
 * @return  array       Array of mail messages if any exist
 */
function mail_get_unseen_messages()
{
    $conn_string = mail_create_connection_string();
    $user = get_option('mail_inbound_user');
    $pass = decrypt_data(get_option('mail_inbound_pass'));

    // Connect to IMAP or POP3
    $conn = imap_open($conn_string."INBOX", $user, $pass, 0, 1, array('DISABLE_AUTHENTICATOR' => 'GSSAPI'));

    $messages = array();
    $unseen_messages = imap_search($conn, 'UNSEEN');

    // If failed search, close connection and return empty messages
    if ($unseen_messages !== false) {

        // Get messages if there are any
        $num_messages = count($unseen_messages);
        if ($num_messages > 0) {
            foreach ($unseen_messages as $i) {

                $structure = imap_fetchstructure($conn, $i);
                $header = imap_headerinfo($conn, $i);

                $messages[$i] = array(
                    'header' => $header
                );

                // Depending on structure (1 == MULTI-PART) get plain text body
                if ($structure->type == 1) {
                    $messages[$i]['body'] = imap_fetchbody($conn, $i, 1);
                } else {
                    $messages[$i]['body'] = imap_body($conn, $i);
                }

                // Remove message from inbox
                imap_delete($conn, $i);

            }
        }

    }

    // Close connection properly
    imap_expunge($conn);
    imap_close($conn);

    return $messages;
}


/**
 * Creates a connection string for IMAP/POP3 connections
 *
 * @return  string      IMAP/POP3 connection string
 */
function mail_create_connection_string()
{
    $host = get_option('mail_inbound_host');
    $port = get_option('mail_inbound_port', 143);
    $conn_type = get_option('mail_inbound_type', 'imap');
    $conn_sec = get_option('mail_inbound_encryption');
    $conn_validate = get_option('mail_inbound_validate', 1);
    $conn_debug = false;

    // Make connection string
    $conn_string = '{'.$host.':'.$port;

    // Add debug if we need to
    if ($conn_debug) {
        $conn_string .= '/debug';
    }

    // Set type
    if ($conn_type == 'imap') {
        $conn_string .= '/imap';
    } else if ($conn_type == 'pop3') {
        $conn_string .= '/pop3';
    }

    // Set encryption
    if (in_array($conn_sec, array('ssl', 'tls', 'notls'))) {
        $conn_string .= '/'.$conn_sec;
    }

    // Set validation of SSL certificate
    if (!$conn_validate) {
        $conn_string .= '/novalidate-cert';
    }

    $conn_string .= '}';

    return $conn_string;
}


/**
 * Generate the mail-processing hash at the bottom of
 * the notification message
 *
 * @param   string  $host       Host name
 * @param   string  $service    Service name (optional)
 * @return  string              Notification hash message
 */
function mail_notification_hash($host, $service = '')
{
    $data = array('host' => $host);
    if (!empty($service)) {
        $data['service'] = $service;
    }

    // Encode, encrypt, and encode for email
    $encoded = encrypt_data(json_encode($data));

    // Generate the actual text that will go in the email
    $text = "\n-----\n\n";
    $text .= _("To reply, keep the following data in your response email.")."\n\n";
    $text .= "##".$encoded."##";

    return $text;
}
