<?php
//
//  Nagios Core Config Manager
//  Copyright (c) 2010-2021 Nagios Enterprises, LLC
//
//  File: command_test.php
//  Desc: Script that runs a test check command in the shell for a host/service and returns the value
//        out to the user in the user interface.
//

// Include the Nagios XI helper functions through the component helper file and initialize
// anything we will need to authenticate ourselves to the CCM
require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// Initialization stuff
pre_init();
init_session();

// Grab GET or POST variables and check pre-reqs
grab_request_vars();
check_prereqs();
check_authentication(false);

// Verify access
if (!user_can_access_ccm()) {
    die(_('You do not have access to this page.'));
}

// Set the location of the CCM root directory
define('BASEDIR', dirname(__FILE__).'/');
require_once('includes/constants.inc.php');
require_once('includes/session.inc.php');

// Check authentication and grab the token and command (there shouldn't be one
// but we check this anyway...)
$cmd = ccm_grab_request_var('cmd', '');
$token = ccm_grab_request_var('token', '');
if ($AUTH !== true) { $cmd = 'login'; }

// Verify that the command was submitted from the form and if it's not then the user will be
// routed to the login page if it's an illegal operation otherwise route the request for download
verify_token($cmd, $token);
route_request($cmd);

/**
 * Directs page navigation and input requests for command tests and verifies user authentication
 *
 * @param string $cmd requires a valid command to do anything, if auth it bad this will be '' or 'login'
 */
function route_request($cmd='')
{
    // Bail on no authentication
    if ($cmd == 'login') {
        header('Location: index.php?cmd=login');
        return;
    }

    $mode = ccm_grab_request_var('mode', '');
    switch ($mode)
    {
        case 'help':
            $plugin = escapeshellcmd(ccm_grab_request_var('plugin', ''));
            $hacks = array('&&', '../', 'cd /', ';', '\\'); 
            foreach ($hacks as $h) {
                if (strpos($h, $plugin)) { break; }
            }

            // Print plugin help output
            get_plugin_doc($plugin);
            break;

        case 'test':
            test_command(); 
            break; 
        
        default:
            // Don't do anything, page will just exit
            break; 
    }
}

/**
 * Cleans input variables and executes them from the command-line and returns output to browser
 */
function test_command()
{
    // check against the session if we are in nagios xi
    check_nagios_session_protector();
   
    global $cfg;
    global $ccm;

    // Get all necessary parameters for a check command
    $cid  = intval(ccm_grab_request_var('cid')); 
    $address = ccm_grab_request_var('address', ''); 
    $host = ccm_grab_array_var($_REQUEST, 'host', '');
    $arg1 = ccm_grab_array_var($_REQUEST, 'arg1', '');
    $arg2 = ccm_grab_array_var($_REQUEST, 'arg2', '');
    $arg3 = ccm_grab_array_var($_REQUEST, 'arg3', '');
    $arg4 = ccm_grab_array_var($_REQUEST, 'arg4', '');
    $arg5 = ccm_grab_array_var($_REQUEST, 'arg5', '');
    $arg6 = ccm_grab_array_var($_REQUEST, 'arg6', '');
    $arg7 = ccm_grab_array_var($_REQUEST, 'arg7', '');
    $arg8 = ccm_grab_array_var($_REQUEST, 'arg8', '');

    // Grab the command we were sent from the database
    $query = "SELECT `command_name`, `command_line` FROM tbl_command WHERE `id` = '$cid' && command_type = 1 LIMIT 1";
    $command = $ccm->db->query($query); 
    
    // If the command isn't in the database then fail now
    if (!isset($command[0]['command_name'])) {
        print "ERROR: Unable to locate the command in the database";
        exit(); 
    }

    // Create all the database variables and then replace all variables that need to be replaced
    // before running the actual command and echoing the output to the screen.
    $name = $command[0]['command_name'];
    $cmd_line = $command[0]['command_line'];
    $haystack = array($cfg['component_info']['nagioscore']['plugin_dir'], $address, $arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8);
    $needles = array('$USER1$', '$HOSTADDRESS$', '$ARG1$', '$ARG2$', '$ARG3$', '$ARG4$', '$ARG5$', '$ARG6$', '$ARG7$', '$ARG8$');
    
    $fullcommand = str_replace($needles, $haystack, $cmd_line);

    // break apart everything after an unescaped semi-colon
    $fullcommand = str_replace("\;", "%%%%%", $fullcommand);
    $fullcommand_array = explode(';', $fullcommand);
    $fullcommand = $fullcommand_array[0];
    $fullcommand = str_replace("%%%%%", "\;", $fullcommand);    

    // Grab the current value of $fullcommand and store it to display in the run check command results box in order to maintain obfuscation
    $displaycommand = $fullcommand;

    $fullcommand = nagiosccm_replace_user_macros($fullcommand);

    $id = submit_command(COMMAND_RUN_CHECK_CMD, $fullcommand);

    if ($id <= 0) {
        $output = _("Error submitting command.");
    }
    else {
        for ($x = 0; $x < 40; $x++) {
            usleep(500000);
            $args = array(
                "command_id" => $id
            );
            $xml = get_command_status_xml($args);
            if ($xml) {
                if ($xml->command[0]) {
                    if (intval($xml->command[0]->status_code) == 2) {
                        $output = $xml->command[0]->result;
                        break;
                    }
                }
            }
        }
    }

    $hn = (function_exists('gethostname')) ? gethostname() : php_uname('n');
    if (stripos('.', $hn)) {
        $hna = explode('.', $hn);
        $hn = $hna[0];
    }

    // $output is already encoded so we don't need to do encode_form_val()
    echo "<pre class='monospace-textarea'>[nagios@{$hn} ~]$ ".encode_form_val($displaycommand)."\n"; 
    print $output."\n";
    echo "</pre>";
}

/**
 * Executes plugin from the command-line with -h flag and prints output between pre tags
 *
 * @param string $plugin The plugin name
 */
function get_plugin_doc($plugin)
{
    global $cfg;
    $output = array();
    exec($cfg['component_info']['nagioscore']['plugin_dir'].'/'.$plugin.' -h', $output);
    print "<pre>";
    if (!empty($output)) {
        foreach ($output as $line) {
            print encode_form_val($line)."\n";
        }
    }
    print "</pre>";
}