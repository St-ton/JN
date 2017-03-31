/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

(function($, document, window, viewport){
    'use strict';

    var _stock_info = ['out-of-stock', 'in-short-supply', 'in-stock'],
        $v,
        ArticleClass = function() {
            this.init();
        };

    /*******************************************************************************************************************
     * ArticleClass - Base class for article handling, used on article details
     */
    ArticleClass.DEFAULTS = {
        input: {
            id: 'a'
        },
        action: {
            compareList: 'Vergleichsliste',
            compareListRemove: 'Vergleichsliste.remove'
        },
        selector: {
            navBadgeUpdate: '#shop-nav li.compare-list-menu',
            navBadgeAppend: '#shop-nav li.cart-menu',
            boxContainer: 'section.box-compare'
        }
    };

    ArticleClass.prototype = {
        constructor: ArticleClass,

        init: function() {
            this.options = ArticleClass.DEFAULTS;
        },
        
        onLoad: function() {
            if ($('#buy_form').length > 0) {
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

        register: function() {
            var $wrapper = $('#result-wrapper');

            this.registerGallery();
            this.registerConfig();
            this.registerSimpleVariations($wrapper);
            this.registerSwitchVariations($wrapper);
            this.registerImageSwitch($wrapper);
            this.registerFinish();
        },

        registerGallery: function() {
            var $gallery = $('#gallery');

            if ($gallery.length === 1) {
                this.gallery          = $gallery.gallery();
                this.galleryIndex     = 0;
                this.galleryLastIdent = '_';
            } else {
                this.gallery = null;
            }
        },

        registerConfig: function() {
            var that   = this,
                config = $('.product-configuration')
                    .closest('form')
                    .find('input[type="radio"], input[type="checkbox"], input[type="number"], select');

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
                /*mobile: true*/
            });

            $('.simple-variations input[type="radio"]', $wrapper)
                .on('change', function() {
                    var val = $(this).val(),
                        key = $(this).parent().data('key');
                    $('.simple-variations [data-key="' + key + '"]').removeClass('active');
                    $('.simple-variations [data-value="' + val + '"]').addClass('active');
                });

            $('.simple-variations input[type="radio"], .simple-variations select', $wrapper)
                .on('change', function () {
                    that.variationPrice(this, true);
                });
        },

        registerSwitchVariations: function($wrapper) {
            var that = this;

            $('.switch-variations input[type="radio"], .switch-variations select', $wrapper)
                .on('change', function () {
                    that.variationSwitch(this, true);
                });

            if ("ontouchstart" in document.documentElement) {
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

        registerImageSwitch: function($wrapper) {
            var imgSwitch = function(context, temporary, force) {
                var $context = $(context),
                    id       = $context.attr('data-key'),
                    value    = $context.attr('data-value'),
                    data     = $context.data('list'),
                    title    = $context.attr('data-title'),
                    gallery  = $.evo.article().gallery;

                if (typeof temporary === 'undefined') {
                    temporary = true;
                }

                if (gallery !== null && !$(context).hasClass('active') || force) {
                    if (!!data) {
                        gallery.setItems([data], value);

                        if (!temporary) {
                            var items  = [data];
                            var stacks = gallery.getStacks();
                            for (var s in stacks) {
                                if (stacks.hasOwnProperty(s) && s.match(/^_[0-9a-zA-Z]*$/) && s !== '_' + id) {
                                    items = $.merge(items, stacks[s]);
                                }
                            }

                            gallery.setItems([data], '_' + id);
                            gallery.setItems(items, '__');
                            gallery.render('__');

                            $.evo.article().galleryIndex = gallery.index;
                            $.evo.article().galleryLastIdent = gallery.ident;
                        } else {
                            gallery.render(value);
                        }
                    }
                }
            };

            $('.variations .bootstrap-select select', $wrapper)
                .change(function() {
                    var tmp_idx = parseInt($('.variations .bootstrap-select li.selected').attr('data-original-index')) + 1;
                    var sel     = $(this).find('option:nth-child(' + tmp_idx + ')');
                    var cont    = $(this).closest('.variations');
                    if (cont.hasClass('simple-variations')) {
                        imgSwitch(sel, false, false);
                    } else {
                        imgSwitch(sel, true, false);
                    }
                });

            var touchCapable = 'ontouchstart' in window || (window.DocumentTouch && document instanceof window.DocumentTouch);
            if (!touchCapable || ResponsiveBootstrapToolkit.current() !== 'xs') {
                $('.variations .bootstrap-select .dropdown-menu li', $wrapper)
                    .hover(function () {
                        var tmp_idx = parseInt($(this).attr('data-original-index')) + 1,
                            sel     = $(this).closest('.bootstrap-select').find('select option:nth-child(' + tmp_idx + ')');
                        imgSwitch(sel);
                    }, function () {
                        var tmp_idx = parseInt($(this).attr('data-original-index')) + 1,
                            p       = $(this).closest('.bootstrap-select').find('select option:nth-child(' + tmp_idx + ')'),
                            id      = $(p).attr('data-key'),
                            data    = $(p).data('list'),
                            gallery = $.evo.article().gallery,
                            active;

                        if (!!data && gallery !== null) {
                            active = $(p).find('.variation.active');
                            gallery.render($.evo.article().galleryLastIdent);
                            gallery.activate($.evo.article().galleryIndex);
                        }
                    });
            }

            $('.variations.simple-variations .variation', $wrapper)
                .click(function() {
                    imgSwitch(this, false);
                });

            if (!touchCapable || ResponsiveBootstrapToolkit.current() !== 'xs') {
                $('.variations .variation', $wrapper).hover(function () {
                    imgSwitch(this);
                }, function () {
                    var p = $(this).closest('.variation'),
                        data    = $(this).data('list'),
                        gallery = $.evo.article().gallery,
                        active  = $(p).find('.variation.active');

                    if (!!data && gallery !== null) {
                        gallery.render($.evo.article().galleryLastIdent);
                        gallery.activate($.evo.article().galleryIndex);
                    }
                });
            }
        },

        registerFinish: function() {
            $('#jump-to-votes-tab').click(function () {
                $('#content a[href="#tab-votes"]').tab('show');
            });

            if ($('.switch-variations').length === 1) {
                this.variationSwitch();
            }

            this.registerProductActions();
        },

        registerProductActions: function($container) {
            if (typeof $container === 'undefined') {
                $container = $('body');
            }

            $('*[data-toggle="product-actions"] button', $container).on('click', function(event) {
                var data = $(this.form).serializeObject();

                if ($.evo.article().handleProductAction(this, data)) {
                    event.preventDefault();
                }
            });
            $('a[data-toggle="product-actions"]', $container).on('click', function(event) {
                var data  = $(this).data('value');
                this.name = $(this).data('name');

                if ($.evo.article().handleProductAction(this, data)) {
                    event.preventDefault();
                }
            });
        },

        addToComparelist: function(data) {
            var productId = parseInt(data[this.options.input.id]);
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
                                    message: errorlist
                                });
                                break;
                            case 1: // forwarding
                                window.location.href = response.cLocation;
                                break;
                            case 2: // added to comparelist
                                that.updateComparelist(response);
                                eModal.alert({
                                    title: response.cTitle,
                                    message: response.cNotification
                                });
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
                                    message: errorlist
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

        configurator: function(init) {
            var that      = this,
                container = $('#cfg-container'),
                sidebar   = $('#product-configuration-sidebar'),
                width,
                form;

            if (container.length === 0) {
                return;
            }

            if (viewport.current() !== 'lg') {
                sidebar.removeClass('affix');
            }

            if (!sidebar.hasClass('affix')) {
                sidebar.css('width', '');
            }

            sidebar.css('width', sidebar.width());

            if (init) {
                sidebar.affix({
                    offset: {
                        top: function () {
                            var top = container.offset().top - $('#evo-main-nav-wrapper.affix').outerHeight(true);
                            if (viewport.current() !== 'lg') {
                                top = 999999;
                            }
                            return top;
                        },
                        bottom: function () {
                            var bottom = $('body').height() - (container.height() + container.offset().top);
                            if (viewport.current() !== 'lg') {
                                bottom = 999999;
                            }
                            return bottom;
                        }
                    }
                });
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

                sidebar.affix('checkPosition');

                // groups
                for (i = 0; i < result.oKonfig_arr.length; i++) {
                    grp = result.oKonfig_arr[i];
                    quantityWrapper = that.getConfigGroupQuantity(grp.kKonfiggruppe);
                    quantityInput = that.getConfigGroupQuantityInput(grp.kKonfiggruppe);
                    if (grp.bAktiv) {
                        enableQuantity = grp.bAnzahl;
                        for (j = 0; j < grp.oItem_arr.length; j++) {
                            item = grp.oItem_arr[j];
                            if (item.bAktiv) {
                                if (item.cBildPfad) {
                                    that.setConfigItemImage(grp.kKonfiggruppe, item.cBildPfad.cPfadKlein);
                                } else {
                                    that.setConfigItemImage(grp.kKonfiggruppe, grp.cBildPfad);
                                }
                                if (item.kArtikel > 0) {
                                    if (item.hasOwnProperty('length') && item.cKurzBeschreibung.length > 0) {
                                        cBeschreibung = item.cKurzBeschreibung;
                                    } else {
                                        cBeschreibung = "";
                                    }
                                } else {
                                    cBeschreibung = item.cBeschreibung;
                                }
                                that.setConfigItemDescription(grp.kKonfiggruppe, cBeschreibung);
                                enableQuantity = item.bAnzahl;
                                if (!enableQuantity) {
                                    quantityInput
                                        .attr('min', item.fInitial)
                                        .attr('max', item.fInitial)
                                        .val(item.fInitial)
                                        .attr('disabled', true);
                                    if (item.fInitial == 1) {
                                        quantityWrapper.slideUp(200);
                                    } else {
                                        quantityWrapper.slideDown(200);
                                    }
                                } else {
                                    if (quantityWrapper.css('display') === 'none' && !init) {
                                        quantityInput.val(item.fInitial);
                                    }
                                    quantityWrapper.slideDown(200);
                                    quantityInput
                                        .attr('disabled', false)
                                        .attr('min', item.fMin)
                                        .attr('max', item.fMax);
                                    value = quantityInput.val();
                                    if (value < item.fMin || value > item.fMax) {
                                        quantityInput.val(item.fInitial);
                                    }
                                }
                            }
                        }
                    }
                    else {
                        that.setConfigItemDescription(grp.kKonfiggruppe, '');
                        quantityInput.attr('disabled', true);
                        quantityWrapper.slideUp(200);
                    }
                }
            });
        },

        getConfigGroupQuantity: function(groupId) {
            return $('.cfg-group[data-id="' + groupId + '"] .quantity');
        },

        getConfigGroupQuantityInput: function(groupId) {
            return $('.cfg-group[data-id="' + groupId + '"] .quantity input');
        },

        getConfigGroupImage: function(groupId) {
            return $('.cfg-group[data-id="' + groupId + '"] .group-image img');
        },

        getCurrent: function(item) {
            var $current = $(item).hasClass('variation') ? $(item) : $(item).closest('.variation');
            if ($current.context.tagName === 'SELECT') {
                $current = $(item).find('option:selected');
            }

            return $current;
        },

        handleProductAction: function(action, data) {
            switch (action.name) {
                case this.options.action.compareList:
                    return this.addToComparelist(data);
                case this.options.action.compareListRemove:
                    return this.removeFromCompareList(data);
            }

            return false;
        },

        setConfigItemImage: function(groupId, img) {
            $('.cfg-group[data-id="' + groupId + '"] .group-image img').attr('src', img).first();
        },

        setConfigItemDescription: function (groupId, itemBeschreibung) {
            var groupItems                       = $('.cfg-group[data-id="' + groupId + '"] .group-items');
            var descriptionDropdownContent       = groupItems.find('#filter-collapsible_dropdown_' + groupId + '');
            var descriptionDropdownContentHidden = groupItems.find('.hidden');
            var descriptionCheckdioContent       = groupItems.find('div[id^="filter-collapsible_checkdio"]');
            var multiselect                      = groupItems.find('select').attr("multiple");

            //  Bisher kein Content mit einer Beschreibung vorhanden, aber ein Artikel mit Beschreibung ausgewählt
            if (descriptionDropdownContentHidden.length > 0 && descriptionCheckdioContent.length === 0 && itemBeschreibung.length > 0 && multiselect !== "multiple") {
                groupItems.find('a[href="#filter-collapsible_dropdown_' + groupId + '"]').removeClass('hidden');
                descriptionDropdownContent.replaceWith('<div id="filter-collapsible_dropdown_' + groupId + '" class="collapse top10 panel-body">' + itemBeschreibung + '</div>');
            //  Bisher Content mit einer Beschreibung vorhanden, aber ein Artikel ohne Beschreibung ausgewählt
            } else if (descriptionDropdownContentHidden.length === 0 && descriptionCheckdioContent.length === 0 && itemBeschreibung.length === 0 && multiselect !== "multiple") {
                groupItems.find('a[href="#filter-collapsible_dropdown_' + groupId + '"]').addClass('hidden');
                descriptionDropdownContent.addClass('hidden');
            //  Bisher Content mit einer Beschreibung vorhanden und ein Artikel mit Beschreibung ausgewählt
            } else if (descriptionDropdownContentHidden.length === 0 && descriptionCheckdioContent.length === 0 && itemBeschreibung.length > 0 && multiselect !== "multiple") {
                descriptionDropdownContent.replaceWith('<div id="filter-collapsible_dropdown_' + groupId + '" class="collapse top10 panel-body">' + itemBeschreibung + '</div>');
            }
        },
        
        setPrice: function(price, fmtPrice, priceLabel, wrapper) {
            var $productOffer = $('#product-offer');

            $('.price', $productOffer).html(fmtPrice);
            if (priceLabel.length > 0) {
                $('.price_label', $productOffer).html(priceLabel);
            }
        },

        setStockInformation: function(cEstimatedDelivery, wrapper) {
            $('.delivery-status .estimated-delivery span').html(cEstimatedDelivery);
        },

        setStaffelPrice: function(prices, fmtPrices, wrapper) {
            var $container = $('#product-offer');
            $.each(fmtPrices, function(index, value){
                $('.bulk-price-' + index + ' .bulk-price', $container).html(value);
            });
        },

        setVPEPrice: function(fmtVPEPrice, VPEPrices, fmtVPEPrices, wrapper) {
            var $container = $('#product-offer');
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
            var $articleTabs = $('#article-tabs');

            if ($.isArray(ArticleWeight)) {
                $('.product-attributes .weight-unit', $articleTabs).html(ArticleWeight[0][1]);
                $('.product-attributes .weight-unit-article', $articleTabs).html(ArticleWeight[1][1]);
            } else {
                $('.product-attributes .weight-unit', $articleTabs).html(ArticleWeight);
            }
        },

        setProductNumber: function(productNumber, wrapper){
            $('#product-offer span[itemprop="sku"]').html(productNumber);
        },

        setArticleContent: function(id, variation, url, variations) {
            var $spinner = $.evo.extended().spinner();
            $.evo.extended().loadContent(url, function(content) {
                $.evo.extended().register();
                $.evo.article().register();
                
                $(variations).each(function (i, item) {
                   $.evo.article().variationSetVal(item.key, item.value);
                });
                
                if (document.location.href !== url) {
                    history.pushState({ a: id, a2: variation, url: url, variations: variations }, "", url);
                }
                $spinner.stop();
            }, function() {
                $.evo.error('Error loading ' + url);
            });
        },

        updateComparelist: function(response) {
            var $badgeUpd = $(this.options.selector.navBadgeUpdate);
            if (response.nCount > 1 && response.cNavBadge.length) {
                var badge = $(response.cNavBadge);
                if ($badgeUpd.size() > 0) {
                    $badgeUpd.replaceWith(badge);
                } else {
                    $(this.options.selector.navBadgeAppend).before(badge);
                }

                badge.on('click', '.popup', function (e) {
                    var url = e.currentTarget.href;
                    url += (url.indexOf('?') === -1) ? '?isAjax=true' : '&isAjax=true';
                    eModal.ajax({
                        'size': 'lg',
                        'url': url
                    });
                    e.stopPropagation();
                    return false;
                });
            } else if ($badgeUpd.size() > 0) {
                $badgeUpd.remove();
            }

            var $list = $(this.options.selector.boxContainer);
            if ($list.size() > 0) {
                if (response.cBoxContainer.length) {
                    var $boxContent = $(response.cBoxContainer);
                    this.registerProductActions($boxContent);
                    $list.replaceWith($boxContent).removeClass('hidden');
                } else {
                    $list.html('').addClass('hidden');
                }
            }
        },

        variationResetAll: function($wrapper) {
            $('.variation[data-value] input:checked').prop('checked', false);
            $('.variations select option').prop('selected', false);
            $('.variations select').selectpicker('refresh');
        },

        variationDisableAll: function($wrapper) {
            $('.swatches-selected').text('');
            $('[data-value].variation').each(function(i, item) {
                $(item)
                    .removeClass('active')
                    .removeClass('loading')
                    .addClass('not-available');
                $.evo.article()
                    .removeStockInfo($(item));
            });
        },

        variationSetVal: function(key, value, $wrapper) {
            $('[data-key="' + key + '"]')
                .val(value)
                .closest('select')
                    .selectpicker('refresh');
        },

        variationEnable: function(key, value, $wrapper) {
            this.variationEnableItem($('[data-value="' + value + '"].variation'));
        },

        variationEnableItem: function(item) {
            item.removeClass('not-available');
            item.closest('select')
                .selectpicker('refresh');
        },

        variationActive: function(key, value, def, $wrapper) {
            this.variationActiveItem($('[data-value="' + value + '"].variation'), key);
        },

        variationActiveItem: function(item, key) {
            item.addClass('active')
                .removeClass('loading')
                .find('input')
                .prop('checked', true)
                .end()
                .prop('selected', true);
                
            item.closest('select')
                .selectpicker('refresh');

            $('[data-id="'+key+'"].swatches-selected')
                .text($(item).attr('data-original'));
        },
        
        removeStockInfo: function(item) {
            var type = item.attr('data-type');
            
            switch (type) {
                case 'option':
                    var label   = item.data('content'),
                        wrapper = $('<div />').append(label);
                    $(wrapper)
                        .find('.label-not-available')
                        .remove();
                    label = $(wrapper).html();
                    item.data('content', label)
                        .attr('data-content', label);
                    
                    item.closest('select')
                        .selectpicker('refresh');
                break;
                case 'radio':
                    var elem = item.find('.label-not-available');
                    if (elem.length === 1) {
                        $(elem).remove();
                    }
                break;
                case 'swatch':
                    item.tooltip('destroy');
                break;
            }

            item.removeAttr('data-stock');
        },

        variationInfo: function(value, status, note) {
            var item = $('[data-value="' + value + '"].variation'),
                type = item.attr('data-type'),
                label;
            
            item.attr('data-stock', _stock_info[status]);

            switch (type) {
                case 'option':
                    var content = item.data('content'),
                        wrapper = $('<div />');
                    
                    wrapper.append(content);
                    wrapper
                        .find('.label-not-available')
                        .remove();

                    label = $('<span />')
                        .addClass('label label-default label-not-available')
                        .text(note);
                        
                    wrapper.append(label);

                    item.data('content', $(wrapper).html())
                        .attr('data-content', $(wrapper).html());
                    
                    item.closest('select')
                        .selectpicker('refresh');
                break;
                case 'radio':
                    item.find('.label-not-available')
                        .remove();

                    label = $('<span />')
                        .addClass('label label-default label-not-available')
                        .text(note);
                    
                    item.append(label);
                break;
                case 'swatch':
                    item.tooltip({
                        title: note,
                        trigger: 'hover',
                        container: 'body'
                    });
                break;
            }
        },

        variationSwitch: function(item, animation) {
            var key      = 0,
                value    = 0,
                io       = $.evo.io(),
                args     = io.getFormValues('buy_form'),
                $current,
                $spinner = null,
                $wrapper = $('#result-wrapper');
            
            if (animation) {
                $wrapper.addClass('loading');
                $spinner = $.evo.extended().spinner();
            }

            if (item) {
                $current = this.getCurrent(item);

                if ($current.context.tagName === 'SELECT') {
                    $current = $(item).find('option:selected');
                }

                $current.addClass('loading');

                key   = $current.data('key');
                value = $current.data('value');
            }

            $.evo.article()
                .variationDispose();

            io.call('checkVarkombiDependencies', [args, key, value], item, function(error, data) {
                $wrapper.removeClass('loading');
                if (animation) {
                    $spinner.stop();
                }
                if (error) {
                    $.evo.error('checkVarkombiDependencies');
                }
            });
        },

        variationDispose: function() {
            $('[role="tooltip"]').remove();
        },
        
        variationPrice: function(item, animation) {
            var io       = $.evo.io(),
                args     = io.getFormValues('buy_form'),
                $spinner = null,
                $wrapper = $('#result-wrapper');
            
            if (animation) {
                $wrapper.addClass('loading');
                $spinner = $.evo.extended().spinner();
            }

            io.call('checkDependencies', [args], null, function(error, data) {
                $wrapper.removeClass('loading');
                if (animation) {
                    $spinner.stop();
                }
                if (error) {
                    $.evo.error('checkDependencies');
                }
            });
        }
    };

    /*******************************************************************************************************************
     * ArticleListClass - Extende class for article handling, used on article lists
     */
    var ArticleListClass = function() {
        ArticleClass.call(this);
    };

    ArticleListClass.prototype = $.extend(Object.create(ArticleClass.prototype), {
        constructor: ArticleListClass,

        registerGallery: function() {
            this.gallery = null;
        },

        registerConfig: function() {
            // nur im Single-Artikel Context benutzen!!!
        },

        registerImageSwitch: function($wrapper) {
            var imgSwitch = function(context, replace) {
                var value   = $(context).attr('data-value'),
                    data    = $(context).data('list'),
                    title   = $(context).attr('data-title');

                if (!!data) {
                    var $wrapper = $(context).closest('.product-wrapper');
                    var $img     = $('.image-box img', $wrapper);
                    if ($img.length === 1) {
                        $img.attr('src', data.md.src);
                        if (replace) {
                            $img.attr('data-src', data.md.src);
                        }
                    }
                }
            };

            $('.variations .bootstrap-select select', $wrapper)
                .change(function() {
                    var tmp_idx = parseInt($('li.selected', this.parentElement).attr('data-original-index')) + 1;
                    imgSwitch($(this).find('option:nth-child(' + tmp_idx + ')'), true);
                });

            $('.variations .bootstrap-select .dropdown-menu li', $wrapper)
                .hover(function() {
                    var tmp_idx = parseInt($(this).attr('data-original-index')) + 1;
                    var sel     = $(this).closest('.bootstrap-select').find('select option:nth-child(' + tmp_idx + ')');
                    imgSwitch(sel, false);
                }, function() {
                    var tmp_idx = parseInt($(this).attr('data-original-index')) + 1,
                        p       = $(this).closest('.bootstrap-select').find('select option:nth-child(' + tmp_idx + ')'),
                        data    = $(p).data('list');

                    if (!!data) {
                        var $wrapper = $(this).closest('.product-wrapper');
                        var $img     = $('.image-box img', $wrapper);
                        if ($img.length === 1) {
                            $img.attr('src', $img.attr('data-src'));
                        }
                    }
                });

            var $varVariations = $('.variations .variation', $wrapper);
            $varVariations.click(function() {
                imgSwitch(this, true);
            });

            $varVariations.hover(function() {
                imgSwitch(this, false);
            }, function() {
                var data = $(this).data('list');

                if (!!data) {
                    var $wrapper = $(this).closest('.product-wrapper');
                    var $img     = $('.image-box img', $wrapper);
                    if ($img.length === 1) {
                        $img.attr('src', $img.attr('data-src'));
                    }
                }
            });
        },

        setPrice: function(price, fmtPrice, priceLabel, wrapper) {
            var $wrapper = $(wrapper);
            var $price   = $('.price_wrapper', $wrapper);

            $('.price span:first-child', $price).html(fmtPrice);
            if (priceLabel.length > 0) {
                $('.price_label', $price).html(priceLabel);
            }
        },

        setArticleWeight: function(ArticleWeight, wrapper) {
            var $wrapper = $(wrapper);

            if ($.isArray(ArticleWeight)) {
                $('.attr-weight .value', $wrapper).html(ArticleWeight[0][1]);
                $('.attr-weight.weight-unit-article .value', $wrapper).html(ArticleWeight[1][1]);
            } else {
                $('.attr-weight .value', $wrapper).html(ArticleWeight);
            }
        },

        setArticleContent: function(id, variation, url, variations) {
            var wrapper   = '#result-wrapper_buy_form_' + id;
            var listStyle = $('#ed_list.active').length > 0 ? 'list' : 'gallery';
            var $spinner  = $.evo.extended().spinner($(wrapper)[0]);

            $.evo.extended().loadContent(url + (url.indexOf('?') >= 0 ? '&' : '?') + 'isListStyle=' + listStyle, function (content) {
                var $wrapper = $(wrapper);

                $.evo.extended().imagebox(wrapper);
                $.evo.article().registerSimpleVariations($wrapper);
                $.evo.article().registerSwitchVariations($wrapper);
                $.evo.article().registerImageSwitch($wrapper);

                $('*[data-toggle="basket-add"]', $wrapper).on('submit', function(event) {
                    event.preventDefault();

                    var $form = $(this);
                    var data  = $form.serializeObject();
                    data['a'] = variation;

                    $.evo.basket().addToBasket($form, data);
                });

                $(variations).each(function (i, item) {
                    $.evo.article().variationSetVal(item.key, item.value, $wrapper);
                });

                $.evo.extended().autoheight();
                $spinner.stop();
            }, function () {
                $.evo.error('Error loading ' + url);
            }, false, wrapper);
        },

        variationResetAll: function($wrapper) {
            $('.variation[data-value] input:checked', $wrapper).prop('checked', false);
            $('.variations select option', $wrapper).prop('selected', false);
            $('.variations select', $wrapper).selectpicker('refresh');
        },

        variationDisableAll: function($wrapper) {
            $('.swatches-selected', $wrapper).text('');
            $('[data-value].variation', $wrapper).each(function (i, item) {
                $(item)
                    .removeClass('active')
                    .removeClass('loading')
                    .addClass('not-available');
                $.evo.article()
                    .removeStockInfo($(item));
            });
        },

        variationSetVal: function(key, value, $wrapper) {
            $('[data-key="' + key + '"]', $wrapper)
                .val(value)
                .closest('select')
                .selectpicker('refresh');
        },

        variationEnable: function(key, value, $wrapper) {
            this.variationEnableItem($('[data-value="' + value + '"].variation', $wrapper));
        },

        variationActive: function(key, value, def, $wrapper) {
            this.variationActiveItem($('[data-value="' + value + '"].variation', $wrapper), key);
        },

        removeStockInfo: function(item) {
            // nur im Single-Artikel Context benutzen!!!
        },

        variationInfo: function(value, status, note) {
            // nur im Single-Artikel Context benutzen!!!
        },

        variationSwitch: function(item, animation) {
            if (item) {
                var formID   = $(item).closest('form').attr('id'),
                    $current = this.getCurrent(item),
                    key      = $current.data('key'),
                    value    = $current.data('value'),
                    io       = $.evo.io(),
                    args     = io.getFormValues(formID),
                    $wrapper = $('#result-wrapper_' + formID),
                    $spinner = animation ? $.evo.extended().spinner($wrapper[0]) : null;

                args.wrapper = '#' + $wrapper.attr('id');
                $current.addClass('loading');

                if (animation) {
                    $wrapper.addClass('loading');
                }

                $.evo.article().variationDispose();
                io.call('checkVarkombiDependencies', [args, key, value], item, function (error, data) {
                    $wrapper.removeClass('loading');
                    if (animation) {
                        $spinner.stop();
                    }
                    if (error) {
                        $.evo.error('checkVarkombiDependencies');
                    }
                });
            }
        },

        variationPrice: function(item, animation) {
            if (item) {
                var formID   = $(item).closest('form').attr('id'),
                    io       = $.evo.io(),
                    args     = io.getFormValues(formID),
                    $wrapper = $('#result-wrapper_' + formID),
                    $spinner = animation ? $.evo.extended().spinner($wrapper[0]) : null;


                args.wrapper = '#' + $wrapper.attr('id');

                if (animation) {
                    $wrapper.addClass('loading');
                }

                io.call('checkDependencies', [args], null, function (error, data) {
                    $wrapper.removeClass('loading');
                    if (animation) {
                        $spinner.stop();
                    }
                    if (error) {
                        $.evo.error('checkDependencies');
                    }
                });
            }
        }
    });

    /*******************************************************************************************************************
     * Article classloader - if #buy_form exists (on article details), load ArticleClass and ArticleListClass otherwise
     */
    var ie = /(msie|trident)/i.test(navigator.userAgent) ? navigator.userAgent.match(/(msie |rv:)(\d+(.\d+)?)/i)[2] : false;
    if (ie && parseInt(ie) <= 9) {
        $(document).ready(function () {
            $v = ($('#buy_form').length === 1) ? new ArticleClass() : new ArticleListClass();
            $v.onLoad();
            $v.register();
        });
    } else {
        $(window).on('load', function() {
            $v = ($('#buy_form').length === 1) ? new ArticleClass() : new ArticleListClass();
            $v.onLoad();
            $v.register();
        });
    }

    $(window).resize(
        viewport.changed(function(){
            $v.configurator();
        })
    );

    // PLUGIN DEFINITION
    // =================
    $.evo.article = function() {
       return $v;
    };
})(jQuery, document, window, ResponsiveBootstrapToolkit);