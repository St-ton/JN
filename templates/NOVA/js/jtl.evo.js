/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

(function () {
    'use strict';

    if (!$.evo) {
        $.evo = {};
    }

    var EvoClass = function() {};

    EvoClass.prototype = {
        options: { captcha: {} },

        constructor: EvoClass,

        generateSlickSlider: function() {
            /*
             * box product slider
             */

            $('.evo-box-slider:not(.slick-initialized)').slick({
                //dots: true,
                arrows: true,
                lazyLoad: 'ondemand',
                slidesToShow: 1
            });

            $('.evo-slider-half:not(.slick-initialized)').slick({
                //dots: true,
                arrows: true,
                lazyLoad: 'ondemand',
                slidesToShow: 3,
                responsive: [
                    {
                        breakpoint: 992, // md
                        settings: {
                            slidesToShow: 1,
                            centerMode: true,
                            centerPadding: '60px',
                        }
                    },
                    {
                        breakpoint: 1200, // lg
                        settings: {
                            slidesToShow: 2,
                            centerMode: true,
                            centerPadding: '60px',
                        }
                    }
                ]
            });

            $('.evo-box-vertical:not(.slick-initialized)').slick({
                //dots: true,
                arrows:          true,
                vertical:        true,
                adaptiveHeight:  true,
                verticalSwiping: true,
                prevArrow:       '<button class="slick-up" aria-label="Previous" type="button">' +
                    '<i class="fa fa-chevron-up"></i></button>',
                nextArrow:       '<button class="slick-down" aria-label="Next" type="button">' +
                    '<i class="fa fa-chevron-down"></i></button>',
                lazyLoad:        'progressive',
                slidesToShow:    1,
            }).on('afterChange', function () {
                var heights = [];
                $('.evo-box-vertical:not(.eq-height) .product-wrapper').each(function (i, element) {
                    var $element       = $(element);
                    var elementHeight;
                    // Should we include the elements padding in it's height?
                    var includePadding = ($element.css('box-sizing') === 'border-box')
                        || ($element.css('-moz-box-sizing') === 'border-box');

                    if (includePadding) {
                        elementHeight = $element.innerHeight();
                    } else {
                        elementHeight = $element.height();
                    }

                    heights.push(elementHeight);
                });
                $('.evo-box-vertical.evo-box-vertical:not(.eq-height) .product-wrapper')
                    .css('height', Math.max.apply(window, heights) + 'px');
                $('.evo-box-vertical.evo-box-vertical:not(.eq-height)')
                    .addClass('eq-height');
            });

            /*
             * responsive slider (content)
             */
            var evoSliderOptions = {
                //dots: true,
                arrows: true,
                lazyLoad: 'ondemand',
                slidesToShow: 5,
                slidesToScroll: 5,
                responsive: [
                    {
                        breakpoint: 576, // xs
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            // centerMode: true,
                            // centerPadding: '60px',
                        }
                    },
                    {
                        breakpoint: 768, // sm
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                            // centerMode: true,
                            // centerPadding: '60px',
                        }
                    },
                    {
                        breakpoint: 992, // md
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3,
                            // centerMode: true,
                            // centerPadding: '60px',
                        }
                    }
                ]
            };
            evoSliderOptions.slidesToShow = 2;
            // initialize "pushed-success"-slider for detailed customization
            $('#pushed-success .evo-slider:not(.slick-initialized)').slick(evoSliderOptions);

            if ($('#content').hasClass('col-lg-9')) {
                evoSliderOptions.slidesToShow = 4;
            } else {
                evoSliderOptions.slidesToShow = 6;
            }
            $('.evo-slider:not(.slick-initialized)').slick(evoSliderOptions);

            // product list image slider
            $('.product-list .list-gallery:not(.slick-initialized)').slick({
                lazyLoad: 'ondemand',
                infinite: false,
                dots:     false,
                arrows:   true
            });

            var optionsNewsSlider = {
                slidesToShow:   3,
                slidesToScroll: 1,
                arrows:         true,
                infinite:       false,
                lazyLoad: 'ondemand',
                responsive:     [
                    {
                        breakpoint: 576, // xs
                        settings: {
                            slidesToShow: 1
                        }
                    },
                    {
                        breakpoint: 768, // sm
                        settings: {
                            slidesToShow: 2
                        }
                    }
                ]
            };
            if ($('#content').hasClass('col-lg-9')) {
                optionsNewsSlider.slidesToShow = 2;
            }

            $('.news-slider:not(.slick-initialized)').slick(optionsNewsSlider);

            // freegift slider at basket
            $('#freegift form .row').slick({
                slidesToShow:   3,
                slidesToScroll: 3,
                infinite: false,
                responsive: [
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 2
                        }
                    }
                ]
            });
        },

        addSliderTouchSupport: function () {
            $('.carousel').each(function () {
                if ($(this).find('.item').length > 1) {
                    $(this).find('.carousel-control').css('display', 'block');
                    $(this).swiperight(function () {
                        $(this).carousel('prev');
                    }).swipeleft(function () {
                        $(this).carousel('next');
                    });
                } else {
                    $(this).find('.carousel-control').css('display', 'none');
                }
            });
        },

        addTabsTouchSupport: function () {
            $(".tab-content").swiperight(function() {
                var $tab = $('#product-tabs .active').parent().prev();
                if ($tab.length > 0)
                    $tab.find('a').tab('show');
            });
            $(".tab-content").swipeleft(function() {
                var $tab = $('#product-tabs .active').parent().next();
                if ($tab.length > 0)
                    $tab.find('a').tab('show');
            });
        },

        scrollStuff: function() {
            var breakpoint = 0,
                pos,
                sidePanel = $('#sidepanel_left');

            if(sidePanel.length) {
                breakpoint = sidePanel.position().top + sidePanel.hiddenDimension('height');
            }

            pos = breakpoint - $(this).scrollTop();

            if ($(this).scrollTop() > 200 && !$('#to-top').hasClass('active')) {
                $('#to-top').addClass('active');
            } else if($(this).scrollTop() < 200 && $('#to-top').hasClass('active')) {
                $('#to-top').removeClass('active');
            }

            if ($(window).width() > 768) {
                var $document = $(document),
                    $element = $('.navbar-fixed-top'),
                    className = 'nav-closed';

                $document.scroll(function() {
                    $element.toggleClass(className, $document.scrollTop() >= 150);
                });

            }
        },

        productTabsPriceFlow: function() {
            var chartOptions = {
                responsive:       true,
                    scaleBeginAtZero: false,
                    tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var label = data.datasets[tooltipItem.datasetIndex].label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += Math.round(tooltipItem.yLabel * 100) / 100;
                            label += window.chartDataCurrency;
                            return label;
                        }
                    }
                }
            };
            if ($('#tab-link-tb-prcFlw').length) {
                // using tabs
                $('#tab-tb-prcFlw').on('shown.bs.tab', function () {
                    if (typeof window.priceHistoryChart !== 'undefined' && window.priceHistoryChart === null) {
                        window.priceHistoryChart = new Chart(window.ctx, {
                            type: 'bar',
                            data: window.chartData,
                            options: chartOptions
                        });
                    }
                });
                $('#tab-content-product-tabs').on('afterChange', function (event, slick) {
                    if (typeof window.priceHistoryChart !== 'undefined' && window.priceHistoryChart === null) {
                        window.priceHistoryChart = new Chart(window.ctx, {
                            type: 'bar',
                            data: window.chartData,
                            options: chartOptions
                        });
                    }
                });
            } else {
                // using cards
                $('#tab-priceFlow').on('shown.bs.collapse', function () {
                    if (typeof window.priceHistoryChart !== 'undefined' && window.priceHistoryChart === null) {
                        window.priceHistoryChart = new Chart(window.ctx, {
                            type: 'bar',
                            data: window.chartData,
                            options: chartOptions
                        });
                    }
                });
            }
        },

        autoheight: function() {
            $('.row-eq-height').each(function(i, e) {
                $(e).children('[class*="col-"]').children().responsiveEqualHeightGrid();
            });
            $('.row-eq-height.gallery > [class*="col-"], #product-list .product-wrapper').each(function(i, e) {
                $(e).height($('div', $(e)).outerHeight());
            });
        },

        tooltips: function() {
            $('[data-toggle="tooltip"]').tooltip();
        },

        imagebox: function(wrapper) {
            var $wrapper = (typeof wrapper === 'undefined' || wrapper.length === 0) ? $('#result-wrapper') : $(wrapper),
                // square   = $('.image-box', $wrapper).first().height() + 'px',
                padding  = $(window).height() / 2;

            /*$('.image-box', $wrapper).each(function(i, item) {
                var box = $(this),
                    img = box.find('img'),
                    src = img.data('src');

                // img.css('max-height', square);
                // box.css('max-height', square);

                if (src && src.length > 0) {
                    //if (src === 'gfx/keinBild.gif') {
                    //    box.removeClass('loading')
                    //        .addClass('none');
                    //    box.parent().find('.overlay-img').remove();
                    //} else {
                        $(img).lazy(padding, function() {
                            $(this).load(function() {
                                // img.css('max-height', square);
                                // box.css('line-height', square)
                                //     .css('max-height', square)
                                    box.removeClass('loading')
                                    .addClass('loaded');
                            }).error(function() {
                                box.removeClass('loading')
                                    .addClass('error');
                            });
                        });
                    //}
                }
            });*/
            $('img.lazy', 'body').each(function(i, item) {
                var img = $(this),
                    src = img.data('src');

                if (src && src.length > 0) {
                    $(img).lazy(padding, function() {
                        $(this).on('load', function() {
                            img.removeClass('loading')
                                .addClass('loaded');
                        }).on('error', function() {
                            img.removeClass('loading')
                                .addClass('error');
                        });
                    });
                }
            });
        },

        bootlint: function() {
            (function(){
                var p = window.alert;
                var s = document.createElement("script");
                window.alert = function() {
                    console.info(arguments);
                };
                s.onload = function() {
                    bootlint.showLintReportForCurrentDocument([]);
                    window.alert = p;
                };
                s.src = "https://maxcdn.bootstrapcdn.com/bootlint/latest/bootlint.min.js";
                document.body.appendChild(s);
            })();
        },

        showNotify: function(options) {
            eModal.alert({
                size: 'xl',
                buttons: false,
                title: options.title,
                message: options.text,
                keyboard: true,
                tabindex: -1})
                .then(
                 function() {
                    $.evo.generateSlickSlider();
                }
            );
        },

        renderCaptcha: function(parameters) {
            if (typeof parameters !== 'undefined') {
                this.options.captcha =
                    $.extend({}, this.options.captcha, parameters);
            }

            if (typeof grecaptcha === 'undefined' && !this.options.captcha.loaded) {
                this.options.captcha.loaded = true;
                var lang                    = document.documentElement.lang;
                $.getScript("https://www.google.com/recaptcha/api.js?render=explicit&onload=g_recaptcha_callback&hl=" + lang);
            } else {
                $('.g-recaptcha').each(function(index, item) {
                    parameters = $.extend({}, $(item).data(), parameters);
                    try {
                        grecaptcha.render(item, parameters);
                    }
                    catch(e) { }
                });
            }
            $('.g-recaptcha-response').attr('required', true);
        },

        popupDep: function() {
            $('#main-wrapper').on('click', '.popup-dep', function(e) {
                var id    = '#popup' + $(this).attr('id'),
                    title = $(this).attr('title'),
                    html  = $(id).html();
                eModal.alert({
                    message: html,
                    title: title,
                    keyboard: true,
                    buttons: false,
                    tabindex: -1})
                    .then(
                        function () {
                            //the modal just copies all the html.. so we got duplicate IDs which confuses recaptcha
                            var recaptcha = $('.tmp-modal-content .g-recaptcha');
                            if (recaptcha.length === 1) {
                                var siteKey = recaptcha.data('sitekey'),
                                    newRecaptcha = $('<div />');
                                if (typeof  siteKey !== 'undefined') {
                                    //create empty recapcha div, give it a unique id and delete the old one
                                    newRecaptcha.attr('id', 'popup-recaptcha').addClass('g-recaptcha form-group');
                                    recaptcha.replaceWith(newRecaptcha);
                                    grecaptcha.render('popup-recaptcha', {
                                        'sitekey' : siteKey,
                                        'callback' : 'captcha_filled'

                                    });
                                }
                            }
                            addValidationListener();
                            $('.g-recaptcha-response').attr('required', true);
                        }
                    );
                return false;
            });
        },

        popover: function() {
            /*
             * <a data-toggle="popover" data-ref="#popover-content123">Click me</a>
             * <div id="popover-content123" class="popover">content here</div>
             */
            $('[data-toggle="popover"]').popover({
                trigger: 'hover',
                html: true,
                sanitize: false,
                content: function() {
                    var ref = $(this).attr('data-ref');
                    return $(ref).html();
                }
            });
        },

        smoothScrollToAnchor: function(href, pushToHistory) {
            var anchorRegex = /^#[\w\-]+$/;
            if (!anchorRegex.test(href)) {
                return false;
            }

            var target, targetOffset;
            target = $('#' + href.slice(1));

            if (target.length > 0) {
                // scroll below the static megamenu
                var nav         = $('#evo-nav-wrapper.sticky-top');
                var fixedOffset = nav.length > 0 ? nav.outerHeight() : 0;

                targetOffset = target.offset().top - fixedOffset - parseInt(target.css('margin-top'));
                $('html, body').animate({scrollTop: targetOffset});

                if (pushToHistory) {
                    history.pushState({}, document.title, location.pathname + href);
                }

                return true;
            }

            return false;
        },

        smoothScroll: function() {
            var supportHistory = (history && history.pushState) ? true : false;
            var that = this;

            this.smoothScrollToAnchor(location.hash, false);
            $(document).delegate('a[href^="#"]', 'click', function(e) {
                var elem = e.target;
                if (!e.isDefaultPrevented()) {
                    // only runs if no other click event is fired
                    if (that.smoothScrollToAnchor(elem.getAttribute('href'), supportHistory)) {
                        e.preventDefault();
                    }
                }
            });
        },

        smoothScroll2: function() {
            // Select all links with hashes
            $('a[href*="#"]')
            // Remove links that don't actually link to anything
                .not('[data-toggle="collapse"]')
                .not('[href="#"]')
                .not('[href="#0"]')
                .on('click', function(event) {
                    // On-page links
                    if (
                        location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '')
                        &&
                        location.hostname == this.hostname
                    ) {
                        // Figure out element to scroll to
                        var target = $(this.hash);
                        target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                        // Does a scroll target exist?
                        if (target.length) {
                            // Only prevent default if animation is actually gonna happen
                            event.preventDefault();
                            $('html, body').animate({
                                scrollTop: target.offset().top
                            }, 800, function() {
                                // Callback after animation
                                // Must change focus!
                                var $target = $(target);
                                $target.focus();
                                if ($target.is(":focus")) { // Checking if the target was focused
                                    return false;
                                } else {
                                    $target.attr('tabindex','-1'); // Adding tabindex for elements not focusable
                                    $target.focus(); // Set focus again
                                }
                            });
                        }
                    }
                });
        },

        preventDropdownToggle: function() {
            $('a.dropdown-toggle').on('click', function(e){
                var elem = e.target;
                if (elem.getAttribute('aria-expanded') == 'true' && elem.getAttribute('href') != '#') {
                    window.location.href = elem.getAttribute('href');
                    e.preventDefault();
                }
            });
        },

        addCartBtnAnimation: function() {
            var animating = false;

            initCustomization();

            function initCustomization() {
                var addToCartBtn = $('#add-to-cart button[type="submit"]'),
                    form         = $('#buy_form');
                //detect click on the add-to-cart button
                form.on('submit', function(e) {
                    if(!animating ) {
                        //animate if not already animating
                        animating =  true;

                        addToCartBtn.addClass('is-added').find('path').eq(0).animate({
                            //draw the check icon
                            'stroke-dashoffset':0
                        }, 300, function(){
                            setTimeout(function(){
                                addToCartBtn.removeClass('is-added').find('span.btn-basket-check').on('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function(){
                                    //wait for the end of the transition to reset the check icon
                                    addToCartBtn.find('path').eq(0).css('stroke-dashoffset', '19.79');
                                    animating =  false;
                                });

                                if( $('.no-csstransitions').length > 0 ) {
                                    // check if browser doesn't support css transitions
                                    addToCartBtn.find('path').eq(0).css('stroke-dashoffset', '19.79');
                                    animating =  false;
                                }
                            }, 600);
                        });
                    }
                });
            }

        },

        checkout: function() {
            // show only the first submit button (i.g. the button from payment plugin)
            var $submits = $('#checkout-shipping-payment')
                .closest('form')
                .find('button[type="submit"]');
            $submits.addClass('d-none');
            $submits.first().removeClass('d-none');

            $('input[name="Versandart"]', '#checkout-shipping-payment').on('change', function() {
                var id    = parseInt($(this).val());
                var $form = $(this).closest('form');

                if (isNaN(id)) {
                    return;
                }

                $form.find('fieldset, button[type="submit"]')
                    .attr('disabled', true);

                var url = 'bestellvorgang.php?kVersandart=' + id;
                $.evo.loadContent(url, function() {
                    $.evo.checkout();
                }, null, true);
            });
        },

        loadContent: function(url, callback, error, animation, wrapper) {
            var that        = this;
            var $wrapper    = (typeof wrapper === 'undefined' || wrapper.length === 0) ? $('#result-wrapper') : $(wrapper);
            var ajaxOptions = {data: 'isAjax'};
            if (animation) {
                $wrapper.addClass('loading');
            }

            that.trigger('load.evo.content', { url: url });

            $.ajax(url, ajaxOptions).done(function(html) {
                var $data = $(html);
                if (animation) {
                    $data.addClass('loading');
                }
                $wrapper.replaceWith($data);
                $wrapper = $data;
                if (typeof callback === 'function') {
                    callback();
                }
            })
            .fail(function() {
                if (typeof error === 'function') {
                    error();
                }
            })
            .always(function() {
                $wrapper.removeClass('loading');
                that.trigger('contentLoaded'); // compatibility
                that.trigger('loaded.evo.content', { url: url });
            });
        },

        spinner: function(target) {
            var opts = {
              lines: 12             // The number of lines to draw
            , length: 7             // The length of each line
            , width: 5              // The line thickness
            , radius: 10            // The radius of the inner circle
            , scale: 2.0            // Scales overall size of the spinner
            , corners: 1            // Roundness (0..1)
            , color: '#000'         // #rgb or #rrggbb
            , opacity: 1/4          // Opacity of the lines
            , rotate: 0             // Rotation offset
            , direction: 1          // 1: clockwise, -1: counterclockwise
            , speed: 1              // Rounds per second
            , trail: 100            // Afterglow percentage
            , fps: 20               // Frames per second when using setTimeout()
            , zIndex: 2e9           // Use a high z-index by default
            , className: 'spinner'  // CSS class to assign to the element
            , top: '50%'            // center vertically
            , left: '50%'           // center horizontally
            , shadow: false         // Whether to render a shadow
            , hwaccel: false        // Whether to use hardware acceleration (might be buggy)
            , position: 'absolute'  // Element positioning
            };

            if (typeof target === 'undefined') {
                target = document.getElementsByClassName('product-offer')[0];
            }
            if ((typeof target !== 'undefined' && target.id === 'result-wrapper') || $(target).hasClass('product-offer')) {
                opts.position = 'fixed';
            }

            return new Spinner(opts).spin(target);
        },

        trigger: function(event, args) {
            $(document).trigger('evo:' + event, args);
            return this;
        },

        error: function() {
            if (console && console.error) {
                console.error(arguments);
            }
        },

        initInputSpinner: function(target) {
            var config = {
                decrementButton: "<i class='fas fa-minus'></i>", // button text
                incrementButton: "<i class='fas fa-plus'></i>", // ..
                groupClass: "", // css class of the input-group (sizing with input-group-sm, input-group-lg)
                buttonsClass: "btn-light form-control",
                buttonsWidth: "42px",
                textAlign: "center",
                autoDelay: 500, // ms holding before auto value change
                autoInterval: 100, // speed of auto value change
                boostThreshold: 10, // boost after these steps
                boostMultiplier: "auto", // you can also set a constant number as multiplier
                locale: null // the locale for number rendering; if null, the browsers language is used
            }
            $(target).InputSpinner(config);
        },

        addInactivityCheck: function() {
            var timeoutID;

            this.initInputSpinner("input[type='number']");

            function setup() {
                $('#cart-form .nmbr-cfg-group input').on('change',resetTimer);
                $('#cart-form .choose_quantity input').on('change',resetTimer);
                $('#cart-form .nmbr-cfg-group .btn-decrement, #cart-form .nmbr-cfg-group .btn-increment').on('click',resetTimer);
                $('#cart-form .nmbr-cfg-group .btn-decrement, #cart-form .nmbr-cfg-group .btn-increment').on('touchstart',resetTimer,{passive: true});
                $('#cart-form .nmbr-cfg-group .btn-decrement, #cart-form .nmbr-cfg-group .btn-increment').on('keydown',resetTimer);
            }

            if ($('body').data('page') == 3) {
                setup();
            }

            function startTimer() {
                // wait 2 seconds before calling goInactive
                timeoutID = window.setTimeout(goInactive, 500);
            }

            function resetTimer(e) {
                if (timeoutID == undefined) {
                    startTimer();
                }
                window.clearTimeout(timeoutID);

                startTimer();
            }

            function goInactive() {
                // do something
                $('#cart-form').submit();
            }
        },

        setCompareListHeight: function() {
            var h = parseInt($('.comparelist .equal-height').outerHeight());
            $('.comparelist .equal-height').height(h);
        },

        checkMenuScroll: function() {
            var menu = '.megamenu';

            if ($(menu)[0] != undefined) {
                var scrollWidth = parseInt(Math.round($(menu)[0].scrollWidth));
                var width = parseInt(Math.round($(menu).outerWidth() + 2));
                var btnLeft = $('#scrollMenuLeft');
                var btnRight = $('#scrollMenuRight');
                // reset
                btnLeft.off("click.menuScroll");
                btnRight.off("click.menuScroll");

                checkButtons();
                if (width < scrollWidth) {
                    btnLeft.on("click.menuScroll",function () {
                        var leftPos = parseInt(Math.round($('#navbarToggler').scrollLeft()));
                        var newLeft = leftPos-250;

                        $('#navbarToggler').animate({scrollLeft: newLeft},{
                            duration: 600,
                            start: checkButtons(newLeft)
                        });
                    });

                    btnRight.on("click.menuScroll", function () {
                        var leftPos2 = parseInt(Math.round($('#navbarToggler').scrollLeft()));
                        var newLeft = leftPos2+250;
                        var y = parseInt(Math.round(scrollWidth-(width+newLeft)));

                        $('#navbarToggler').animate({scrollLeft: newLeft},{
                            duration: 600,
                            start: checkButtons(newLeft)
                        });
                    });
                }
            }

            function checkButtons(scrollLeft) {
                if (typeof scrollLeft === 'undefined') {
                    scrollLeft = 0;
                }
                let scrollWidth = parseInt($(menu)[0].scrollWidth);
                let width = parseInt($(menu).outerWidth() + 2);
                let btnLeft = $('#scrollMenuLeft');
                let btnRight = $('#scrollMenuRight');

                btnRight.addClass('d-none');
                btnLeft.addClass('d-none');

                if (scrollLeft > 0) {
                    btnLeft.removeClass('d-none');
                }
                if ((scrollWidth - width) > scrollLeft) {
                    btnRight.removeClass('d-none');
                }
            }
        },

        fixStickyElements: function() {
            var sticky    = '.cart-summary';
            var navHeight = $('#main-nav-wrapper').outerHeight(true);
            navHeight = navHeight === undefined ? 0 : parseInt(navHeight + 40);
            $(sticky).css('top', navHeight);
        },

        setWishlistVisibilitySwitches: function() {
            $('.wl-visibility-switch').on('change', function () {
                $.evo.io().call('setWishlistVisibility', [$(this).data('wl-id'), $(this).is(":checked")], $(this), function(error, data) {
                    if (error) {
                        return;
                    }
                    var $wlPrivate    = $('span[data-switch-label-state="private-' + data.response.wlID + '"]'),
                        $wlPublic     = $('span[data-switch-label-state="public-' + data.response.wlID + '"]'),
                        $wlURLWrapper = $('#wishlist-url-wrapper'),
                        $wlURL        = $('#wishlist-url');
                    if (data.response.state) {
                        $wlPrivate.addClass('d-none');
                        $wlPublic.removeClass('d-none');
                        $wlURLWrapper.removeClass('d-none');
                        $wlURL.val($wlURL.data('static-route') + data.response.url)
                    } else {
                        $wlPrivate.removeClass('d-none');
                        $wlPublic.addClass('d-none');
                        $wlURLWrapper.addClass('d-none');
                    }
                });
            });
        },

        /**
         * $.evo.extended() is deprecated, please use $.evo instead
         */
        extended: function() {
            return $.evo;
        },

        register: function() {
            // this.addSliderTouchSupport();
            this.productTabsPriceFlow();
            this.generateSlickSlider();
            $('.nav-pills, .nav-tabs').tabdrop();
            //this.addTabsTouchSupport();
            //this.autoheight();
            this.tooltips();
            this.imagebox();
            // this.renderCaptcha();
            this.popupDep();
            this.popover();
            // this.preventDropdownToggle();
            // this.smoothScroll2();
            this.addCartBtnAnimation();
            this.checkout();
            this.addInactivityCheck();
            this.setCompareListHeight();
            this.checkMenuScroll();
            this.fixStickyElements();
            this.setWishlistVisibilitySwitches();
        }
    };

    var ie = /(msie|trident)/i.test(navigator.userAgent) ? navigator.userAgent.match(/(msie |rv:)(\d+(.\d+)?)/i)[2] : false;
    if (ie && parseInt(ie) <= 9) {
        $(document).ready(function () {
            $.evo.register();
        });
    } else {
        $(window).on('load', function () {
            $.evo.register();
        });
    }

    $(window).on('resize', function () {
      /*  console.log('resize');
        $.evo.autoheight();*/
        $.evo.checkMenuScroll();
    });

    // PLUGIN DEFINITION
    // =================
    $.evo = new EvoClass();
})(jQuery);

function g_recaptcha_callback() {
    $.evo.renderCaptcha();
}