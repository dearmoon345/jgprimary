/*
* iziModal | v1.0
* http://izimodal.dolce.ninja
* by Marcelo Dolce.
* js修改过
*/
(function(jQuery){

	"use strict";

	var PLUGIN_NAME = 'iziModal';

	var STATES = {
		CLOSING: 'closing',
		CLOSED: 'closed',
		OPENING: 'opening',
		OPENED: 'opened',
		DESTROYED: 'destroyed'
	};

	function whichAnimationEvent(){
		var t,
			el = document.createElement("fakeelement");

		var animations = {
			"animation"      : "animationend",
			"OAnimation"     : "oAnimationEnd",
			"MozAnimation"   : "animationend",
			"WebkitAnimation": "webkitAnimationEnd"
		};
		for (t in animations){
			if (el.style[t] !== undefined){
				return animations[t];
			}
		}
	}
	var animationEvent = whichAnimationEvent();

	var isMobile = false;
	if (/Mobi/.test(navigator.userAgent)) {
	    isMobile = true;
	}

	var iziModal = function (element, options) {
		this.init(element, options);
	};

	iziModal.prototype = {

		constructor: iziModal,

		init: function (element, options) {
			
			var that = this;

			this.jQueryelement = jQuery(element);
			this.id = this.jQueryelement.attr('id');
			this.state = STATES.CLOSED;
			this.options = options;
			this.timer = null;
            this.headerHeight = 0;
			            this.jQueryheader = jQuery('<div class="'+PLUGIN_NAME+'-header"><h1 class="'+PLUGIN_NAME+'-header-title">' + options.title + '</h1><p class="'+PLUGIN_NAME+'-header-subtitle">' + options.subtitle + '</p><a href="javascript:void(0)" class="'+PLUGIN_NAME+'-button-close" data-'+PLUGIN_NAME+'-close><i class="ui-icon-return"></i></a><div id="wst-switch"><div></div>');
            this.jQueryoverlay = jQuery('<div class="'+PLUGIN_NAME+'-overlay" style="background-color:'+options.overlayColor+'"></div>');

            if (options.subtitle === '') {
        		this.jQueryheader.addClass(PLUGIN_NAME+'-noSubtitle');
            }

            if (options.iframe === true) {
                this.jQueryelement.html('<div class="'+PLUGIN_NAME+'-wrap"><div class="'+PLUGIN_NAME+'-content '+PLUGIN_NAME+'-content-loader"><iframe class="'+PLUGIN_NAME+'-iframe"></iframe>' + this.jQueryelement.html() + "</div></div>");
                
	            if (options.iframeHeight !== null) {
	                this.jQueryelement.find('.'+PLUGIN_NAME+'-iframe').css('height', options.iframeHeight);
	            }

            } else {
            	this.jQueryelement.html('<div class="'+PLUGIN_NAME+'-wrap"><div class="'+PLUGIN_NAME+'-content">' + this.jQueryelement.html() + '</div></div>');
            }
            
            jQuery(document.body).find('style[rel='+this.id+']').remove();

            if(typeof options.padding !== 'undefined' || options.padding !== 0)
                this.jQueryelement.find('.'+PLUGIN_NAME+'-content').css('padding', options.padding);


            if (options.title !== "" || options.subtitle !== "") {

                if (options.headerColor !== null) {
                    this.jQueryelement.css('border-bottom', '3px solid ' + options.headerColor + '');
                    this.jQueryheader.css('background', this.options.headerColor);
                }
                if (options.iconClass !== null) {
                    this.jQueryheader.prepend('<i class="'+PLUGIN_NAME+'-header-icon ' + options.iconClass + '"></i>');
                    this.jQueryheader.find("."+PLUGIN_NAME+'-header-icon').css('color', options.iconColor);
                }
                this.jQueryelement.prepend(this.jQueryheader);
            }

			var separators = /%|px|em|cm/,
				wClear = String(options.width).split(separators),
				w = String(options.width),
				medida = "px";
				wClear = String(wClear).split(",")[0];

			if(isNaN(options.width)){
				if( String(options.width).indexOf("%") != -1){
					medida = "%";
				} else {
					medida = w.slice("-2");
				}
			}

            this.jQueryelement.css({
                'margin-left': -(wClear / 2) + medida,
                'max-width': parseInt(wClear) + medida
            });

			this.mediaQueries = '<style rel="' + this.id + '">@media handheld, only screen and (max-width: ' + wClear + 'px) { #' + this.jQueryelement[0].id + '{ width: 100% !important; max-width: 100% !important; margin-left: 0 !important; left: 0 !important; } }</style>';
        	jQuery(document.body).append(this.mediaQueries);

            // Adjusting horizontal positioning
            this.jQueryelement.addClass(PLUGIN_NAME + " " + options.theme);

            // Adjusting vertical positioning
            this.jQueryelement.css('margin-top', parseInt(-(this.jQueryelement.innerHeight() / 2)) + 'px');


            if(this.jQueryelement.find('.'+PLUGIN_NAME+'-header').length){
            	this.jQueryelement.css('overflow', 'hidden');
            }

            // Close on overlay click
            this.jQueryoverlay.click(function () {
                if (that.options.overlayClose && !that.jQueryelement.hasClass(that.options.transitionOutModal)) {
                    that.close();
                }
            });

            // Close when button pressed
            this.jQueryelement.on('click', '[data-'+PLUGIN_NAME+'-close]', function (e) {
                e.preventDefault();
                that.close();
            });
		},

		toggle: function () {

			if(this.state == STATES.OPENED){
				this.close();
			}
			if(this.state == STATES.CLOSED){
				this.open();
			}

		},

		open: function (param) {

			var that = this;

			if (param && typeof(param) === "function") {
		        param(that);
		    }

			if(this.options.iframe === true){

				var href = null;
				if(this.options.iframeURL !== null){
					href = this.options.iframeURL;
				} else {
					try {
						href = param.target.href;
						if(href !== undefined){
							href = param.target.href;
						}
					} catch(e) {
						console.warn(e);
					}
				}
			    this.jQueryelement.find('.'+PLUGIN_NAME+'-iframe').attr('src', href);
			}

            this.jQueryelement.trigger(STATES.OPENING);
			this.state = STATES.OPENING;

			console.info('[ '+PLUGIN_NAME+' | '+this.id+' ] Opening...');

			if (this.options.bodyOverflow || isMobile){
				jQuery(document.body).css('overflow', 'hidden');
			}

			that.options.onOpening.call(this);

			function opened(){
		    	that.jQueryelement.trigger(STATES.OPENED);
				that.state = STATES.OPENED;

			    console.info('[ '+PLUGIN_NAME+' | '+that.id+' ] Opened.');

				that.options.onOpened.call(this);
			}

			this.jQueryoverlay.appendTo('body');

			if (this.options.transitionInOverlay) {
				this.jQueryoverlay.addClass(this.options.transitionInOverlay);
			}

			if (this.options.transitionInModal !== '') {

				this.jQueryelement.addClass(this.options.transitionInModal).show();

				this.jQueryelement.find('.'+PLUGIN_NAME+'-wrap').one(animationEvent, function () {

				    that.jQueryelement.removeClass(that.options.transitionInModal);
				    that.jQueryoverlay.removeClass(that.options.transitionInOverlay);

					opened();
				});

			} else {
				this.jQueryelement.show();
				opened();
			}

			if (that.options.focusInput){
		    	that.jQueryelement.find(':input:not(button):enabled:visible:first').focus(); // Focus on the first field
			}
			
			(function updateTimer(){
			    that.recalculateLayout();
			    that.timer = setTimeout(updateTimer, 500);
			})();

            // Close when the Escape key is pressed
            jQuery(document).keydown(function (e) {
                if (that.options.closeOnEscape && e.keyCode === 27) {
                    that.close();
                }
            });

		},

		close: function (param) {

			var that = this;

			if (param && typeof(param) === "function") {
		        param(that);
		    }

            jQuery(document).off("keydown");

			this.state = STATES.CLOSING;
			this.jQueryelement.trigger(STATES.CLOSING);
			console.info('[ '+PLUGIN_NAME+' | '+this.id+' ] Closing...');

            clearTimeout(that.timer);

			that.options.onClosing.call(this);

			function closed(){

                if (that.options.iframe === true) {
                    that.jQueryelement.find('.'+PLUGIN_NAME+'-iframe').attr('src', "");
                }

				if (that.options.bodyOverflow || isMobile){
					jQuery(document.body).css('overflow', 'initial');
				}

				jQuery(document.body).removeClass(PLUGIN_NAME+'-attached');

                that.jQueryelement.trigger(STATES.CLOSED);
                that.state = STATES.CLOSED;
                
                console.info('[ '+PLUGIN_NAME+' | '+that.id+' ] Closed.');

				that.options.onClosed.call(this);
			}

            if (this.options.transitionOutModal !== '') {

                //this.jQueryelement.removeClass(this.options.transitionInModal).addClass(this.options.transitionOutModal);
                //this.jQueryoverlay.removeClass(this.options.transitionInOverlay).addClass(this.options.transitionOutOverlay);

                this.jQueryelement.attr('class', PLUGIN_NAME + " " + this.options.theme + " " + this.options.transitionOutModal);
				this.jQueryoverlay.attr('class', PLUGIN_NAME + "-overlay " + this.options.transitionOutOverlay);

                this.jQueryelement.one(animationEvent, function () {
                    
                    if( that.jQueryelement.hasClass(that.options.transitionOutModal) ){

                        that.jQueryelement.removeClass(that.options.transitionOutModal).hide();
                        that.jQueryoverlay.removeClass(that.options.transitionOutOverlay).remove();
                        
						closed();
                    }
                });
            }
            else {
                this.jQueryelement.hide();
                this.jQueryoverlay.remove();
                
                closed();
            }
		},

		destroy: function () {
			var e = jQuery.Event('destroy');

			this.jQueryelement.trigger(e);

            jQuery(document).off("keydown");

			clearTimeout(this.timer);

			if (this.options.iframe === true) {
				this.jQueryelement.find('.'+PLUGIN_NAME+'-iframe').remove();
			}
			this.jQueryelement.html(this.jQueryelement.find('.'+PLUGIN_NAME+'-content').html());

			jQuery(document.body).find('style[rel='+this.id+']').remove();

			this.jQueryelement.off('click', '[data-'+PLUGIN_NAME+'-close]');

			this.jQueryelement
				.off('.'+PLUGIN_NAME)
				.removeData(PLUGIN_NAME)
				.attr('style', '');
			
			this.jQueryoverlay.remove();
			this.jQueryelement.trigger(STATES.DESTROYED);
			this.jQueryelement = null;
		},

		getState: function(){

			console.info(this.state);
			
			return this.state;
		},

		setTitle: function(title){

			if (this.options.title !== null) {
				
				this.jQueryheader.find('.'+PLUGIN_NAME+'-header-title').html(title);

				this.options.title = title;
			}
		},

		setSubtitle: function(subtitle){

			if (this.options.subtitle !== null) {
				
				this.jQueryheader.find('.'+PLUGIN_NAME+'-header-subtitle').html(subtitle);

				this.options.subtitle = subtitle;
			}
		},

		setIconClass: function(iconClass){

			if (this.options.iconClass !== null) {
				
				this.jQueryheader.find('.'+PLUGIN_NAME+'-header-icon').attr('class', PLUGIN_NAME+'-header-icon ' + iconClass);

				this.options.iconClass = iconClass;
			}
		},

		setHeaderColor: function(headerColor){

	        if (this.options.headerColor !== null) {
	            this.jQueryelement.css('border-bottom', '3px solid ' + headerColor + '');
	            this.jQueryheader.css('background', headerColor);

	            this.options.headerColor = headerColor;
	        }
		},

		startLoading: function(){
			if( !this.jQueryelement.find('.'+PLUGIN_NAME+'-loader').length ){
				this.jQueryelement.append('<div class="'+PLUGIN_NAME+'-loader '+this.options.transitionInOverlay+'"></div>');
			}
		},

		stopLoading: function(){
			var that = this;
			this.jQueryelement.find('.'+PLUGIN_NAME+'-loader').removeClass(this.options.transitionInOverlay).addClass(this.options.transitionOutOverlay);
			this.jQueryelement.find('.'+PLUGIN_NAME+'-loader').one(animationEvent, function () {
                that.jQueryelement.find('.'+PLUGIN_NAME+'-loader').removeClass(that.options.transitionOutOverlay).remove();
            });
		},

		recalculateLayout: function(){

            if(this.jQueryelement.find('.'+PLUGIN_NAME+'-header').length){
            	this.headerHeight = parseInt(this.jQueryelement.find('.'+PLUGIN_NAME+'-header').innerHeight()) + 2/*border bottom of modal*/;
            	this.jQueryelement.css('overflow', 'hidden');
            }

            var windowHeight = jQuery(window).height(),
                contentHeight = this.jQueryelement.find('.'+PLUGIN_NAME+'-content')[0].scrollHeight,
                modalMargin = parseInt(-((this.jQueryelement.innerHeight() + 1) / 2)) + 'px';

            if(this.state == STATES.OPENED || this.state == STATES.OPENING){

				if (this.options.iframe === true) {
					
					// Se a altura da janela é menor que o modal com iframe
					if(windowHeight < (this.options.iframeHeight + this.headerHeight)){

						jQuery(document.body).addClass(PLUGIN_NAME+'-attached');

						this.jQueryelement.find('.'+PLUGIN_NAME+'-iframe').css({
							'height': parseInt(windowHeight - this.headerHeight) + 'px',
						});

					} else {
						jQuery(document.body).removeClass(PLUGIN_NAME+'-attached');

					    this.jQueryelement.find('.'+PLUGIN_NAME+'-iframe').css({
					        'height': parseInt(this.options.iframeHeight) + 'px',
					    });
					}

				} else {

	                if (windowHeight > (contentHeight + this.headerHeight)) {
						jQuery(document.body).removeClass(PLUGIN_NAME+'-attached');
						//内部窗口高;
						var screenHeight=window.innerHeight-45
						|| document.documentElement.clientHeight
						|| document.body.clientHeight;
	                    this.jQueryelement.find('.'+PLUGIN_NAME+'-wrap').css({'height': screenHeight+'px'});
	                }

                	if (this.jQueryelement.innerHeight() > windowHeight || this.jQueryelement.innerHeight() < contentHeight) {
						jQuery(document.body).addClass(PLUGIN_NAME+'-attached');

	                    this.jQueryelement.find('.'+PLUGIN_NAME+'-wrap').css({
	                        'height': parseInt(windowHeight - this.headerHeight) + 'px',
	                    });
	                }

                    var scrollTop = this.jQueryelement.find('.'+PLUGIN_NAME+'-wrap').scrollTop(),
                    	internoHeight = this.jQueryelement.find('.'+PLUGIN_NAME+'-content').innerHeight(),
                    	externoHeight = this.jQueryelement.find('.'+PLUGIN_NAME+'-wrap').innerHeight();	
                    /*
	                if ((externoHeight + scrollTop) < (internoHeight - 50)) {
	                    this.jQueryelement.addClass('hasScroll');
	                } else {
	                    this.jQueryelement.removeClass('hasScroll');
	                }
					*/
				}
            }

            // Corrige margin-top caso o modal sofra alterações na altura de seu conteúdo
            if (this.jQueryelement.css('margin-top') != modalMargin && this.jQueryelement.css('margin-top') != "0px") {
                // this.jQueryelement.css('margin-top', modalMargin);
            }
		}

	};

	jQuery.fn[PLUGIN_NAME] = function (option, args) {
		return this.each(function () {
			var jQuerythis = jQuery(this),
				data = jQuerythis.data(PLUGIN_NAME),
				options = jQuery.extend({}, jQuery.fn.iziModal.defaults, jQuerythis.data(), typeof option == 'object' && option);

			if (!data && (!option || typeof option == 'object')){
				jQuerythis.data(PLUGIN_NAME, (data = new iziModal(this, options)));
			}
			if (typeof option == 'string' && typeof data != 'undefined'){
				data[option].apply(data, [].concat(args));
			}
			else if (options.autoOpen){ // Automatically open the modal if autoOpen setted true
				data.open();
			}
		});
	};
	//内部窗口宽
	var screenWidth=window.innerWidth
	|| document.documentElement.clientWidth
	|| document.body.clientWidth;
	jQuery.fn[PLUGIN_NAME].defaults = {
	    title: "",
	    subtitle: "",
	    theme: "",
	    headerColor: "#88A0B9",
	    overlayColor: "rgba(0, 0, 0, 0.4)",
	    iconColor: "",
	    iconClass: null,
	    width: screenWidth,
	    padding: 0,
	    iframe: false,
	    iframeHeight: 400,
	    iframeURL: null,
	    overlayClose: true,
	    closeOnEscape: true,
	    bodyOverflow: false,
	    focusInput: true,
	    autoOpen: false,
	    transitionInModal: 'transitionIn',
	    transitionOutModal: 'transitionOut',
	    transitionInOverlay: 'fadeIn',
	    transitionOutOverlay: 'fadeOut',
        onOpening: function() {},
        onOpened: function() {},
        onClosing: function() {},
        onClosed: function() {}
	};

	jQuery.fn[PLUGIN_NAME].Constructor = iziModal;

}).call(this, window.jQuery);