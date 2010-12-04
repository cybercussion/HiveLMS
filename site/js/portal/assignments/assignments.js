/**
 * Assignments
 * Initialized with the assignment object from the LMS.  This is currently coming from the Login.js
 * to elminate another round trip to the server.  It will construct the HTML markup for the assignment based
 * on the data in the object.
*/
function Assignments(options) {
	// Constructor
	var defaults = {
			version: "1.0",
			moddate: "2010-11-20 11:20",
			createdate: "2010-11-20 11:20",
			assignments: []
		},
		settings = $.extend(defaults, options);
		
	// End	
	// Private Methods ///////////////
	function clearAssignmentGroups() {
		$('#assignments #Assign_C .toolbar_c').html("");
	}
	function buildAssignmentGroup(id) {
		// Technically you could theme the assignment group based on the course thats being added here.  For now its just a default style.
		$('#assignments #Assign_C .toolbar_c').append("<div id=\"assign_"+ id +"\"class=\"assigngroup\">" + 
												"<div class=\"container rounded L_bg1\">" +
													"<ol class=\"steps rounded\">" +
													"</ol>" +
												"</div>" +
											"</div>");
	}
	
	
	function Construct() {
		// Note: may need to change this around a bit for multiple courses. (just another tier)
		$(settings.assignments.course).each(function(i) {
			buildAssignmentGroup(this.id);
			// Limit additions to the newly added group
			$("#assign_"+this.id + " .container .steps").append("<li>" +
					"<a class=\"arrow\" href=\"#lessons\">" +
						"<div class=\"list_bar\">" +
							"<h6>" + this.name + "</h6>" +
							"<div class=\"list_nav\">" +
								"<div class=\"nav_icon\"></div>" +
							"</div>" +
						"</div>" +
						"<div class=\"list_desc\">" +
							"<div class=\"minical rounded floatR\">" +
								"<div class=\"bar roundedTop\">December</div>" +
								"<div class=\"date\">3</div>" +
							"</div>" +
							"<p>" + this.data.description + "</p>" +
						"</div>" +
					"</a>" +
				"</li>");
		});
	}
	
	
	// Public Methods ////////////////
	this.init = function(_obj) {
		clearAssignmentGroups();
		debug("Initializing Assignments ...", 4);
		settings.assignments = _obj;
		Construct();
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