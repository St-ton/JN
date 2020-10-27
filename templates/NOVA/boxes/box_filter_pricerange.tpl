{block name='boxes-box-filter-pricerange'}
    {if !empty($oBox->getItems()->getOptions())
        && $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
        && !($isMobile || $Einstellungen.template.productlist.filter_placement === 'modal')}
        {block name='boxes-box-filter-pricerange-content'}
            <div id="sidebox{$oBox->getID()}" class="box box-filter-price d-none d-lg-block js-price-range-box">
                {button
                    variant="link"
                    class="text-decoration-none px-0 text-left-util dropdown-toggle"
                    block=true
                    role="button"
                    data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
                }
                    <span class="text-truncate">
                        {lang key='rangeOfPrices'}
                    </span>
                {/button}
                {collapse class="pb-4" id="cllps-box{$oBox->getID()}" visible=true}
                    {block name='boxes-box-filter-pricerange-include-price-slider'}
                        {include file='snippets/filter/price_slider.tpl' id='price-slider-box'}
                    {/block}
                {/collapse}
                {block name='boxes-box-filter-pricerange-hr'}
                    <hr class="my-2">
                {/block}
            </div>
        {/block}
    {/if}
{/block}
