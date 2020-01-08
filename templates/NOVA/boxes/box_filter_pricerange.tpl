{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-pricerange'}
    {if !empty($oBox->getItems()->getOptions()) && $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE && (!$device->isMobile() || $device->isTablet())}
        {block name='boxes-box-filter-pricerange-content'}
            <div id="sidebox{$oBox->getID()}" class="box box-filter-price">
                {button
                    variant="link"
                    class="text-decoration-none px-0 text-left dropdown-toggle"
                    block=true
                    role="button"
                    data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
                }
                    {lang key='rangeOfPrices'}
                {/button}
                {collapse class="pb-4" id="cllps-box{$oBox->getID()}" visible=$Einstellungen.template.sidebar_settings.always_show_price_range === 'Y'}
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
