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

    ArticleClass.prototype = {
        constructor: ArticleClass,

        init: function () {
        },
        
        onLoad: function() {
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

            window.addEventListener('popstate', function(event) {
                if (event.state) {
                    that.setArticleContent( event.state.a, event.state.a2, event.state.url, event.state.variations);
                }
            }, false);
        },

        register: function () {
            var that = this,
                config,
                inner;
            this.gallery = $('#gallery').gallery();
            this.galleryIndex = 0;
            this.galleryLastIdent = '_';

            config = $('.product-configuration')
                .closest('form')
                .find('input[type="radio"], input[type="checkbox"], input[type="number"], select');

            if (config.length > 0) {
                config.on('change', function () {
                    that.configurator();
                })
                .keypress(function (e) {
                    if (e.which == 13) {
                        return false;
                    }
                });
                that.configurator(true);
            }
            
            $('.variations select').selectpicker({
                iconBase: 'fa',
                tickIcon: 'fa-check',
                hideDisabled: true,
                showTick: true
                /*mobile: true*/
            });

            $('.simple-variations input[type="radio"]').on('change', function () {
                var val = $(this).val(),
                    key = $(this).parent().data('key');
                $('.simple-variations [data-key="' + key + '"]').removeClass('active');
                $('.simple-variations [data-value="' + val + '"]').addClass('active');
            });
            
            $('.simple-variations input[type="radio"], .simple-variations select')
                .on('change', function () {
                    that.variationPrice(true);
                });
            
            $('.switch-variations input[type="radio"], .switch-variations select')
                .on('change', function () {
                    that.variationSwitch(this, true);
                });

            if ("ontouchstart" in document.documentElement) {
                $('.variations .swatches .variation').on('mouseover', function() {
                    $(this).trigger('click');
                });
            }

            // ie11 fallback
            if (typeof document.body.style.msTransform === 'string') {
                $('.variations label.variation')
                    .on('click', function (e) {
                        if (e.target.tagName === 'IMG') {
                            $(this).trigger('click');
                        }
                    });
            }
            
            inner = function(context, temporary, force) {
                var id = $(context).attr('data-key'),
                    value = $(context).attr('data-value'),
                    data  = $(context).data('list'),
                    title = $(context).attr('data-title'),
                    gallery = $.evo.article().gallery;

                if (typeof temporary === 'undefined') {
                    temporary = true;
                }

                $.evo.article().galleryIndex = gallery.index;
                $.evo.article().galleryLastIdent = gallery.ident;

                if (!$(context).hasClass('active') || force) {
                    if (!!data) {
                        gallery.setItems([data], value);

                        if (!temporary) {
                            var items  = [data];
                            var stacks = gallery.getStacks();
                            for (var s in stacks) {
                                if (stacks.hasOwnProperty(s) && s.match(/^_[0-9a-zA-Z]*$/) && s != '_' + id) {
                                    items = $.merge(items, stacks[s]);
                                }
                            }

                            gallery.setItems([data], '_' + id);
                            gallery.setItems(items, '__');
                            gallery.render('__');
                        } else {
                            gallery.render(value);
                        }
                    }
                }
            };

            $('.variations .bootstrap-select select').change(function() {
                var tmp_idx = parseInt($('.variations .bootstrap-select li.selected').attr('data-original-index')) + 1;
                inner($(this).find('option:nth-child(' + tmp_idx + ')'));
            });

            $('.variations .bootstrap-select .dropdown-menu li').hover(function() {
                var tmp_idx = parseInt($(this).attr('data-original-index')) + 1;
                var sel = $(this).closest('.bootstrap-select').find('select option:nth-child(' + tmp_idx + ')');
                inner(sel);

            }, function() {
                var tmp_idx = parseInt($(this).attr('data-original-index')) + 1,
                    p = $(this).closest('.bootstrap-select').find('select option:nth-child(' + tmp_idx + ')'),
                    id = $(p).attr('data-key'),
                    data  = $(p).data('list'),
                    scope,
                    gallery,
                    active;

                if (!!data) {
                    scope = '_';
                    gallery = $.evo.article().gallery;
                    active = $(p).find('.variation.active');

                    gallery.render($.evo.article().galleryLastIdent);
                    gallery.activate($.evo.article().galleryIndex);
                }
            });
            
            $('.variations .variation').click(function() {
                inner(this, false);
            });
            
            $('.variations .variation').hover(function() {
                inner(this);
            }, function() {
                var p = $(this).closest('.variation'),
                    id = $(this).attr('data-key'),
                    data  = $(this).data('list'),
                    scope,
                    gallery,
                    active;
                if (!!data) {
                    scope = '_';
                    gallery = $.evo.article().gallery;
                    active = $(p).find('.variation.active');
                        
                    gallery.render($.evo.article().galleryLastIdent);
                    gallery.activate($.evo.article().galleryIndex);
                }
            });

            $('.variations .variation.active, .variations option.variation[selected]').each(function (idx, el) {
                inner(el, false, true);
            });

            $('#jump-to-votes-tab').click(function () {
                $('#content a[href="#tab-votes"]').tab('show');
            });
            
            if ($('.switch-variations').length == 1) {
                this.variationSwitch();
            }
        },

        configurator: function (init) {
            var that = this,
                container = $('#cfg-container'),
                width,
                form,
                sidebar = $('#product-configuration-sidebar');

            if (container.length === 0) {
                return;
            }

            if (viewport.current() != 'lg') {
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
                            if (viewport.current() != 'lg') {
                                top = 999999;
                            }
                            return top;
                        },
                        bottom: function () {
                            var bottom = $('body').height() - (container.height() + container.offset().top);
                            if (viewport.current() != 'lg') {
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
                                    cBeschreibung = item.cKurzBeschreibung;
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
                                    if (!(form.quantity[grp.kKonfiggruppe])) {
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

        getConfigGroupQuantity: function (groupId) {
            return $('.cfg-group[data-id="' + groupId + '"] .quantity');
        },

        getConfigGroupQuantityInput: function (groupId) {
            return $('.cfg-group[data-id="' + groupId + '"] .quantity input[type="number"]');
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
            var descriptionDropdownContentHidden = groupItems.find('.hidden');
            var descriptionCheckdioContent       = groupItems.find('div[id^="filter-collapsible_checkdio"]');
            var multiselect                      = groupItems.find('select').attr("multiple");

            //  Bisher kein Content mit einer Beschreibung vorhanden, aber ein Artikel mit Beschreibung ausgewählt
            if (descriptionDropdownContentHidden.length > 0 && descriptionCheckdioContent.length == 0 && itemBeschreibung.length > 0 && multiselect !== "multiple") {
                groupItems.find('a[href="#filter-collapsible_dropdown_' + groupId + '"]').removeClass('hidden');
                descriptionDropdownContent.replaceWith('<div id="filter-collapsible_dropdown_' + groupId + '" class="collapse top10 panel-body">' + itemBeschreibung + '</div>');
            //  Bisher Content mit einer Beschreibung vorhanden, aber ein Artikel ohne Beschreibung ausgewählt
            } else if (descriptionDropdownContentHidden.length == 0 && descriptionCheckdioContent.length == 0 && itemBeschreibung.length == 0 && multiselect !== "multiple") {
                groupItems.find('a[href="#filter-collapsible_dropdown_' + groupId + '"]').addClass('hidden');
                descriptionDropdownContent.addClass('hidden');
            //  Bisher Content mit einer Beschreibung vorhanden und ein Artikel mit Beschreibung ausgewählt
            } else if (descriptionDropdownContentHidden.length == 0 && descriptionCheckdioContent.length == 0 && itemBeschreibung.length > 0 && multiselect !== "multiple") {
                descriptionDropdownContent.replaceWith('<div id="filter-collapsible_dropdown_' + groupId + '" class="collapse top10 panel-body">' + itemBeschreibung + '</div>');
            }
        },
        
        setPrice: function(price, fmtPrice, priceLabel) {
            $('#product-offer .price').html(fmtPrice);
            if (priceLabel.length > 0) {
                $('#product-offer .price_label').html(priceLabel);
            }
        },

        setUnitWeight: function(UnitWeight, newUnitWeight) {
            $('#article-tabs .product-attributes .weight-unit').html(newUnitWeight);
        },

        setProductNumber: function(productNumber){
            $('#product-offer span[itemprop="sku"]').html(productNumber);
        },

        setArticleContent: function(id, variation, url, variations) {
            $.evo.extended().loadContent(url, function(content) {
                $.evo.extended().register();
                $.evo.article().register();
                
                $(variations).each(function (i, item) {
                   $.evo.article().variationSetVal(item.key, item.value);
                });
                
                if (document.location.href != url) {
                    history.pushState({ a: id, a2: variation, url: url, variations: variations }, "", url);
                }
            }, function() {
                $.evo.error('Error loading ' + url);
            });
        },

        variationResetAll: function() {
            $('.variation[data-value] input:checked').prop('checked', false);
            $('.variations select option').prop('selected', false);
            $('.variations select').selectpicker('refresh');
        },

        variationDisableAll: function() {
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

        variationSetVal: function(key, value) {
            $('[data-key="' + key + '"]')
                .val(value)
                .closest('select')
                    .selectpicker('refresh');
        },

        variationEnable: function(key, value) {
            var item = $('[data-value="' + value + '"].variation');

            item.removeClass('not-available');
            item.closest('select')
                .selectpicker('refresh');
        },

        variationActive: function(key, value, def) {
            var item = $('[data-value="' + value + '"].variation');

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
            var type = item.attr('data-type'),
                elem,
                label,
                wrapper;
            
            switch (type) {
                case 'option':
                    label = item.data('content');
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
                    elem = item.find('.label-not-available');
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
                text,
                content,
                wrapper,
                label;
            
            item.attr('data-stock', _stock_info[status]);

            switch (type) {
                case 'option':
                    text = ' (' + note + ')';
                    content = item.data('content');
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
            var key = 0,
                value = 0,
                io = $.evo.io(),
                args = io.getFormValues('buy_form'),
                $current,
                $spinner = null,
                $wrapper = $('#result-wrapper');
            
            if (animation) {
                $wrapper.addClass('loading');
                $spinner = $.evo.extended().spinner();
            }

            if (item) {
                $current = $(item).hasClass('variation') ?
                    $(item) :
                    $(item).closest('.variation'); 

                if ($current.context.tagName === 'SELECT') {
                    $current = $(item).find('option:selected');
                }

                $current.addClass('loading');

                key = $current.data('key');
                value = $current.data('value');
            }

            $.evo.article()
                .variationDispose();

            io.call('checkVarkombiDependencies', [args, key, value], item, function (error, data) {
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
        
        variationPrice: function(animation) {
            var io = $.evo.io(),
                args = io.getFormValues('buy_form');
                
            var $spinner = null,
                $wrapper = $('#result-wrapper');
            
            if (animation) {
                $wrapper.addClass('loading');
                $spinner = $.evo.extended().spinner();
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

    $(window).resize(
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