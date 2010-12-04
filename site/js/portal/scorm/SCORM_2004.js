/**
 * @author Mark Statkus mark@cybercussion.com
 * @version 1.0
 * Description
 * SCORM 2004 is searching for API_1484_11 namespace when it launches.
 * Create that and support some (or eventually) all of the calls.
 * @requires patience
 * @constructor
 * @namespace API_1484_11
 * @param options {Object} This allows for override/new values.
*/
function SCORM_2004(options) {
	/** @default version, moddate, createdate, errorCode, initialized, terminated */
	var defaults = {
			version: "1.0",
			moddate: "2010-7-17 08:15",
			createdate: "2010-7-17 08:15",
			errorCode: 0,
			diagnostic: '',
			initialized: 0,
			terminated: 0
		},
	/** Settings merged with defaults and extended options */
		settings = $.extend(defaults, options);
	
	/**
	* Initialize Session (SCORM) only once!
	* @param name {String} Appears to be unused
	* @returns "true" or "false" depending on if its been initialized prior
	*/
	this.Initialize = function() {
		if(this.isWaiting()) {
			this.set('initialized', 1);
			return "true";
		} else {
			if(this.get('terminated')) {
				this.set('errorCode', 104);
			} else {
				this.set('errorCode', 103);
			}
			return "false";
		}
	};
	
	/**
	* GetValue (SCORM)
	* @param name {String}
	* @returns "true" or "false" depending on if its been initialized prior
	*/
	this.GetValue = function(param) {
		if(this.isRunning()) {
			// to do
			return null;
			
		} else {
			return "false";
		}
	};
	
	/**
	* SetValue (SCORM)
	* @param name {String}
	* @returns "true" or "" depending on if its been initialized prior
	*/
	this.SetValue = function(param) {
		if(this.isRunning()) {
			 // to do
			 
			 return "true";
		} else {
			return "";
		}
	};
	
	this.Commit = function(param) {
        settings.errorCode = "0";
        if (param === "") {
            if (this.isRunning()) {
                //result = StoreData(cmi,false);
                return "true";
            } else {
                if (this.get('terminated')) {
                    settings.errorCode = "143";
                } else {
                    settings.errorCode = "142";
                }
            }
        } else {
            settings.errorCode = "201";
        }
        return "false";
	};
	
	/**
	* GetErrorString (SCORM) - Returns the error string from the associated Number
	* @param param number
	* @returns string
	*/
	this.GetErrorString = function(param) {
		if (param !== "") {
            var errorString = "";
            switch(param) {
                case 0:
                    errorString = "No error";
                break;
                case 101:
                    errorString = "General exception";
                break;
                case 102:
                    errorString = "General Initialization Failure";
                break;
                case 103:
                    errorString = "Already Initialized";
                break;
                case 104:
                    errorString = "Content Instance Terminated";
                break;
                case 111:
                    errorString = "General Termination Failure";
                break;
                case 112:
                    errorString = "Termination Before Initialization";
                break;
                case 113:
                    errorString = "Termination After Termination";
                break;
                case 122:
                    errorString = "Retrieve Data Before Initialization";
                break;
                case 123:
                    errorString = "Retrieve Data After Termination";
                break;
                case 132:
                    errorString = "Store Data Before Initialization";
                break;
                case 133:
                    errorString = "Store Data After Termination";
                break;
                case 142:
                    errorString = "Commit Before Initialization";
                break;
                case 143:
                    errorString = "Commit After Termination";
                break;
                case 201:
                    errorString = "General Argument Error";
                break;
                case 301:
                    errorString = "General Get Failure";
                break;
                case 351:
                    errorString = "General Set Failure";
                break;
                case 391:
                    errorString = "General Commit Failure";
                break;
                case 401:
                    errorString = "Undefined Data Model";
                break;
                case 402:
                    errorString = "Unimplemented Data Model Element";
                break;
                case 403:
                    errorString = "Data Model Element Value Not Initialized";
                break;
                case 404:
                    errorString = "Data Model Element Is Read Only";
                break;
                case 405:
                    errorString = "Data Model Element Is Write Only";
                break;
                case 406:
                    errorString = "Data Model Element Type Mismatch";
                break;
                case 407:
                    errorString = "Data Model Element Value Out Of Range";
                break;
                case 408:
                    errorString = "Data Model Dependency Not Established";
                break;
                default: 
                	errorString = "Unknown error ID passed " + param;
                break;
			}
			return errorString;  
		} else {
            return "";
		}
	};
	
	/**
	* GetLastError (SCORM) - Returns the error number from the last error
	* @param param number
	* @returns number
	*/
	this.GetLastError = function() {
		return this.get('errorCode');
	};
	
	this.GetDiagnostic = function(param) {
        if (settings.diagnostic !== "") {
            return settings.diagnostic;
        }
        return param;
    };
	
	/**
	* Terminate Session (SCORM) only once!
	* @param name {String} Appears to be unused
	* @returns "true" or "false" depending on if its been initialized prior
	*/
	this.Terminate = function() {
		if(this.isRunning()) {
			this.set('initialized', 0);
			this.set('terminated', 1);
			// Need to Store Data
			// Need to support nav request
			// But also will need to send back to Lessons, and clear out the object
			var that = this;
			setTimeout(function() {
				portal.scoTerminated();
				that.resetAPI();
			}, 500);
			//portal.navigation.goBack(); // Note flip, cube and swap a little visually buggy in chrome.
			return "true";
		} else {
			this.set('errorCode', 201);
			return "false";
		}
	};
    
    /**
	* isWaiting, Returns true if initialized is 0 and terminated is 0
	* @returns true or false
	*/
    this.isWaiting = function() {
    	if(!this.get('initialized') && !this.get('terminated')) {
    		return true;
    	} else {
    		return false;
    	}
    };
    
    /**
	* isRunning, Returns true if initialized is 1 and terminated is 0
	* @returns true or false
	*/
    this.isRunning = function() {
    	if(this.get('initialized') && !this.get('terminated')) {
    		return true;
    	} else {
    		return false;
    	}
    };
    
    this.resetAPI = function() {
    	if(this.get('terminated')) {
    		settings.errorCode   = 0;
			settings.diagnostic  = '';
			settings.initialized = 0;
			settings.terminated = 0;
			debug("API_1484_11 Reset", 4);
    	} else {
    		debug("Attempting to reset API while SCO is in progress not allowed!", 4);
    	}
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
var API_1484_11 = new SCORM_2004();