<?php
//
// NRDP Config File
//
// Copyright (c) 2010-2017 - Nagios Enterprises, LLC.
// License: Nagios Open Software License <http://www.nagios.com/legal/licenses>
//
// An array of one or more tokens that are valid for this NRDP install
// a client request must contain a valid token in order for the NRDP to response or honor the request
// NOTE: Tokens are just alphanumeric strings - make them hard to guess!
$cfg['authorized_tokens'] = array();

// By default, all authorized tokens are allowed to submit any
// external command (unless it's disable below)
// This is a deny mapping in the form of COMMAND => TOKEN or TOKENS
// You can specify a whole command, or use * as a wildcard
// Or you can specify 'all' to stop any token from using any external command
// the tokens specified can either be a string with 1 token, or an array of 1 or more tokens
//
// $cfg['external_commands_deny_tokens'] = array(
//    "ACKNOWLEDGE_HOST_PROBLEM" => array("mysecrettoken", "myothertoken"),
//    "ACKNOWLEDGE_SVC_PROBLEM" => "mysecrettoken",
//    "all" => array("mysecrettoken", "myothertoken"),
//    "ACKNOWLEDGE_*" => "mysecrettoken",
//    "*_HOST_*" => array("mysecrettoken", "myothertoken"),
// );
//
$cfg['external_commands_deny_tokens'] = array();

// Do we require that HTTPS be used to access NRDP?
// set this value to 'false' to disable HTTPS requirement
$cfg["require_https"] = false;

// Do we require that basic authentication be used to access NRDP?
// set this value to 'false' to disable basic auth requirement 
$cfg["require_basic_auth"] = false;

// What basic authentication users are allowed to access NRDP?
// comment this variable out to allow all authenticated users access to the NRDP
$cfg["valid_basic_auth_users"] = array();
    
// The name of the system group that has write permissions to the external command file
// this group is also used to set file permissions when writing bulk commands or passive check results
// NOTE: both the Apache and Nagios users must be a member of this group
$cfg["nagios_command_group"] = "nagcmd";

// Full path to Nagios external command file
$cfg["command_file"] = "/usr/local/nagios/var/rw/nagios.cmd";

// Full path to check results spool directory
$cfg["check_results_dir"] = "/usr/local/nagios/var/spool/checkresults";

// Should we allow external commands? Set to true or false (Boolean, not a string)
$cfg["disable_external_commands"] = false;

// Allows Nagios XI to send old check results directly into NDO if configured
$cfg["allow_old_results"] = true;

// Enable debug logging
$cfg["debug"] = false;

// Where should the logs go?
$cfg["debug_log"] = "/usr/local/nrdp/server/debug.log";

// Temp directory for Nagios XI
$cfg["tmp_dir"] = "/usr/local/nagiosxi/tmp";
