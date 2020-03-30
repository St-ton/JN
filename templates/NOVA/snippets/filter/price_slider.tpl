{block name='snippets-filter-price-slider'}
    {block name='snippets-filter-price-slider-content'}
        {inputgroup class="mb-3" size="sm"}
            {input id="{$id}-from" class="price-range-input mr-4 font-weight-bold" placeholder=0 aria=["label" => {lang key='differentialPriceFrom' section='productOverview'}]}
            {input id="{$id}-to" class="price-range-input ml-4 font-weight-bold" placeholder=$priceRangeMax aria=["label" => {lang key='differentialPriceTo' section='productOverview'}]}
            {input data=['id'=>'js-price-range'] type="hidden" value="{$priceRange}"}
            {input data=['id'=>'js-price-range-max'] type="hidden" value="{$priceRangeMax}"}
            {input data=['id'=>'js-price-range-id'] type="hidden" value="{$id}"}
        {/inputgroup}
        <div id="{$id}" class="mx-2"></div>
    {/block}
    {block name='snippets-filter-price-slider-script'}
        {inline_script}<script>
            $(window).on('load', function() {
                $.evo.initPriceSlider($('.js-price-range-box'), $('#js-price-redirect').val() != 1);
            });
        </script>{/inline_script}
    {/block}
{/block}
