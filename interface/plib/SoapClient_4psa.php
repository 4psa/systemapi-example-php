<?php
/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Class that prepares a soap client, by downloading locally the soap schemes
 */
	
include_once("config/config.php");

class SoapClient_4psa {
	/* the soap client */
	private $soap_client = null;
	/* the scheme files */
	private $scheme_files = array(
			'voipnowservice.wsdl',
			'Common.xsd',
			'HeaderData.xsd',
			'Account' => array(
				'Account.wsdl',
				'AccountData.xsd',
				'AccountMessages.xsd',
			),
			'Billing' => array(
				'Billing.wsdl',
				'BillingData.xsd',
				'BillingMessages.xsd',
			),
			'Channel' => array(
				'Channel.wsdl',
				'ChannelData.xsd',
				'ChannelMessages.xsd',
			),
			'Extension' => array(
				'Extension.wsdl',
				'ExtensionData.xsd',
				'ExtensionMessages.xsd',
			),
			'GlobalOp' => array(
				'GlobalOp.wsdl',
				'GlobalOpData.xsd',
				'GlobalOpMessages.xsd'
			),
			'PBX' => array(
				'PBX.wsdl',
				'PBXData.xsd',
				'PBXMessages.xsd'
			),
			'Report' => array(
				'Report.wsdl',
				'ReportData.xsd',
				'ReportMessages.xsd',
			),			
			'ServiceProvider' => array(
				'ServiceProvider.wsdl',
				'ServiceProviderData.xsd',
				'ServiceProviderMessages.xsd',
			),
			'User' => array(
				'User.wsdl',
				'UserData.xsd',
				'UserMessages.xsd',
			),
			'Organization' => array(
				'Organization.wsdl',
				'OrganizationData.xsd',
				'OrganizationMessages.xsd',
			),
		);
		
	/**
     * The constructor of the class
	 * Verifies each schema file if it needs to be downloaded and it downloads it, if it needs 
	 * Creates a soap client and puts it in $this->soap_client variable
	 * @param string $local_location - the location of the local schemes, where the schemes will be downloaded or are already downloaded
	 * @param array $options - the options used at the creation of the soap client 
     */	 
	function __construct($local_location, $options) {
		
		global $voipnow_ip, $voipnow_port, $voipnow_version;
		/* the subfolder containing the schemes */
		$local_schema_subfolder = "tmp/wsdl/" . "$voipnow_ip" . "_$voipnow_port/soap2/schema/$voipnow_version/";
		/* the location of the schemes on the local server */
		$local_schema_location = $local_location . $local_schema_subfolder;
		/* create the temporary folder if it does not exist */
		$this->createTempFolder($local_schema_subfolder, $local_location);
		foreach ($this->scheme_files as $folder => $files) {
			if (is_array($files)) {
				foreach ($files as $file) {
					if ($this->mustUpdate($folder, $file, $local_schema_location)) {
						$this->update($folder, $file, $local_schema_location);
					}
				}
			} else {
				if ($this->mustUpdate(null, $files, $local_schema_location)) {
					$this->update(null, $files, $local_schema_location);
				}
			}
		}
		
		/* creates the client based on the schemes downloaded locally */
		$this->soap_client =  new SoapClient($local_schema_location."/voipnowservice.wsdl", $options) ;
		
	}

	/**
	 *
	 * This method will try to create the temporary folder that contains the schema, if it does not exist. Otherwise, it does nothing
	 *
	 * @param <string> $schema_temp_subfolder : the subfolder that should contain the schema
	 * @param <string> $main_temp_folder : the base path that contains the scripts and the temporary folder we will create.
	 */
	function createTempFolder($schema_temp_subfolder, $main_temp_folder) {

		$current_folder = $main_temp_folder;
		$tok = strtok($schema_temp_subfolder, "/\n");

		while ($tok !== false) {
			$current_folder = $current_folder . '/' . $tok;
			if(!is_dir($current_folder)) {
				mkdir($current_folder, 0777);
			}			
			$tok = strtok("/\n");
		}
	}
	
	/**
	 * Verifies if a local schema file must be updated or not based on mtime
	 * @param string $folder - the parent folder of the scheme file or null if the parent folder is the version folder  
	 * @param string $file - the name of the schema file
	 * @param string local_schema_location - the local location of the schema file
	 * @return boolean - true if the scheme file must be downloaded, false if it doesn't
	 */
	function mustUpdate($folder, $file, $local_schema_location) {
		/* check if the file exists locally */
		if (empty($folder)) {
			$local_filepath = $local_schema_location . $file;
		} else {
			$local_filepath = $local_schema_location . $folder . "/$file";
		}
		
		$glob_file = glob($local_filepath, GLOB_ERR);
		
		if (!empty($glob_file)) {
			/* the file exists */
			/* check the last time when it was modified */
			if (!$this->checkModifiedDate(filemtime($local_filepath))) {
				/* the file was modified */
				return false;
			} else {
				/* the file must be downloaded */
				return true;
			}
		} else {
			/* the file does not exist */
			return true;
		}
	}
	
	/**
	 * Downloads a specific schema file 
	 * @param string $folder - the parent folder of the scheme file or null if the parent folder is the version folder  
	 * @param string $file - the name of the schema file
	 * @param string local_schema_location - the local location of the schema file
	 */
	function update($folder, $file, $local_schema_location) {
		global $voipnow_ip, $voipnow_version;
		
		/* the remote location of the schema file */
		$remote_location = "https://" . $voipnow_ip . "/soap2/schema/" . $voipnow_version . "/";
		if (empty($folder)) {
			$remote_location = $remote_location . $file;
			$local_location = $local_schema_location . $file;
			$local_directory_location = $local_schema_location;
		} else {
			$remote_location = $remote_location . $folder . "/$file";
			$local_location = $local_schema_location . $folder . "/$file";
			$local_directory_location = "$local_schema_location" . $folder . "/";
		}
		/* the content of the schema file */
		$content = file_get_contents($remote_location);
		if ($content) {

			/* create the folder location */
			if(!is_dir($local_directory_location)) {
				mkdir($local_directory_location, 0777);
			}
			/* check if the folder was created */
			if (!glob($local_directory_location, GLOB_ERR)) {
				error_log("Could not create $local_directory_location folder. Please check if the parent directory has permissions or create the folder by yourself.");
				echo "Could not create $local_directory_location folder. Please check if the parent directory has permissions or create the folder by yourself.<br/>";
			} else {
				file_put_contents($local_location, $content);
			}
		} else {
			error_log("PHP SOAP Client: file get contents error for $remote_location file");
			echo "PHP SOAP Client: file get contents error for "+$remote_location+" file";
		}
	}
			
	/**
	 * Checks if the modify timeout interval specified in config.php has passed since a specific date moment
	 * @param int $lastModified - the date moment, specified as a timestamp 
	 * @return true - if the interval has passed, false otherwise
	 */
	function checkModifiedDate($lastModified) {
		global $modify_timeout;
		/* how many hours the schemes may not be modified */
		$hours_interval = $modify_timeout;
		$seconds_interval = $hours_interval * 60 * 60;
		
		if (time() - $seconds_interval > $lastModified) {
			return true;
		} else {
			return false;
		}
	}
		
	/**
	 * Gets the soap client created in the constructor
	 * @return soapclient $this->soap_client - the soap client created in the constructo
	 */
	function getSoapClient() {
		return $this->soap_client;
	}
}
?>