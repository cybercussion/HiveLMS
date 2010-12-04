/**
 * @author Mark Statkus mark@cybercussion.com
 * @version 1.0
 * Navigation supports moving thru the home page by either preloaded or externally called pages.  I'm borrowing
 * a navigation configuration to jQTouch, but altering it to handle the needs of this project. (detail later)
 * This has been enhanced to support webkit, or native animate transformations, and those of IE or FireFox.
 * I will be breakig up jQTouch a bit because there is a lot jammed into it for that project.  I liked the navigation 
 * approach so I'm borrowing from that concept.  The design though rely's solely on webkit, so CSS and further styling
 * adjustments have to be considered for IE/FireFox/Opera.
 * Provides API support for navigating DIV ID's and calling dynamic pages.
 * @constructor
 * @namespace portal.navigation
 * @param options {Object} This allows for override/new values.
 */
function Navigation(options) {
	// Constructor
	var defaults = {
			version: "1.0",
			moddate: "2010-7-1 10:52",
			createdate: "2010-7-1 10:52",
			addGlossToIcon: true,
	        backSelector: '.back, .cancel, .goback',
	        cacheGetRequests: true,
	        cubeSelector: '.cube',
	        dissolveSelector: '.dissolve',
	        fadeSelector: '.fade',
	        fixedViewport: true,
	        flipSelector: '.flip',
	        formSelector: 'form',
	        fullScreen: true,
	        fullScreenClass: 'fullscreen',
	        icon: null,
	        touchSelector: 'a, .touch',
	        popSelector: '.pop',
	        preloadImages: false,
	        slideSelector: 'li a, .slide',
	        slideupSelector: '.slideup',
	        startupScreen: null,
	        statusBar: 'default', // other options: black-translucent, black
	        submitSelector: '.submit',
	        swapSelector: '.swap',
	        useAnimations: true,
	        useFastTouch: false // Experimental. Mark: I noticed if this is true the touch event gets very touchy and fires wierd.
		},
    	settings = $.extend(defaults, options),
    	$body, 
        $head=$('head'), 
        hist=[], 
        newPageCount=0, 
        NAVSettings={}, 
        hashCheckInterval, 
        currentPage, 
        orientation, 
        isMobileWebKit = new RegExp(" Mobile/").test(navigator.userAgent),
        tapReady=true,
        lastAnimationTime=0,
        touchSelectors=[],
        publicObj={},
        tapBuffer=351,
        defaultAnimations=['slide','flip','slideup','swap','cube','pop','dissolve','fade','back'], 
        animations=[], 
        hairextensions='';
    // Set support values
    $.support.WebKitCSSMatrix = (typeof WebKitCSSMatrix === "object");
    $.support.touch = (typeof Touch === "object");
    $.support.WebKitAnimationEvent = (typeof WebKitTransitionEvent === "object" || typeof WebKitTransitionEvent === "function");  
	// Extend JQuery for tap (This works like a click)
	$.fn.tap = function(fn){
        if ($.isFunction(fn)) {
            var tapEvent = (settings.useFastTouch && $.support.touch) ? 'tap' : 'click';
            return $(this).live(tapEvent, fn);
        } else {
            return $(this).trigger('tap');
        }
    };
	$.fn.unselect = function(obj) {
        if (obj) {
            obj.removeClass('active');
        } else {
            $('.active').removeClass('active');
        }
    };
    $.fn.makeActive = function(){
        return $(this).addClass('active');
    };
    $.fn.isExternalLink = function() {
        var $el = $(this);
        return ($el.attr('target') == '_blank' || $el.attr('rel') == 'external' || $el.is('input[type="checkbox"], input[type="radio"], a[href^="http://maps.google.com"], a[href^="mailto:"], a[href^="tel:"], a[href^="javascript:"], a[href*="youtube.com/v"], a[href*="youtube.com/watch"]'));
    };
    // End Constructor
    
    // Private Functions //////////////////////////////////////////
	
	/**
     *
     *
     */
	function addAnimation(animation) {
		//debug("Adding animation for  " + animation.name, 4);
        if (typeof(animation.selector) === 'string' && typeof(animation.name) === 'string') {
            animations.push(animation);
            $(animation.selector).tap(liveTap);
            touchSelectors.push(animation.selector);
        }
    }
    
    /**
     *
     *
     */
    function addPageToHistory(page, animation, reverse) {
        // Grab some info
        var pageId = page.attr('id');
        // Prepend info to page history
        hist.unshift({
            page: page, 
            animation: animation, 
            reverse: reverse || false,
            id: pageId
        });
    }
    
    /**
     *
     *
     */
    function liveTap(e){
        // Grab the clicked element
        var $el = $(e.target);

        if ($el.attr('nodeName')!=='A' && $el.attr('nodeName')!=='AREA') {
            $el = $el.closest('a, area');
        }
		
		if($el.attr('rel')) {
        	//debug("Link target " + $el.attr('rel'), 4);
        	portal.setSCO($el.attr('rel'));
        }
		
        var target = $el.attr('target'),
            hash = $el.attr('hash'),
            animation=null;

        if (tapReady === false) { //} || !$el.length) {
            debug("Not able to tap element.", 2);
            return false;
        }
		
		if ($el.isExternalLink()) {
            $el.removeClass('active');
            return true;
        }
		
        // Figure out the animation to use
        for (var i = animations.length - 1; i >= 0; i--) {
            if ($el.is(animations[i].selector)) {
                animation = animations[i];
                break;
            }
        }

        // User clicked an internal link, fullscreen mode
        if (target === '_webapp') {
            window.location = $el.attr('href');
        }
        // User clicked a back button
        else if ($el.is(settings.backSelector)) {
            goBack(hash);
            portal.playAudio('click');
        }
        // Allow tap on item with no href
        else if ($el.attr('href') === '#') {
            $el.unselect();
            return true;
        }
        // Branch on internal or external href
        else if (hash && hash!=='#') {
        	debug("Internal HREF pressed " + $el.attr('id'), 4);
        	if(hash === "#sco_wrapper" && portal.get('windowLaunch')) {
        		// Ignore this stay on Lessons
        	} else {
            	$el.addClass('active');
            	goTo($(hash).data('referrer', $el), animation, $(this).hasClass('reverse'));
            	portal.playAudio('click');
            	if(hash ==="#lessons") { // need to check live quiz
            		LIVEQUIZ_API.quizCheckup(true);
            	}
            }
        } else {
        	debug("External HREF pressed " + $el.attr('href'), 4);
            $el.addClass('loading active');
            
            showPageByHref($el.attr('href'), {
                animation: animation,
                callback: function() {
                    $el.removeClass('loading'); setTimeout($.fn.unselect, 250, $el);
                },
                $referrer: $el
            });
        }
        return false;
    }
    
    /**
     *
     *
     */
    function goTo(toPage, animation, reverse) {
    	debug("Going to " + $(toPage).attr('id') + " using " + animation.name, 4);
        var fromPage = hist[0].page;
        
        if (typeof(toPage) === 'string') {
            toPage = $(toPage);
        }
        if (typeof(animation) === 'string') {
            for (var i=animations.length - 1; i >= 0; i--){
                if (animations[i].name === animation) {
                    animation = animations[i];
                    break;
                }
            }
        }
        // Revision 147
        /*if (typeof(toPage) === 'string') {
			nextPage = $(toPage);
			if (nextPage.length < 1) {
				showPageByHref(toPage, {
					'animation': animation
				});
				return;
			} else {
				toPage = nextPage;
			}
		}*/
        
        if (animatePages(fromPage, toPage, animation, reverse)) {
            addPageToHistory(toPage, animation, reverse);
            return publicObj;
        } else {
            debug('Could not animate pages.', 1);
            return false;
        }
    }
	
	/**
     *
     *
     */
	function goBack(to) {
        // Init the param 
        var numberOfPages = Math.min(parseInt(to || 1, 10), hist.length-1),
        	curPage = hist[0],
        	curPageID = curPage.page.attr('id');
        debug("History " + hist.length + " and current page is " + curPageID, 4);
        /* Back SCORM trap
		if(curPageID == "sco_wrapper" && API_1484_11.get('terminated') == 0) {
			debug("Attempting to leave the SCO via browser back button", 4);
			$('#_sco').trigger('beforeunload').trigger("unload");
			
		
		} else {*/
	        // Search through the history for an ID
	        if( isNaN(numberOfPages) && typeof(to) === "string" && to !== '#' ) {
	            for( var i=1, length=hist.length; i < length; i++ ) {
	                if( '#' + hist[i].id === to ) {
	                    numberOfPages = i;
	                    break;
	                }
	            }
	        }
			
	        // If still nothing, assume one
	        if( isNaN(numberOfPages) || numberOfPages < 1 ) {
	            numberOfPages = 1;
	        }
	
			if(hist.length > 1) {
				// Remove all pages in front of the target page
				hist.splice(0, numberOfPages);
				animatePages(curPage.page, hist[0].page, curPage.animation, curPage.reverse === false);
			} else {
				location.hash = '#' + curPage.id;
			}
		//}
        
        return publicObj;
    }
    
    /**
     *
     *
    */
    function insertPages(nodes, animation) {
        var targetPage = null;
        $(nodes).each(function(index, node){
            var $node = $(this);
            if (!$node.attr('id')) {
            	debug("No ID, adding our own on " + $node.nodeName, 2);
                $node.attr('id', 'page-' + (++newPageCount));
            }
            
	        $body.trigger('pageInserted', {page: $node.appendTo($body)});

            if ($node.hasClass('current') || !targetPage ) {
                targetPage = $node;
            }
        });
        if (targetPage !== null) {
        	$('.scroller').touchScroll();
            goTo(targetPage, animation);
            return targetPage;
        } else {
            return false;
        }
    }
    
    /**
     *
     *
    */
    function showPageByHref(href, options) {
        var localdefaults = {
            data: null,
            method: 'GET',
            animation: null,
            callback: null,
            $referrer: null
        };
        tapReady = false;
        var localsettings = $.extend({}, localdefaults, options);
		debug("ShowPageByHref " + href, 4);
        if (href !== '#') {
            $.ajax({
                url: href,
                data: localsettings.data,
                type: localsettings.method,
                success: function (data, textStatus) {
                	debug("Success loading external HREF " + href, 4);
                    var firstPage = insertPages(data, localsettings.animation);
                    if (firstPage) {
                        if (localsettings.method === 'GET' && settings.cacheGetRequests === true && localsettings.$referrer) {
                            localsettings.$referrer.attr('href', '#' + firstPage.attr('id'));
                        }
                        if (localsettings.callback) {
                            localsettings.callback(true);
                        }
                    }
                },
                error: function (data) {
                	debug("Error: Can't load external HREF " + href, 1);
                    if (localsettings.$referrer) {
        		    	localsettings.$referrer.unselect();
        			}
                    if (localsettings.callback) {
                        localsettings.callback(false);
                    }
                }
            });
        } else if (localsettings.$referrer) {
            localsettings.$referrer.unselect();
        }
    }
    

	/**
     *
     *
     */
	function animatePages(fromPage, toPage, animation, backwards) {
        // Error check for target page
        tapReady = false;
        if(toPage.length === 0){
            $.fn.unselect();
            debug('Target element is missing.', 1);
            return false;
        }
        // Error check for frompage=toPage Pick up 125
        if(toPage.hasClass('current')) {
        	$.fn.unselect();
        	debug("Target element is the current page.", 2);
        	return false;
        }
        // Collapse the keyboard
        $(':focus').blur();

        // Make sure we are scrolled up to hide location bar
        portal.setScrollTo(0);
        //toPage.css('top', window.pageYOffset);
        
        // See - http://code.google.com/p/jqtouch/issues/detail?id=301
		if (animation.name === "slide") {
			var toStart = 'translateX(' + (backwards ? '-' : '') + window.innerWidth + 'px)';
			fromPage.css( 'webkitTransform', toStart );
		}
		// end

        // Define callback to run after animation completes
        var callback = function animationEnd(event){
        	//fromPage[0].removeEventListener('webkitTransitionEnd', callback);
        	//fromPage[0].removeEventListener('webkitAnimationEnd', callback);
        	// See - http://code.google.com/p/jqtouch/issues/detail?id=301
		    if (animation.name === "slide") {
            	fromPage.css('webkitTransform', '');
        	} else {
			    fromPage.css('display', 'none'); // fix for chrome flicker
			}
		    // end
            if (animation) {
                toPage.removeClass('start in ' + animation.name); // new
                fromPage.removeClass('start out current ' + animation.name); // new
                if (backwards) {
                	toPage.toggleClass('reverse');
                	fromPage.toggleClass('reverse');
                }
                toPage.css('top', 0); // new
            } else {
            	fromPage.removeClass('current');
            }

            toPage.trigger('pageAnimationEnd', { direction: 'in', reverse: backwards });
	        fromPage.trigger('pageAnimationEnd', { direction: 'out', reverse: backwards });
            
            clearInterval(hashCheckInterval);
            currentPage = toPage;
            location.hash = '#' + currentPage.attr('id');
            startHashCheck();

            var $originallink = toPage.data('referrer');
            if ($originallink) {
                $originallink.unselect();
            }
            lastAnimationTime = (new Date()).getTime();
            tapReady = true;
            // Clean up sco_content IFRAME
            debug("Last Page was " + fromPage.attr('id'), 4);
            if(fromPage.attr('id') === "sco_wrapper") {
            	if(!portal.get('windowLaunch')) {
            		debug("Not a new window removing iframe", 4);
            		$('#sco_content').remove();
            		//$('#'+portal.curriFrame).hide(); not a good idea
            		toPage.addClass('current');
            	} else {
            		//this.back();
            	}
            } else if (fromPage.attr('id') === "lessons") {
            	LIVEQUIZ_API.quizCheckup(false);
            }
        };

        fromPage.trigger('pageAnimationStart', { direction: 'out' });
        toPage.trigger('pageAnimationStart', { direction: 'in' });
		
        if ($.support.WebKitAnimationEvent && animation && settings.useAnimations) {
        	//tapReady = false;
        	debug("Doing Webkit Animation " + animation.name, 4);
            toPage.one('webkitAnimationEnd', callback);
            if (backwards) {                    
                toPage.toggleClass('reverse');
                fromPage.toggleClass('reverse');
            }
            
            // Support both transitions and animations
            //fromPage[0].addEventListener('webkitTransitionEnd', callback);
            //fromPage[0].addEventListener('webkitAnimationEnd', callback);
            
            toPage.addClass(animation.name + ' in current ');
            fromPage.addClass(animation.name + ' out');
            
            /*setTimeout(function() {
            	toPage.addClass('start');
            	fromPage.addClass('start');
            });*/
            
            portal.updateOrientation();
        } else {
            /**
             * Since the webkit isn't supported use JQuery's animate ability
             * @author Mark Statkus cybercussion.com
             * Adding native JQuery support to sustain the look and feel of jTouch
             * I am slightly uncertain if using the fromPage.width() is reliable, but will see.
             */
            debug("Doing Stock Animation: " +  $.support.WebKitAnimationEvent + " " + settings.useAnimations, 4);
            //toPage.one('webkitAnimationEnd', callback);
            //tapReady = false;
            if (backwards) {                    
                toPage.toggleClass('reverse');
                fromPage.toggleClass('reverse');
            }
            toPage.addClass(animation.name + ' in current ');
            fromPage.addClass(animation.name + ' out');
            portal.updateOrientation();
            switch(animation.name) {
            	case 'slideup':
	            	// From page stays where its at, because its not a webkit transition we have to hide the overlow-y
	            	$('#portal').css('overflowY', 'hidden');
	            	var target = toPage;
	            	if(!backwards) {
	            		target.css('marginTop', target.height());
	            	} else {
	            		target = fromPage;
	            	}
	            	target.animate({
		               'marginTop': (backwards ? target.height() : 0) + 'px'
		            }, 350, function(){callback(); $('#portal').css('overflowY', 'auto');});
		        break;
		        case 'dissolve':
		        	// need to spend more time making this look nicer
		        	if(backwards) {
		        		fromPage.fadeOut(350, function(){callback();});
		        		toPage.fadeIn(350);
		        	} else {
		        		toPage.fadeIn(350, function(){callback();});
		        		fromPage.fadeOut(350);
		        	}
		        break;
		        default:
           	    	$('#portal').css('overflowX', 'hidden');
		            fromPage.animate({
		               'marginLeft': (backwards ? fromPage.width() : '-' + fromPage.width()) + 'px'
		            }, 350, function(){callback(); $('#portal').css('overflowX', 'auto');});
		            toPage.css('marginLeft', (backwards ? '-' + fromPage.width() : fromPage.width()) + 'px');
		            //toPage.addClass('current');
		            toPage.animate({'marginLeft': (backwards ? '0' : '0') + 'px'}, 340);
		        break;
	        }
            // END
            // Original Code
            //toPage.addClass('current');
            //callback();
        }
        return true;
    }
    
    /**
     *
     *
     */
    function hashCheck() {
        var curid = currentPage.attr('id');
        if (location.hash === '') {
            location.hash = '#' + curid;
        } else if (location.hash !== '#' + curid) {
            clearInterval(hashCheckInterval);
            goBack(location.hash);
        }
    }
    
    function startHashCheck() {
    	hashCheckInterval = setInterval(hashCheck, 100);
    }
    
    
    /**
     * Handle Touch - these are the touch events for the nav for swipes and gestures.
     * Its important to note that this is firing off touch events and that desktops handle clicks and mouseovers etc...
     * When Animations are added those events are setup at that time so its either a tap or a click.
     */
     function handleTouch(e) {
        var $el = $(e.target);
		// Private touch functions
        function touchmove(e) {
            
            updateChanges();
            var absX = Math.abs(deltaX);
            var absY = Math.abs(deltaY);
                            
            // Check for swipe
            if (absX > absY && (absX > 35) && deltaT < 1000) {
                $el.trigger('swipe', {direction: (deltaX < 0) ? 'left' : 'right', deltaX: deltaX, deltaY: deltaY }).unbind('touchmove',touchmove).unbind('touchend',touchend);
            } else if (absY > 1) {
                $el.removeClass('active');
            }

            clearTimeout(hoverTimeout);
        } 
        
        function touchend(){
            updateChanges();
        
            if (deltaY === 0 && deltaX === 0) {
                $el.makeActive();
                $el.trigger('tap');
            } else {
                $el.removeClass('active');
            }
            $el.unbind('touchmove',touchmove).unbind('touchend',touchend);
            clearTimeout(hoverTimeout);
        }
        
        function updateChanges(){
            var first = event.changedTouches[0] || null;
            deltaX = first.pageX - startX;
            deltaY = first.pageY - startY;
            deltaT = (new Date()).getTime() - startTime;
        }
		
        // Only handle touchSelectors
        if (!$(e.target).is(touchSelectors.join(', '))) {
        	// Mark: Updates to Revsion 120 of JQTouch
            //var $link = $(e.target).closest('a');
            var $link = $(e.target).closest('a, area');
            //if ($link.length) {
            if ($link.length && $link.is(touchSelectors.join(', '))){
                $el = $link;
            } else {
                return;
            }
        }
        if (event) {
            var hoverTimeout = null,
                startX = event.changedTouches[0].clientX,
                startY = event.changedTouches[0].clientY,
                startTime = (new Date()).getTime(),
                deltaX = 0,
                deltaY = 0,
                deltaT = 0;

            // Let's bind these after the fact, so we can keep some internal values
            $el.bind('touchmove', touchmove).bind('touchend', touchend);

            hoverTimeout = setTimeout(function(){
                $el.makeActive();
            }, 100);
            
        }
    } // End touch handler
    
    // Public Functions //////////////////////////////////
    /**
     * Initialize Navigation
     * Changing the approach I've used in the past slightly to re-reference internal funcitons
     * So I don't have to append 'this' before everything.  This will add the default animations
     * and prepare all the stock nav events.
     */
	this.init = function() {
		touchSelectors.push('input');
        touchSelectors.push(settings.touchSelector);
        touchSelectors.push(settings.backSelector);
        touchSelectors.push(settings.submitSelector);
        $(touchSelectors.join(', ')).css('-webkit-touch-callout', 'none');
        $(settings.backSelector).tap(liveTap);
        //$(settings.submitSelector).tap(submitParentForm);
		
		$body = $('#portal');
		// Create custom live events
        $body.bind('touchstart', handleTouch);
            //.bind('orientationchange', updateOrientation)
            //.trigger('orientationchange')
            //.submit(submitForm);
		
		for (var i=0; i<defaultAnimations.length; i++) {
		    var name = defaultAnimations[i];
		    var selector = settings[name + 'Selector'];
		    if (typeof(selector) === 'string') {
		        addAnimation({name:name, selector:selector});
		    }
		}

		
		// Make sure exactly one child of body has "current" class
        //if ($('body > .current').length == 0) {
            currentPage = $('#login'); //$('body > *:first');
        //} else {
        //    currentPage = $('body > .current:first');
        //    $('body > .current').removeClass('current');
        //}

		// Go to the top of the "current" page
	    $(currentPage).addClass('current');
	    location.hash = '#' + $(currentPage).attr('id');
	    debug(location.hash, 4);
		addPageToHistory(currentPage);
		startHashCheck();
	};
	
	/**
	 * Hook for Private GoTo
	*/
	this.goTo = function(_href, _ani) {
		goTo(_href, _ani);
	};
	
	/**
     * Hook for Private Back
    */
    this.back = function() {
    	var curPage = hist[0],
    		$el     = $('#' + curPage.id),
    		hash = $el.attr('hash');
    	goBack(hash);
    };
    
    /**
     * Portal will inform the navigation that the SCO has terminated
     * This needs to manage how the SCO returns back to the lesson (window, iframe etc ...)
    */
    this.scoEnded = function() {
    	if(!portal.get('windowLaunch')) {
    		// check to see if current page is indeed the sco_wrapper (may of been a browser back button)
    		var curPage = hist[0],
    			curPageID = curPage.page.attr('id');
    		if(curPageID === "sco_wrapper") {
    			this.back();
    		}
    	}
    };
}
