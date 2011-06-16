/*global $, JQuery, debug, enableDebug*/
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
  	
  	// Private /////////////////	
  	function currentTime() {
		var d = new Date();
		return d.getTime() + (Date.remoteOffset || 0);
	}
	
	// End Private /////////////
	// Public //////////////////
	/**
	 * Send Message
	 * Public Method to send JSON objects to the Backend via Ajax.
	 * @param _send {object} Object the data is contained in
	 * @param _callBack {Function} Target of what to call after this finishes
	 * @param _from {String} Handy info as to who requested this for debugging
	 */
	this.sendMessage = function(send, callBack, from, sync) {
		var startTime = currentTime(),
			endTime,
			responseTime,
			dnsDrag,
			recieve_output, 
			json_obj,
			send_output,
			returnMethod = callBack,
			send_str     = JSON.stringify(send),
			// This is tricky, syncronous 'true' means this is going to stall the browser till its done (like a exit), but it has to be done
			// because if the LMS is refreshed the SCO The data could be lost without it.
			forcedSync  = (sync) ? false : true;     // Should be false unless Commit() is called from SCORM 
			                                         // (would like to make this smart so it doesn't hold up the boat.)
			//exiting      = isExit ? false : ;      // isExit isn't available yet since the event ladder hasn't made it here yet.
		debug("Communication: Is sending a async communication " + forcedSync, 3);
		if(typeof(from) !== "string") {
			debug("Communication: Developer Warning - Please pass the requested script name for trouble shooting purposes!!", 2);
		}
		if(enableDebug) { // Handle Added process of stringifying out submission
			send_output = JSON.stringify(send, null, " ");
			debug("Comm/JSON Send: From- " + from + " " + send_output, 4);
		}
		$.ajax({  
			type: "POST",  
			url: "php/communication.php",
			dataType: 'text',
			async: forcedSync, // default is true, if forcedSync came thru it would be false
			proccessData: false,
			data: {'data': send_str},
			cache: false, 
			success: function(obj) {
				endTime      = new Date().getTime();
				responseTime = (endTime - startTime) / 1000;
				try {
					json_obj = JSON.parse(obj); // Convert back into Object (if text above)
				} catch(err) {
					// This will typically be a string error from the server.  Lets put it into a JSON obj.
					json_obj = {status: 'fatal', msg: obj};
				}
				dnsDrag = responseTime - parseFloat(json_obj.rt);
				debug("Comm: Ajax Response Time: " + responseTime + "ms DNS Drag approx: " + dnsDrag + "ms", 3);
				if(enableDebug) {
					recieve_output = JSON.stringify(json_obj, null, " ");
					//debug("ReturnMethod " + returnMethod.toString(), 2);
					//if(json_obj.status) {
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
					//} else {
					//	debug("No status supplied - " + recieve_output, 2);
					//}
				}
				// Pass back to requestor
				returnMethod(json_obj);
			},
			error: function(xhr, desc, exceptionobj) {
				// Error Handling
				returnMethod("{error: 'Requested by: " + from + "  :: " + desc + " :: " + xhr.status + " " + xhr.statusText + "'}");
				debug("Communication: Sorry a server error has occurred. Please notify a administrator. Requested by: " + from + "  :: " + desc + " :: " + xhr.status + " " + xhr.statusText + " :: " + exceptionobj, 1);
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
	
	return true;
}