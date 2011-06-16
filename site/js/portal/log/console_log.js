/** Base Debug Functionality to write to the console (see firebug, safari dev tools etc ...) http://getfirebug.com/logging
	1 = Error
	2 = Warning
	3 = Log/General
	4 = Info/Report
	Usage:  debug("Message", 3);
 */
var enableDebug, domainURL;

// dead function for no console .. could route to alert prompts but I'm not that evil.
function noconsole(msg) {} // Mostly for IE7 and prior
function debug(msg, lvl) {
	if (enableDebug === 1) {
		if (!window.console) { // Just in case the developer doesn't have a debug log installed in there browser
			window.console = {};
			window.console.info = noconsole;
			window.console.log = noconsole;
			window.console.warn = noconsole;
			window.console.error = noconsole;
			window.console.trace = noconsole;
		}
		switch (lvl) {
			case 1:
				console.error(msg);
				break;
		    case 2:
		    	console.warn(msg);
		        break;
		    case 4:
		        console.info(msg);
		        break;
		    default:
		       	console.log(msg);
		        break;
        }
	}
}

// Old XML output from Literature (some others may of used it)
// May have to turn the passedData into XML to see it nice and cleanly in the console.
function reportCleanXML(passedData){
	console.trace(passedData);
}

/** Query String Management */
var qsvars = location.search.substring(1, location.search.length); // Querystring Vars
if (qsvars.length > 0) {
	var nvpairs = qsvars.split("&"); // Split the & 
	for (var i = 0; i < nvpairs.length; i++) { // Parse Name Value Pairs
		var nv = nvpairs[i].split("="); // Split the =
		// Handle Name Value Pairs ////////////////////
		switch (nv[0].toLowerCase()) {
			case "domainURL":
				domainURL = nv[1];
				break;
			case "debug":
			case "enabledebug":
				enableDebug = parseInt(nv[1], 10);
				break;
			default:
				debug("Portal: Unknown QueryString data sent ... " + nv[0] + " = " + nv[1], 2);
				break;
		}
		debug("Portal: QueryString Override -  name:" + nv[0] + "  value:" + nv[1] + " enableDebug is now " + enableDebug, 3);
	}
}
// Time Stamp //////////////////////////////////////
/*function getTime(type) { // If you pass "MMDDYY" you can get the full date.
    var today_date = new Date();
    var date_str;
    var timeMarker;
    var timeMinute;
    if (today_date.getHours() < 12) {
        timeMarker = "AM";
    } else {
        timeMarker = "PM";
    }
    if (today_date.getMinutes() < 10) {
        timeMinute = "0" + today_date.getMinutes();
    } else {
        timeMinute = today_date.getMinutes();
    }
    if (type == "MMDDYY") {
        date_str = ((today_date.getMonth() + 1) + "/" + today_date.getDate() + "/" + today_date.getFullYear() + " - " + today_date.getHours() + ":" + timeMinute + " " + timeMarker);
    } else {
        var mseconds = String(today_date.getMilliseconds());
        if (mseconds.length < 2) {
            mseconds += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        } else if (mseconds.length < 3) {
            mseconds += "&nbsp;&nbsp;";
        }
        var seconds = today_date.getSeconds().toString();
        if (seconds.length == 1) {
            seconds = "0" + seconds;
        }
        date_str = (today_date.getHours() + ":" + timeMinute + ":" + seconds + ":" + mseconds + " " + timeMarker);
    }
    return date_str;
}*/