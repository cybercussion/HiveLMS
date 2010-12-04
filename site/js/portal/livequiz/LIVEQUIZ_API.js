function LivequizAPI(options) {
	var defaults = {
			version: "1.0",
			date: "2010-11-13 06:00",
			isError: false,
			live_quiz: null
		},
		settings = $.extend(defaults, options),
		check4Quiz;
	// End Constructor
	
	// Private LMS call
	function getQuiz() {
		var data_obj = {
	 		action: "Live_Quiz_Get",               // PHP Class
	 		status: 1                              // Quiz in lobby
 		};
 		portal.communication.sendMessage(data_obj, handleGetQuiz, "LIVEQUIZ_API.js: getQuiz");
	}
	
	function handleGetQuiz(_obj) {
		switch(_obj.status) {
			case 'success':
				settings.live_quiz = _obj.live_quiz;
				if(settings.live_quiz.length > 1) {
					debug("Looks like we have " + settings.live_quiz.length + " Quiz(s) running.", 4);
					$(settings.live_quiz).each(function(i) {
						debug("Quiz: " + this.id + " at " + this.time, 4);
						// Maybe add mulitple quizes?  This shouldn't be for the demo.
					});
				} else {
					addNewAssignmentGroup(settings.live_quiz[0].guid); // setting the assignment group to the teachers guid so its unique
					addQuizToLessons(settings.live_quiz[0]);
				}
				clearInterval(check4Quiz); // stop checking or maybe check less often?
			break;
			default:
				debug('Login: Sorry, unexpected type received via Login', 2);
			break;
		}
	}
	
	// LMS call
	function addNewAssignmentGroup(guid) {
		// Check to see if list group is already added
		if($('#lq_'+guid).length == 0) {
			// Add it
			$('#lesson_list').append("<div class=\"assigngroup\"><div class=\"container rounded L_bg1\"><ol id=\"lq_"+guid+"\" class=\"steps rounded\"></ol></div></div>");
			debug("Adding Assignment group lq_" + guid, 4);
		} else {
			// Ignore it
			debug("Sorry, you have already added this Assignment Group " + guid, 4);
		}	
	}
	
	// LMS call
	function addQuizToLessons(live_quiz) {
		if($('#lq_'+live_quiz.id).length == 0) {
			// This will add a new assignment group to the lessons screen
			$('#lq_'+live_quiz.guid).append("<li id=\"lq_"+live_quiz.id+"\"><a class=\"arrow\" rel=\""+ live_quiz.path +"?lq="+live_quiz.id+"\" href=\"#sco_wrapper\"><div class=\"list_bar\"><h6>"+live_quiz.title+"</h6><div class=\"list_nav\"><div class=\"nav_icon\"></div></div></div><div class=\"list_desc\"><p>Time Created: "+live_quiz.time+"</p><p>This is a live quiz, driven by the Teacher.</p></div></a></li>");
			debug("Adding quiz lq_"+live_quiz.id+ " to assignment group lq_"+live_quiz.guid, 4);
		} else {
			debug("This quiz was already added, maybe we need to update here?", 4);
		}
	}
	
	// Public API's
	/**
	 * Enter Class
	 * This will log the student into the Live Quiz Class so the teacher can see them enter.
	*/
	this.enterClass = function(params) {
		var data_obj = {
		 		action: "Live_Quiz_Class_Set",                     // PHP Class
		 		tierid: params.tierid,                             // LiveQuiz ID
		 		guid: portal.login.get('profile').guid,            // Portal User Guid from Login (change later)
		 		page: params.page,                                 // Page Student is on
		 		totalpage: params.totalpage,                       // Total Pages in Quiz
		 		correct: 0,                                        // default 0
		 		answer: null
	 		};
	 	portal.communication.sendMessage(data_obj, params.callBack, "LIVEQUIZ_API.js: enterClass");
	};
	/**
	 * Update Page
	 * page update only
	*/
	this.updatePage = function(params) {
		var data_obj = {
		 		action: "Live_Quiz_Class_Set",                     // PHP Class
		 		id: params.id,                             // LiveQuiz Class ID
		 		page: params.page                          // Page number they are on
	 		};
	 	portal.communication.sendMessage(data_obj, params.callBack, "LIVEQUIZ_API.js: updatePage");
	};
	/**
	 * Update Score
	 * Update correct, answer
	*/
	this.updateScore = function(params) {
		var data_obj = {
		 		action: "Live_Quiz_Class_Set",                     // PHP Class
		 		id: params.id,                             // LiveQuiz Class ID
		 		correct: params.correct,                          // Page number they are on
	 			answer: params.answer
	 		};
	 	portal.communication.sendMessage(data_obj, params.callBack, "LIVEQUIZ_API.js: updateScore");
	};
	/**
	 * Lobby Status
	 *
	*/
	this.getQuizStatus = function(params) {
		// Check the Lobby to see if the teacher has started it yet.
		debug("Checking Quiz status for " + params.id, 4);
		var data_obj = {
	 		action: "Live_Quiz_Get",               // PHP Class
	 		id: params.id                              // Quiz in lobby
 		};
 		portal.communication.sendMessage(data_obj, params.callBack, "LIVEQUIZ_API.js: getQuiz");
	};
	
	// LMS public API - yeah I know, break this into its own 'object' but its the weekend and this needs to get done.
	/**
	 * Quiz Checkup 
	 * Used by LMS to check with the server to see if a Quiz is Available for this Lesson (rudimentary now)
	 * @param type {Boolean} true or false (start or stop)
	*/
	this.quizCheckup = function(type) {
		debug("Quiz Checkup " + type);
		if(type) {
			// setInterval to look up a quiz
			getQuiz();
			check4Quiz = setInterval(getQuiz, 5000);
		} else {
			// clearInterval to look up a quiz
			clearInterval(check4Quiz);
		}
	};
}
var LIVEQUIZ_API = new LivequizAPI();