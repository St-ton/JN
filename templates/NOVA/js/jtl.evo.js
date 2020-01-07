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
                arrows:         false,
                lazyLoad:       'ondemand',
                slidesToShow:   2,
                swipeToSlide:   true,
                slidesToScroll: 2,
                mobileFirst:    true,
                responsive: [
                    {
                        breakpoint: 992, // md
                        settings: {
                            arrows: true,
                        }
                    },
                    {
                        breakpoint: 1200, // lg
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            arrows: true,
                        }
                    }
                ]
            });

            $('.evo-slider-half:not(.slick-initialized)').slick({
                //dots: true,
                arrows:       true,
                lazyLoad:     'ondemand',
                swipeToSlide:   true,
                slidesToShow: 3,
                responsive:   [
                    {
                        breakpoint: 992, // md
                        settings: {
                            slidesToShow: 1,
                        }
                    },
                    {
                        breakpoint: 1200, // lg
                        settings: {
                            slidesToShow: 2,
                        }
                    }
                ]
            });

            $('.evo-box-vertical:not(.slick-initialized)').slick({
                //dots: true,
                arrows:          true,
                vertical:        true,
                adaptiveHeight:  true,
                swipeToSlide:    true,
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
                rows:           0,
                arrows:         false,
                lazyLoad:       'ondemand',
                slidesToShow:   2,
                slidesToScroll: 2,
                swipeToSlide:   true,
                mobileFirst:    true,
                responsive:     [
                    {
                        breakpoint: 768, // xs
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 992, // sm
                        settings: {
                            slidesToShow:5,
                            arrows: true,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 1300,
                        settings: {
                            slidesToShow:7,
                            arrows: true,
                            slidesToScroll: 1
                        }
                    }
                ]
            };
            $('.evo-slider:not(.slick-initialized)').slick(evoSliderOptions);

            // product list image slider
            /*$('.product-list .list-gallery:not(.slick-initialized)').slick({
                lazyLoad: 'ondemand',
                infinite: false,
                dots:     false,
                arrows:   true
            });*/
            var optionsNewsSlider = {
                rows:           0,
                slidesToShow:   1,
                slidesToScroll: 1,
                arrows:         false,
                swipeToSlide:   true,
                infinite:       false,
                lazyLoad:       'ondemand',
                mobileFirst:    true,
                responsive:     [
                    {
                        breakpoint: 768, // xs
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 992, // sm
                        settings: {
                            slidesToShow:3,
                            arrows: true
                        }
                    },
                    {
                        breakpoint: 1300,
                        settings: {
                            slidesToShow:4,
                            arrows: true
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
                swipeToSlide:   true,
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
            var dateFormat = 'DD.MM.YYYY';
            if ($('html').attr('lang') !== 'de') {
                dateFormat = 'MM/DD/YYYY';
            }
            var chartOptions = {
                responsive:       true,
                scaleBeginAtZero: false,
                aspectRatio:3,
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var label = window.chartDataTooltip;
                            label += Math.round(tooltipItem.yLabel * 100) / 100;
                            label += ' '+window.chartDataCurrency;
                            return label;
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {
                            parser: 'DD.MM.YYYY',
                            // round: 'day'
                            tooltipFormat: dateFormat
                        },
                        display: false
                    }],
                }
            };
            if ($('#tab-link-tb-prcFlw').length) {
                // using tabs
                $('#tab-link-tb-prcFlw').on('shown.bs.tab', function () {
                    if (typeof window.priceHistoryChart !== 'undefined' && window.priceHistoryChart === null) {
                        window.priceHistoryChart = new Chart(window.ctx, {
                            type: 'line',
                            data: window.chartData,
                            options: chartOptions
                        });
                    }
                });
                $('#tab-content-product-tabs').on('afterChange', function (event, slick) {
                    if (typeof window.priceHistoryChart !== 'undefined' && window.priceHistoryChart === null) {
                        window.priceHistoryChart = new Chart(window.ctx, {
                            type: 'line',
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
                            type: 'line',
                            data: window.chartData,
                            options: chartOptions
                        });
                    }
                });
            }
        },

        tooltips: function() {
            $('[data-toggle="tooltip"]').tooltip();
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
                var nav         = $('#jtl-nav-wrapper.sticky-top');
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

            $('#country').on('change', function (e) {
                var val = $(this).find(':selected').val();

                $.evo.io().call('checkDeliveryCountry', [val], {}, function (error, data) {
                    var $shippingSwitch = $('#checkout_register_shipping_address');

                    if (data.response) {
                        $shippingSwitch.removeAttr('disabled');
                        $shippingSwitch.parent().removeClass('d-none');
                    } else {
                        $shippingSwitch.attr('disabled', true);
                        $shippingSwitch.parent().addClass('d-none');
                        if ($shippingSwitch.prop('checked')) {
                            $shippingSwitch.prop('checked', false);
                            $('#select_shipping_address').collapse('show');
                        }
                    }
                });
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

        addInactivityCheck: function() {
            var timeoutID;

            function setup() {
                $('#cart-form .form-counter input').on('change',resetTimer);
                $('#cart-form .choose_quantity input').on('change',resetTimer);
                $('#cart-form .form-counter .btn-decrement, #cart-form .form-counter .btn-increment').on('click',resetTimer);
                $('#cart-form .form-counter .btn-decrement, #cart-form .form-counter .btn-increment').on('touchstart',resetTimer,{passive: true});
                $('#cart-form .form-counter .btn-decrement, #cart-form .form-counter .btn-increment').on('keydown',resetTimer);
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

        fixStickyElements: function() {
            var sticky    = '.cart-summary';
            var navHeight = $('#jtl-nav-wrapper').outerHeight(true);
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

        initEModals: function () {
            $('.author-modal').on('click', function (e) {
                e.preventDefault();
                let modalID = $(this).data('target');
                eModal.alert({
                    title: $(modalID).attr('title'),
                    message: $(modalID).html(),
                    buttons: false
                });
            });
        },

        initPriceSlider: function ($wrapper, redirect) {
            let priceRange      = $wrapper.find('[data-id="js-price-range"]').val(),
                priceRangeID    = $wrapper.find('[data-id="js-price-range-id"]').val(),
                priceRangeMin   = 0,
                priceRangeMax   = $wrapper.find('[data-id="js-price-range-max"]').val(),
                currentPriceMin = priceRangeMin,
                currentPriceMax = priceRangeMax,
                $priceRangeFrom = $("#" + priceRangeID + "-from"),
                $priceRangeTo = $("#" + priceRangeID + "-to"),
                $priceSlider = document.getElementById(priceRangeID);

            if (priceRange) {
                let priceRangeMinMax = priceRange.split('_');
                currentPriceMin = priceRangeMinMax[0];
                currentPriceMax = priceRangeMinMax[1];
                $priceRangeFrom.val(currentPriceMin);
                $priceRangeTo.val(currentPriceMax);
            }
            noUiSlider.create($priceSlider, {
                start: [parseInt(currentPriceMin), parseInt(currentPriceMax)],
                connect: true,
                range: {
                    'min': parseInt(priceRangeMin),
                    'max': parseInt(priceRangeMax)
                },
                step: 1
            });
            $priceSlider.noUiSlider.on('end', function (values, handle) {
                $.evo.redirectToNewPriceRange(values[0] + '_' + values[1], redirect, $wrapper);
            });
            $priceSlider.noUiSlider.on('slide', function (values, handle) {
                $priceRangeFrom.val(values[0]);
                $priceRangeTo.val(values[1]);
            });
            $('.price-range-input').change(function () {
                let prFrom = $priceRangeFrom.val(),
                    prTo = $priceRangeTo.val();
                $.evo.redirectToNewPriceRange(
                    (prFrom > 0 ? prFrom : priceRangeMin) + '_' + (prTo > 0 ? prTo : priceRangeMax),
                    redirect,
                    $wrapper
                );
            });
        },

        initFilters: function (href) {
            let $wrapper = $('.js-collapse-filter'),
                $spinner = $.evo.extended().spinner($wrapper.get(0))
                self=this;

            $wrapper.addClass('loading');
            $.ajax(href, {data: {'isAjax':1}})
            .done(function(data) {
                $wrapper.html(data);
                self.initPriceSlider($wrapper, false);
            })
            .always(function() {
                $spinner.stop();
                $wrapper.removeClass('loading');
            });
        },

        redirectToNewPriceRange: function (priceRange, redirect, $wrapper) {
            let currentURL  = window.location.href;
            if (!redirect) {
                currentURL  = $wrapper.find('[data-id="js-price-range-url"]').val();
            }
            let redirectURL = $.evo.updateURLParameter(
                currentURL,
                'pf',
                priceRange
            );
            if (redirect) {
                window.location.href = redirectURL;
            } else {
                $.evo.initFilters(redirectURL);
            }
        },

        updateURLParameter: function (url, param, paramVal) {
            let newAdditionalURL = '',
                tempArray        = url.split('?'),
                baseURL          = tempArray[0],
                additionalURL    = tempArray[1],
                temp             = '';
            if (additionalURL) {
                tempArray = additionalURL.split('&');
                for (let i=0; i<tempArray.length; i++){
                    if(tempArray[i].split('=')[0] != param){
                        newAdditionalURL += temp + tempArray[i];
                        temp = '&';
                    }
                }
            }

            return baseURL + '?' + newAdditionalURL + temp + param + '=' + paramVal;
        },

        /**
         * $.evo.extended() is deprecated, please use $.evo instead
         */
        extended: function() {
            return $.evo;
        },

        register: function() {
            this.productTabsPriceFlow();
            this.generateSlickSlider();
            setTimeout(() => {
                $('.nav-tabs').tabdrop();
            }, 200);
            this.tooltips();
            this.popupDep();
            this.popover();
            this.addCartBtnAnimation();
            this.checkout();
            this.addInactivityCheck();
            this.setCompareListHeight();
            this.fixStickyElements();
            this.setWishlistVisibilitySwitches();
            this.initEModals();
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

    // PLUGIN DEFINITION
    // =================
    $.evo = new EvoClass();
})(jQuery);

function g_recaptcha_callback() {
    $.evo.renderCaptcha();
}