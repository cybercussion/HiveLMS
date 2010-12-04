/**
 * @author Mark Statkus mark@cybercussion.com
 * @version 1.0
 * Description
 * All this code is dependant on the use of the JQuery Framework.
 * @requires 
 * @constructor
 * @namespace portal
 * @param options {Object} This allows for override/new values.
 */
function Portal(options) {
	// Constructor
	/** @default version, date, isError, domainURL, scoXMLFile, state, language, learnerlevel, grade, bucketid */
	var defaults = {
			version: "1.0",
			moddate: "2010-7-7 09:55",
			createdate: "2010-7-7 09:55",
			isError: false,
			orientation: '',
			userAgent: navigator.userAgent.toLowerCase(),
			standalone: false,
			fullScreenClass: 'fullscreen',
			statusBar: 'default',
			windowLaunch: false
		},
	/** Settings merged with defaults and extended options */
		settings = $.extend(defaults, options);
		windowRef = '';
	settings.useTouch = ('ontouchstart' in window) && !/chrome/i.test(settings.userAgent);
	if(settings.userAgent.match(/iphone/i) || settings.userAgent.match(/ipod/i) || settings.userAgent.match(/ipad/i) || settings.userAgent.match(/android/i)) {
		settings.mobile = true;
		settings.windowLaunch=true;
	}
		
	this.communication  = new Communication();
	this.login          = new Login();
	this.navigation     = new Navigation();
	this.assignments    = new Assignments();
	// End Constructor
	
	// Private Methods
	/**
	 * Touch Events
	 * Managing the behaviors for touch
	 * @param e {Object} Event object from touch
	*/
	function onTouchStart(e) {
    	//debug("touch start", 4);
    	//e.preventDefault();
    }
    
	function onTouchMove(e) {
        if(settings.useTouch) {
            e.preventDefault();
       		//e.stopPropagation();
       	}
    }
    
    function onTouchEnd() {
    	//debug("touch end", 4);
    }
    
    /**
	 * On Window Close
	 * Wraps up the closing of a external window (typically a SCO)
	*/
    function onWindowClose() {
  		setTimeout(function() {
  			if(windowRef === null || windowRef.closed) {
  				$('li a.active').removeClass('active');
  				$.unblockUI();
  				debug("SCO Window closed, attempting to go back to lessons", 4);
  			} else {
  				//debug("window still open " , 4);
  				if(API_1484_11.get('terminated') !== 0) {
  					//debug("SCO was terminated, attempting to close window", 4);
  					//windowRef.self.close();
  				}	
  				onWindowClose(windowRef);
  			}
  		}, 20);
    }
	
	/**
	 * Set Scroll To
	 * Takes care of the calls to scrollTo for iPhone/iPod.  Making this call against IE can cause syntax errors.
	*/
	function setScrollTo(_time) {
		var time = _time || 100;
		if(settings.userAgent.match(/iphone/i) || settings.userAgent.match(/ipod/i)) {
			setTimeout(function() {window.scrollTo(0, 1);}, time);
		}
	}
	
	/**
	 * Handle Response
	 * Takes care of the response from a Ajax call below with communication to the backend
	 * @param _obj {Object} JSON Object from server
	*/
	function handleResponse(_obj) {
	
	}
	
	// Public Methods
	/** Initialize */
	this.init = function() {
		if(!settings.useTouch) {
			// append Audio
			$('body').append('<div id=\"audiocontainer\"><audio id=\"uiaud_btn\" src=\"media/aud/tick.mp3\" controls=\"\"></audio><audio id=\"uiaud_focus\" src=\"media/aud/tabfocus.mp3\" controls=\"\"></audio><audio id=\"uiaud_success\" src=\"media/aud/success.mp3\" controls=\"\"></audio><audio id=\"uiaud_error\" src=\"media/aud/error.mp3\" controls=\"\"></audio></div>');
		}
		
		// Check the Server
		this.setScrollTo = setScrollTo;
		debug("Server Checkup.... ", 4);
		var data_obj = {
    	        action: "Server_Checkup"
	        };
		portal.communication.sendMessage(data_obj, handleResponse, "portal.js: Server_Checkup");
		// End
		var imagesLoaded = this.preloadImages(['css/img/btn_new.png',
							'css/img/buttons/default.png',
							'css/img/bg/bg_sky.jpg',
							'css/img/bg/R_Corner1.jpg',
							'css/img/bg/L_Corner1.jpg',
							'css/img/bg/L_Corner2.jpg',
							'css/img/Assignment.png']);
		debug("Preloaded base images: " + imagesLoaded);
		$('#portal').fadeIn('slow');
		this.navigation.init();
		this.login.init();
		// Mobile Device Orientation Management
	    $(window).bind('orientationchange resize', this.updateOrientation)
	             .trigger('orientationchange');
	    //$('#password').blur(); // adds chroma-hash
	    
		// Lets manage the default behavior for touching
		if (settings.useTouch) {
            $(document).bind('touchstart', onTouchStart)
						.bind('touchmove', onTouchMove)
						.bind('touchend', onTouchEnd);
        } else {
        	$(document).bind('mousedown', onTouchStart)
						.bind('mousemove', onTouchMove)
						.bind('mouseup', onTouchEnd);
        }
        $('#Assign_C').touchScroll();
        $('#Lesson_C').touchScroll();
        //myScroll = new iScroll('scroller');
        setScrollTo(1000); // Hide location bar was paused 1000
        if(window.navigator.standalone === true) {
        	settings.standalone = true;
        	//alert("Homescreen launch, not ready yet.  You will have issues launching content and getting back to the LMS.");
        }
		
		if (settings.fullScreenClass && settings.standalone == true) {
           $body.addClass(settings.fullScreenClass + ' ' + settings.statusBar);
        }
		
		return true;
	};
	
	/** Exit */
	this.exit = function() {
		return true;
	}; // End Exit
	

	/** Orientation Update */
	this.updateOrientation = function() {
		settings.orientation = Math.abs(window.orientation) === 90 ? 'landscape' : 'portrait'; 
        portal.set('orientation', settings.orientation);
        $('body').removeClass('portrait landscape').addClass(settings.orientation).trigger('turn', {orientation: settings.orientation});
        debug("Orientation Change: " + settings.orientation + ": " + $(window).width() + "x" + $(window).height(), 4);
        if(!portal.get('useTouch')) {
	        var headerH = $('div.current .header').height() || 0,
				footerH = $('div.current .footer').outerHeight() || 0,
				wrapperH = document.body.clientHeight - headerH - footerH;
				differenceH = parseInt($('.page').css('minHeight')) - footerH;
				if(differenceH === "NaN") {differenceH = 0;}
				//alert(wrapperH + ' vs ' + differenceH + " " + footerH);
			if(wrapperH > differenceH) {
				$('div.current .wrapper').css('height', wrapperH + 'px');
			}
			// Compatibility with desktop scroll
			$('div.current .wrapper').css('overflowY', 'auto');
		}
        setScrollTo();
        return true;
    }; // End Orientation
    
    /**
     * Preload Images
     * Allows subsequent "pages" to preload there assets when not in view
     * @param preloadImages {Array} Array of images to preload
    */    
    this.preloadImages = function(preloadImages) {
    	// Preload images
        if (preloadImages) {
			for (var i = preloadImages.length - 1; i >= 0; i--){
				(new Image()).src = preloadImages[i];
			}
		}
		return true;
    };
    
    /**
     * Window  Open
     * Opens new window currently for iPad/iPhone/Android to get around IFRAME issue
     * @param path {String} path to HTML file to load
    */ 
    this.windowOpen = function(path) {
    	windowRef = window.open(path, "sco");
    	onWindowClose();
    };
    
    /**
     * Set SCO
     * Typically called by Lessons launching SCO's, will make determination on launching in IFRAME or new Window
     * @param path {String} path of HTML file (sco) to load
    */     
    this.setSCO = function(path) {
    	// Do SCORM Stuff, possibly stall page animation.
    	debug("Setting SCO ... ", 4);
    	/* For New Window for iOS */
    	if(settings.windowLaunch) {
    		debug("Opening window ...", 4);
    		$.blockUI({message: null, onBlock: this.windowOpen(path)});
    	} else {
    		debug("Populating IFRAME...", 4);
			$('#_sco').append("<iframe id=\"sco_content\" class=\"sco_content\" name=\"sco_content\" frameborder=\"0\" src=\"" + path + "\" seamless><p>Sorry, frames didn't work for this browser.</p></iframe>");
    		
    	}
    };
	
	/** SCO Terminated Handler to ensure the portal wraps up its business
	  * This could be buried into the navigation
	*/ 
    this.scoTerminated = function() {
    	// Erase the sco from being loaded into the iframe
    	// Setting the src on a IFRAME Seems to do weird things to the iOS devices.
    	//$('#sco_content').attr('src', 'html/blank.html');
    	this.navigation.scoEnded();
    	debug("Portal has cleared the last SCO", 4);
    };
    
    this.playAudio = function(_type, _force){
    	var play = true,
    		uiaudio;
    	switch(_type) {
    		case "success":
    			uiaudio = document.getElementById('uiaud_success');
    		break;
    		case "error":
    			uiaudio = document.getElementById('uiaud_error');
    		break;
    		case "click":
    			uiaudio = document.getElementById('uiaud_btn');
    		break;
    		case "focus":
    			uiaudio = document.getElementById('uiaud_focus');
    		break;

    		default:
    			play = false;
    		break;
    	}
    	if(play && !settings.useTouch && typeof(uiaudio.play) === "function") {
    		if (uiaudio.currentTime) { uiaudio.currentTime = 0;}
    		if(_force){uiaudio.load();}
    		uiaudio.play();
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
    
} // End Portal