<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Script that gets the regions and the timezones for a specific country using SOAP requests: getRegions, getTimezone
 * Called from javascript
 */

/* require config file */
include_once('config/config.php');
/* require file for getLocalSchemesLocation */
include_once('plib/misc.php');
/* the class that prepares the soapClient */
include_once('plib/SoapClient_4psa.php');

/* the country for which the script gets the regions and timezones */
$country_code = $_GET['country']; 

/* create SOAP client */
$client = createSoapClient();

/* get the regions */	
$data = new stdClass();
$data->code = $country_code;
$regions_list = $client->getRegions($data);
if (is_soap_fault($regions_list)) {
	error_log('PHP SOAP Client : SOAP fault error: faultcode '.$regions_list->faultcode.', faultstring: '.$regions_list->faultstring);
} else {	
	if (!empty($regions_list)) {
		$regions_array = (array) $regions_list;
		if (count($regions_array) > 0) {
			$regions = $regions_list->region;
		} else {
			$regions = null;
		}
	} else {
		$regions = null;
	}
	$regions_xml = '';
	if (count($regions) > 0) {
		/* create an xml string with the regions */
		$regions_xml = '<regions>';
		foreach ($regions as $region) {
			$regions_xml = $regions_xml . '<region>';
			$regions_xml = $regions_xml . '<id>' . $region->ID . '</id>';
			$regions_xml = $regions_xml . '<name>' . htmlentities($region->name) . '</name>';
			$regions_xml = $regions_xml . '</region>';
		}
		$regions_xml = $regions_xml . '</regions>';
	}
}	
/* get the timezones for the country */
$data = new stdClass();
$data->code = $country_code;
$timezones_list = $client->GetTimezone($data);	
$timezones = array();
if (!is_soap_fault($timezones_list)) {
	$timezones_array = (array) $timezones_list;
	if (count($timezones_array) > 0) {
		$timezones = $timezones_list->timezone;
	} else {
		/* the timezone for this country is not set */
		/* get all the timezones */
		$timezones_list = $client->getTimezone();
		$timezones_array = (array) $timezones_list;
		if (count($timezones_array) > 0) {
			$timezones = $timezones_list->timezone;
		} else {
			$timezones = null;
		}
	}
	
	/* create an xml string with the timezones */
	$timezones_xml = '';
	if (count($timezones) > 0) {
		$timezones_xml = '<timezones>';
		foreach ($timezones as $key => $value) {
			if (get_class($value) == 'stdClass') {
				$timezones_xml = $timezones_xml . '<timezone>';
				$timezones_xml = $timezones_xml . '<id>' . $value->ID . '</id>';
				$timezones_xml = $timezones_xml . '<name>' . htmlentities($value->name) . '</name>';
				$timezones_xml = $timezones_xml . '</timezone>';
			} else {
				if ($key == 'ID') {
					$timezones_xml = $timezones_xml . '<timezone>';
					$timezones_xml = $timezones_xml . '<id>' . $value . '</id>';	
				} 
				if ($key == 'name') {
					$timezones_xml = $timezones_xml . '<name>' . htmlentities($value) . '</name>';
					$timezones_xml = $timezones_xml . '</timezone>';
				}
			}
		}
		$timezones_xml = $timezones_xml . '</timezones>';
	}
}

if (isset($regions_xml) || isset($timezones_xml)) {
	$xml ='<data>';
	$xml = $xml . $regions_xml . $timezones_xml . '</data>';
	/* send the XML response to the javascript */
	header('Content-Type: text/xml');
	echo $xml;
} else {
	echo null;
}

?>


