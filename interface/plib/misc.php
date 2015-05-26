<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Auxiliary functions for xml SOAP resquest/response format, and for creating soap client.
 */

/* require constant definitions */
include_once('definitions.inc.php');
/* the class that prepares the soapClient */
include_once('SoapClient_4psa.php');
/**
 * Formats an XML string into an array of tags
 * @param <string> $xml - string with SOAP request/response
 * @return <array> $format_xml - array with tags
 */
function formatXml($xml) {
	$format_xml = array();
	for ($i = 0; $i < strlen($xml); $i++) {
		$start_pos = stripos($xml, '<');
		$stop_pos = stripos($xml, '>');
		/* get the tag of an element - it is between a '<' and a '>' character */
		$tag = substr($xml, $start_pos+1, $stop_pos-$start_pos-1);
		/* remaining string from xml, after eliminating the found tag */
		$xml = substr($xml, $stop_pos+1);

		/* search for ending tag in remaining $xml */
		$end_tag = strstr($xml, '/' . $tag);
		/* if end tag found */
		if (strlen($end_tag) > 0) {
			
			$end_tag_pos = strlen($xml) - strlen($end_tag);
			if ((stripos($xml, '<')+1) == $end_tag_pos) {
				/* get the value from the tag */
				$value = substr($xml, 0, $end_tag_pos);
				$tag_name = $tag;
				$tag = $tag_name . '>' .$value. '/' .$tag_name;
				/* tag is now similar to 'tag>value</tag'.
				 * remaining xml is stored in $xml variable */
				$xml = substr($end_tag, strlen($tag_name)+2);
			}
		}
		$format_xml[] = $tag;
	}
	$tags_no = count($format_xml);
	if ($tags_no > 0) {
		/* add missing '<' and '>' characters. */
		$format_xml[0] = '<'.$format_xml[0];
		$format_xml[$tags_no-1] = $format_xml[$tags_no-1].'>';
	}
	if (!empty($format_xml[1])) {
		$envelope = explode(' ', $format_xml[1]);
		/* put new lines between tags */
		for ($i = 1; $i < count($envelope)-1; $i++) {
			$envelope[$i] = $envelope[$i]."\n";
		}
		$format_xml[1] = implode(' ', $envelope);
		/* return the formated xml */
		return $format_xml;
	} else {
		return FALSE;
	}
}

/**
 * Formats a hash of languages in format 'code' => 'name'
 * @param <array> $languages - object with languages information
 * @return <array> $format_lang - array of languages
 */
function formatLanguages($languages) {
	$format_lang = array();
	if (is_array($languages) && !empty($languages)) {
		foreach ($languages as $key => $val) {
			$lang = get_object_vars($val);
			$format_lang[$lang['code']] = $lang['name'];
		}
	}
	return $format_lang;
}

/**
 * Splits a faultstring to fit to a fix area
 * @param <array> $xml_array - array with xml tags
 * @param <type> $width - fix area size
 * @return <array> $xml_array - formated array with XML tags
 */
function splitFaultstring($xml_array, $width = '') {
	if (!empty($xml_array)) {
		foreach ($xml_array as $key => $val) {
			if (is_numeric(strpos($val, 'faultstring>')) || is_numeric(strpos($val, 'message>'))) {
				$length = 20;
				if (!empty($width)) {
					$length = $width/7;
				}
				$val = wordwrap($val, $length, "\n");
				$xml_array[$key] = $val;
			}
		}
		return $xml_array;
	}
	
	return FALSE;
}

/**
 * Gets the location of the local schemes, based on the location of a php script
 * @param string $script_location - the location of the script
 * @return string $location - the location of the local schemes
 */
function getLocalSchemesLocation($script_location) {
	$lastslashpos = strrpos($script_location, '/');
	$location = substr($script_location, 0, $lastslashpos);
	$secondlastpos = strrpos($location, '/');
	$location = substr($location, 0, $secondlastpos + 1);
	return $location;
}


/**
 * Function that creates soap client, sets the header containing access token and voinow version
 * @return object $client - the soap client
 */
function createSoapClient() {
	global $voipnow_access_token, $voipnow_version;
	$script_location = $_SERVER['SCRIPT_FILENAME'];
	/* create SOAP client based on WSDL, with trace for debugging */
	$files_location = getLocalSchemesLocation($script_location);
	$client_class = new SoapClient_4psa($files_location, array('trace' => 1, 'exceptions' => 0, 'cache_wsdl' => WSDL_CACHE_BOTH));
	$client = $client_class->getSoapClient();
	
	$auth = new stdClass();
	$auth->accessToken = $voipnow_access_token;
	$authvalues = new SoapVar($auth, SOAP_ENC_OBJECT, HEADERURL.$voipnow_version);
	$header = new SoapHeader(HEADERURL.$voipnow_version, 'userCredentials', $authvalues, false);
	$client->__setSoapHeaders(array($header));
	return $client;
}
?>