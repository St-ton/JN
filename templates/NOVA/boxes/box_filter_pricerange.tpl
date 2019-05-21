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
                let priceRange       = (new URL(window.location.href)).searchParams.get("pf"),
                    priceRangeMin    = 0,
                    priceRangeMax    = {$priceRangeMax},
                    currentPriceMin  = priceRangeMin,
                    currentPriceMax  = priceRangeMax,
                    $priceRangeFrom  = $("#price-range-from"),
                    $priceRangeTo    = $("#price-range-to");
                if (priceRange != null) {
                    let priceRangeMinMax = priceRange.split('_');
                    currentPriceMin      = priceRangeMinMax[0];
                    currentPriceMax      = priceRangeMinMax[1];
                    $priceRangeFrom.val(currentPriceMin);
                    $priceRangeTo.val(currentPriceMax);
                }
                $('#price-range-slider').slider({
                    range: true,
                    min: priceRangeMin,
                    max: priceRangeMax,
                    values: [currentPriceMin, currentPriceMax],
                    slide: function(event, ui) {
                        $priceRangeFrom.val(ui.values[0]);
                        $priceRangeTo.val(ui.values[1]);
                    },
                    stop: function(event, ui) {
                        redirectToNewPriceRange(ui.values[0] + '_' + ui.values[1]);
                    }
                });
                $('.price-range-input').change(function () {
                    let prFrom = $priceRangeFrom.val(),
                        prTo   = $priceRangeTo.val();
                    redirectToNewPriceRange(
                        (prFrom > 0 ? prFrom : priceRangeMin) + '_' + (prTo > 0 ? prTo : priceRangeMax)
                    );
                });

                function redirectToNewPriceRange(priceRange) {
                    window.location.href = updateURLParameter(
                        window.location.href,
                        'pf',
                        priceRange
                    );
                }

                function updateURLParameter(url, param, paramVal){
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
                }
            });
        </script>
        {/block}
    {/if}
{/block}
