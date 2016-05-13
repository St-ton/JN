/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

(function($, document, window, viewport){
    'use strict';
    
    var _stock_info = ['out-of-stock', 'in-short-supply', 'in-stock' ];

    var ArticleClass = function () {
        this.init();
    };

    ArticleClass.prototype = {
        constructor: ArticleClass,

        init: function () {
        },
        
        onLoad: function() {
            var that = this;
            var form = $.evo.io().getFormValues('buy_form');
            
            history.replaceState({ a: form.a, a2: form.VariKindArtikel || form.a, url: document.location.href, variations: {} }, document.title, document.location.href);

            window.addEventListener('popstate', function(event) {
                if (event.state) {
                    that.setArticleContent( event.state.a, event.state.a2, event.state.url, event.state.variations);
                }
            }, false);
        },

        register: function () {
            var that = this;
            this.gallery = $('#gallery').gallery();
            this.galleryIndex = 0;
            this.galleryLastIdent = '_';

            var config = $('.product-configuration')
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
            
            var inner = function(context) {
                var id = $(context).attr('data-key'),
                    value = $(context).attr('data-value'),
                    img = $(context).find('img'),
                    data = $(img).data('list'),
                    title = $(img).attr('title'),
                    gallery = $.evo.article().gallery;

                $.evo.article().galleryIndex = gallery.index;
                $.evo.article().galleryLastIdent = gallery.ident;

                if (!$(context).hasClass('active')) {
                    if (!!data) {
                        gallery.setItems([data], value);
                        gallery.render(value);
                    }
                }
            };
            
            $('.variations .swatches .variation').click(function() {
                inner(this);
            });
            
            $('.variations .swatches .variation').hover(function() {
                inner(this);
            }, function() {
                var p = $(this).closest('.swatches'),
                    id = $(this).attr('data-key'),
                    img = $(this).find('img'),
                    data = $(img).data('list');

                if (!!data) {
                    var scope = '_',
                        gallery = $.evo.article().gallery,
                        active = $(p).find('.variation.active');
                        
                    gallery.render($.evo.article().galleryLastIdent);
                    gallery.activate($.evo.article().galleryIndex);
                }
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
                    quantityWrapper,
                    grp,
                    value,
                    enableQuantity,
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
                var nNetto = result.nNettoPreise;
                that.setPrice(result.fGesamtpreis[nNetto], result.cPreisLocalized[nNetto]);

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
                                if(item.cBildPfad) {
                                    that.setConfigItemImage(grp.kKonfiggruppe, item.cBildPfad.cPfadKlein);
                                } else {
                                    that.setConfigItemImage(grp.kKonfiggruppe, grp.cBildPfad);
                                }
                                enableQuantity = item.bAnzahl;
                                if (!enableQuantity) {
                                    quantityInput
                                        .attr('min', item.fInitial)
                                        .attr('max', item.fInitial)
                                        .val(item.fInitial)
                                        .attr('disabled', true)
                                    quantityWrapper.slideUp(200);
                                }
                                else {
                                    quantityWrapper.slideDown(200);
                                    quantityInput
                                        .attr('disabled', false)
                                        .attr('min', item.fMin)
                                        .attr('max', item.fMax);
                                    value = quantityInput.val();
                                    if (value < item.fMin || value > item.fMax)
                                        quantityInput.val(item.fInitial);
                                }
                            }
                        }
                    }
                    else {
                        quantityInput.attr('disabled', true)
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
        
        setPrice: function(price, fmtPrice) {
            $('#product-offer .price').html(fmtPrice);
        },

        setUnitWeight: function(UnitWeight, newUnitWeight) {
            $('#article-tabs .product-attributes .weight-unit').html(newUnitWeight);
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
            var type = item.attr('data-type');
            
            switch (type) {
                case 'option':
                    var label = item.data('content');
                    var wrapper = $('<div />').append(label);
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
                    if (elem.length == 1) {
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
            var item = $('[data-value="' + value + '"].variation');
            var type = item.attr('data-type');
            
            item.attr('data-stock', _stock_info[status]);

            switch (type) {
                case 'option':
                    var text = ' (' + note + ')';
                    var content = item.data('content');
                    var wrapper = $('<div />');
                    
                    wrapper.append(content);
                    wrapper
                        .find('.label-not-available')
                        .remove();
                    
                    var label = $('<span />')
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

                    var label = $('<span />')
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
                disabled = false,
                io = $.evo.io(),
                args = io.getFormValues('buy_form');
                
            var $spinner = null,
                $wrapper = $('#result-wrapper');
            
            if (animation) {
                $wrapper.addClass('loading');
                $spinner = $.evo.extended().spinner();
            }

            if (item) {
                var $current = $(item).hasClass('variation') ? $(item) :
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

    var $v = new ArticleClass();

    $(window).on('load', function () {
        $v.onLoad();
        $v.register();
    });

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