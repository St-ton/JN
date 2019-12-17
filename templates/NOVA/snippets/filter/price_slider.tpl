{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-price-slider'}
    {block name='snippets-filter-price-slider-content'}
        {inputgroup class="mb-3" size="sm"}
            {input id="{$id}-from" class="price-range-input mr-4 font-weight-bold" placeholder=0 aria=["label" => {lang key='differentialPriceFrom' section='productOverview'}]}
            {input id="{$id}-to" class="price-range-input ml-4 font-weight-bold" placeholder=$priceRangeMax aria=["label" => {lang key='differentialPriceTo' section='productOverview'}]}
            {input id="js-price-range" type="hidden" value="{$priceRange}"}
        {/inputgroup}
        <div id="{$id}" class="mx-2"></div>
    {/block}
    {block name='snippets-filter-price-slider-script'}
        {inline_script}<script>
            $(window).on('load', function(){
                initPriceSlider($('#js-price-redirect').val() != 1);
            });
            window.initPriceSlider = function(redirect) {
                let priceRange = $('#js-price-range').val(),
                    priceRangeMin = 0,
                    priceRangeMax = {$priceRangeMax},
                    currentPriceMin = priceRangeMin,
                    currentPriceMax = priceRangeMax,
                    $priceRangeFrom = $("#{$id}-from"),
                    $priceRangeTo = $("#{$id}-to"),
                    $priceSlider = document.getElementById('{$id}');
                if (priceRange) {
                    let priceRangeMinMax = priceRange.split('_');
                    currentPriceMin = priceRangeMinMax[0];
                    currentPriceMax = priceRangeMinMax[1];
                    $priceRangeFrom.val(currentPriceMin);
                    $priceRangeTo.val(currentPriceMax);
                }
                noUiSlider.create($priceSlider, {
                    start: [currentPriceMin, currentPriceMax],
                    connect: true,
                    range: {
                        'min': priceRangeMin,
                        'max': priceRangeMax
                    },
                    step: 1
                });
                $priceSlider.noUiSlider.on('end', function (values, handle) {
                    redirectToNewPriceRange(values[0] + '_' + values[1], redirect);
                });
                $priceSlider.noUiSlider.on('slide', function (values, handle) {
                    $priceRangeFrom.val(values[0]);
                    $priceRangeTo.val(values[1]);
                });
                $('.price-range-input').change(function () {
                    let prFrom = $priceRangeFrom.val(),
                        prTo = $priceRangeTo.val();
                    redirectToNewPriceRange(
                        (prFrom > 0 ? prFrom : priceRangeMin) + '_' + (prTo > 0 ? prTo : priceRangeMax),
                        redirect
                    );
                });
            };

            function redirectToNewPriceRange(priceRange, redirect) {
                let redirectURL = updateURLParameter(
                    window.location.href,
                    'pf',
                    priceRange
                );
                if (redirect) {
                    window.location.href = redirectURL;
                } else {
                    window.reloadFilter(redirectURL);
                }
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
        </script>{/inline_script}
    {/block}
{/block}