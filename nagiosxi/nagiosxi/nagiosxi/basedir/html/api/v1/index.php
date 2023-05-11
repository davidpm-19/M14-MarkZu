<?php
//
//  Nagios XI 5 API v1
//  Copyright (c) 2015-2020 Nagios Enterprises, LLC. All rights reserved.
//

require_once(dirname(__FILE__) . '/../../includes/common.inc.php');

db_connect_all();

// Define backend if we haven't already
if (!defined('BACKEND')) {
    define('BACKEND', true);
}

require_once(dirname(__FILE__) . '/../includes/utils.inc.php');
require_once(dirname(__FILE__) . '/../includes/utils-api.inc.php');

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : "Unknown";
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');

if (array_key_exists('outputtype', $_GET)) {
    if ($_GET['outputtype'] == 'xml') {
        $_GET['pretty'] = 0;
        header("Content-Type: application/xml");
    } else {
        header("Content-Type: application/json");
    }
} else {
    header("Content-Type: application/json");
}

try {
    if (empty($_REQUEST['request'])) {
        throw new Exception("No request was made");
    }
    $api = new API($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $api->process_api();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage())) . "\n";
}