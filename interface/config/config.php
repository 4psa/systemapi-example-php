<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Configuration File: authentication and owner account IDs settings
 *
 */

/* In this variable you can write what account type to add. If 'choose', the form will let you choose the account type.
Valid choices = service provider/organization/user/extension/choose */
$account_type = 'choose';

/* If you add an organization you need this variable setup with the serviceprovider_id */
$serviceprovider_id = '';

/* If you add an user you need this variable setup with the organization_id */
$organization_id = '';

/* If you add an extension you need this variable setup with the user_id*/
$user_id = '';

/* The template ID. Make sure that the template type matches the type of the object you try to add! */
$template_id = '';

/* Billing plan ID. Set the ID only if you want to overwrite the template billing plan or the template does not have a billing plan asociated */
$billing_plan_id = '';

/* VoipNow server IP where you want to add accounts */
$voipnow_ip = 'voipnow2demo.4psa.com';

/* VoipNow port */
$voipnow_port = '443';

/* VoipNow schema version */
$voipnow_version = '3.0.0';

/* How many hours the schemes may not be modified (default value: 1 week) */
$modify_timeout = 168; 
	
/* Access token for oauth */
$voipnow_access_token = 'CHANGEME';

?>