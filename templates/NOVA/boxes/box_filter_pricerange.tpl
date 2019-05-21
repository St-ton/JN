{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-pricerange'}
    {if !empty($oBox->getItems()->getOptions()) && $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
        {block name='boxes-box-filter-pricerange-content'}
            <div class="h4">
                {button
                variant="link"
                class="text-decoration-none"
                role="button"
                data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
                }
                    {lang key='rangeOfPrices'} +
                {/button}
            </div>

            {collapse class="box box-filter-price" id="sidebox{$oBox->getID()}" visible=true}
                {inputgroup}
                    {input id="price-range-from" class="price-range-input"}
                    {input id="price-range-to" class="price-range-input"}
                {/inputgroup}
                <div id="price-range-slider"></div>
                <div id="amount">0 {$smarty.session.Waehrung->getHtmlEntity()} - {$priceRangeMax} {$smarty.session.Waehrung->getHtmlEntity()}</div>
            {/collapse}
            <hr class="mt-0 mb-4">
        {/block}

        {block name='boxes-box-filter-pricerange-script'}
        <script>
            $(window).on('load', function(){
                var currentHref      = window.location.href,
                    priceRange       = (new URL(currentHref)).searchParams.get("pf"),
                    priceRangeMin    = 0,
                    priceRangeMax    = {$priceRangeMax},
                    priceRangeMinMax = [priceRangeMin, priceRangeMax],
                    $priceRangeFrom  = $("#price-range-from"),
                    $priceRangeTo    = $("#price-range-to");
                if (priceRange != null) {
                    priceRangeMinMax = priceRange.split('_');
                    $priceRangeFrom.val(priceRangeMinMax[0]);
                    $priceRangeTo.val(priceRangeMinMax[1]);
                }
                $('#price-range-slider').slider({
                    range: true,
                    min: priceRangeMin,
                    max: priceRangeMax,
                    values: [priceRangeMinMax[0], priceRangeMinMax[1]],
                    slide: function(event, ui) {
                        $priceRangeFrom.val(ui.values[0]);
                        $priceRangeTo.val(ui.values[1]);
                    },
                    stop: function(event, ui) {
                        window.location.href = updateURLParameter(
                            currentHref,
                            'pf',
                            ui.values[0] + '_' + ui.values[1]
                        );
                    }
                });
                $('.price-range-input').change(function () {
                    var prFrom = $priceRangeFrom.val(),
                        prTo   = $priceRangeTo.val();
                    window.location.href = updateURLParameter(
                        currentHref,
                        'pf',
                        (prFrom > 0 ? prFrom : priceRangeMin) + '_' + (prTo > 0 ? prTo : priceRangeMax)
                    );
                });

                function updateURLParameter(url, param, paramVal){
                    var newAdditionalURL = '',
                        tempArray        = url.split('?'),
                        baseURL          = tempArray[0],
                        additionalURL    = tempArray[1],
                        temp             = '';
                    if (additionalURL) {
                        tempArray = additionalURL.split('&');
                        for (var i=0; i<tempArray.length; i++){
                            if(tempArray[i].split('=')[0] != param){
                                newAdditionalURL += temp + tempArray[i];
                                temp = '&';
                            }
                        }
                    }

                    return baseURL + '?' + newAdditionalURL + temp + param + '=' + paramVal;
                }
            });
        </script>
        {/block}
    {/if}
{/block}
