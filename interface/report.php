<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Call Cost Report Page
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
/* check if version greater than 5.1.2 */
if (intval($php_version{0}) < 5 || 
	(intval($php_version{0}) == 5 && intval($php_version{2}) < 1) || 
	(intval($php_version{0}) == 5 && intval($php_version{2}) == 1 && intval($php_version{4}) < 2)) {
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
$error_handler = set_error_handler("myErrorHandler");

/* try class for SOAP client creation based on WSDL, with trace for debugging */
if (!class_exists('soapclient')) {
	$errors['err_soap'] = 1;
	myErrorHandler();
}

if (!empty($errors['err_soap']) || !empty($errors['err_version']) || !empty($errors['err_voip'])) {
	header("Location:index.php");
}


/* define user account types for account type selection */
$user_type_selection = array('--', SERVICEPROVIDER_TYPE, ORGANIZATION_TYPE, USER_TYPE);

/* create SOAP client */
$client = createSoapClient();

/* get serviceprovider account list */
$serviceproviders_list = $client->GetServiceProviders();
$serviceproviders = array();
if (is_soap_fault($serviceproviders_list)) {
	error_log('PHP SOAP Client : soap fault error');
	echo "PHP SOAP Client: SOAP Fault: (faultcode: ".$serviceproviders_list->faultcode.', faultstring: '.$serviceproviders_list->faultstring;
	$serviceproviders['err_msg'] = $serviceproviders_list->faultstring;
	$key = array_search(SERVICEPROVIDER_TYPE, $user_type_selection);
	if ($key !== false) {
            unset($user_type_selection[$key]);
	}
} else {
    $result = $serviceproviders_list->serviceProvider; 
	echo "<script language='JavaScript'>var serviceproviders_arr = new Array();";
	if (is_array($result) && !empty($result)) {
		/* more than one serviceprovider */
		foreach ($result as $key=>$val) {
			$serviceproviders[$val->ID] = $val->name;
			echo "arr_size = serviceproviders_arr.length;";
			echo "serviceproviders_arr[arr_size] = {'id':'".$val->ID."', 'name':'".addslashes($val->name)."'};";
		}
	} elseif (is_object($result)) {
		$serviceproviders[$result->id] = $result->name;
		echo "serviceproviders_arr[0] = {'id':'".$result->id."', 'name':'".addslashes($result->name)."'};";
	}
	echo "</script>";
}

/* get organization account list */
/* the oauth token is for an admin account, so it will fetch all the existent organizations */
$organizations_list = $client->GetOrganizations();
$organizations = array();

if (is_soap_fault($organizations_list)) {	
	
	$organizations['err_msg'] = $organizations_list->faultstring;	
	$key = array_search(ORGANIZATION_TYPE, $user_type_selection);
	if ($key !== false) {
		unset($user_type_selection[$key]);
	}
} else {
	$result = $organizations_list->organization;
	echo "<script language='JavaScript'>var organizations_arr = new Array;";
	if (is_array($result) && !empty($result)) {		
		/* more than one organization */
		$count = 0;	
		foreach ($result as $key=>$val) {
			$organizations[$val->ID] = $val->name;
			echo "organizations_arr[".$count."] = {'id':'".$val->ID."','name':'".addslashes($val->name)."'};";
			$count++;
		}
	} elseif (is_object($result)) {
		$organizations[$result->id] = $result->name;		
		echo "organizations_arr[0] = {'id':'".$result->id."','name':'".addslashes($result->name)."'};";		
	}	
	echo "</script>";
}

/* get user account list */
/* the oauth token is for an admin account, so it will fetch all the existent users */
$users_list = $client->GetUsers();
$users = array();

if (is_soap_fault($users_list)) {	
	$users['err_msg'] = $users_list->faultstring;	
	$key = array_search(USER_TYPE, $user_type_selection);
	if ($key !== false) {
		unset($user_type_selection[$key]);
	}
} else {
	$result = $users_list->user;
	echo "<script language='JavaScript'>var users_arr = new Array;";
	if (is_array($result) && !empty($result)) {		
		/* more than one organization */
		$count = 0;
		foreach ($result as $key=>$val) {
			$users[$val->ID] = $val->name;
			echo "users_arr[".$count."] = {'id':'".$val->ID."','name':'".addslashes($val->name)."'};";
			$count++;
		}
	} elseif (is_object($result)) {
		$users[$result->id] = $result->name;		
		echo "users_arr[0] = {'id':'".$result->id."','name':'".addslashes($result->name)."'};";				
	}	
	echo "</script>";
}



/* form data has been send, format SOAP object */
$backup = array();

if (!empty($_POST)) {
	/* build the object with posted data */
	$posted = $_POST;
	$obj = new stdClass();
	if (isset($posted['acc_type']) && !empty($posted['acc_type'])) {
		switch($posted['acc_type']) {
			case SERVICEPROVIDER_TYPE: {
				if (isset($posted['serviceprovider_id']) && is_numeric($posted['serviceprovider_id'])) {
					$obj->userID = $posted['serviceprovider_id'];
				}
				break;
			}
			case ORGANIZATION_TYPE: {
				if (isset($posted['organization_id']) && is_numeric($posted['organization_id'])) {
					$obj->userID = $posted['organization_id'];
				}
				break;
			}
			case USER_TYPE: {
				if (isset($posted['user_id']) && is_numeric($posted['user_id'])) {
					$obj->userID = $posted['user_id'];
				}
				break;
			}
			default: {
				/* implicitlly logged user defined in config file */
				break;
			}
		}
	}
	if (isset($posted['month']) && is_numeric($posted['month']) && !empty($posted['month'])) {
		$obj->month = $posted['month'];
	}
	if (isset($posted['year']) && is_numeric($posted['year']) && $posted['year'] >= 2005) {
		$obj->year = $posted['year'];
	}	
	$costs_report = $client->CallCosts($obj);
	if (is_soap_fault($costs_report)) {
		$backup = $posted;
		$report_error = $costs_report->faultstring; 
	} else {
		$report = get_object_vars($costs_report);
	}
}

/* define months */
$months = array(
	'0' => '--',
	'1' => 'January', 
	'2' => 'February',
	'3' => 'March',
	'4' => 'April',
	'5' => 'May',
	'6' => 'June',
	'7' => 'July',
	'8' => 'August',
	'9' => 'September',
	'10' => 'October',
	'11' => 'November',
	'12' => 'December',
);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<link rel="stylesheet" href="skin/style.css">
		<script language="JavaScript" src="js/functions.js"></script>
		<title><?php echo $msg_arr['pg_index_title'];?></title>
	</head>
	<body onLoad="document.getElementById('acc_type').value = '--'";>
		<div class="content">
			<h2><?php echo $msg_arr['pg_reporting'];?></h2>
			<div align="center">
			<form id="report_form" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" onSubmit="return false;">
				<table class="info_table">
			    	<tr><td><?php echo $msg_arr['reporting_info'];?></td></tr>
				</table>
				<div class="block_title"><?php echo $msg_arr['choose_report_search'];?></div>
				<table class="form_table">
				<tr>
					<td class="label"><?php echo $msg_arr['account_type'];?></td>
					<td>
						<select name="acc_type" id="acc_type" onChange="displayUsers(this)" onLoad="displayUsers(this)">						
						<?php
						foreach($user_type_selection as $key => $val) {
							$selected = '';
							if ($val == '--') {
								$selected = 'selected';
							}
							echo "<option value='" .$val. "' " .$selected. ">" .(isset($msg_arr[$val]) ? $msg_arr[$val] : $val). "</option>";
						}
						?>
						</select>
					</td>
				</tr>
				</table>
				
				<!-- serviceproviders list -->
				<div id="serviceprovider" style="display:none">
				<table class="form_table">
					<tr>
						<td class="label"><?php echo $msg_arr['serviceproviders'];?></td>
						<td>
							<?php
							if (isset($serviceproviders['err_msg'])) {
								echo $serviceproviders['err_msg'];
							} else {
							?>
							<select name="serviceprovider_id" id="serviceprovider_id" >
							<!-- populated from javascript -->
							</select>
							<?php }	?>
						</td>
					</tr>
				</table>
				</div>
				
				<!-- organizations list -->
				<div id="organization" style="display:none">
				<table class="form_table">
					<tr>
						<td class="label"><?php echo $msg_arr['organizations'];?></td>
						<td>
							<?php
							if (isset($organizations['err_msg'])) {
								echo $organizations['err_msg'];
							} else {
							?>
							<select name="organization_id" id="organization_id">
							<!-- populated from javascript -->
							</select>
							<?php }	?>
						</td>
					</tr>
				</table>
				</div>
				
				<!-- users list -->
				<div id="user" style="display:none">
				<table class="form_table">
					<tr>
						<td class="label"><?php echo $msg_arr['users'];?></td>
						<td>
							<?php
							if (isset($users['err_msg'])) {
								echo $users['err_msg'];
							} else {
							?>
							<select name="user_id" id="user_id" >
							<!-- populated from javascript -->
							</select>
							<?php }	?>
						</td>
					</tr>
				</table>
				</div>
				
				
				
				<table class="form_table">
				<tr class="form_table">
					<td class="label"><?php echo $msg_arr['month'];?></td>
					<td>
					<select id="month" name="month">
						<?php
						foreach ($months as $key => $val) {
							$selected = '';
							if ((!isset($backup['month']) && empty($key)) || (isset($backup['month']) && $backup['month'] == $key)) {
								$selected = 'selected';
							}
							echo "<option value='" .$key. "' " .$selected. ">" .$val. "</option>";
						}
						?>
					</select>
					</td>
				</tr>
				
				<tr class="form_table">
					<td class="label"><?php echo $msg_arr['year'];?></td>
					<td>
					<select name="year" id="year">
						<?php
							echo "<option value='-1'>--</option>";
							for ($i = 2005; $i <= date('Y'); $i++) {
								$selected = '';
								if (isset($backup['year']) && $backup['year'] == $i) {
									$selected = 'selected';
								} 
								echo "<option value='".$i."' ".$selected.">" .$i. "</option>";
							}
						?>
					</select> 
					</td>
				</tr>
			</table>
			
			<table class="btn_table">
				<tr>
					<td><a href="index.php"><?php echo $msg_arr['home'];?></a></td>
					<td align="right">
					<div class="someBtn">
						<button type="submit" name="bname_ok" onClick="validateUser()"><?php echo $msg_arr['btn_ok'];?></button><span><?php echo $msg_arr['btn_ok'];?></span>
					</div>
					</td>
				</tr>
				<tr>
					<td colspan = "2">
					<!-- display SOAP request and response -->
					<?php 
					if (isset($costs_report)) {
					?>
					<div class="div_response">
						<div class="div_header"><b>
						<?php 
						$report_search = '';
						if (isset($posted['acc_type']) && $posted['acc_type'] != '--') {
							if ($posted['acc_type'] == SERVICEPROVIDER_TYPE && isset($posted['serviceprovider_id']) && !empty($posted['serviceprovider_id']) && $posted['serviceprovider_id'] != 'all') {
								$report_search = ' '.$serviceproviders[$posted['serviceprovider_id']];
							} elseif ($posted['acc_type'] == ORGANIZATION_TYPE && isset($posted['organization_id']) && !empty($posted['organization_id']) && $posted['organization_id'] != 'all') {
								$report_search = ' '.$organizations[$posted['organization_id']]; 
							} elseif ($posted['acc_type'] == USER_TYPE && isset($posted['user_id']) && !empty($posted['user_id'])&& $posted['user_id'] != 'all') {
								$report_search = ' '.$users[$posted['user_id']]; 
							} 
						}
						if (isset($posted['month']) && !empty($posted['month'])) {
							if (!empty($report_search)) {
								$report_search .= ' -';
							}
							$report_search .= ' '.$months[$posted['month']];
						}
						if (isset($posted['year']) && $posted['year'] != -1) {
							
							$report_search .= ' '.$posted['year'];
						}
						echo $msg_arr['lg_report'].$report_search;
						?>
						</b></div>
					<?php
						if (isset($report)) {
					?>
					<table class="report_table">
						<tr>
							<td><?php echo $msg_arr['local_calls']?></td>
							<td><?php echo $report['localCall']?></td>
						</tr>
						<tr>
							<td><?php echo $msg_arr['elocal_calls']?></td>
							<td><?php echo $report['elocalCall']?></td>
						</tr>
						<tr>
							<td><?php echo $msg_arr['external_calls']?></td>
							<td><?php echo $report['externalCall']?></td>
						</tr>
						<tr>
							<td><?php echo $msg_arr['incoming_calls']?></td>
							<td><?php echo $report['incomingCall']?></td>
						</tr>
						<tr>
							<td><?php echo $msg_arr['outgoing_calls']?></td>
							<td><?php echo $report['outgoingCall']?></td>
						</tr>
						<tr>
							<td><?php echo $msg_arr['cost']?></td>
							<td>
							<?php 
								if (empty($report['cost'])) {
									$report['cost'] = 0;
								}
								echo $report['cost']
							?>
							</td>
						</tr>
						<tr>
							<td><?php echo $msg_arr['profit']?></td>
							<td>
							<?php 
								if (empty($report['profit'])) {
									$report['profit'] = 0;
								}
								echo $report['profit']
							?>
							</td>
						</tr>
					</table>
					<!-- some error has occured while trying to get call cost report -->
					<?php
						} elseif (isset($report_error)) {
							echo $report_error;
						} else {
							echo $msg_arr['report_error'];
						}
					?>
					</div>
					<?php 
					}
					?>
					<!--</div>-->
					</td>
				</tr>
			</table>
			</form>
                    </div>
		</div>
	</body>
</html>
<?php
ob_end_flush();
restore_error_handler();
?>