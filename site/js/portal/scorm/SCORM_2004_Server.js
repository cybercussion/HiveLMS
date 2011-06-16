/*global $, JQuery, debug, portal, Communication, loadContent, API_1484_11*/
/**
 * @author Mark Statkus mark@cybercussion.com
 * @version 1.0
 * Description
 * SCORM 2004 is searching for API_1484_11 namespace when it launches.
 * Create that and support some (or eventually) all of the calls.
 * @requires jQuery, debug, portal, Communicaiton
 * @constructor
 * @namespace API_1484_11
 * @param options {Object} This allows for override/new values.
*/
function SCORM_2004(options) {
	/** @default version, moddate, createdate, errorCode, initialized, terminated */
	var defaults = {
			version:    "1.0",
			moddate:    "2011-4-20 20:13",
			createdate: "2010-7-17 08:15",
			prefix:     "API_1484_11",
			errorCode:  0,
			diagnostic: '',
			initialized:0,
			terminated: 0,
			navClick: false // Added to support a non exiting commit (sync vs async)
		},
	/** Settings merged with defaults and extended options */
		settings       = $.extend(defaults, options),
		dataObj = {},
		cmi = {}, // SCORM CMI Object
		ssp = {}, // Shared State Persistence
		adl = {}, // Definition later...
		exitTimer,
		startTime,
		communication  = new Communication(), // local instance
		currSCOID,
		buffer_sco_id  = 0,
		last_sco_id    = 0,
		requested_sco_id = false,
		student_id;
	/**
	* Handle Respose requesting CMI Object
	* @param obj {String}
	*/
	function handleCMIObject(obj) {
		if(obj.status === "success") {
			dataObj = obj.data; // master container of all data
			
			// Oh glory to the containment.... sigh
			cmi = dataObj.cmi;  // just the cmi object
			adl = dataObj.adl;  // just the adl object
			ssp = dataObj.ssp;  // just the ssp object
			loadContent(); // hook back to main
		} else {
			debug(settings.prefix + ": Error: Could not get SCORM 2004 Data for student", 1);
		}
	}
	
	/**
	* Handle Respose requesting CMI Commit
	* @param obj {String}
	*/
	function handleCommit(obj) {
		if(obj.status === "success") {
			debug(settings.prefix + ": Success committing CMI Object", 3);
		} else {
			debug(obj.msg, 2);
		}
	}
	
	/**
	* set Data (Private)
	* Borrowed this from http://stackoverflow.com/questions/2061325/javascript-json-key-value-coding-dynamically-setting-a-nested-value
	* Looked way easier to deal with SCORM "cmi.something.something" than using eval or parsing it to death.
	*/
	function setData (path, value) {
	    if (path.indexOf('.') !== -1) {
	        path = path.split('.');
	        for (var i = 0, l = path.length; i < l; i++) {
	            if (typeof(cmi[path[i]]) === 'object') {
	                continue;
	            } else {
	                cmi[path[i - 1]][path[i]] = value;
	            }
	        }
	    } else {
	        cmi[path] = value;
	    }
	}
	
	function totalTime (first, second) {
        var timestring   = 'P',
       		matchexpr    = /^P((\d+)Y)?((\d+)M)?((\d+)D)?(T((\d+)H)?((\d+)M)?((\d+(\.\d{1,2})?)S)?)?$/,
        	firstarray   = first.match(matchexpr),
        	secondarray  = second.match(matchexpr),
        	firstsecs    = 0,
        	secondsecs   = 0,
        	secs,
        	firstmins    = 0,
        	secondmins   = 0,
        	mins,
        	change,
        	firsthours   = 0,
        	secondhours  = 0,
        	hours,
        	firstdays    = 0,
        	seconddays   = 0,
        	days,
        	firstmonths  = 0,
        	secondmonths = 0,
        	months,
        	years,
        	firstyears = 0,
        	secondyears = 0;
        if ((firstarray !== null) && (secondarray !== null)) {
            if(parseFloat(firstarray[13],10)>0){ firstsecs=parseFloat(firstarray[13],10); }
            if(parseFloat(secondarray[13],10)>0){ secondsecs=parseFloat(secondarray[13],10); }
            secs = firstsecs+secondsecs;  //Seconds
            change = Math.floor(secs/60);
            secs = Math.round((secs-(change*60))*100)/100;
            if(parseInt(firstarray[11],10)>0){ firstmins=parseInt(firstarray[11],10); }
            secondmins=0;
            if(parseInt(secondarray[11],10)>0){ secondmins=parseInt(secondarray[11],10); }
            mins = firstmins+secondmins+change;   //Minutes
            change = Math.floor(mins / 60);
            mins = Math.round(mins-(change*60));
            if(parseInt(firstarray[9],10)>0){ firsthours=parseInt(firstarray[9],10); }
            if(parseInt(secondarray[9],10)>0){ secondhours=parseInt(secondarray[9],10); }
            hours = firsthours+secondhours+change; //Hours
            change = Math.floor(hours/24);
            hours = Math.round(hours-(change*24));
            if(parseInt(firstarray[6],10)>0){ firstdays=parseInt(firstarray[6],10); }
            if(parseInt(secondarray[6],10)>0){ firstdays=parseInt(secondarray[6],10); }
            days = Math.round(firstdays+seconddays+change); // Days
            if(parseInt(firstarray[4],10)>0){ firstmonths=parseInt(firstarray[4],10); }
            if(parseInt(secondarray[4],10)>0){ secondmonths=parseInt(secondarray[4],10); }
            months = Math.round(firstmonths+secondmonths);
            if(parseInt(firstarray[2],10)>0){ firstyears=parseInt(firstarray[2],10); }
            if(parseInt(secondarray[2],10)>0){ secondyears=parseInt(secondarray[2],10); }
            years = Math.round(firstyears+secondyears);
        }
        if (years > 0) {
            timestring += years + 'Y';
        }
        if (months > 0) {
            timestring += months + 'M';
        }
        if (days > 0) {
            timestring += days + 'D';
        }
        if ((hours > 0) || (mins > 0) || (secs > 0)) {
            timestring += 'T';
            if (hours > 0) {
                timestring += hours + 'H';
            }
            if (mins > 0) {
                timestring += mins + 'M';
            }
            if (secs > 0) {
                timestring += secs + 'S';
            }
        }
        return timestring;
    }
	
	function currentTime() {
		var d = new Date();
		return d.getTime() + (Date.remoteOffset || 0);
	}
	
	// Public API's ////////////////////////////////
	
	/**
	* Get CMI Object from Server
	*
	*/
	this.GetCMI = function(id) {
		debug("------ BEGIN ------- SCOID: " + id + " >>>>>>", 3);
		student_id = portal.login.get('profile').id;
		var data_obj = {
    	        action:  "SCORM_2004_CMI",
    	        user_id: student_id,
    	        sco_id:  id,
    	        mode: 'normal'
	        };
	    // Store sco id
	    buffer_sco_id = data_obj.sco_id;
		communication.sendMessage(data_obj, handleCMIObject, "API_1484_11: GetCMI()");
	};
	
	
	/**
	* Initialize Session (SCORM) only once!
	* @param name {String} Appears to be unused
	* @returns "true" or "false" depending on if its been initialized prior
	*/
	this.Initialize = function() {
		if(this.isWaiting()) {
			this.set('initialized', 1);
			debug(settings.prefix + ": API SCORM_2004 version " + settings.version + " modified " + settings.moddate + " ready.", 3);
			startTime = currentTime();
						
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
		debug(settings.prefix + ": Running: " + this.isRunning() + " GetValue: " + param + "...", 4);
		var r = "false";
		if(this.isRunning()) {
			switch(param) {
				//Write Only
				case "cmi.exit": case "cmi.session_time":
					settings.errorCode = 405;
					return 'false';
				//break;
				
				default:
					/*jslint evil: true */
					r = eval(param);
					debug("LMS GetValue " + param + " " + r, 3);
					/*jslint evil: false */
					// Filter
					if(r === undefined || r === null) {
						settings.errorCode = 401;
						r = "false";
					}
					
					return r;	
				//break;
			}
		} else {
			settings.errorCode = 123;
			return r;
		}
	};
	
	/**
	* SetValue (SCORM)
	* @param key {String}
	* #param value {String]
	* @returns "true" or "" depending on if its been initialized prior
	*/
	this.SetValue = function(key, value) {
		debug(settings.prefix + ": Running: " + this.isRunning() + " SetValue: " + key + " :: " + value, 4);
		var s,
			data = value + '', // ensure string
			obj,
			ka=[];
			
		if(this.isRunning()) {
			 //eval(param + "=" + value +";");
			 //s = data;
			 // Do more logical checkups later
			 if (!obj) { obj = data;} //outside (non-recursive) call, use "data" as our base object
			  ka = key.split(/\./); //split the key by the dots
			  if (ka.length < 2) { 
			    obj[ka[0]] = value; //only one part (no dots) in key, just set value
			  } else {
			    if (!obj[ka[0]]) { obj[ka[0]] = {};} //create our "new" base obj if it doesn't exist
			    obj = obj[ka.shift()]; //remove the new "base" obj from string array, and hold actual object for recursive call
			    setData(ka.join("."),data,obj); //join the remaining parts back up with dots, and recursively set data on our new "base" obj
			  }    
			 
			 return "true";
		} else {
			// Determine Error Code
			if(settings.terminated) {
				settings.errorCode = 133;
			} else {
				settings.errorCode = 132;
			}
			return "false";
		}
	};
	
	/**
	* Commit (SCORM)
	* @param param {String}
	* Typically empty, I'm unaware of anyone ever passing anything.
	* @returns "true" or "false" 
	*/
	this.Commit = function(param) {
        settings.errorCode = "0";
        var data_obj = {},
        	comm = new Communication();
        if (param === "") {
            if (this.isRunning()) {
            	
            	// Set Total Time
            	cmi.total_time = totalTime(cmi.total_time, cmi.session_time);

            	// Save this to the Server
            	
            	data_obj = {
            		action: "SCORM_2004_Commit",
            		sco_id: buffer_sco_id,
            		data: dataObj
            	};
	            // Need to set total time (total time + session time)
                comm.sendMessage(data_obj, handleCommit, "API_1484_11: Commit() via nav:" + settings.navClick, (settings.navClick) ? false : true); // Don't need sync call made on a nav click, otherwise exit will be a sync call so we can store data safely.
                settings.navClick = false;
                last_sco_id = buffer_sco_id; // for the purpose of logging
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
            var errorString = "",
            	nparam = parseInt(param, 10);
            switch(nparam) {
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
		clearTimeout(exitTimer);
		debug(settings.prefix + ": Clearing exit timer ...", 3);
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
			//debug(JSON.stringify(cmi, null, " "), 4);
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
			settings.terminated  = 0;
			debug(settings.prefix + ": Reset", 4);
			debug("------- END -------- SCOID: " + last_sco_id + " <<<<<<< \n\n\n\n", 3);
			if(requested_sco_id !== false) {
				debug(settings.prefix + ": Attempting to load new SCO " + requested_sco_id + "\n\n\n\n", 4);
				API_1484_11.GetCMI(requested_sco_id); // request new CMI Object
				requested_sco_id = false;
			}
    	} else {
    		debug(settings.prefix + ": Attempting to reset API while SCO is in progress not allowed!", 4);
    	}
    };
    
    /**
    * Start Exit Timer
    * Fail safe to commit/save content and keep the train moving
    */
    this.startExitTimer = function(dTime, id) {
    	var time = dTime !== null ? dTime : 3000,
    		that = this; // 3 seconds might be enough, maybe not
    	requested_sco_id = id ? id : false; // save requested SCO until the last one finishes
    	debug("Staring Exit Countdown " + time + "...");
    	exitTimer = setTimeout(function() {
    		debug(settings.prefix + ": Warning Developer, I had to terminate this SCO", 2);
    		that.Commit("");
    		that.Terminate();
    	}, time);
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