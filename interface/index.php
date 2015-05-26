<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Main Page: check requirements for demo pages
 */

/* require config file */
include_once('config/config.php');

/* require language pack */
include_once('language/en-US/interface.php');

/* require file for getLocalSchemesLocation */
include_once('plib/misc.php');

/* the class that prepares the soapClient */
include_once('plib/SoapClient_4psa.php');

/* require constant definitions */
include_once('plib/definitions.inc.php');


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
 * Define error handling function: use variables to debug if errors occur
 * @global <type> $msg_arr - an array with messages defined in interface.php
 * @global <type> $errors - the errors array
 * @global <type> $php_version - php version defined in config.php
 * @param <number> $errno - error level
 * @param <string> $errstr - the error message
 * @param <string> $errfile - the filename in which the error occurred
 * @param <number> $errline - the line number in which the error occurred
 */
function myErrorHandler($errno, $errstr, $errfile, $errline) {
	global $msg_arr, $errors, $php_version;
		
	$error_index += 1;
	if (class_exists('soapclient')) {
		if (strstr($errstr,'SoapClient::__construct') || strstr($errstr,'SoapClient::SoapClient')) {
			$errors['err_voip'] = 1;
		}
	} else {
		$errors['err_soap'] = 1;
	}
	
	if(strpos($errstr, 'file_get_contents') !== false && strpos($errfile, 'plib/SoapClient_4psa.php') !== false){
		/* Schema not found */
		$errors['err_voip'] = 1;
	}

    if(strstr($errstr,'Permission denied')){
        $errors['err_voip_perm'] = 1;
        $errors['err_voip'] = 1;
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
		<div class="index_content">
			<h2><?php echo $msg_arr['pg_index'];?></h2>
			<div align="center">
				<table class="info_table">
					<tr><td><?php echo $msg_arr['requirements_info'];?></td></tr>
				</table>
			</div>
		</div>
		<div class="content">
			<div align="center">
				<!-- requests for running the scripts successfully -->
				<div class = "div_error_handler">
					<table>
						<tr>
							<td class="requirement"><?php echo $msg_arr['php_version']; ?></td>
							<td class = "check_icon">
							<?php
							
							if (!$errors['err_version']) {
								echo "<img src='skin/images/ok.gif'>";
							} else {
								echo "<img src='skin/images/error.gif'>";
							}
							?>
							</td>
						</tr>
						<?php
						if ($errors['err_version']) {
							echo "<tr class='advice'><td colspan='2'>".str_replace('{version}', $php_version, $msg_arr['err_version_hint'])."</td></tr>";
						}
						?>
						<tr>
							<td><?php echo str_replace('{style}', '<i>', $msg_arr['soap_enabled']); ?></td>
							<td>
							<?php
							if (!$errors['err_soap']) {
								echo "<img src='skin/images/ok.gif'>";
							} else {
								echo "<img src='skin/images/error.gif'>";
							}
							?>
							</td>
						</tr>
						<?php
						if ($errors['err_soap']) {
							echo "<tr class='advice'><td colspan='2'>".str_replace('{style}', '<i>', $msg_arr['err_soap_hint'])."</td></tr>";
						}
						?>
						<tr>
							<td><?php echo $msg_arr['server_available']; ?></td>
							<td>
							<?php
							if (!$errors['err_voip']) {
								echo "<img src='skin/images/ok.gif'>";
							} else {
								echo "<img src='skin/images/error.gif'>";
							}
							?>
							</td>
						</tr>
						<?php
						if ($errors['err_voip']) {
                            if($errors['err_voip_perm']){
                                echo "<tr class='advice'><td colspan='2'>" . $msg_arr['err_voip_hint_perm'] . "</td></tr>";
                            } else {
                                echo "<tr class='advice'><td colspan='2'>" . $msg_arr['err_voip_hint'] . "</td></tr>";
                            }
						}
						?>
					</table>
				</div>
			</div>
		</div>		
		</body>
</html>
<?php
	restore_error_handler();
	exit(1);
}

/* set error handling function */
$error_handler = set_error_handler("myErrorHandler");

/* create SOAP client based on WSDL, with trace for debugging */
if (!class_exists('soapclient')) {
	$errors['err_soap'] = 1;
	myErrorHandler();
}

/* create SOAP client */
$client = createSoapClient();

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
	<?php if (empty($errors['err_soap']) && empty($errors['err_version']) && empty($errors['err_voip'])) { ?>
		<div class="index_content">
			<h2><?php echo $msg_arr['pg_index'];?></h2>
			<div align="center">
				<table class="info_table">
					<tr><td><?php echo $msg_arr['method_info'];?></td></tr>
				</table>
				<div class="index_links">
					<a href='account_manag.php'><?php echo $msg_arr['pg_add_account'];?></a>
				</div>
				<div class="index_links">
					<a href='report.php'><?php echo $msg_arr['pg_reporting'];?></a>
				</div>
			</div>
		</div>
		<br>
	<?php } else { ?>
		<div class="index_content">
			<h2><?php echo $msg_arr['pg_index'];?></h2>
			<div align="center">
				<table class="info_table">
					<tr><td><?php echo $msg_arr['requirements_info'];?></td></tr>
				</table>
			</div>
		</div>
		<div class="content">
			<div align="center">
				<!-- requests for running the scripts successfully -->
				<div class = "div_error_handler">
					<table>
						<tr>
							<td class="requirement"><?php echo $msg_arr['php_version']; ?></td>
							<td class = "check_icon">
							<?php
							if (!$errors['err_version']) {
								echo "<img src='ok.gif'>";
							} else {
								echo "<img src='error.gif'>";
							}
							?>
							</td>
						</tr>
						<?php
						if ($errors['err_version']) {
							echo "<tr class='advice'><td colspan='2'>".str_replace('{version}', $php_version, $msg_arr['err_version_hint'])."</td></tr>";
						}
						?>
						<tr>
							<td><?php echo str_replace('{style}', '<i>', $msg_arr['soap_enabled']); ?></td>
							<td>
							<?php
							if (!$errors['err_soap']) {
								echo "<img src='ok.gif'>";
							} else {
								echo "<img src='error.gif'>";
							}
							?>
							</td>
						</tr>
						<?php
						if ($errors['err_soap']) {
							echo "<tr class='advice'><td colspan='2'>".str_replace('{style}', '<i>', $msg_arr['err_soap_hint'])."</td></tr>";
						}
						?>
						<tr>
							<td><?php echo $msg_arr['server_available']; ?></td>
							<td>
							<?php
							if (!$errors['err_voip']) {
								echo "<img src='ok.gif'>";
							} else {
								echo "<img src='error.gif'>";
							}
							?>
							</td>
						</tr>
						<?php
						if ($errors['err_voip']) {
							echo "<tr class='advice'><td colspan='2'>".$msg_arr['err_voip_hint']."</td></tr>";
						}
						?>
					</table>
				</div>
			</div>
		</div>
		<?php } ?>
	</body>
</html>
<?php
ob_end_flush();
restore_error_handler();
?>
