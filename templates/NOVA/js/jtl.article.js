/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

(function($, document, window, viewport){
    'use strict';

    var _stock_info = ['out-of-stock', 'in-short-supply', 'in-stock'],
        $v,
        ArticleClass = function () {
            this.init();
        };

    ArticleClass.DEFAULTS = {
        input: {
            id: 'a',
            childId: 'VariKindArtikel',
            quantity: 'anzahl'
        },
        action: {
            compareList: 'Vergleichsliste',
            compareListRemove: 'Vergleichsliste.remove',
            wishList: 'Wunschliste',
            wishListRemove: 'Wunschliste.remove'
        },
        selector: {
            navUpdateCompare: '#comparelist-dropdown-content',
            navBadgeUpdateCompare: '#comparelist-badge',
            navCompare: '#shop-nav-compare',
            navContainerWish: '#wishlist-dropdown-container',
            navBadgeWish: '#badge-wl-count',
            navBadgeAppend: '#shop-nav li.cart-menu',
            boxContainer: '#sidebox',
            boxContainerWish: '#sidebox',
            quantity: 'input.quantity'
        },
        modal: {
            id: 'modal-article-dialog',
            wrapper: '#result-wrapper',
            wrapper_modal: '#result-wrapper-modal'
        }
    };

    ArticleClass.prototype = {
        modalShown: false,
        modalView: null,

        constructor: ArticleClass,

        init: function () {
            this.options = ArticleClass.DEFAULTS;
            this.gallery = null;
        },
        
        onLoad: function() {
            if (this.isSingleArticle()) {
                var that = this;
                var form = $.evo.io().getFormValues('buy_form');

                if (typeof history.replaceState === 'function') {
                    history.replaceState({
                        a: form.a,
                        a2: form.VariKindArtikel || form.a,
                        url: document.location.href,
                        variations: {}
                    }, document.title, document.location.href);
                }

                window.addEventListener('popstate', function (event) {
                    if (event.state) {
                        that.setArticleContent(event.state.a, event.state.a2, event.state.url, event.state.variations);
                    }
                }, false);
            }
        },

        isSingleArticle: function() {
            return $('#buy_form').length > 0;
        },

        getWrapper: function(wrapper) {
            return typeof wrapper === 'undefined' ? $(this.options.modal.wrapper) : $(wrapper);
        },

        getCurrent: function($item) {
            var $current = $item.hasClass('variation') ? $item : $item.closest('.variation');
            if ($current.tagName === 'SELECT') {
                $current = $item.find('option:selected');
            }

            return $current;
        },

        register: function(wrapper) {
            var $wrapper = this.getWrapper(wrapper);

            if (this.isSingleArticle()) {
                this.registerGallery($wrapper);
                this.registerConfig();
                this.registerHoverVariations($wrapper);
            }

            this.registerSimpleVariations($wrapper);
            this.registerSwitchVariations($wrapper);
            this.registerBulkPrices($wrapper);
            // this.registerImageSwitch($wrapper);
            //this.registerArticleOverlay($wrapper);
            this.registerFinish($wrapper);
        },

        registerGallery: function(wrapper) {
            /*
             * product slider and zoom (details)
             */
            function slickinit(fullscreen, current = 0)
            {
                var previewSlidesToShow = 5;

                var options = {
                    lazyLoad: 'ondemand',
                    infinite: true,
                    dots:     false,
                    swipeToSlide:   true,
                    arrows:   false,
                    speed: 500,
                    fade: true,
                    cssEase: 'linear',
                    asNavFor: '#gallery_preview',
                    responsive:     [
                        {
                            breakpoint: 992,
                            settings: {
                                dots: true
                            }
                        }
                    ]
                };

                var options_preview = {
                    lazyLoad:       'ondemand',
                    slidesToShow:   previewSlidesToShow,
                    slidesToScroll: 1,
                    asNavFor:       '#gallery',
                    dots:           false,
                    swipeToSlide:   true,
                    arrows:         true,
                    focusOnSelect:  true,
                    responsive:     [
                        {
                            breakpoint: 768,
                            settings:   {
                                slidesToShow: 4
                            }
                        },
                        {
                            breakpoint: 576,
                                settings: {
                                slidesToShow: 3
                            }
                        }
                    ]
                };

                $('#gallery').slick(options);
                $('#gallery_preview').slick(options_preview);
            }

            function toggleFullscreen(fullscreen = false)
            {
                var maxHeight= Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
                var otherElemHeight = 0;
                var current = ($('#gallery .slick-current').data('slick-index'));

                if (fullscreen) {
                    $('#image_wrapper').addClass('fullscreen');

                    otherElemHeight = $('#image_wrapper .product-detail-image-topbar').outerHeight() +
                        parseInt($('#image_wrapper .product-detail-image-topbar').css('marginBottom')) +
                        230;

                    $('#gallery picture *').removeAttr('sizes');
                    lazySizes.autoSizer.updateElem($('#gallery picture *'));
                } else {
                    $('#image_wrapper').removeClass('fullscreen');
                }

                $('#gallery img').css('max-height', maxHeight-otherElemHeight);

                $('#gallery').slick('slickSetOption','initialSlide', current, true);
                $('#gallery_preview').slick('slickGoTo', current, true);
            }

            function addClickListener() {
                $('#gallery img').off('click').on('click', e => {
                    if (window.innerWidth > globals.breakpoints.lg) {
                        toggleFullscreen(true);
                    }
                });
            }

            slickinit(false);

            if (wrapper[0].id.indexOf(this.options.modal.wrapper_modal.substr(1)) === -1) {
                addClickListener();

                $(document).keyup(e => {
                    if (e.key === "Escape") {
                        toggleFullscreen();
                        addClickListener();
                    }
                });

                $('#image_fullscreen_close').on('click', e => {
                    toggleFullscreen();
                    addClickListener();
                });
            }
        },

        registerConfig: function() {
            var that   = this,
                config = $('#product-configurator')
                    .closest('form')
                    .find('input[type="radio"], input[type="checkbox"], input[type="number"], select'),
                dropdown = $('#product-configurator')
                    .closest('form')
                    .find('select');

            if (dropdown.length > 0) {
                dropdown.on('change', function () {
                    var item = $(this).val();
                    $(this).parents('.cfg-group').find('.cfg-drpdwn-item.collapse.show').collapse('hide');
                    $('#drpdwn_qnt_' + item).collapse('show');
                })
            }

            if (config.length > 0) {
                config.on('change', function() {
                    that.configurator();
                })
                    .keypress(function (e) {
                        if (e.which === 13) {
                            return false;
                        }
                    });
                that.configurator(true);
            }
        },

        registerSimpleVariations: function($wrapper) {
            var that = this;

            $('.variations select', $wrapper).selectpicker({
                iconBase: 'fa',
                tickIcon: 'fa-check',
                hideDisabled: true,
                showTick: true
            });

            $('.simple-variations input[type="radio"]', $wrapper)
                .on('change', function() {
                    var val = $(this).val(),
                        key = $(this).parent().data('key');
                    $('.simple-variations [data-key="' + key + '"]').removeClass('active');
                    $('.simple-variations [data-value="' + val + '"]').addClass('active');
                    $(this).closest(".swatches").addClass("radio-selected");
                });

            $('.simple-variations input[type="radio"], .simple-variations select', $wrapper)
                .each(function(i, item) {
                    var $item   = $(item),
                        wrapper = '#' + $item.closest('form').closest('div[data-wrapper="true"]').attr('id');

                    $item.on('change', function () {
                        that.variationPrice($(this), true, wrapper);
                    });
                });
        },

        registerBulkPrices: function($wrapper) {
            var $bulkPrice = $('.bulk-price', $wrapper),
                that       = this,
                $config    = $('#product-configurator');

            if ($bulkPrice.length > 0 && $config.length === 0) {
                $('#quantity', $wrapper)
                    .each(function(i, item) {
                        var $item   = $(item),
                            wrapper = '#' + $item.closest('form').closest('div[data-wrapper="true"]').attr('id');

                        $item.on('change', function () {
                            that.variationPrice($(this), true, wrapper);
                        });
                    });
            }
        },

        registerSwitchVariations: function($wrapper) {
            var that = this;

            $('.switch-variations input[type="radio"], .switch-variations select', $wrapper)
                .each(function(i, item) {
                    var $item   = $(item),
                        wrapper = '#' + $item.closest('form').closest('div[id]').attr('id');

                    $item.on('change', function () {
                        that.variationSwitch($(this), false, wrapper);
                    });
                });

            if (isTouchCapable()) {
                $('.variations .swatches .variation', $wrapper)
                    .on('mouseover', function() {
                        $(this).trigger('click');
                    });
            }

            // ie11 fallback
            if (typeof document.body.style.msTransform === 'string') {
                $('.variations label.variation', $wrapper)
                    .on('click', function (e) {
                        if (e.target.tagName === 'IMG') {
                            $(this).trigger('click');
                        }
                    });
            }
        },

        registerHoverVariations: function ($wrapper) {
            $('.variations label.variation', $wrapper)
                .on('mouseenter', function (e) {
                    var $item      = $(this),
                        $variation = $item.data('value');
                    $('.variation-image-preview.lazyloaded.vt' + $variation).addClass('show');
                    $('.variation-image-preview.lazyload.vt' + $variation).removeClass('d-none').on('lazyloaded', function () {
                        $('.variation-image-preview.vt' + $variation).addClass('show');
                    });
                }).on('mouseleave', function (e) {
                    var $item      = $(this),
                        $variation = $item.data('value');
                    $('.variation-image-preview.lazyloaded.vt' + $variation).removeClass('show');
                    $('.variation-image-preview.lazyload.vt' + $variation).on('lazyloaded', function () {
                        $('.variation-image-preview.vt' + $variation).removeClass('show');
                    });
            });

            $('.variations .selectpicker').on('show.bs.select', function () {
                var $item = $(this).parent();
                $item.find('li .variation').on('mouseenter', function () {
                    var $variation = $(this).find('span[data-value]').data("value");
                    $('.variation-image-preview.lazyloaded.vt' + $variation).addClass('show');
                    $('.variation-image-preview.lazyload.vt' + $variation).removeClass('d-none').on('lazyloaded', function () {
                        $('.variation-image-preview.vt' + $variation).addClass('show');
                    });
                }).on('mouseleave', function () {
                    var $variation = $(this).find('span[data-value]').data("value");
                    $('.variation-image-preview.lazyloaded.vt' + $variation).removeClass('show');
                    $('.variation-image-preview.lazyload.vt' + $variation).on('lazyloaded', function () {
                        $('.variation-image-preview.vt' + $variation).removeClass('show');
                    });
                });
            });

            $('.variations .selectpicker').on('hide.bs.select', function () {
                var $item = $(this).parent();
                $item.find('li .variation').off('mouseenter mouseleave');
            });
        },

        registerImageSwitch: function($wrapper) {
            var that     = this,
                imgSwitch,
                gallery  = this.gallery;

            if (gallery !== null) {
                imgSwitch = function (context, temporary, force) {
                    var $context = $(context),
                        id       = $context.attr('data-key'),
                        value    = $context.attr('data-value'),
                        data     = $context.data('list'),
                        title    = $context.attr('data-title');

                    if (typeof temporary === 'undefined') {
                        temporary = true;
                    }

                    if ((!$context.hasClass('active') || force) && !!data) {
                        gallery.setItems([data], value);

                        if (!temporary) {
                            var items  = [data],
                                stacks = gallery.getStacks();
                            for (var s in stacks) {
                                if (stacks.hasOwnProperty(s) && s.match(/^_[0-9a-zA-Z]*$/) && s !== '_' + id) {
                                    items = $.merge(items, stacks[s]);
                                }
                            }

                            gallery.setItems([data], '_' + id);
                            gallery.setItems(items, '__');
                            gallery.render('__');

                            that.galleryIndex     = gallery.index;
                            that.galleryLastIdent = gallery.ident;
                        } else {
                            gallery.render(value);
                        }
                    }
                }
            } else {
                imgSwitch = function (context, temporary) {
                    var $context = $(context),
                        value    = $context.attr('data-value'),
                        data     = $context.data('list'),
                        title    = $context.attr('data-title');

                    if (typeof temporary === 'undefined') {
                        temporary = true;
                    }

                    if (!!data) {
                        var $wrapper = $(context).closest('.product-wrapper'),
                            $img     = $('.image-box img', $wrapper);
                        if ($img.length === 1) {
                            $img.attr('src', data.md.src);
                            if (!temporary) {
                                $img.data('src', data.md.src);
                            }
                        }
                    }
                };
            }

            $('.variations .bootstrap-select select', $wrapper)
                .on('change', function() {
                    var sel  = $(this).find('[value=' + this.value + ']'),
                        cont = $(this).closest('.variations');

                    if (cont.hasClass('simple-variations')) {
                        imgSwitch(sel, false, false);
                    } else {
                        imgSwitch(sel, true, false);
                    }
                });

            if (!isTouchCapable() || ResponsiveBootstrapToolkit.current() !== 'xs') {
                $('.variations .bootstrap-select .dropdown-menu li', $wrapper)
                    .on('hover', function () {
                        var tmp_idx = parseInt($(this).attr('data-original-index')) + 1,
                            rule    = 'select option:nth-child(' + tmp_idx + ')',
                            sel     = $(this).closest('.bootstrap-select').find(rule);
                        imgSwitch(sel);
                    }, function () {
                        var tmp_idx = parseInt($(this).attr('data-original-index')) + 1,
                            rule    = 'select option:nth-child(' + tmp_idx + ')',
                            sel     = $(this).closest('.bootstrap-select').find(rule),
                            gallery = that.gallery,
                            active;

                        if (gallery !== null) {
                            active = $(sel).find('.variation.active');
                            gallery.render(that.galleryLastIdent);
                            gallery.activate(that.galleryIndex);
                        } else {
                            var $wrapper = $(sel).closest('.product-wrapper'),
                                $img     = $('.image-box img', $wrapper);
                            if ($img.length === 1) {
                                $img.attr('src', $img.data('src'));
                            }
                        }
                    });
            }

            $('.variations.simple-variations .variation', $wrapper)
                .on('click', function () {
                    imgSwitch(this, false);
                });

            if (!isTouchCapable() || ResponsiveBootstrapToolkit.current() !== 'xs') {
                $('.variations .variation', $wrapper)
                    .on('hover', function () {
                        imgSwitch(this);
                    }, function () {
                        var sel     = $(this).closest('.variation'),
                            gallery = that.gallery;

                        if (gallery !== null) {
                            gallery.render(that.galleryLastIdent);
                            gallery.activate(that.galleryIndex);
                        } else {
                            var $wrapper = $(sel).closest('.product-wrapper'),
                                $img     = $('.image-box img', $wrapper);
                            if ($img.length === 1) {
                                $img.attr('src', $img.data('src'));
                            }
                        }
                    });
            }
        },

        registerFinish: function($wrapper) {
            $('#jump-to-votes-tab', $wrapper).on('click', function () {
                $('#content a[href="#tab-votes"]').tab('show');
            });

            if (this.isSingleArticle()) {
                if ($('.switch-variations .form-group', $wrapper).length === 1) {
                    var wrapper = '#' + $($wrapper).attr('id');
                    this.variationSwitch($('.switch-variations', $wrapper), false, wrapper);
                }
            }
            else {
                var that = this;

                $('.product-cell.hover-enabled')
                    .on('click', function (event) {
                        if (isTouchCapable() && ResponsiveBootstrapToolkit.current() !== 'xs') {
                            var $this = $(this);

                            if (!$this.hasClass('active')) {
                                event.preventDefault();
                                event.stopPropagation();
                                $('.product-cell').removeClass('active');
                                $this.addClass('active');
                            }
                        }
                    })
                    .on('mouseenter', function (event) {
                        var $this = $(this),
                            wrapper = '#' + $this.attr('id');

                        if (!$this.data('varLoaded') && $('.switch-variations .form-group', $this).length === 1) {
                            that.variationSwitch($('.switch-variations', $this), false, wrapper);
                        }
                        $this.data('varLoaded', true);
                    });
            }

            this.registerProductActions($('#sidepanel_left'));
            this.registerProductActions($('#footer'));
            this.registerProductActions($('#shop-nav'));
            this.registerProductActions($wrapper);
            this.registerProductActions('#cart-form');
        },

        registerProductActions: function($wrapper) {
            var that = this;

            $('*[data-toggle="product-actions"] button', $wrapper)
                .on('click', function(event) {
                    var data = $(this.form).serializeObject();

                    if ($wrapper === '#cart-form') {
                        data.wlPos = $(this).data('wl-pos');
                        data.a = $(this).data('product-id-wl');
                    }

                    if (that.handleProductAction(this, data)) {
                        event.preventDefault();
                    }
                });
            $('a[data-toggle="product-actions"]', $wrapper)
                .on('click', function(event) {
                    var data  = $(this).data('value');
                    this.name = $(this).data('name');

                    if (that.handleProductAction(this, data)) {
                        event.preventDefault();
                    }
                });
        },

        loadModalArticle: function(url, wrapper, done, fail) {
            var that       = this,
                $wrapper   = this.getWrapper(wrapper),
                id         = wrapper.substring(1),
                $modalBody = $('.modal-body', this.modalView);

            $wrapper.addClass('loading');

            $.ajax(url, {data: {'isAjax':1, 'quickView':1}})
                .done(function(data) {
                    var $html      = $('<div />').html(data);
                    var $headerCSS = $html.find('link[type="text/css"]');
                    var $headerJS  = $html.find('script[src][src!=""]');
                    var content    = $html.find(that.options.modal.wrapper).html();

                    $headerCSS.each(function (pos, item) {
                        var $cssLink = $('head link[href="' + item.href + '"]');
                        if ($cssLink.length === 0) {
                            $('head').append('<link rel="stylesheet" type="text/css" href="' + item.href + '" >');
                        }
                    });

                    $headerJS.each(function (pos, item) {
                        if (typeof item.src !== 'undefined' && item.src.length > 0) {
                            var $jsLink = $('head script[src="' + item.src + '"]');
                            if ($jsLink.length === 0) {
                                $('head').append('<script defer src="' + item.src + '" >');
                            }
                        }
                    });

                    $modalBody.html($('<div id="' + id + '" />').html(content));

                    var $modal  = $modalBody.closest(".modal-dialog"),
                        title   = $modal.find('.modal-body h1'),
                        $config = $('#product-configurator', $modalBody);

                    if ($config.length > 0) {
                        // Configurator in child article!? Currently not supported!
                        $config.remove();
                        $modalBody.addClass('loading');
                        var spinner = $.evo.extended().spinner($modalBody.get(0));
                        location.href = url;
                    }
                    if (title.length > 0 && title.text().length > 0) {
                        $modal.find('.modal-title').text(title.text());
                        title.remove();
                    }

                    $('form', $modalBody).on('submit', function(event) {
                        event.preventDefault();

                        var $form = $(this);
                        var data  = $form.serializeObject();
                        if (data['VariKindArtikel']) {
                            data['a'] = data['VariKindArtikel'];
                        }

                        $.evo.basket().addToBasket($form, data);
                        that.modalView.modal('hide');
                    });

                    if (typeof done === 'function') {
                        done();
                    }
                })
                .fail(function() {
                    if (typeof fail === 'function') {
                        fail();
                    }
                })
                .always(function() {
                    $wrapper.removeClass('loading');
                });
        },

        addToComparelist: function(data) {
            var productId = parseInt(data[this.options.input.id]);
            var childId = parseInt(data[this.options.input.childId]);
            if (childId > 0) {
                productId = childId;
            }
            if (productId > 0) {
                var that = this;
                $.evo.io().call('pushToComparelist', [productId], that, function(error, data) {
                    if (error) {
                        return;
                    }

                    var response = data.response;

                    if (response) {
                        switch (response.nType) {
                            case 0: // error
                                var errorlist = '<ul><li>' + response.cHints.join('</li><li>') + '</li></ul>';
                                eModal.alert({
                                    title: response.cTitle,
                                    message: errorlist,
                                    keyboard: true,
                                    tabindex: -1,
                                    buttons: false
                                });
                                break;
                            case 1: // forwarding
                                window.location.href = response.cLocation;
                                break;
                            case 2: // added to comparelist
                                that.updateComparelist(response);
                                break;
                        }
                    }
                });

                return true;
            }

            return false;
        },

        removeFromCompareList: function(data) {
            var productId = parseInt(data[this.options.input.id]);
            if (productId > 0) {
                var that = this;
                $.evo.io().call('removeFromComparelist', [productId], that, function(error, data) {
                    if (error) {
                        return;
                    }

                    var response = data.response;

                    if (response) {
                        switch (response.nType) {
                            case 0: // error
                                var errorlist = '<ul><li>' + response.cHints.join('</li><li>') + '</li></ul>';
                                eModal.alert({
                                    title: response.cTitle,
                                    message: errorlist,
                                    keyboard: true,
                                    tabindex: -1,
                                    buttons: false
                                });
                                break;
                            case 1: // forwarding
                                window.location.href = response.cLocation;
                                break;
                            case 2: // removed from comparelist
                                that.updateComparelist(response);
                                break;
                        }
                    }
                });

                return true;
            }

            return false;
        },

        updateComparelist: function(data) {
            var $badgeUpd = $(this.options.selector.navUpdateCompare);

            var badge = $(data.navDropdown);
            $badgeUpd.html(badge);
            $(this.options.selector.navBadgeUpdateCompare).html(data.nCount);

            if (data.nCount > 0) {
                $(this.options.selector.navCompare).removeClass('d-none');
            } else {
                $(this.options.selector.navCompare).addClass('d-none');
                $('#nav-comparelist-collapse').removeClass('show');
            }
            if (data.nCount > 1) {
                $('#nav-comparelist-goto').removeClass('d-none');
            } else {
                $('#nav-comparelist-goto').addClass('d-none');
            }
            this.registerProductActions($('#shop-nav'));

            if (data.productID) {
                let $action = $('button[data-product-id-cl="' + data.productID + '"]')
                $action.removeClass("on-list");
                $action.next().removeClass("press");
            }

            for (var ind in data.cBoxContainer) {
                var $list = $(this.options.selector.boxContainer+ind);

                if ($list.length > 0) {
                    if (data.cBoxContainer[ind].length) {
                        var $boxContent = $(data.cBoxContainer[ind]);
                        this.registerProductActions($boxContent);
                        $list.replaceWith($boxContent).removeClass('d-none');
                    } else {
                        $list.html('').addClass('d-none');
                    }
                }
            }
        },

        addToWishlist: function(data) {
            var productId = parseInt(data[this.options.input.id]);
            var childId = parseInt(data[this.options.input.childId]);
            var qty =  parseInt(data[this.options.input.quantity]);
            if (childId > 0) {
                productId = childId;
            }
            if (productId > 0) {
                var that = this;
                $.evo.io().call('pushToWishlist', [productId, qty], that, function(error, data) {
                    if (error) {
                        return;
                    }

                    var response = data.response;

                    if (response) {
                        switch (response.nType) {
                            case 0: // error
                                var errorlist = '<ul><li>' + response.cHints.join('</li><li>') + '</li></ul>';
                                eModal.alert({
                                    title: response.cTitle,
                                    message: errorlist,
                                    keyboard: true,
                                    tabindex: -1,
                                    buttons: false
                                });
                                break;
                            case 1: // forwarding
                                window.location.href = response.cLocation;
                                break;
                            case 2: // added to wishlist
                                that.updateWishlist(response);
                                break;
                        }
                    }
                });

                return true;
            }

            return false;
        },

        removeFromWishList: function(data) {
            var productId = parseInt(data[this.options.input.id]);
            if (productId > 0) {
                var that = this;
                $.evo.io().call('removeFromWishlist', [productId], that, function(error, data) {
                    if (error) {
                        return;
                    }

                    var response = data.response;

                    if (response) {
                        switch (response.nType) {
                            case 0: // error
                                var errorlist = '<ul><li>' + response.cHints.join('</li><li>') + '</li></ul>';
                                eModal.alert({
                                    title: response.cTitle,
                                    message: errorlist,
                                    keyboard: true,
                                    tabindex: -1,
                                    buttons: false
                                });
                                break;
                            case 1: // forwarding
                                window.location.href = response.cLocation;
                                break;
                            case 2: // removed from wishlist
                                that.updateWishlist(response);
                                break;
                        }
                    }
                });

                return true;
            }

            return false;
        },

        updateWishlist: function(data) {
            var $navContainerWish = $(this.options.selector.navContainerWish);
            var $navBadgeWish = $(this.options.selector.navBadgeWish);

            if (data.wlPosRemove) {
                let $action = $('button[data-wl-pos="' + data.wlPosRemove + '"]');
                $action.removeClass("on-list");
                $action.next().removeClass("press");
                $action.find('.wishlist-icon').addClass('far').removeClass('fas');
            }
            if (data.wlPosAdd) {
                let $action = $('button[data-product-id-wl="' + data.productID + '"]');
                $action.attr('data-wl-pos', data.wlPosAdd);
                $action.data('wl-pos', data.wlPosAdd);
                $action.closest('form').find('input[name="wlPos"]').val(data.wlPosAdd)
                $action.find('.wishlist-icon').addClass('fas').removeClass('far');
            }
            $.evo.io().call('updateWishlistDropdown', [$navContainerWish, $navBadgeWish], this, function(error, data) {
                if (error) {
                    return;
                }
                if (data.response.currentPosCount > 0) {
                    $navBadgeWish.removeClass('d-none');
                } else {
                    $navBadgeWish.addClass('d-none');
                }
                $navContainerWish.html(data.response.content);
                $navBadgeWish.html(data.response.currentPosCount);
            });

            for (var ind in data.cBoxContainer) {
                var $list = $(this.options.selector.boxContainerWish+ind);
                if ($list.length > 0) {
                    if (data.cBoxContainer[ind].length) {
                        var $boxContent = $(data.cBoxContainer[ind]);
                        this.registerProductActions($boxContent);
                        $list.replaceWith($boxContent).removeClass('d-none');
                    } else {
                        $list.html('').addClass('d-none');
                    }
                }
            }
        },

        handleProductAction: function(action, data) {
            let $action = $(action);
            switch (action.name) {
                case this.options.action.compareList:
                    if ($action.hasClass('action-tip-animation-b')) {
                        if ($action.hasClass('on-list')) {
                            $action.removeClass("on-list");
                            $action.next().removeClass("press");
                            $action.next().next().addClass("press");
                            return this.removeFromCompareList(data);
                        } else {
                            $action.addClass("on-list");
                            $action.next().addClass("press");
                            $action.next().next().removeClass("press");
                            return this.addToComparelist(data);
                        }
                    } else {
                        return this.addToComparelist(data);
                    }
                case this.options.action.compareListRemove:
                    return this.removeFromCompareList(data);
                case this.options.action.wishList:
                    data[this.options.input.quantity] = $('#buy_form_'+data.a+' '+this.options.selector.quantity).val();
                    if ($action.hasClass('action-tip-animation-b')) {
                        if ($action.hasClass('on-list')) {
                            $action.removeClass("on-list");
                            $action.next().removeClass("press");
                            $action.next().next().addClass("press");
                            data.a = data.wlPos;
                            return this.removeFromWishList(data);
                        } else {
                            $action.addClass("on-list");
                            $action.next().addClass("press");
                            $action.next().next().removeClass("press");
                            return this.addToWishlist(data);
                        }
                    } else {
                        return this.addToWishlist(data);
                    }
                case this.options.action.wishListRemove:
                    return this.removeFromWishList(data);
            }

            return false;
        },

        configurator: function(init) {
            if (this.isSingleArticle()) {
                var that      = this,
                    container = $('#cfg-container'),
                    sidebar   = $('#cfg-sticky-sidebar'),
                    width,
                    form;

                if (container.length === 0) {
                    return;
                }

                if (init) {

                }

                $('#buy_form').find('*[data-selected="true"]')
                    .attr('checked', true)
                    .attr('selected', true)
                    .attr('data-selected', null);

                form = $.evo.io().getFormValues('buy_form');

                $.evo.io().call('buildConfiguration', [form], that, function (error, data) {
                    var result,
                        i,
                        j,
                        item,
                        cBeschreibung,
                        quantityWrapper,
                        grp,
                        value,
                        enableQuantity,
                        nNetto,
                        quantityInput;
                    if (error) {
                        $.evo.error(data);
                        return;
                    }
                    result = data.response;

                    if (!result.oKonfig_arr) {
                        $.evo.error('Missing configuration groups');
                        return;
                    }

                    // global price
                    nNetto = result.nNettoPreise;
                    that.setPrice(result.fGesamtpreis[nNetto], result.cPreisLocalized[nNetto], result.cPreisString);
                    that.setStockInformation(result.cEstimatedDelivery);

                    $('#content .summary').html(result.cTemplate);

                    $.evo.extended()
                        .trigger('priceChanged', result);
                });
            }
        },

        variationRefreshAll: function($wrapper) {
            $('.variations select', $wrapper).selectpicker('refresh');
        },

        getConfigGroupQuantity: function (groupId) {
            return $('.cfg-group[data-id="' + groupId + '"] .quantity');
        },

        getConfigGroupQuantityInput: function (groupId) {
            return $('.cfg-group[data-id="' + groupId + '"] .quantity input');
        },

        getConfigGroupImage: function (groupId) {
            return $('.cfg-group[data-id="' + groupId + '"] .group-image img');
        },

        setConfigItemImage: function (groupId, img) {
            $('.cfg-group[data-id="' + groupId + '"] .group-image img').attr('src', img).first();
        },

        setConfigItemDescription: function (groupId, itemBeschreibung) {
            var groupItems                       = $('.cfg-group[data-id="' + groupId + '"] .group-items');
            var descriptionDropdownContent       = groupItems.find('#filter-collapsible_dropdown_' + groupId + '');
            var descriptionDropdownContentHidden = groupItems.find('.d-none');
            var descriptionCheckdioContent       = groupItems.find('div[id^="filter-collapsible_checkdio"]');
            var multiselect                      = groupItems.find('select').attr("multiple");

            //  Bisher kein Content mit einer Beschreibung vorhanden, aber ein Artikel mit Beschreibung ausgewählt
            if (descriptionDropdownContentHidden.length > 0 && descriptionCheckdioContent.length === 0 && itemBeschreibung.length > 0 && multiselect !== "multiple") {
                groupItems.find('a[href="#filter-collapsible_dropdown_' + groupId + '"]').removeClass('d-none');
                descriptionDropdownContent.replaceWith('<div id="filter-collapsible_dropdown_' + groupId + '" class="collapse top10 panel-body">' + itemBeschreibung + '</div>');
                //  Bisher Content mit einer Beschreibung vorhanden, aber ein Artikel ohne Beschreibung ausgewählt
            } else if (descriptionDropdownContentHidden.length === 0 && descriptionCheckdioContent.length === 0 && itemBeschreibung.length === 0 && multiselect !== "multiple") {
                groupItems.find('a[href="#filter-collapsible_dropdown_' + groupId + '"]').addClass('d-none');
                descriptionDropdownContent.addClass('d-none');
                //  Bisher Content mit einer Beschreibung vorhanden und ein Artikel mit Beschreibung ausgewählt
            } else if (descriptionDropdownContentHidden.length === 0 && descriptionCheckdioContent.length === 0 && itemBeschreibung.length > 0 && multiselect !== "multiple") {
                descriptionDropdownContent.replaceWith('<div id="filter-collapsible_dropdown_' + groupId + '" class="collapse top10 panel-body">' + itemBeschreibung + '</div>');
            }
        },

        setPrice: function(price, fmtPrice, priceLabel, wrapper) {
            var $wrapper = this.getWrapper(wrapper);

            if (this.isSingleArticle()) {
                $('#product-offer .price', $wrapper).html(fmtPrice);
                if (priceLabel.length > 0) {
                    $('#product-offer .price_label', $wrapper).html(priceLabel);
                }
            } else {
                var $price = $('.price_wrapper', $wrapper);

                $('.price span:first-child', $price).html(fmtPrice);
                if (priceLabel.length > 0) {
                    $('.price_label', $price).html(priceLabel);
                }
            }

            $.evo.trigger('changed.article.price', { price: price });
        },

        setStockInformation: function(cEstimatedDelivery, wrapper) {
            var $wrapper = this.getWrapper(wrapper);

            $('.delivery-status .estimated-delivery span', $wrapper).html(cEstimatedDelivery);
        },

        setStaffelPrice: function(prices, fmtPrices, wrapper) {
            var $wrapper   = this.getWrapper(wrapper),
                $container = $('#product-offer', $wrapper);

            $.each(fmtPrices, function(index, value){
                $('.bulk-price-' + index + ' .bulk-price', $container).html(value);
            });
        },

        setVPEPrice: function(fmtVPEPrice, VPEPrices, fmtVPEPrices, wrapper) {
            var $wrapper   = this.getWrapper(wrapper),
                $container = $('#product-offer', $wrapper);

            $('.base-price .value', $container).html(fmtVPEPrice);
            $.each(fmtVPEPrices, function(index, value){
                $('.bulk-price-' + index + ' .bulk-base-price', $container).html(value);
            });
        },

        /**
         * @deprecated since 4.05 - use setArticleWeight instead
         */
        setUnitWeight: function(UnitWeight, newUnitWeight) {
            $('#article-tabs .product-attributes .weight-unit').html(newUnitWeight);
        },

        setArticleWeight: function(ArticleWeight, wrapper) {
            if (this.isSingleArticle()) {
                var $articleTabs = $('#article-tabs');

                if ($.isArray(ArticleWeight)) {
                    $('.product-attributes .weight-unit', $articleTabs).html(ArticleWeight[0][1]);
                    $('.product-attributes .weight-unit-article', $articleTabs).html(ArticleWeight[1][1]);
                } else {
                    $('.product-attributes .weight-unit', $articleTabs).html(ArticleWeight);
                }
            } else {
                var $wrapper = this.getWrapper(wrapper);

                if ($.isArray(ArticleWeight)) {
                    $('.attr-weight .value', $wrapper).html(ArticleWeight[0][1]);
                    $('.attr-weight.weight-unit-article .value', $wrapper).html(ArticleWeight[1][1]);
                } else {
                    $('.attr-weight .value', $wrapper).html(ArticleWeight);
                }
            }

        },

        setProductNumber: function(productNumber, wrapper) {
            var $wrapper = this.getWrapper(wrapper);

            $('#product-offer span[itemprop="sku"]', $wrapper).html(productNumber);
        },

        setArticleContent: function(id, variation, url, variations, wrapper) {
            var $wrapper  = this.getWrapper(wrapper),
                listStyle = $('#ed_list.active').length > 0 ? 'list' : 'gallery',
                $spinner  = $.evo.extended().spinner($wrapper.get(0));

            if (this.modalShown) {
                this.loadModalArticle(url, wrapper,
                    function() {
                        var article = new ArticleClass();
                        article.register(wrapper);
                        $spinner.stop();
                    },
                    function() {
                        $spinner.stop();
                        $.evo.error('Error loading ' + url);
                    }
                );
            } else if (this.isSingleArticle()) {
                $.evo.extended().loadContent(url, function (content) {
                    $.evo.extended().register();
                    $.evo.article().register(wrapper);

                    $(variations).each(function (i, item) {
                        $.evo.article().variationSetVal(item.key, item.value, wrapper);
                    });

                    if (document.location.href !== url) {
                        history.pushState({a: id, a2: variation, url: url, variations: variations}, "", url);
                    }

                    $spinner.stop();

                    window.initNumberInput();
                }, function () {
                    $.evo.error('Error loading ' + url);
                    $spinner.stop();
                }, false, wrapper);
            } else {
                $.evo.extended().loadContent(url + (url.indexOf('?') >= 0 ? '&' : '?') + 'isListStyle=' + listStyle, function (content) {
                    $.evo.article().register(wrapper);

                    $('[data-toggle="basket-add"]', $(wrapper)).on('submit', function(event) {
                        event.preventDefault();
                        event.stopPropagation();

                        var $form = $(this);
                        var data  = $form.serializeObject();
                        data['a'] = variation;

                        $.evo.basket().addToBasket($form, data);
                    });

                    $(variations).each(function (i, item) {
                        $.evo.article().variationSetVal(item.key, item.value, wrapper);
                    });

                    if (!$wrapper.hasClass('productbox-hover')) {
                        $.evo.extended().autoheight();
                    }
                    $spinner.stop();

                    window.initNumberInput();

                    $(wrapper + ' .list-gallery:not(.slick-initialized)').slick({
                        lazyLoad: 'ondemand',
                        infinite: false,
                        dots:     false,
                        arrows:   true
                    });
                }, function () {
                    $.evo.error('Error loading ' + url);
                    $spinner.stop();
                }, false, wrapper);
            }
        },

        variationResetAll: function(wrapper) {
            var $wrapper = this.getWrapper(wrapper);

            $('.variation[data-value] input:checked', $wrapper).prop('checked', false);
            $('.variations select option', $wrapper).prop('selected', false);
            $('.variations select', $wrapper).selectpicker('refresh');
        },

        variationDisableAll: function(wrapper) {
            var $wrapper = this.getWrapper(wrapper);

            $('.swatches-selected', $wrapper).text('');
            $('[data-value].variation', $wrapper).each(function(i, item) {
                $(item)
                    .removeClass('active')
                    .removeClass('loading')
                    .addClass('not-available');
                $.evo.article()
                    .removeStockInfo($(item));
            });
        },

        variationSetVal: function(key, value, wrapper) {
            var $wrapper = this.getWrapper(wrapper);

            $('[data-key="' + key + '"]', $wrapper).val(value);
        },

        variationEnable: function(key, value, wrapper) {
            var $wrapper = this.getWrapper(wrapper),
                $item    = $('[data-value="' + value + '"].variation', $wrapper);

            $item.removeClass('not-available');
        },

        variationActive: function(key, value, def, wrapper) {
            var $wrapper = this.getWrapper(wrapper),
                $item    = $('[data-value="' + value + '"].variation', $wrapper);
            $item.addClass('active')
                .removeClass('loading')
                .find('input')
                .prop('checked', true)
                .end()
                .prop('selected', true);

            $('[data-id="'+key+'"].swatches-selected')
                .text($item.attr('data-original'));
        },

        removeStockInfo: function($item) {
            if (this.isSingleArticle()) {
                var type = $item.attr('data-type'),
                    elem,
                    label,
                    wrapper;

                switch (type) {
                    case 'option':
                        label = $item.data('content');
                        wrapper = $('<div />').append(label);
                        $(wrapper)
                            .find('.badge-not-available')
                            .remove();
                        label = $(wrapper).html();
                        $item.data('content', label)
                            .attr('data-content', label);

                        break;
                    case 'radio':
                        elem = $item.find('.badge-not-available');
                        if (elem.length === 1) {
                            $(elem).remove();
                        }
                        break;
                    case 'swatch':
                        $item.tooltip('dispose');
                        break;
                }

                $item.removeAttr('data-stock');
            }
        },

        variationInfo: function(value, status, note) {
            var $item = $('[data-value="' + value + '"].variation'),
                type = $item.attr('data-type'),
                text,
                content,
                $wrapper,
                label;

            $item.attr('data-stock', _stock_info[status]);

            switch (type) {
                case 'option':
                    text     = ' (' + note + ')';
                    content  = $item.data('content');
                    $wrapper = $('<div />');

                    $wrapper.append(content);
                    $wrapper
                        .find('.badge-not-available')
                        .remove();

                    label = $('<span />')
                        .addClass('badge badge-danger badge-not-available')
                        .text(' '+note);

                    $wrapper.append(label);

                    $item.data('content', $wrapper.html())
                        .attr('data-content', $wrapper.html());

                    $item.closest('select')
                        .selectpicker('refresh');
                    break;
                case 'radio':
                    $item.find('.badge-not-available')
                        .remove();

                    label = $('<span />')
                        .addClass('badge badge-danger badge-not-available')
                        .text(' '+note);

                    $item.append(label);
                    break;
                case 'swatch':
                    $item.tooltip({
                        title: note,
                        trigger: 'hover',
                        container: 'body'
                    });
                    break;
            }
        },

        variationSwitch: function($item, animation, wrapper) {
            if ($item) {
                var formID   = $item.closest('form').attr('id'),
                    $current = this.getCurrent($item),
                    key      = $current.data('key'),
                    value    = $current.data('value'),
                    io       = $.evo.io(),
                    args     = io.getFormValues(formID),
                    $spinner = null,
                    $wrapper = this.getWrapper(wrapper);

                if (animation) {
                    $wrapper.addClass('loading');
                    $spinner = $.evo.extended().spinner();
                } else {
                    $('.updatingStockInfo', $wrapper).show();
                }

                $current.addClass('loading');
                args.wrapper = wrapper;

                $.evo.article()
                    .variationDispose(wrapper);

                io.call('checkVarkombiDependencies', [args, key, value], $item, function (error, data) {
                    $wrapper.removeClass('loading');
                    if (animation) {
                        $spinner.stop();
                    }
                    $('.updatingStockInfo', $wrapper).hide();
                    if (error) {
                        $.evo.error('checkVarkombiDependencies');
                    }
                });
            }
        },

        variationPrice: function($item, animation, wrapper) {
            var formID   = $item.closest('form').attr('id'),
                $wrapper = this.getWrapper(wrapper),
                io       = $.evo.io(),
                args     = io.getFormValues(formID),
                $spinner = null;

            if (animation) {
                $wrapper.addClass('loading');
                $spinner = $.evo.extended().spinner();
            }

            args.wrapper = wrapper;
            io.call('checkDependencies', [args], null, function (error, data) {
                $wrapper.removeClass('loading');
                if (animation) {
                    $spinner.stop();
                }
                if (error) {
                    $.evo.error('checkDependencies');
                }
            });
        },

        variationDispose: function(wrapper) {
            var $wrapper = this.getWrapper(wrapper);

            $('[role="tooltip"]', $wrapper).remove();
        }
    };

    $v     = new ArticleClass();
    var ie = /(msie|trident)/i.test(navigator.userAgent) ? navigator.userAgent.match(/(msie |rv:)(\d+(.\d+)?)/i)[2] : false;
    if (ie && parseInt(ie) <= 9) {
        $(document).ready(function () {
            $v.onLoad();
            $v.register();
        });
    } else {
        $(window).on('load', function () {
            $v.onLoad();
            $v.register();
        });
    }

    $(window).on('resize',
        viewport.changed(function(){
            $v.configurator();
        })
    );

    // PLUGIN DEFINITION
    // =================
    $.evo.article = function () {
       return $v;
    };
})(jQuery, document, window, ResponsiveBootstrapToolkit);