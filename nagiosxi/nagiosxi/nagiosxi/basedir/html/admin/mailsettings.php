<?php
//
// Mail settings for Nagios XI sent emails
// Copyright (c) 2008-2020 Nagios Enterprises, LLC. All rights reserved.
//

require_once(dirname(__FILE__) . '/../includes/common.inc.php');

// Initialization stuff
pre_init();
init_session();

// Grab GET or POST variables and check pre-reqs
grab_request_vars();
check_prereqs();
check_authentication();


// Only admins can access this page
if (is_admin() == false) {
    echo _("You are not authorized to access this feature. Contact your system administrator for more information, or to obtain access to this feature.");
    exit();
}


route_request();


function route_request()
{
    global $request;

    if (isset($request['update'])) {
        do_update_settings();
    } else {
        show_settings();
    }
}


function show_settings()
{
    global $cfg;

    // Get defaults
    $mailmethod = get_option("mail_method");
    if ($mailmethod == "") {
        $mailmethod = "sendmail";
    }

    $fromaddress = get_option("mail_from_address", sprintf("%s <root@localhost>", get_product_name()));

    $smtphost = get_option("smtp_host");
    $smtpport = get_option("smtp_port");
    $smtpusername = get_option("smtp_username");
    $smtppassword = get_option("smtp_password");
    $smtpsecurity = get_option("smtp_security");
    $debug = get_option("php_sendmail_debug");

    if ($smtpsecurity == "") {
        $smtpsecurity = "none";
    }

    $xisys = $cfg['root_dir'] . '/var/xi-sys.cfg';
    $ini = parse_ini_file($xisys);
    if ($ini['distro'] == "Debian") {
        $exta = _("To continue, configure with the following command as root (or using sudo) to configure the system").": <code>dpkg-reconfigure exim4-config</code>";
    } else if ($ini['distro'] == "Ubuntu") {
        $exta = _("To continue, configure with the following command as root (or using sudo) to configure the system").": <code>dpkg-reconfigure postfix</code>";
    }

    // Get variables submitted to us
    $mailmethod = grab_request_var("mailmethod", $mailmethod);
    $fromaddress = grab_request_var("fromaddress", $fromaddress);
    $smtphost = grab_request_var("smtphost", $smtphost);
    $smtpport = grab_request_var("smtpport", $smtpport);
    $smtpusername = grab_request_var("smtpusername", $smtpusername);
    $smtppassword = grab_request_var("smtppassword", $smtppassword);
    $smtpsecurity = grab_request_var("smtpsecurity", $smtpsecurity);

    // Inbound settings
    $mail_inbound_process = grab_request_var("mail_inbound_process", get_option("mail_inbound_process", 0));
    $mail_inbound_replyto = grab_request_var("mail_inbound_replyto", get_option("mail_inbound_replyto"));
    $mail_inbound_process_time = grab_request_var("mail_inbound_process_time", get_option("mail_inbound_process_time", 2));
    $mail_inbound_type = grab_request_var("mail_inbound_type", get_option("mail_inbound_type", "imap"));
    $mail_inbound_host = grab_request_var("mail_inbound_host", get_option("mail_inbound_host"));
    $mail_inbound_port = grab_request_var("mail_inbound_port", get_option("mail_inbound_port"));
    $mail_inbound_user = grab_request_var("mail_inbound_user", get_option("mail_inbound_user"));
    $mail_inbound_pass = grab_request_var("mail_inbound_pass", decrypt_data(get_option("mail_inbound_pass")));
    $mail_inbound_encryption = grab_request_var("mail_inbound_encryption", get_option("mail_inbound_encryption", "none"));
    $mail_inbound_validate = grab_request_var("mail_inbound_validate", get_option("mail_inbound_validate", 1));

    do_page_start(array("page_title" => _('Email Settings')), true);
?>

    <script type="text/javascript">
    $(document).ready(function() {
        $('input[name="mailmethod"]').change(function() {
            var method = $(this).val();
            if (method == 'sendmail') {
                $('.alert-sendmail').show();
                $('.smtp-settings').hide();
            } else {
                $('.alert-sendmail').hide();
                $('.smtp-settings').show();
            }
        });
    });
    </script>
    
    <h1><?php echo _('Email Settings'); ?></h1>

    <p><?php echo sprintf(_('Modify the settings used by your %s system for sending email alerts and informational messages.'), get_product_name()); ?></p>
    <p style="margin: 10px 0;"><a href="testemail.php" class="btn btn-sm btn-info"><i class="fa fa-paper-plane"></i> <?php echo _("Send a Test Email"); ?></a></p>

    <form id="manageMailSettingsForm" method="post">
        <?php echo get_nagios_session_protector(); ?>
        <input type="hidden" name="update" value="1">
        <input type="hidden" value="outbound" id="tab_hash" name="tab_hash">

        <div id="tabs" class="hide">

            <ul>
                <li><a href="#outbound"><i class="fa fa-paper-plane"></i> <?php echo _('Outbound'); ?></a></li>
                <li><a href="#inbound"><i class="fa fa-inbox"></i> <?php echo _('Inbound'); ?></a></li>
            </ul>

            <div id="outbound">

                <p class="alert alert-info" style="margin: 10px 0;"><strong><?php echo _('Note'); ?>:</strong> <?php echo _('Mail messages may fail to be delivered if your XI server does not have a valid DNS name. For more information, read'); ?> <a href="https://assets.nagios.com/downloads/nagiosxi/docs/Understanding-Email-Sending-In-Nagios-XI.pdf" target="_blank"><?php echo _('Understanding Email Sending in Nagios XI') ?></a>.</p>

                <h5 class="ul"><?php echo _('Outbound Mail Settings'); ?></h5>

                <table class="table table-condensed table-no-border table-auto-width">
                    <tr>
                        <td class="vt">
                            <label><?php echo _('Send From'); ?>:</label>
                        </td>
                        <td>
                            <input name="fromaddress" type="text" class="textfield form-control" value="<?php echo encode_form_val($fromaddress); ?>" size="40">
                        </td>
                    </tr>
                    <tr>
                        <td class="vt">
                            <label><?php echo _('Send Method'); ?>:</label>
                        </td>
                        <td>
                            <div class="radio" style="margin: 0;">
                                <label>
                                    <input name="mailmethod" type="radio" value="sendmail" <?php echo is_checked($mailmethod, "sendmail"); ?>>Sendmail
                                </label>
                            </div>
                            <div class="radio" style="margin: 0;">
                                <label>
                                    <input name="mailmethod" type="radio" value="smtp" <?php echo is_checked($mailmethod, "smtp"); ?>>SMTP
                                </label>
                            </div>
                            <?php if ($ini['distro'] == 'Debian' || $ini['distro'] == 'Ubuntu') { ?>
                            <div class="alert alert-sendmail alert-info" style="margin: 10px 0 0 0; <?php if ($mailmethod == 'smtp') { echo "display: none;"; } ?>">
                                <b><?php echo _('Note'); ?>:</b> <?php echo _('On some systems, sendmail may not be configured to send emails outside of localhost. We highly recommend using SMTP configuration.'); ?><br>
                                <?php echo $extra; ?>
                            </div>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label><?php echo _('Logging'); ?>:</label>
                        </td>
                        <td class="checkbox">
                            <label>
                                <input type="checkbox" class="checkbox" id="debug" name="debug" <?php echo is_checked($debug, 1); ?>><?php echo _('Enable logging of mail sent with the internal mail component (PHPMailer) <b>') . get_tmp_dir() . '/phpmailer.log</b>'; ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <div class="smtp-settings" <?php if ($mailmethod != 'smtp') { echo 'style="display: none;"'; } ?>>

                    <h5 class="ul"><?php echo _('SMTP Settings'); ?></h5>

                    <table class="table table-condensed table-no-border table-auto-width">
                        <tr>
                            <td>
                                <label><?php echo _('Host'); ?>:</label>
                            </td>
                            <td>
                                <input name="smtphost" type="text" class="textfield form-control" value="<?php echo encode_form_val($smtphost); ?>" size="40">
                                <i class="fa fa-question-circle pop" data-content="<?php echo _('You can set up a failover/backup mail server by using a semi-colon (;) to define multiple SMTP hosts.'); ?><br><br><?php echo _('Example'); ?>:<br>smtp@test.com;smtp2@test.com"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo _('Port'); ?>:</label>
                            </td>
                            <td>
                                <input name="smtpport" type="text" class="textfield form-control" value="<?php echo encode_form_val($smtpport); ?>" size="4">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo _('Username'); ?>:</label>
                            </td>
                            <td>
                                <input name="smtpusername" type="text" class="textfield form-control" value="<?php echo encode_form_val($smtpusername); ?>" size="20">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo _('Password'); ?>:</label>
                            </td>
                            <td>
                                <input name="smtppassword" type="password" class="textfield form-control" value="<?php echo encode_form_val($smtppassword); ?>" size="20" <?php echo sensitive_field_autocomplete(); ?>>
                                <button type="button" style="vertical-align: top;" class="btn btn-sm btn-default tt-bind btn-show-password" title="<?php echo _("Show password"); ?>"><i class="fa fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="vt">
                                <label><?php echo _('Security'); ?>:</label>
                            </td>
                            <td>
                                <div class="radio" style="margin: 0;">
                                    <label>
                                        <input name="smtpsecurity" type="radio" value="none" <?php echo is_checked($smtpsecurity, "none"); ?>><?php echo _("None"); ?>
                                    </label>
                                </div>
                                <div class="radio" style="margin: 0;">
                                    <label>
                                        <input name="smtpsecurity" type="radio" value="tls" <?php echo is_checked($smtpsecurity, "tls"); ?>>TLS
                                    </label>
                                </div>
                                <div class="radio" style="margin: 0;">
                                    <label>
                                        <input name="smtpsecurity" type="radio" value="ssl" <?php echo is_checked($smtpsecurity, "ssl"); ?>>SSL
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </table>

                </div>

            </div>

            <div id="inbound">

                <div class="checkbox" style="margin: 10px 0;">
                    <label style="margin-right: 4px;">
                        <input type="checkbox" name="mail_inbound_process" value="1" <?php echo is_checked($mail_inbound_process, 1); ?>>
                        <?php echo _('Enable incoming email processing'); ?>
                    </label>
                    <i class="fa fa-question-circle pop" data-content="<?php echo _('Processing incoming mail allows you to set up an email address that will be used as a reply-to address for alerts.').'<br><br>'._('Recipients can respond to emails with commands to acknowledge, schedule downtime, and more.'); ?>"></i>
                </div>

                <h5 class="ul"><?php echo _('Inbound Mail Settings'); ?></h5>

                <p>
                    <?php echo _('Enter the email address for the inbox below to be parsed for notification replies. Nagios XI will automatically add this as the reply-to address for notification emails it sends.'); ?><br><b><?php echo _('This email address/inbox should not be used by anyone except Nagios XI. Emails will be deleted after processing.'); ?></b> <a target="_blank" href="https://assets.nagios.com/downloads/nagiosxi/docs/Inbound-Email-Commands-for-Nagios-XI.pdf"><?php echo _('View all commands you can send to Nagios XI'); ?></a>.
                </p>

                <table class="table table-condensed table-no-border table-auto-width">
                    <tr>
                        <td class="vt">
                            <label><?php echo _('Reply-to Address'); ?>:</label>
                        </td>
                        <td>
                            <input name="mail_inbound_replyto" type="text" class="textfield form-control" value="<?php echo encode_form_val($mail_inbound_replyto); ?>" size="40">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label><?php echo _('Check Emails Every'); ?>:</label>
                        </td>
                        <td>
                            <div class="input-group" style="display: inline-table; width: 110px;">
                                <input type="text" name="mail_inbound_process_time" class="form-control" value="<?php echo $mail_inbound_process_time; ?>">
                                <label class="input-group-addon"><?php echo _('minutes'); ?></label>
                            </div>
                            <i class="fa fa-question-circle tt-bind" style="font-size: 14px; vertical-align: top; margin: 8px 0 0 8px;" title="<?php echo _('The amount of time between connecting to the email server and parsing them. Lowest setting is 1.'); ?>"></i>
                        </td>
                    </tr>
                </table>

                <h5 class="ul"><?php echo _('Inbox Connection Settings'); ?></h5>

                <table class="table table-condensed table-no-border table-auto-width">
                    <tr>
                        <td class="vt">
                            <label><?php echo _('Connection'); ?>:</label>
                        </td>
                        <td>
                            <div class="radio" style="margin: 0;">
                                <label>
                                    <input name="mail_inbound_type" type="radio" value="imap" <?php echo is_checked($mail_inbound_type, "imap"); ?>><?php echo _("IMAP"); ?>
                                </label>
                            </div>
                            <div class="radio" style="margin: 0;">
                                <label>
                                    <input name="mail_inbound_type" type="radio" value="pop3" <?php echo is_checked($mail_inbound_type, "pop3"); ?>>POP3
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label><?php echo _('Host'); ?>:</label>
                        </td>
                        <td>
                            <input name="mail_inbound_host" type="text" class="textfield form-control" value="<?php echo encode_form_val($mail_inbound_host); ?>" size="40">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label><?php echo _('Port'); ?>:</label>
                        </td>
                        <td>
                            <input name="mail_inbound_port" type="text" class="textfield form-control" value="<?php echo encode_form_val($mail_inbound_port); ?>" placeholder="143" size="4">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label><?php echo _('Username'); ?>:</label>
                        </td>
                        <td>
                            <input name="mail_inbound_user" type="text" class="textfield form-control" value="<?php echo encode_form_val($mail_inbound_user); ?>" size="20">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label><?php echo _('Password'); ?>:</label>
                        </td>
                        <td>
                            <input name="mail_inbound_pass" type="password" class="textfield form-control" value="<?php echo encode_form_val($mail_inbound_pass); ?>" size="20" <?php echo sensitive_field_autocomplete(); ?>>
                            <button type="button" style="vertical-align: top;" class="btn btn-sm btn-default tt-bind btn-show-password" title="<?php echo _("Show password"); ?>"><i class="fa fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label><?php echo _('Encryption'); ?>:</label>
                        </td>
                        <td>
                            <div class="radio" style="margin: 0;">
                                <label>
                                    <input name="mail_inbound_encryption" type="radio" value="none" <?php echo is_checked($mail_inbound_encryption, "none"); ?>><?php echo _("None"); ?>
                                </label>
                            </div>
                            <div class="radio" style="margin: 0;">
                                <label>
                                    <input name="mail_inbound_encryption" type="radio" value="tls" <?php echo is_checked($mail_inbound_encryption, "tls"); ?>>TLS
                                </label>
                            </div>
                            <div class="radio" style="margin: 0;">
                                <label>
                                    <input name="mail_inbound_encryption" type="radio" value="ssl" <?php echo is_checked($mail_inbound_encryption, "ssl"); ?>>SSL
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label></label>
                        </td>
                        <td class="checkbox">
                            <label>
                                <input type="checkbox" name="mail_inbound_validate" value="1" <?php echo is_checked($mail_inbound_validate, 1); ?>>
                                <?php echo _('Validate SSL certificate of mail server host'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <?php
                            // Check if we have settings saved
                            $can_test = true;
                            if (empty($mail_inbound_host) || empty($mail_inbound_user) || empty($mail_inbound_pass)) {
                                $can_test = false;
                            }
                            ?>
                            <button type="submit" class="submitbutton btn btn-sm btn-info" <?php if (!$can_test) { echo 'disabled'; } ?> name="testimapconn" id="testimapconn">
                                <i class="fa fa-plug fa-l"></i> <?php echo _('Test Connection'); ?>
                            </button>
                            <?php if (!$can_test) { echo '<div style="padding-top: 4px;">'._("Note: Must fill out and save all settings before testing.").'</div>'; } ?>
                        </td>
                    </tr>
                </table>

            </div>

        </div>

        <div id="formButtons">
            <button type="submit" class="submitbutton btn btn-sm btn-primary" name="updateButton" id="updateButton"><?php echo _('Update Settings'); ?></button>
            <button type="submit" class="submitbutton btn btn-sm btn-default" name="cancelButton" id="cancelButton"><?php echo _('Cancel'); ?></button>
        </div>

    </form>

    <?php
    do_page_end(true);
    exit();
}


function do_update_settings()
{
    global $request;

    // Test IMAP connection
    if (isset($request["testimapconn"])) {
        $errors = mail_test_settings();
        if (empty($errors)) {
            flash_message(_("Connection was successful."), FLASH_MSG_SUCCESS);
        } else {
            $error_html = "";
            foreach ($errors as $e) {
                $error_html .= $e."<br>";
            }
            $opts = array('details' => $error_html);
            flash_message(_("Could not connect with the inbound settings given."), FLASH_MSG_ERROR, $opts);
        }
        header("Location: mailsettings.php");
        return;
    }

    // user pressed the cancel button
    if (isset($request["cancelButton"])){
        header("Location: main.php");
		return;
	}

    // check session
    check_nagios_session_protector();

    $errmsg = array();
    $errors = 0;

    // Get outbound variables
    $mailmethod = grab_request_var("mailmethod", "sendmail");
    $fromaddress = grab_request_var("fromaddress", "");
    $smtphost = grab_request_var("smtphost", "");
    $smtpport = grab_request_var("smtpport", "");
    $smtpusername = grab_request_var("smtpusername", "");
    $smtppassword = grab_request_var("smtppassword", "");
    $smtpsecurity = grab_request_var("smtpsecurity", "");
    $debug = grab_request_var("debug", "");

    // Get inbound variables
    $mail_inbound_process = grab_request_var("mail_inbound_process", 0);
    $mail_inbound_replyto = grab_request_var("mail_inbound_replyto", "");
    $mail_inbound_process_time = intval(grab_request_var("mail_inbound_process_time", 2));
    $mail_inbound_type = grab_request_var("mail_inbound_type", "imap");
    $mail_inbound_host = grab_request_var("mail_inbound_host", "");
    $mail_inbound_port = grab_request_var("mail_inbound_port", "");
    $mail_inbound_user = grab_request_var("mail_inbound_user", "");
    $mail_inbound_pass = grab_request_var("mail_inbound_pass", "");
    $mail_inbound_encryption = grab_request_var("mail_inbound_encryption", "none");
    $mail_inbound_validate = grab_request_var("mail_inbound_validate", 0);

    // make sure we have requirements
    if (in_demo_mode()) {
        $errmsg[$errors++] = _("Changes are disabled while in demo mode.");
    }
    if (!have_value($fromaddress)) {
        $errmsg[$errors++] = _("No from address specified.");
    }
    if ($mailmethod == "smtp") {
        if (!have_value($smtphost))
            $errmsg[$errors++] = _("No SMTP host specified.");
        if (!have_value($smtpport))
            $errmsg[$errors++] = _("No SMTP port specified.");
    }

    // Force inbound process time to at least 1 minute
    if ($mail_inbound_process_time < 1) {
        $mail_inbound_process_time = 1;
    }

    if (!in_demo_mode()) {

        // Outbound settings
        set_option("mail_method", $mailmethod);
        set_option("mail_from_address", $fromaddress);
        set_option("smtp_host", $smtphost);
        set_option("smtp_port", $smtpport);
        set_option("smtp_username", $smtpusername);
        set_option("smtp_password", $smtppassword);
        set_option("smtp_security", $smtpsecurity);
        set_option("php_sendmail_debug", $debug);

        // Inbound settings
        set_option("mail_inbound_process", $mail_inbound_process);
        set_option("mail_inbound_replyto", $mail_inbound_replyto);
        set_option("mail_inbound_process_time", $mail_inbound_process_time);
        set_option("mail_inbound_type", $mail_inbound_type);
        set_option("mail_inbound_host", $mail_inbound_host);
        set_option("mail_inbound_port", $mail_inbound_port);
        set_option("mail_inbound_user", $mail_inbound_user);
        set_option("mail_inbound_pass", encrypt_data($mail_inbound_pass));
        set_option("mail_inbound_encryption", $mail_inbound_encryption);
        set_option("mail_inbound_validate", $mail_inbound_validate);

    }

    if ($errors > 0) {
        flash_message($errmsg, FLASH_MSG_ERROR);
        show_settings();
    }

    // Mark that settings were updated
    set_option("mail_settings_configured", 1);

    send_to_audit_log("Updated global mail settings", AUDITLOGTYPE_CHANGE);

    flash_message(_("Mail settings updated."));
    header('Location: mailsettings.php');
}
