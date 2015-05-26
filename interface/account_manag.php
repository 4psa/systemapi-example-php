<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Account Management Page: add customers on service provider, organization, user or extension level
 */

/* the class that prepares the soapClient */
include_once('plib/SoapClient_4psa.php'); 

/* require config file */
include_once('config/config.php');

/* require language pack */
include_once('language/en-US/interface.php');

/* require countries data */
include_once('plib/countries.php');

/* require format xml functions */
include_once('plib/misc.php');

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

/* retain the last SOAP request/response for adding an account */
$xml_add_request = '';
$xml_add_response = '';


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
$user_type_selection = array(SERVICEPROVIDER_TYPE, ORGANIZATION_TYPE, USER_TYPE, EXTENSION_TYPE);

/* create SOAP client */
$client = createSoapClient();
/* get interface and phone languages */
$interface_lang_list = $client->GetInterfaceLang();
$interface_lang = array();
if (!is_soap_fault($interface_lang_list)) {
	$lang_result = $interface_lang_list->interfaceLang;
	if (is_array($lang_result) && !empty($lang_result)) {
		/* multiple record response */
		foreach($lang_result as $key => $val) {
			/*check default language trigger */
			if (strpos($val->code, '(-1)') >0) {
				$val->code = -1;
			}
			$interface_lang[$val->code] = $val->name;
		}
	} elseif (is_object($lang_result)) {
		/*check default language trigger */
		if (strpos($val->code, '(-1)') >0) {
			$val->code = -1;
		}		
		$interface_lang[$lang_result->code] = $lang_result->name;
	}
} else {
	error_log('PHP SOAP Client : SOAP fault error: faultcode '.$interface_lang_list->faultcode.', faultstring: '.$interface_lang_list->faultstring);
}

$phone_lang_list = $client->GetPhoneLang();
$phone_lang = array();
if (!is_soap_fault($phone_lang_list)) {
	$lang_result = $phone_lang_list->phoneLang;
	if (is_array($lang_result) && !empty($lang_result)) {
		/* multiple record response */
		foreach($lang_result as $key => $val) {
			if (strpos($val->code, '(-1)') >0) {
				$val->code = -1;
			}				
			$phone_lang[$val->code] = $val->name;
		}
	} elseif (is_object($lang_result)) {
		if (strpos($val->code, '(-1)') >0) {
			$val->code = -1;
		}			
		$phone_lang[$lang_result->code] = $lang_result->name;
	}
}

$did_selection = false;
$location = 'did_selection.php';

/* these are the fields used for editing a service provider's PL */
$serviceprovider_edit_fields = array('organizationsMax', 'usersMax', 'extensionsMax');

/* form data has been sent, format SOAP object */

if (!empty($_POST)) {
	$_POST['region'] = $_POST['region_select'];
	/* build the object with posted data */	
	$posted = $_POST;
	$user_data = new stdClass();
	
	foreach ($posted as $key => $val) {
		$user_data->$key = $val;
	}
	
	/* setup the billing plan and the tpl from the config file */
	if (isset($template_id)) {
		$user_data->templateID = $template_id;
	}
	if (isset($billing_plan_id) && $billing_plan_id != '') {
		$user_data->chargingPlanID = $billing_plan_id;
	}

	if ($account_type == 'choose') {
		if (isset($posted['acc_type'])) {
			/* get the value of the account selection field */
			$account = $posted['acc_type'];
		} else {
			$account = 'serviceprovider';
		}
	} else {
                
		/* get the value from config.php */
		$account = $account_type;
        $posted['acc_type'] = $account_type;
	}
	/* set implicit extension type: phone terminal for extension account creation */
	if ($account == EXTENSION_TYPE) {
		$user_data->extensionType = 'term';
	}
	/* setup parent id for account user and send request;
	backup the posted data in case of SOAP fault: data is mentained in the form to be easily modified */	
	switch ($account) {
		case SERVICEPROVIDER_TYPE : {
        	$user = new stdClass();
			$user = $user_data;
			$result = $client->AddServiceProvider($user);
			$xml_add_request = $client->__getLastRequest();
			$xml_add_response = $client->__getLastResponse();
					
			if (is_soap_fault($result)) {
				$backup = $posted;
			} 
			break;
		}
		case ORGANIZATION_TYPE : {
			$user_data->parentID = $serviceprovider_id;
			$user = new stdClass();
			$user = $user_data;
			$result = $client->AddOrganization($user);
			$xml_add_request = $client->__getLastRequest();
			$xml_add_response = $client->__getLastResponse();
						
			if (is_soap_fault($result)) {
				$backup = $posted;
			}
			break;
		}
		case USER_TYPE: {
			$user_data->parentID = $organization_id;
			$user = new stdClass();
			$user = $user_data;
			$result = $client->AddUser($user);
			
			$xml_add_request = $client->__getLastRequest();
			$xml_add_response = $client->__getLastResponse();
			
			if (is_soap_fault($result)) {
				$backup = $posted;
			}  else {
				/* adding public number is possible for an user */
				$did_selection = true;
				$location = 'did_selection.php?id='.$result->ID;
			}
			break;
		}
		case EXTENSION_TYPE : {
			$user_data->parentID = $user_id;
			$user = new stdClass();
			$user = $user_data;
			$result = $client->AddExtension($user);
			
			$xml_add_request = $client->__getLastRequest();
			$xml_add_response = $client->__getLastResponse();
			
			if (is_soap_fault($result)) {
				$backup = $posted;
			} 
			break;
		}
	}
	
} else {
	$backup=0;
}

/* get regions and timezones for the saved country in backup */
if (!empty($backup)) {
	$current_country = $backup['country'];
	
	/* get the regions */
	$data = new stdClass();
	$data->code = $backup['country'];
	$regions_list = $client->getRegions($data);
    $backup_regions = $regions_list->region;
	
	/* get the timezones */
	$data = new stdClass();
	$data->code = $backup['country'];
	$timezones_list = $client->getTimezone($data);
    $backup_timezones = $timezones_list->timezone;
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
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<table class="info_table">
			    <tr><td><?php echo str_replace('{required}', "<span class='required'>*</span>", $msg_arr['required_fields']);?></td></tr>
			    <tr><td><?php echo $msg_arr['validation_info'];?></td></tr>
			</table>

			<!-- display information on extension type only if account type is extension -->
			<div id="ext_type_info" <?php if ($account_type == EXTENSION_TYPE) {?>style="display:block"<?php } else {?>style="display:none"<?php } ?> >
				<table class="ext_info_table">
					<tr><td><?php echo $msg_arr['extension_type'];?></td></tr>
				</table>
			</div>

			<!-- display information on public phone number selection after an user has been added -->
			<div id="ext_type_info" <?php if ($did_selection) {?>style="display:block"<?php } else {?>style="display:none"<?php } ?> >
				<table class="user_info_table">
					<tr><td><span class='required'><?php echo $msg_arr['public_no_info'];?></span></td></tr>
				</table>
			</div>
			
			<!-- display account selection field only if $account_type = 'choose' in config.php file -->
			<?php if ($account_type == 'choose') { ?>
			<div class="block_title"><?php echo $msg_arr['choose_acc_type'];?></div>
			<table class="form_table">
				<tr>
					<td class="label"><?php echo $msg_arr['account_type'];?></td>
					<td>
						<select name="acc_type" id="acc_type" onChange="displayAccount(this.value)">
						<?php
						foreach($user_type_selection as $key => $val) {
							$selected = '';
							if ((!isset($backup['acc_type']) && $val == SERVICEPROVIDER_TYPE) || ($backup['acc_type'] == $val)) {
								$selected = 'selected';
							}
						    echo "<option value='" .$val. "' " .$selected. ">" .$val. "</option>";
						}
						?>
						</select>
					</td>
				</tr>
			</table>
			<?php } ?>

			<!-- display extension number field only if account type is extension -->
			<div id="ext_setup" <?php if ((!isset($backup['acc_type']) && $account_type == EXTENSION_TYPE) || (isset($backup['acc_type']) && $backup['acc_type'] == EXTENSION_TYPE)) {?>style="display:block"<?php } else {?>style="display:none"<?php } ?>>
				<div class="block_title"><?php echo $msg_arr['info_ext_setup'];?></div>
				<table class="form_table">
					<tr>
						<td class="label"><?php echo $msg_arr['number'];?>&nbsp;<span class="required">*</span></td>
						<td><input type="text" class="text_long" name="extensionNo" id="extensionNo" value="<?php if (isset($backup)) { echo $backup['extensionNo']; }?>"></td>
					</tr>
					<tr>
						<td class="label"><?php echo $msg_arr['label'];?></td>
						<td><input type="text" class="text_long" name="label" id="label" value="<?php if (isset($backup)) { echo $backup['extensionNo']; }?>"></td>
					</tr>				
					<tr>
						<td class="label"><?php echo $msg_arr['password_auto'];?></td>
						<td><input class=checkbox type="checkbox" name="passwordAuto" id="passwordAuto" value="1" onclick="javascript:switchPassword(this.checked);"></td>
					</tr>				
					<tr>
						<td class="label"><?php echo $msg_arr['password'];?>&nbsp;<span class="required">*</span></td>
						<td><input type="password" class="text_long" name="password" id="password" value="<?php if (isset($backup)) { echo $backup['password']; }?>">&nbsp;<?php echo $msg_arr['passwd_info'];?></td>
					</tr>
				
				</table>
			</div>

			<!-- display form title relative to the account type defined -->
			<div id="serviceprovider_form" <?php if ((!isset($backup['acc_type']) && ($account_type == 'choose' || $account_type == SERVICEPROVIDER_TYPE)) || (isset($backup['acc_type']) && $backup['acc_type'] == SERVICEPROVIDER_TYPE)) {?>style="display:block"<?php } else {?>style="display:none"<?php } ?> >
				<div class="block_title"><?php echo $msg_arr['serviceprovider_form'];?></div>
			</div>
			<div id="organization_form" <?php if ((!isset($backup['acc_type']) && $account_type == ORGANIZATION_TYPE) || (isset($backup['acc_type']) && $backup['acc_type'] == ORGANIZATION_TYPE)) {?>style="display:block"<?php } else {?>style="display:none"<?php } ?>>
				<div class="block_title"><?php echo $msg_arr['organization_form'];?></div>
			</div>
			<div id="adduser_form" <?php if ((!isset($backup['acc_type']) && $account_type == USER_TYPE) || (isset($backup['acc_type']) && $backup['acc_type'] == USER_TYPE)) {?>style="display:block"<?php } else {?>style="display:none"<?php } ?>>
				<div class="block_title"><?php echo $msg_arr['adduser_form'];?></div>
			</div>
			<div id="extension_form" <?php if ((!isset($backup['acc_type']) && $account_type == EXTENSION_TYPE) || (isset($backup['acc_type']) && $backup['acc_type'] == EXTENSION_TYPE)) {?>style="display:block"<?php } else {?>style="display:none"<?php } ?>>
				<div class="block_title"><?php echo $msg_arr['extension_form'];?></div>
			</div>

			<table id = "account_type_form" class="form_table" <?php if ((!isset($backup['acc_type']) && $account_type == EXTENSION_TYPE) || (isset($backup['acc_type']) && $backup['acc_type'] == EXTENSION_TYPE)) {?>style="display:none"<?php } else {?>style="display:block"<?php } ?>>
				<tr>
					<td class="label"><?php echo $msg_arr['company'];?></td>
					<td><input type="text" class="text_long" name="company" id="company" value="<?php if (isset($backup)) { echo $backup['company']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['contact'];?>&nbsp;<span class="required">*</span></td>
					<td><input type="text" class="text_long" name="name" id="name" value="<?php if (isset($backup)) { echo $backup['name']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['login'];?>&nbsp;<span class="required">*</span></td>
					<td><input type="text" class="text_long" name="login" id="login" value="<?php if (isset($backup)) { echo $backup['login']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['password_auto'];?></td>
					<td><input class=checkbox type="checkbox" name="passwordAuto" id="passwordAuto" value="1" onclick="javascript:switchPassword(this.checked);"></td>
				</tr>				
				<tr>
					<td class="label"><?php echo $msg_arr['password'];?>&nbsp;<span class="required">*</span></td>
					<td><input type="password" class="text_long" name="password" id="password" value="<?php if (isset($backup)) { echo $backup['password']; }?>">&nbsp;<?php echo $msg_arr['passwd_info'];?></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['phone'];?></td>
					<td><input type="text" class="text_long" name="phone" id="phone" value="<?php if (isset($backup)) { echo $backup['phone']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['fax'];?></td>
					<td><input type="text" class="text_long" name="fax" id="fax" value="<?php if (isset($backup)) { echo $backup['fax']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['email'];?>&nbsp;</td>
					<td><input type="text" class="text_long" name="email" id="email" value="<?php if (isset($backup)) { echo $backup['email']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['address'];?></td>
					<td><input type="text" class="text_long" name="address" id="address" value="<?php if (isset($backup)) { echo $backup['address']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['city'];?></td>
					<td><input type="text" class="text_long" name="city" id="city" value="<?php if (isset($backup)) { echo $backup['city']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['pcode'];?></td>
					<td><input type="text" class="text_long" name="pcode" id="pcode" value="<?php if (isset($backup)) { echo $backup['pcode']; }?>"></td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['country'];?>&nbsp;<span class="required">*</span></td>
					<td>
					<select name="country" id="country" onChange="switchStateInput(this.value)">
					    <?php
							foreach($country as $key => $val) {
								$selected = '';
								if (isset($backup) && strtoupper($backup['country']) == strtoupper($key)) {
									$selected = 'selected';
								}
								echo "<option value='" . $key . "' " . $selected . ">" . $val . "</option>";
							}
					    ?>
					</select>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['region'];?>&nbsp;<span class="required">*</span></td>
					<td>
						<select name="region_select" id="region_select" <?php
							if (isset($backup)) {
								if (empty($backup_regions)) {
									echo 'disabled=""';
								} 
							}
							?>>
							<?php
								if (!empty($backup)) {
									if (!empty($backup_regions)) {
										foreach ($backup_regions as $region) {
											$selected = '';
											if (isset($backup) && $backup['region_select'] == $region->ID) {
												$selected = 'selected';
											}
											echo "<option value='" . $region->ID . "' "  . $selected . ">" . $region->name . "</option>";
										}
									} 
								} else {
									echo "<script type=\"text/javascript\"> getRegionsFromSelectedCountry(); </script>";
								}
						    ?>
					    </select>
					</td>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['timezone'];?>&nbsp;<span class="required">*</span></td>
					<td>
					<select name="timezone" id="timezone">
					<?php
						if (!empty($backup)) {
							$selected = "";
							$id = "";
							foreach($backup_timezones as $key => $value) {
								if (get_class($value) == 'stdClass') {
									if ($backup['timezone'] == $value->ID) {
										$selected = 'selected';
									}
									echo "<option value='" . $value->ID . "' " . $selected . ">" . $value->name . "</option>";
									$selected = "";
								} else {
									if ($key == 'ID') {
										$id = $value;
										if ($backup['timezone'] == $value) {
											$selected = 'selected';
										}
									} 
									if ($key == 'name') {
										echo "<option value='" . $id . "' " . $selected . ">" . $value . "</option>";
										$selected = "";
									}
								}
							}
						}
						?>
					</select>
				</tr>
				<tr>
					<td class="label"><?php echo $msg_arr['interface_lang'];?>&nbsp;<span class="required">*</span></td>
					<td>
					<select name="interfaceLang" id="InterfaceLang">
						<?php
							if (is_array($interface_lang) && !empty($interface_lang)) {
								foreach ($interface_lang as $key => $val) {
									$selected = '';
									if (isset($backup) && strtoupper($backup['interfaceLang']) == strtoupper($key)) {
										$selected = 'selected';
									}
									echo "<option value='" .$key. "' " .$selected. ">" .$val. "</option>";
								}
							} else {
								echo "<option value='-1'>Default</option>";
							}
							?>
					</select>
					</td>
				</tr>
				
			</table>

			
			
			<!-- display phone language field only if account type is extension -->
			<div id="phone_lang" <?php if ((!isset($backup['acc_type']) && $account_type == EXTENSION_TYPE) || (isset($backup['acc_type']) && $backup['acc_type'] == EXTENSION_TYPE)) {?>style="display:block"<?php } else {?>style="display:none"<?php } ?>>
				<table class="form_table">
					<tr>
						<td class="label"><?php echo $msg_arr['phone_lang'];?>&nbsp;<span class="required">*</span></td>
						<td>
						<select name="phoneLang" id="phoneLang">
							<?php
							if (is_array($phone_lang) && !empty($phone_lang)) {
								foreach ($phone_lang as $key => $val) {
									$selected = '';
									if (isset($backup) && strtoupper($backup['phoneLang']) == strtoupper($key)) {
										$selected = 'selected';
									}
									echo "<option value='" .$key. "' " .$selected. ">" .$val. "</option>";
								}
							} else {
								echo "<option value='-1'>Default</option>";
							}
							?>
						</select>
						</td>
					</tr>
				</table>
			</div>

			
			<table class="form_table">
				<tr>
					<td class="label"><?php echo $msg_arr['user_notes'];?></td>
					<td><textarea name="notes" id="notes" rows="5"><?php if (isset($backup)) { echo $backup['notes']; }?></textarea></td>
				</tr>
			</table>

			
			<table class="btn_table">
				<tr>
					<td><a href="index.php"><?php echo $msg_arr['home'];?></a></td>
					<?php if ($did_selection) { ?>
					<td align="right"  width="100%">
						<div class="someBtn">
							<button type="submit" name="bname_ok" onClick="document.location='<?php echo $location;?>';return false;"><?php echo $msg_arr['btn_next'];?></button>
						</div>
					</td>
					<?php } ?>
					<td align="right">
						<div class="someBtn">
							<button type="submit" name="bname_ok"><?php echo $msg_arr['btn_ok'];?></button>
						</div>
					</td>
				</tr>
				<tr>
				<td colspan = <?php if ($did_selection) { echo "3"; } else {echo "2";} ?>>
					<!-- display SOAP request and response -->
					<?php
					if (isset($result)) {
					?>
						<div class = "div_response">
						<div class = "div_header"><b><?php echo $msg_arr['lg_request'];?></b></div>
							<div class = "xml_response">
							<?php
							/* display the last SOAP request for adding an account: formatted for a better view */
							$xml_string = formatXml($xml_add_request);
							$xml_string = implode(">\n<", $xml_string);
							echo "<pre>" . htmlentities($xml_string, ENT_QUOTES, "UTF-8") . "</pre>";
							$xml_string = formatXml($xml_edit_request);
							$xml_string = implode(">\n<", $xml_string);
							echo "<pre>" . htmlentities($xml_string, ENT_QUOTES, "UTF-8") . "</pre>";
							
							?>
							</div>
						</div>
						<div class = "div_response">
						<div class = "div_header"><b><?php echo $msg_arr['lg_response'];?></b></div>
							<div class = "xml_response">
							<?php
							/* display the last SOAP response for adding an account: formatted for a better view */
							$xml_string = $xml_add_response;
							if (!empty($xml_string)) {
								if ($xml_string = formatXml($xml_add_response)) {
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
<?php
ob_end_flush();
restore_error_handler();
?>