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
            {input id="js-price-range-max" type="hidden" value="{$priceRangeMax}"}
            {input id="js-price-range-id" type="hidden" value="{$id}"}
        {/inputgroup}
        <div id="{$id}" class="mx-2"></div>
    {/block}
    {block name='snippets-filter-price-slider-script'}
        {inline_script}<script>
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