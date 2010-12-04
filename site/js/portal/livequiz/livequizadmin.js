/**
 * Live Quiz Admin
 * Series of API's to test the server side scripts.
 * Logging in window vs console
 */
 var enableDebug =1,
 	 settings = {},
 	 page = 0,
 	 started = false,
 	 checkLiveQuizClass = null,
 	 communication = new Communication(); // Portal's default communication handler
 
 // Local log in DIV window
 function localLog(msg) {
 	$('#remote_log').append("<p>"+msg+"</p>")
 }
 // Create Live Quiz
 function createLiveQuiz() {
 	if(!settings.live_quiz) {
	  	var data_obj = {
		 		action: "Live_Quiz_Set",                     // PHP Class
		 		guid: "d5464516d59102dccd60d5471cbff3e4",    // Your Teacher User GUID
		 		title: "Pearson Quiz",                   // Name of your Quiz
		 		path: "content/ic/livequiz/player.html"  // Path to your Quiz
	 		};
	 	communication.sendMessage(data_obj, handleLiveQuizResponse, "livequizadmin.js: createLiveQuiz");
	 } else {
	 	localLog("Sorry, you already created a quiz!");
	 }
 }
 // Start Live Quiz
 function startLiveQuiz() {
 	if(!started) {
	 	var targetpage = page + 1; // auto advance
	 	if(settings.live_quiz != undefined) {
		 	var data_obj = {
			 		action: "Live_Quiz_Set",                     // PHP Class
			 		id: settings.live_quiz[0].id,                // Quiz ID
			 		page: targetpage,                            // Locally controlled Page from 0 to 1
			 		status: 2                                    // 1=Lobby, 2=running, 3=locked, 4=ended
		 		};
		 	communication.sendMessage(data_obj, handleLiveQuizStartResponse, "livequizadmin.js: startiveQuiz");
		 	
		 } else {
		 	localLog("Sorry, you don't actually have a quiz to control!");
		 }
	} else {
		localLog("Silly, you already started it!");
	}
 }
 // End Live Quiz
 function endLiveQuiz() {
 	if(started) {
 		if(settings.live_quiz[0].id) {
		 	var data_obj = {
			 		action: "Live_Quiz_Set",                     // PHP Class
			 		id: settings.live_quiz[0].id,                // Quiz ID
			 		status: 4                                    // 1=Lobby, 2=running, 3=locked, 4=ended
		 		};
		 	communication.sendMessage(data_obj, handleLiveQuizEndResponse, "livequizadmin.js: endLiveQuiz");
		} else {
			localLog("Sorry, you don't actually have a quiz to control!");
		}
	} else {
		localLog("No no, you never started a quiz!");
	}
 }
 // Rejoin Quiz by ID
 function reJoinQuiz(_id) {
 	var data_obj = {
	 		action: "Live_Quiz_Get",                   // PHP Class
	 		id: _id                                    // Quiz in run mode
 		};
 	communication.sendMessage(data_obj, handleLiveQuizResponse, "livequizadmin.js: reJoinQuiz");
 }
  // Get Quiz by status
 function getQuiz(_status) {
 	var data_obj = {
	 		action: "Live_Quiz_Get",                     // PHP Class
	 		status: _status                              // Quiz in run mode
 		};
 	communication.sendMessage(data_obj, handleLiveQuizResponse, "livequizadmin.js: getQuiz");
 }
 
 function dumpQuiz(_id) {
 	var data_obj = {
	 		action: "Live_Quiz_Dump",                     // PHP Class
	 		id: _id ? _id : settings.live_quiz[0].id     // Either the quiz ID you pass in, or the one running
 		};
 	communication.sendMessage(data_obj, handleDumpResponse, "livequizadmin.js: dumpQuiz");
 }
 // Advance Live Quiz Page
 function advanceLiveQuizPage(num) {
 	if(started) {
	 	var targetpage = num ? num : page + 1; // auto advance
	 	if(settings.live_quiz[0].id) {
		 	var data_obj = {
			 		action: "Live_Quiz_Set",                     // PHP Class
			 		id: settings.live_quiz[0].id,                // Quiz ID
			 		page: targetpage                             // Your locally set page
		 		};
		 	communication.sendMessage(data_obj, handleLiveQuizPageResponse, "livequizadmin.js:  advanceLiveQuizPage");
		 } else {
		 	localLog("Sorry, you don't actually have a quiz to control!");
		 }
		 page = targetpage; // increment local
	} else {
		localLog("Gee-wiz, you didn't start the quiz yet.");
	}
 }
 
 function getLiveQuizClassStatus() {
 	//if(!started) {
 		//Class hasn't started yet, log new students
	 	if(settings.live_quiz[0].id) {
		 	var data_obj = {
			 		action: "Live_Quiz_Class_Get",                     // PHP Class
			 		id: settings.live_quiz[0].id,                // Quiz ID
		 		};
		 	communication.sendMessage(data_obj, handleLiveQuizClassStatusResponse, "livequizadmin.js: getLiveQuizClassStatus");
		 } else {
		 	localLog("Sorry, you don't actually have a quiz to control!");
		 }
	//} else {
		//Class is started, obtain student updates
	//}
 
 }
 
 // Handlers /////////////////////////////////
  // Quiz Response
 function handleLiveQuizResponse(_obj) {
 	switch(_obj.status) {
		case 'success':
			settings.live_quiz = _obj.live_quiz;
			if(settings.live_quiz.length > 1) {
				localLog("Looks like you have " + settings.live_quiz.length + " Quiz(s) running.");
				$(settings.live_quiz).each(function(i) {
					localLog("Quiz: " + this.id + " at " + this.time);
				});
			} else {
				localLog("Success getting live quiz. Your ID is " + settings.live_quiz[0].id + ". Please wait for people to join.");
				localLog(" -- At this point you'll want to launch the LMS and see if a live quiz appears under the Innovation Challenge (Assignment)");
			}
			checkLiveQuizClass = setInterval(getLiveQuizClassStatus, 2000);
		break;
		case 'fail':
			
		break;
		case 'error':
		
		break;
		default:
			debug('Login: Sorry, unexpected type received via Login', 2);
		break;
	}
 }
 // Page Handler
  function handleLiveQuizPageResponse(_obj) {
 	switch(_obj.status) {
		case 'success':
			localLog("Success advancing the page.");
			
		break;
		case 'fail':
			
		break;
		case 'error':
		
		break;
		default:
			debug('Login: Sorry, unexpected type received via Login', 2);
		break;
	}
 }
// End Handler
  function handleLiveQuizEndResponse(_obj) {
 	switch(_obj.status) {
		case 'success':
			localLog("Quiz has ended.");
			started = false;
			page = 0;
			settings = {};
			clearInterval(checkLiveQuizClass);
			checkLiveQuizClass = null;
			
		break;
		case 'fail':
			
		break;
		case 'error':
		
		break;
		default:
			debug('Login: Sorry, unexpected type received via Login', 2);
		break;
	}
 }
 // Start handler
 function handleLiveQuizStartResponse(_obj) {
 	switch(_obj.status) {
		case 'success':
			page++;
			started = true;
			localLog("Live Quiz Started.");
			// Begin Polling Class
			//checkLiveQuizClass = setInterval(getLiveQuizClassStatus, 2000);
			
		break;
		case 'fail':
			
		break;
		case 'error':
		
		break;
		default:
			debug('Login: Sorry, unexpected type received via Login', 2);
		break;
	}
 }
 
 // Live Quiz Class Status
 function handleLiveQuizClassStatusResponse(_obj) {
 	switch(_obj.status) {
		case 'success':
			// Do some things.
			$('#classroom_status').html("");
			$(_obj.live_quiz_class).each(function(i) {
				$('#classroom_status').append("<div class=\"row\"><div class=\"floatL studentNum\">"+i+"</div><div class=\"floatL studentGUID\">"+this.guid+"</div><div class=\"floatL studentPage\">"+this.page+"</div><div class=\"floatL studentTotalPage\">"+this.totalpage+"</div><div class=\"floatL studentCorrect\">"+this.correct+"</div><div class=\"floatL studentAnswer\">"+this.answer+"</div><div class=\"floatL studentTimeStamp\">"+this.time+"</div></div></div>");
			});
		break;
		case 'fail':
			$('#classroom_status').html("<p>Sorry, no students have arrived yet.</p>");
		break;
		case 'error':
		
		break;
		default:
			debug('Login: Sorry, unexpected type received via Login', 2);
		break;
	}
 }
 
  // Start handler
 function handleDumpResponse(_obj) {
 	switch(_obj.status) {
		case 'success':
			started = false;
			page = 0;
			settings = {};
			localLog("Live Quiz Dumped.");
			
			clearInterval(checkLiveQuizClass);
			checkLiveQuizClass = null;
			
		break;
		case 'fail':
			
		break;
		case 'error':
		
		break;
		default:
			debug('Login: Sorry, unexpected type received via Login', 2);
		break;
	}
 }