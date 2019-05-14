{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-pricerange'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}

        <div class="h4">
            {button
            variant="link"
            class="text-decoration-none"
            role="button"
            data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
            }
            {lang key='rangeOfPrices'}
            +{/button}
        </div>
        {collapse class="box box-filter-price" id="sidebox{$oBox->getID()}"}
            {input id="price-range-from" class="price-range-input"}
            {input id="price-range-to" class="price-range-input"}
            <div id="price-range-slider"></div>
            <div id="amount">$0 - $500</div>
        {block name='boxes-box-filter-pricerange-content'}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        {/block}
        {/collapse}
        <hr class="mt-0 mb-4">

        <script>
            $(window).on('load', function(){
                var priceRange       = (new URL(window.location.href)).searchParams.get("pf"),
                    priceRangeMinMax = [0, 500];
                if (priceRange != null) {
                    priceRangeMinMax = priceRange.split('_');
                    $("#price-range-from").val(priceRangeMinMax[0]);
                    $("#price-range-to").val(priceRangeMinMax[1]);
                }
                $('#price-range-slider').slider({
                    range: true,
                    min: 0,
                    max: 500,
                    values: [priceRangeMinMax[0], priceRangeMinMax[1]],
                    slide: function(event, ui) {
                        $("#price-range-from").val(ui.values[0]);
                        $("#price-range-to").val(ui.values[1]);
                    },
                    stop: function(event, ui) {
                        var currentHref = window.location.href;
                        window.location.href=currentHref.substr(0, currentHref.indexOf('?'))
                            + '?pf=' + ui.values[0] + '_' + ui.values[1];
                    }
                });
                $('.price-range-input').change(function () {

                });
            });
        </script>
    {else}
        {card class="box box-filter-price mb-7" id="sidebox{$oBox->getID()}" title="{lang key='rangeOfPrices'}"}
            <hr class="mt-0 mb-4">
            {block name='boxes-box-filter-pricerange-content'}
                {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
            {/block}
        {/card}
    {/if}
{/block}
