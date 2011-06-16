/*global $, JQuery, debug, portal*/
/**
 * Events are the onload, onbeforeunload and onunload management for the page.  You
 * can route these events accordingly to manage app startup and exit.
 * -isExit covers the exit method so it only fires once
 * -portal is the namespace for the project.
 */

var isExit = false;
function init() {
	debug("LMS Loaded from window.onload", 4);
	portal.init();
}
function exit() {
	if(!isExit) {
		isExit = true;
		debug("LMS is done unloading.", 4);
		portal.exit();
	}
}

// Load & Exit Events
window.onload         = init;
window.onbeforeunload = exit;
window.onunload       = exit;

// Note: altered to JQuery document ready .. seems to work fine.
// Note: $(window).unload not reliable, sticking with window.x
// Found out 'console' was the problem.  Enabling Debug, then not having a Debug console = bad.
// Updated console_log to now point to noconsole if console not defined.
/* Trouble shooting code
$(document).ready(function(){
	alert("FireFox WTH? init() is a ? " + typeof(init));
	if(typeof(init) == "function") {
		alert("Great its a function, no call it...");
		try{
			init();
		} catch(e) {
			alert(e);
		}
	}
});
*/
