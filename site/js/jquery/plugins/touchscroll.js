/**
 * jQuery touchScroll
 * @author Mark Statkus - www.cybercussion.com
 * I couldn't find anything online that did what I needed so I'm writing my own.
 * This will turn a scrolling div into one that can be touched to scroll.
 * SPECIAL NOTE: I disabled Momentum, Snapping and Bounce for this release.
*/
(function($) {
    $.fn.touchScroll = function(options) {
  		var defaults = {
		  		hasTouch: ('ontouchstart' in window),
		  		bounce: false,
		    	momentum: false,
		    	snap: false,
		    	translateOpen: /android/i.test(navigator.userAgent.toLowerCase()) ? 'translate(' : 'translate3d(',
		    	translateClose: /android/i.test(navigator.userAgent.toLowerCase()) ? ')' : ', 0px)'
	  	 	}, 
	  	 	settings = $.extend({}, defaults, options);
	  	 	
  	 	return this.each(function() {
  	 		if(settings.hasTouch) {
				var $this = $(this);
				$this.cpos = $this.scrollTop(); // non webkit var (remove later)
				$this.css('-webkit-transition-property', '-webkit-transform');
				$this.css('-webkit-transition-timing-function', 'cubic-bezier(0.4, .75, 0.5, .95)'); //'cubic-bezier(0,0,0.25,1');
				$this.css('-webkit-transition-duration', '0ms');
				$this.x = 0;
				$this.y = 0;
				$this.directionX = 0;
				$this.directionY = 0;
				$this.dist = 0;
				
				$this.bind('touchstart', function(event) {
					var e      = event.originalEvent, matrix;
					//e.preventDefault();
					//e.stopPropagation();
					if(e.touches.length >=2) {
						debug("This is a pinch, ignore it?", 2);
						$this.pinch = true;
					} else {
						$this.pinch = false;
					}
					if(!$this.pinch) {
						if (settings.momentum || settings.snap) {
							matrix = new WebKitCSSMatrix(window.getComputedStyle($this[0]).webkitTransform);
							if (matrix.e != $this.x || matrix.f != $this.y) {
								document.removeEventListener('webkitTransitionEnd', $this, false);
								$this.setPosition(matrix.e, matrix.f);							
								$this.moved = true;
							}
						}
						$this.scrolling      = true;
						$this.moved          = false;
						$this.touchStartX    = e.targetTouches[0].pageX || e.pageX;
						$this.touchStartY    = e.targetTouches[0].pageY || e.pageY;
						$this.scrollStartTime = e.timeStamp;
						$this.setBaseVars(e);
					}
				})
				.bind('touchmove', function(event) {
					//console.log("touchScroll move");
					var e = event.originalEvent;
					e.preventDefault();
					if(!$this.pinch) {
						var pageX = e.targetTouches[0].pageX || e.pageX,
							pageY = e.targetTouches[0].pageY || e.pageY,
							leftDelta = $this.scrollX ? pageX - $this.touchStartX : 0,
							topDelta = $this.scrollY ? pageY - $this.touchStartY : 0,
							newX = $this.x + leftDelta,
							newY = $this.y + topDelta;
						if (!$this.scrolling) {
							return;
						}
						$this.dist+= Math.abs($this.touchStartX - pageX) + Math.abs($this.touchStartY - pageY);
						$this.touchStartX = pageX;
						$this.touchStartY = pageY;
						// Slow down if outside of the boundaries
						if (newX > 0 || newX < $this.maxScrollX) { 
							newX = settings.bounce ? Math.round($this.x + leftDelta / 3) : newX >= 0 ? 0 : $this.maxScrollX;
						}
						if (newY > 0 || newY < $this.maxScrollY) { 
							newY = settings.bounce ? Math.round($this.y + topDelta / 3) : newY >= 0 ? 0 : $this.maxScrollY;
						}
						
						if ($this.dist > 5) {			// 5 pixels threshold is needed on Android, but also on iPhone looks more natural
							$this.moved = true;
							$this.directionX = leftDelta > 0 ? -1 : 1;
							$this.directionY = topDelta > 0 ? -1 : 1;
						}
						$this.setPosition(newX, newY);
					}
				})
				.bind('touchend', function(event) {
					//console.log("touchScroll end");
					var e = event.originalEvent;
					if(!$this.pinch) {
						var time = e.timeStamp - $this.scrollStartTime,
							target, ev,
							momentumX, momentumY,
							newDuration = 0,
							newPositionX = $this.x, newPositionY = $this.y,
							snap;
				
						if (!$this.scrolling) {
						console.log("touchScroll scrolling false, return");
							return;
						}
				
						$this.scrolling = false;
				
						if (!settings.snap && time > 250) {			// Prevent slingshot effect
							$this.resetPosition();
							return;
						}
				
						/*if (settings.momentum) {
							momentumX = $this.scrollX === true
								? $this.momentum($this.x - $this.scrollStartX,
												time,
												settings.bounce ? -$this.x + $this.scrollWidth/5 : -$this.x,
												settings.bounce ? $this.x + $this.scrollerWidth - $this.scrollWidth + $this.scrollWidth/5 : $this.x + $this.scrollerWidth - $this.scrollWidth)
								: { dist: 0, time: 0 };
				
							momentumY = $this.scrollY === true
								? $this.momentum($this.y - $this.scrollStartY,
												time,
												settings.bounce ? -$this.y + $this.scrollHeight/5 : -$this.y,
												settings.bounce ? ($this.maxScrollY < 0 ? $this.y + $this.scrollerHeight - $this.scrollHeight : 0) + $this.scrollHeight/5 : $this.y + $this.scrollerHeight - $this.scrollHeight)
								: { dist: 0, time: 0 };
				
							newDuration = Math.max(Math.max(momentumX.time, momentumY.time), 1);		// The minimum animation length must be 1ms
							newPositionX = $this.x + momentumX.dist;
							newPositionY = $this.y + momentumY.dist;
						}
						
						if (settings.snap) {
							snap = $this.snap(newPositionX, newPositionY);
							newPositionX = snap.x;
							newPositionY = snap.y;
							newDuration = Math.max(snap.time, newDuration);
						}*/
				
						$this.scrollTo(newPositionX, newPositionY, newDuration + 'ms');	
					}	
				});
				
				// Privates
				$this.setPosition = function(x, y) {
					$this.css('-webkit-transform', settings.translateOpen + x + 'px,' + y + 'px' + settings.translateClose);
					$this.x = x;
					$this.y = y;
				};
				
				$this.scrollTo = function(destX, destY, runtime) {
					if ($this.x == destX && $this.y == destY) {
					console.log("same destination, returning...");
						$this.onScrollEnd();
						return;
					}

					$this.css('-webkit-transition-duration', runtime || '400ms');
					$this.css('-webkit-transform', settings.translateOpen + destX + 'px,' + destY + 'px' + settings.translateClose);
					$this.x = destX;
					$this.y = destY;
			
					if (runtime==='0' || runtime=='0s' || runtime=='0ms') {
						$this.resetPosition();
						$this.onScrollEnd();
					} else {
						document.addEventListener('webkitTransitionEnd', $this, false);	// At the end of the transition check if we are still inside of the boundaries
					}
				};
				
				/*$this.snap = function(x, y) {
					var time;
			
					if ($this.directionX > 0) {
						x = Math.floor(x/$this.scrollWidth);
					} else if ($this.directionX < 0) {
						x = Math.ceil(x/$this.scrollWidth);
					} else {
						x = Math.round(x/$this.scrollWidth);
					}
					$this.pageX = -x;
					x = x * $this.scrollWidth;
					if (x > 0) {
						x = $this.pageX = 0;
					} else if (x < $this.maxScrollX) {
						$this.pageX = $this.maxPageX;
						x = $this.maxScrollX;
					}
			
					if ($this.directionY > 0) {
						y = Math.floor(y/$this.scrollHeight);
					} else if ($this.directionY < 0) {
						y = Math.ceil(y/$this.scrollHeight);
					} else {
						y = Math.round(y/$this.scrollHeight);
					}
					$this.pageY = -y;
					y = y * $this.scrollHeight;
					if (y > 0) {
						y = $this.pageY = 0;
					} else if (y < $this.maxScrollY) {
						$this.pageY = $this.maxPageY;
						y = $this.maxScrollY;
					}
			
					// Snap with constant speed (proportional duration)
					time = Math.round(Math.max(
						Math.abs($this.x - x) / $this.scrollWidth * 500,
						Math.abs($this.y - y) / $this.scrollHeight * 500
					));
						
					return { x: x, y: y, time: time };
				};
				
				$this.momentum = function(dist, time, maxDistUpper, maxDistLower) {
					var friction = 2.5,
						deceleration = 1.2,
						speed = Math.abs(dist) / time * 1000,
						newDist = speed * speed / friction / 1000,
						newTime = 0;
			
					// Proportinally reduce speed if we are outside of the boundaries 
					if (dist > 0 && newDist > maxDistUpper) {
						speed = speed * maxDistUpper / newDist / friction;
						newDist = maxDistUpper;
					} else if (dist < 0 && newDist > maxDistLower) {
						speed = speed * maxDistLower / newDist / friction;
						newDist = maxDistLower;
					}
					
					newDist = newDist * (dist < 0 ? -1 : 1);
					newTime = speed / deceleration;
			
					return { dist: Math.round(newDist), time: Math.round(newTime) };
				};*/
				
				$this.onScrollEnd = function() {};
				
				$this.setBaseVars = function() {
					$this.scrollWidth    = $('body')[0].clientWidth;
					$this.scrollHeight   = $('body')[0].clientHeight;
					$this.scrollStartX   = $this.x;
					$this.scrollStartY   = $this.y;
					$this.scrollerWidth  = $this[0].offsetWidth;
					$this.scrollerHeight = $this[0].offsetHeight;
					$this.scrollX        = $this.scrollerWidth > $this.scrollWidth;
					$this.scrollY        = !$this.scrollX || $this.scrollerHeight > $this.scrollHeight;
					$this.maxScrollX     = $this.scrollWidth - $this.scrollerWidth;
					$this.maxScrollY     = $this.scrollHeight - $this.scrollerHeight;
				};
				$this.refresh = function() {
					$this.setBaseVars();
					$this.resetPosition('0ms');
				};
				$this.resetPosition = function (time) {
					var resetX = $this.x,
					 	resetY = $this.y;
			
					if ($this.x >= 0) {
						resetX = 0;
					} else if ($this.x < $this.maxScrollX) {
						resetX = $this.maxScrollX;
					}
			
					if ($this.y >= 0 || $this.maxScrollY > 0) {
						resetY = 0;
					} else if ($this.y < $this.maxScrollY) {
						resetY = $this.maxScrollY;
					}
			
					if (resetX != $this.x || resetY != $this.y) {
						$this.scrollTo(resetX, resetY, time);
					} else {
						$this.onScrollEnd();		// Execute custom code on scroll end
						
						// Hide the scrollbars
						/*if ($this.scrollBarX) {
							$this.scrollBarX.hide();
						}
						if ($this.scrollBarY) {
							$this.scrollBarY.hide();
						}*/
					}
				};
				$(window).bind('orientationchange resize', $this.refresh); // if you don't do this landscape vs. portrait gets a little off.
			}
		});
	} // end
})(jQuery); 