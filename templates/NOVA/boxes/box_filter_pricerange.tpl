{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-pricerange'}
    {if !empty($oBox->getItems()->getOptions()) && $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE && (!$device->isMobile() || $device->isTablet())}
        {block name='boxes-box-filter-pricerange-content'}
            <div>
                {button
                variant="link"
                class="text-decoration-none pl-0 text-left"
                block=true
                role="button"
                data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
                }
                    {lang key='rangeOfPrices'} <i class="fas fa-plus float-right"></i>
                {/button}
            </div>

            {collapse class="box box-filter-price pb-4" id="sidebox{$oBox->getID()}" visible=true}
                {block name='boxes-box-filter-pricerange-include-price-slider'}
                    {include file='snippets/filter/price_slider.tpl' id='price-slider-box'}
                {/block}
            {/collapse}
            <hr class="my-2">
        {/block}
    {/if}
{/block}
