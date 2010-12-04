/**
 * Login User.  Validate, handle response.
 *
*/
function Login(options) {
	// Constructor
	var defaults = {
			version: "1.0",
			moddate: "2010-7-31 19:52",
			createdate: "2010-7-31 19:52",
			cookie: 'HiveLMS',
			user: 'student@hivelms.com',
		},
		settings = $.extend(defaults, options);
	//$("#password").chromaHash({bars: 3, salt:"7be82b35cb0199120eea35a4507c9acf", minimum:6});
	// Populate Username from Cookie if it exists
	var cookieEmail = $.cookie('email');
	if(cookieEmail !== null && cookieEmail !== undefined) {
		$('#accountName').val(cookieEmail);
	} else {
		$('#accountName').val(settings.user);
	}
	
	///////////////// Private Functions ////////////////////////////////
	function handleResponse(_obj) {
		switch(_obj.status) {
			case 'success':
				portal.playAudio('success');
				// We'll get back the profile object, set it locally to the Login object
				settings.profile = _obj.login.profile;
				
				// Lets set up some Cookie stuff 
				$.cookie('email', settings.profile.email, {expires: 30}); // set username in cookie
				$('#accountName').val(settings.profile.email);
				// Build Assignments - Note: I wanted to elminate another round trip so it comes back with the profile
				portal.assignments.init(_obj.assignment);
				portal.navigation.goTo('#assignments', 'slide'); // Note flip, cube and swap a little visually buggy in chrome.
				enableInput(true, $('#login_btn'));
				// remove success icon
				setTimeout(function() {
					$('#login_status').removeClass('success');
				}, 500);
			break;
			case 'fail':
				portal.playAudio('error');
				$('#accountName').addClass('inputerror');
				$('#password').addClass('inputerror');
				enableInput(true, $('#login_btn'));
			break;
			case 'error':
			
			break;
			default:
				debug('Login: Sorry, unexpected type received via Login', 2);
			break;
		}
		setStatusMsg(_obj.login.msg, _obj.status); // msg, icon
	}
	
	function setStatusMsg(_msg, _icon) {
		var msg = _msg ? _msg : "";
		$('#login_status').removeClass('success error fail').addClass(_icon).html(msg);
	}
	
	function isValidEmailAddress(_email) {
		var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
		return pattern.test(_email);
	}
	
	function validateUser(user, pass, target) {
		enableInput(false, target);
		var $this = this;
		if(!isValidEmailAddress(user)) {
			$('#accountName').addClass('inputerror');
			// Come back and whip up some validation later
			debug('Invalid Email Address!', 2);
			// Re-enable Login button
			enableInput(true, target);
			portal.playAudio('error');
		} else {
			$('#accountName').removeClass('inputerror');
			$('#password').removeClass('inputerror');
			debug("Validating " + user, 4);
			
			var data_obj = {
	            action: "Login",
	            email: user,
	            password: pass,
	            screensize: getScreenSize(),
	            pixeldepth: getPixelDepth()
	        };
			portal.communication.sendMessage(data_obj, handleResponse, "login.js: validateUser");
		}
	}
	
	function enableInput(type, target) {
		if(type) {
			target.removeAttr('disabled')
				  .removeClass('processing'); 
			target.find('span span').html('Log In');
			$('.active').removeClass('active'); // Clean up touch selections
		} else {
			target.find('span span').html('Processing...');
			target.removeClass('hover')
                   .addClass('processing')
                   .attr('disabled', 'disabled');
		}
	}
	// Maybe offload these to a utility object
	function getScreenSize() {
		if (window.screen) {
			return window.screen.width+"x"+window.screen.height;
		} else {
			return 'Unknown';
		}
	}
	
	function getPixelDepth() {
		if (window.screen.pixelDepth) {
			return window.screen.pixelDepth;
		} else if (window.screen.colorDepth) {
   			return window.screen.colorDepth;
		} else {
			return 'Unknown';
		}
	}
	// End Utility
	
	///////////////// Public Functions ////////////////////////////////
	this.init = function() {
		// Login logic
	    $('#login_btn').click(function() {
	    	//portal.playAudio('click');
            validateUser($('#accountName').val(), $('#password').val(), $(this));
			return false;
		});
		$('#accountName').focus(function() {
			portal.playAudio('focus');
			if($(this).val() === settings.user) {
				$(this).val('');
			}
		});
		$('#accountName').blur(function() {
			if($(this).val() === '') {
				$(this).val(settings.user);
			}
		});
		$('#password').focus(function() {
			portal.playAudio('focus');
			if($(this).val() === 'password') {
				$(this).val('').trigger('keyup');
			}
		});
		$('#password').blur(function() {
			if($(this).val() === '') {
				$(this).val('password').trigger('keyup');
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