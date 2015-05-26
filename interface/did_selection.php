<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Phone Number Selection Page: assign public phone numbers to created accounts
 */

/* require config file */
include_once('config/config.php');

/* require language pack */
include_once('language/en-US/interface.php');

/* require countries data */
include_once('plib/countries.php');

/* require format xml functions */
include_once('plib/misc.php');

/* the class that prepares the soapClient */
include_once('plib/SoapClient_4psa.php');

/* require constant definitions */
include_once('plib/definitions.inc.php');

/* disable cache */
ob_start();
ini_set('soap.wsdl_cache_enabled', 0);

/* initialize errors */
$errors = array('err_version' => 0, 'err_soap' => 0, 'err_voip' => 0);

$php_version = phpversion();
if (intval($php_version{0}) < 5 || (intval($php_version{0}) == 5 && intval($php_version{2}) < 1) || (intval($php_version{0}) == 5 && intval($php_version{2}) == 1 && intval($php_version{4}) < 2)) {
	$errors['err_version'] = 1;
}

if (!function_exists('is_soap_fault')) {
	$errors['err_soap'] = 1;
}

error_reporting(E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);

/**
 * Define error handling function: use the variables to debug if errors occur
 * @param <number> $errno - error level
 * @param <string> $errstr - the error message
 * @param <string> $errfile - the filename in which the error occurred
 * @param <number> $errline - the line number in which the error occurred
 * @param <array> $errcontext - an array containing every variable, and their values, in use when the error occurred
 * @return <void or 'false'>
 */
function myErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	if (strstr($errstr,'SoapClient::__construct')) {
		header("Location:index.php");
		restore_error_handler();
		exit(1);
	}
	return false;
}

/* set error handling function */
$error_handler = set_error_handler('myErrorHandler');

/* try class for SOAP client creation based on WSDL, with trace for debugging */
if (!class_exists('soapclient')) {
	$errors['err_soap'] = 1;
	myErrorHandler();
}

if (!empty($errors['err_soap']) || !empty($errors['err_version']) || !empty($errors['err_voip'])) {
	header("Location:index.php");
}

/* define user account types for account type selection */
$user_type_selection = array(SERVICEPROVIDER_TYPE, ORGANIZATION_TYPE, EXTENSION_TYPE);

/* create SOAP client */
$client = createSoapClient();

$user_id = new stdClass();
$user_id->userID= 0;
if (isset($_GET['id'])){
	$user_id->userID = $_GET['id'];
}

/* if no extension organization id is provided redirect to account management page */
if (empty($user_id->userID)) {
	header("Location:account_manag.php");
}

/* form data has been send, format SOAP object */
if (!empty($_POST)) {
	$assign_no = new stdClass();
	$assign_no->userID = $user_id->userID;
	$assign_no->didID = array();
	if (isset($_POST['public_no']) && is_array($_POST['public_no'])) {
		$assign_no->didID = $_POST['public_no'];
	}
	$result = $client->AssignPublicNo($assign_no);
	$last_request = $client->__getLastRequest();
	$last_response = $client->__getLastResponse();
}

/* public phone number selection */
$public_no = $client->GetPublicNoPoll($user_id);
$dids = array();
if (is_soap_fault($public_no)) {
	error_log('PHP SOAP Client : SOAP fault error: faultcode '.$public_no->faultcode.', faultstring: '.$public_no->faultstring);
} else {
	if (isset($public_no->publicNo->available)) {
		if (is_object($public_no->publicNo->available)) {
			$_dids = new stdClass();
			$_dids = $public_no->publicNo->available;
			$dids[$_dids->ID] = $_dids->channel.' ('.$_dids->externalNo.')';
		} else {
			foreach ($public_no->publicNo->available as $key => $val) {
				$dids[$val->ID] = $val->channel.' ('.$val->externalNo.')';
			}
		}
	}
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<link rel="stylesheet" href="skin/style.css">
		<script language="JavaScript" src="js/functions.js"></script>
		<title><?php echo $msg_arr['pg_index_title'];?></title>
	</head>
	<body>
	<div class="content">
		<h2><?php echo $msg_arr['pg_add_account'];?></h2>
		<div align="center">
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];?>">
			<table class="info_table">
			    <tr><td><?php echo str_replace('{required}', "<span class='required'>*</span>", $msg_arr['required_fields']);?></td></tr>
			    <tr><td><?php echo $msg_arr['validation_info'];?></td></tr>
			</table>

			<div class="block_title"><?php echo $msg_arr['assign_public_numbers'];?></div>
			<table class="form_table">
				<tr>
					<?php if (empty($user_id->userID)) { ?>
						<td align="middle"><span class='required'><?php echo $msg_arr['no_user_id'];?></span></td>
					<?php } elseif (is_array($dids) && !empty($dids)) { ?>
					<td class="label"><?php echo $msg_arr['public_numbers'];?></td>
					<td>
						<select name="public_no[]" name="public_no[]" multiple size="5">
						<?php
						foreach($dids as $key => $val) {
						    echo "<option value='" .$key. "'>" .$val. "</option>";
						}
						?>
						</select>
					</td>
					<?php } else { ?>
						<td align="middle"><span class='required'><?php echo $msg_arr['no_dids'];?></span></td>
					<?php } ?>
					<td/>
				</tr>
			</table>
			<table class="btn_table">
				<tr>
					<td><a href="index.php"><?php echo $msg_arr['home'];?></a></td>
					<td align="right">
					<div class="someBtn">
						<button type="submit" name="bname_ok"><?php echo $msg_arr['btn_ok'];?></button><span><?php echo $msg_arr['btn_ok'];?></span>
					</div>
					</td>
				</tr>
				<tr>
					<td colspan = "2">
					<!-- display SOAP request and response -->
					<?php					
					if (isset($result)) {
					?>
						<div class = "div_response">
						<div class = "div_header"><b><?php echo $msg_arr['lg_request'];?></b></div>
							<div class = "xml_response">
							<?php
							/* display last SOAP request: formatted for a better view */
							$xml_string = formatXml($last_request);
							$xml_string = implode(">\n<", $xml_string);
							echo "<pre>" . htmlentities($xml_string, ENT_QUOTES, "UTF-8") . "</pre>";
							?>
							</div>
						</div>
						<div class = "div_response">
						<div class = "div_header"><b><?php echo $msg_arr['lg_response'];?></b></div>
							<div class = "xml_response">
							<?php
							/* display last SOAP response: formatted for a better view */
							$xml_string = $last_response;
							if (!empty($xml_string)) {
								if ($xml_string = formatXml($last_response)) {
									if ($xml_string = splitFaultstring($xml_string, '600')) {
										$xml_string = implode(">\n<", $xml_string);
										echo "<pre>" . htmlentities($xml_string, ENT_QUOTES, "UTF-8") . "</pre>";
									} else {
										echo $msg_arr['no_answer'];
									}
								}
							} else {
								echo $msg_arr['no_answer'];
							}
							?>
							</div>
						</div>
					<?php
					}
					?>
					</td>
				</tr>
			</table>
			</form>
                </div>
	</div>
	</body>
</html>