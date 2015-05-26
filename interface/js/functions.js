/**
 * 4PSA VoipNow SystemAPI Client for PHP
 *
 * Copyright (c) 2012, Rack-Soft (www.4psa.com). All rights reserved.
 * VoipNow is a Trademark of Rack-Soft, Inc
 * 4PSA is a Registered Trademark of Rack-Soft, Inc.
 * All rights reserved.
 * 
 * Javascript functions 
 */


/**
 * Some constants.
 */
var READYSTATE_COMPLETE = 4; 
var READYSTATE_UNINITIALIZED = 0;
var STATUS_OK = 200;

/**
	* Creates an XMLHttpRequest object, based on the browser type
	* @return xmlHttp - the XMLHttpRequest object 
	*/
function getAJAXObject() {
	var xmlHttp;
	try
	{
		// Firefox, Opera 8.0+, Safari
		xmlHttp = new XMLHttpRequest();
	} catch (e) {
		// Internet Explorer
		try
		{
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try
			{
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				//alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
	
	return xmlHttp;
}

/**
 * Switches regions for a country
 * Calls get_regions.php script
 * @param <string> country - the country
 */
function switchStateInput(country) {
	/* ajax to take the regions for this country */
	var xmlHttp = getAJAXObject();
	
	if (xmlHttp.readyState == READYSTATE_COMPLETE || xmlHttp.readyState == READYSTATE_UNINITIALIZED) {
		var connString = "./get_regions.php?country=" + country + "&time=" + new Date().getTime();
		xmlHttp.onreadystatechange = getRegions;
		xmlHttp.open("GET", connString, true);
		xmlHttp.send(null);
	}	
}

/**
 * Gets the regions for the country that is selected in the country field
 * Calls get_regions.php script in ajax
 */
function getRegionsFromSelectedCountry() {
	var xmlHttp = getAJAXObject();
	
	var country = document.getElementById('country').value;
	if (xmlHttp.readyState == READYSTATE_COMPLETE || xmlHttp.readyState == READYSTATE_UNINITIALIZED) {
		var connString = "./get_regions.php?country=" + country + "&time=" + new Date().getTime();
		xmlHttp.onreadystatechange = getRegions;
		xmlHttp.open("GET", connString, true);
		xmlHttp.send(null);
	}
}

/**
 * Completes the corresponding select with the regions of the newly selected country
 * Receives the response from the ajax to get_regions.php
 */
function getRegions() {
	if (this.readyState == READYSTATE_COMPLETE && this.status == STATUS_OK) {
		var response=this.responseXML;
		/* the regions select */
		var select = document.getElementById('region_select');
		if (response == null) {
			/* remove last options */
			select.innerHTML = "";
			/* disable select */
			select.disabled = true;
		} else {
			select.innerHTML = "";
			/* enable the regions select */
			select.disabled = false;
			/* take the response from ajax */
			var xml = response.documentElement;
			/* the regions */
			var regionElements = xml.getElementsByTagName('region');
			if (regionElements.length == 0) {
				/* the country does not have regions */
				select.disabled = true;
			} else {
				var reg_option_number = 0;
				var options = "";
				/* append the options to the regions select */
				for (var i = 0; i < regionElements.length; i++) {
					var regionElement = regionElements[i];
					var regionChilds = regionElement.childNodes;
					select.options[reg_option_number] = new Option(regionChilds[1].childNodes[0].nodeValue, regionChilds[0].childNodes[0].nodeValue);
					reg_option_number = reg_option_number + 1;
				}
			}
			
			/* the timezones select */
			var tm_select = document.getElementById('timezone');
			tm_select.innerHTML = "";
			/* get the timezones from the response */
			var timezoneElements = xml.getElementsByTagName('timezone');
			var tm_options = "";
			var tm_option_number = 0;
			/* complete the options of the timezones select */
			for (var i = 0; i < timezoneElements.length; i++) {
				var timezoneElement = timezoneElements[i];
				var timezoneChilds = timezoneElement.childNodes;
				tm_select[tm_option_number] = new Option(timezoneChilds[1].childNodes[0].nodeValue, timezoneChilds[0].childNodes[0].nodeValue);
				tm_option_number = tm_option_number + 1;
			}
		}
	}
}

/**
 * Disables the Pasword field if 'Password auto generation' is checked
 * @param <boolean> auto_gen_status - the status of the 'Password auto generation' field
 */
function switchPassword(auto_gen_status) {
    if (auto_gen_status) {
		document.getElementById('password').disabled = true;
		document.getElementById('password').style.backgroundColor = '#C2BFA5';		
	} else {
		document.getElementById('password').disabled = false;
		document.getElementById('password').style.backgroundColor = '#FFFFFF';
	}
}

/**
 * Displays specific account type information
 * @param <string> account_type - the type of the account
 */
function displayAccount(account_type) {
	switch (account_type) {
	case 'service provider':
		 document.getElementById('serviceprovider_form').style.display = 'block';
         document.getElementById('organization_form').style.display = 'none';
         document.getElementById('adduser_form').style.display = 'none';
         document.getElementById('extension_form').style.display = 'none';
        document.getElementById('account_type_form').style.display = 'block';
         break;
	case 'organization':
		document.getElementById('organization_form').style.display = 'block';
        document.getElementById('serviceprovider_form').style.display = 'none';
        document.getElementById('adduser_form').style.display = 'none';
        document.getElementById('extension_form').style.display = 'none';
        document.getElementById('account_type_form').style.display = 'block';
        break;
	case 'user':
		document.getElementById('organization_form').style.display = 'none';
        document.getElementById('serviceprovider_form').style.display = 'none';
        document.getElementById('adduser_form').style.display = 'block';
        document.getElementById('extension_form').style.display = 'none';
        document.getElementById('account_type_form').style.display = 'block';
		break;
	case 'extension':
		 document.getElementById('extension_form').style.display = 'block';
         document.getElementById('serviceprovider_form').style.display = 'none';
         document.getElementById('adduser_form').style.display = 'none';
         document.getElementById('organization_form').style.display = 'none';
         document.getElementById('account_type_form').style.display = 'none';
         break;
	}
	if (account_type == 'extension') {
        document.getElementById('ext_setup').style.display = 'block';
        document.getElementById('phone_lang').style.display = 'block';
        document.getElementById('ext_type_info').style.display = 'block';       
	} else {
        document.getElementById('ext_setup').style.display = 'none';
        document.getElementById('phone_lang').style.display = 'none';
        document.getElementById('ext_type_info').style.display = 'none';
	}
}

/**
 * Populate account type select list relative to the config user account level
 */
function displayUsers(obj) {
	
	var account_type = obj.value;
	document.getElementById('serviceprovider').style.display = 'none';
	document.getElementById('organization').style.display = 'none';
	document.getElementById('user').style.display = 'none';
	
	switch (account_type) {
		case 'service provider' : {
			var element_id = 'serviceprovider_id';
			var account_id = 'serviceprovider';
			var accounts_arr = serviceproviders_arr;
			var first_option_all = 'All service providers';
			break;
		}
		case 'organization' : {
			var element_id = 'organization_id';
			var account_id = 'organization';
			var accounts_arr = organizations_arr;
			var first_option_all = 'All organizations';
			break;
		}
		case 'user': {
			var element_id = 'user_id';
			var account_id = 'user';
			var accounts_arr = users_arr;
			var first_option_all = 'All users';
			break;
		}
	}
	
	var select = document.getElementById(element_id);
	select.options.length = 0;
	var j = 0;			
	if (accounts_arr.length > 0 ) {				
		document.getElementById(account_id).style.display = 'block';								
	} else {				
		document.getElementById(account_id).style.display = 'none';
	}
	/* if there is more than one account of the chosen type, also add the option to select all of them */
	if (accounts_arr.length > 1 ) {			
		select.options[j] = new Option(first_option_all, 'all');			
		j++;
	}
	
	for(i = 0; i < accounts_arr.length; i++) {
		select.options[j] = new Option(accounts_arr[i]['name'], accounts_arr[i]['id']);
		j++;
	}			
}

/**
 * Checks if given value is a number
 * @param <string> value - the string to check
 * @return <boolean> result - true or false
 */
function isNumeric(value) {
   var valid_digits = "0123456789";
   var result = true;
   for (i = 0; i < value.length; i++) {
	   if (valid_digits.indexOf(value.charAt(i)) == -1) {
		   return false;
	   }
   }
   return true;
}

/**
 * Checks if userId is set for call cost report request
 * Submits the form
 * @return true
 */
function validateUser() {
	var account_type = document.getElementById('acc_type').value;
	var form = document.getElementById('report_form');	
	form.submit();
	return true;
}