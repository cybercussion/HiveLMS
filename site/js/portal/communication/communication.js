/**
 * Communication
 * This is the Communication handling for the Portal. It sends JSON based data as text
 * and gets back JSON objects.  I'm choosing to parse it since it was recommended on line
 * To manage JS injection to do it this way.
 */
function Communication(options) {
	/** @default version, date, isError, domainURL*/
	var defaults = {
			version: "1.0",
			createdate: "2009-10-21 18:00",
			moddate: "2010-11-03 18:17",
			isError: false,
			domainURL: ""
  		},
  		settings = $.extend(defaults, options);
	/**
	 * Send Message
	 * Public Method to send JSON objects to the Backend via Ajax.
	 * @param _send {object} Object the data is contained in
	 * @param _callBack {Function} Target of what to call after this finishes
	 * @param _from {String} Handy info as to who requested this for debugging
	 */
	this.sendMessage = function(_send, _callBack, _from) {
		if(typeof(_from) !== "string") {
			debug("Communication: Developer Warning - Please pass the requested script name for trouble shooting purposes!!", 2);
		}
		var send_output,
			returnMethod = _callBack,
			send         = JSON.stringify(_send);
		if(enableDebug) { // Handle Added process of stringifying out submission
			send_output = JSON.stringify(_send, null, " ");
			debug("Comm/JSON Send: From- " + _from + " " + send_output, 4);
		}
		$.ajax({  
			type: "POST",  
			url: "php/communication.php",
			dataType: 'text',
			proccessData: false,
			data: {'data': send},
			cache: false, 
			success: function(_obj) {
				var json_obj;
				try {
					json_obj = JSON.parse(_obj); // Convert back into Object (if text above)
				} catch(err) {
					// This will typically be a string error from the server.  Lets put it into a JSON obj.
					json_obj = {status: 'fatal', msg: _obj};
				}
				
				if(enableDebug) {
					var recieve_output = JSON.stringify(json_obj, null, " ");
				  	switch(json_obj.status) {
						case "success":
							debug('Comm/JSON Receive: ' + recieve_output, 4);
					  		break;
					  	case "error":
					  	case "fatal":
					  		debug('Comm/JSON Receive: ' + recieve_output, 1);
					  		settings.isError=true;
					  		break;
					  	case "fail":
					  	case "warning":
					  		debug('Comm/JSON Receive: ' + recieve_output, 2);
					  		break;
					  	default:
					  		debug("Unexpected status or no status supplied: " + recieve_output, 2);
					  		break;
					}
				}
				// Pass back to requestor
				returnMethod(json_obj);
		},
			error: function(_xhr, _desc, _exceptionobj) {
				// Error Handling
				returnMethod("{error: 'Requested by: " + _from + "  :: " + _desc + " :: " + _xhr.status + " " + _xhr.statusText + "'}");
				debug("Communication: Sorry a server error has occurred. Please notify a administrator. Requested by: " + _from + "  :: " + _desc + " :: " + _xhr.status + " " + _xhr.statusText + " :: " + _exceptionobj, 1);
		    }
	    });
  	};
  	/**
	* Get value of a setting
	* @param name {String} This is the name of the setting you want
	* @returns the setting requested
	* @type String or Number depending
	*/
	this.get = function(name) {
		return settings[name];
	};
	
	/**
	 * Set the value of a existing setting, or add a new one
	 * @param name {String} Name of the setting
	 * @param value {String} Value of the setting 
	*/
	this.set = function(name, value) {
		settings[name] = value;
	};
}