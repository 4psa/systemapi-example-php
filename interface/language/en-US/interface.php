<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Language Pack
 */


	$msg_arr = array();

	/* page titles */
	$msg_arr['pg_add_account'] = 'Account management';
	$msg_arr['pg_reporting'] = 'Call Statistics';
	$msg_arr['pg_index'] = 'VoipNow SystemAPI Demo';
	$msg_arr['pg_title'] = 'WEB services: add account';
	$msg_arr['pg_index_title'] = 'VoipNow SystemAPI Demo';

	/* page info */
	$msg_arr['required_fields'] = 'The fields marked with an asterisk {required} are required in the Schema/WSDL.';
	$msg_arr['validation_info'] = 'This form is not validated for demonstration purposes. Any incorrect input will generate a SOAP fault.';
	$msg_arr['method_info'] = 'This is a SystemAPI Demo for VoipNow. Using this demo you can add server provider, organization, user and extension account or view call reports. For each request you make the demo will also show you the formated SOAP request and response from the VoipNow server.';
	$msg_arr['reporting_info'] = 'If you do not select any account type, the report will be made for the logged user. If you do not select any month, the report will be made on the entire call history list.';
	$msg_arr['extension_type'] = 'The extension type is implicitly phone terminal.';
	$msg_arr['info_ext_setup'] = 'Extension setup';
	$msg_arr['user_form'] = '{user_type} form';
	$msg_arr['serviceprovider_form'] = 'Service provider form';
	$msg_arr['organization_form'] = 'Organization form';
	$msg_arr['extension_form'] = 'Extension form';
	$msg_arr['adduser_form'] = 'User form';
	$msg_arr['permissions_limits_form'] = 'Permissions and limits';
	$msg_arr['choose_acc_type'] = 'Select the type of account you want to add';
	$msg_arr['choose_report_search'] = 'Call cost report search';
	$msg_arr['assign_public_numbers'] = 'Incoming Phone Number selection';
	$msg_arr['public_no_info'] = 'Click Next to select public phone numbers for the new added user.';

	/* labels */
	$msg_arr['public_numbers'] = 'Public phone numbers';
	$msg_arr['number'] = 'Number';
	$msg_arr['label'] = 'Label';
	$msg_arr['company'] = 'Company';
	$msg_arr['year'] = 'Year';
	$msg_arr['month'] = 'Month';
	$msg_arr['contact'] = 'Contact name';
	$msg_arr['login'] = 'Login';
	$msg_arr['password'] = 'Password';
	$msg_arr['password_auto'] = 'Password auto generation ';
	$msg_arr['passwd_info'] = '(at least 6 characters)';
	$msg_arr['phone'] = 'Phone';
	$msg_arr['fax'] = 'Fax';
	$msg_arr['email'] = 'Email';
	$msg_arr['address'] = 'Address';
	$msg_arr['city'] = 'City';
	$msg_arr['state'] = 'State/Province';
	$msg_arr['country'] = 'Country';
	$msg_arr['pcode'] = 'Postal/ZIP code';
	$msg_arr['interface_lang'] = 'Interface language';
	$msg_arr['region'] = 'Region';
	$msg_arr['timezone'] = 'Timezone';
	$msg_arr['phone_lang'] = 'Phone language';
	$msg_arr['user_notes'] = 'Notes';
	$msg_arr['account_type'] = 'Account type';
	$msg_arr['no_answer'] = 'No answer from remote server';
	$msg_arr['serviceproviders'] = 'Service Providers';
	$msg_arr['organizations'] = 'Organizations';
	$msg_arr['extensions'] = 'Extensions';
	$msg_arr['users'] = 'Users';
	$msg_arr['local_calls'] = 'Local calls';
	$msg_arr['elocal_calls'] = 'Extended local calls';
	$msg_arr['external_calls'] = 'External calls';
	$msg_arr['incoming_calls'] = 'Incoming calls';
	$msg_arr['outgoing_calls'] = 'Outgoing calls';
	$msg_arr['cost'] = 'Call cost';
	$msg_arr['profit'] = 'Profit';
	$msg_arr['report_error'] = 'Unable to get call cost report.';
	$msg_arr['home'] = 'Home';
	$msg_arr['php_version'] = 'Current PHP version > 5.1.2';
	$msg_arr['soap_enabled'] = 'PHP is configured with {style}--enable-soap{style}';
	$msg_arr['server_available'] = 'VoipNow SystemAPI is reachable';
	$msg_arr['err_version_hint'] = 'Your current version of PHP is {version}. Please upgrade to PHP 5.1.2 or later.';
	$msg_arr['err_soap_hint'] = 'These scripts require PHP to be compiled with {style}--enable-soap{style} configuration option.';
	$msg_arr['err_voip_hint'] = 'Check your VoipNow server IP in the configuration file.';
    $msg_arr['err_voip_hint_perm'] = 'Permissions problem.';
	$msg_arr['requirements_info'] = 'Some of the requirements for running VoipNow SystemAPI Demo are not fulfilled. Please check the list below for more details.';
	$msg_arr['no_dids'] = 'No public phone numbers available.';
	$msg_arr['no_ext_id'] = 'No extension organization id provided.';
	$msg_arr['service provider'] = 'Service Provider';
	$msg_arr['organization'] = 'Organization';
	$msg_arr['user'] = 'User';
	$msg_arr['extension'] = 'Extension';

	/* btns */
	$msg_arr['btn_ok'] = 'Ok';
	$msg_arr['btn_next'] = 'Next';

	/* legend */
	$msg_arr['lg_request'] = 'Request';
	$msg_arr['lg_response'] = 'Response';
	$msg_arr['lg_report'] = 'Call cost report:';
?>
