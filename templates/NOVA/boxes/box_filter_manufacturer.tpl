{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-manufacturer'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
        && !($device->isMobile() || $device->isTablet() || $Einstellungen.template.productlist.filter_placement === 'M')}
        <div class="box box-filter-manufacturer d-none d-lg-block" id="sidebox{$oBox->getID()}">
            {button
                variant="link"
                class="text-decoration-none px-0 text-left dropdown-toggle"
                block=true
                role="button"
                data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
            }
                {lang key='manufacturers'}
            {/button}
            {collapse id="cllps-box{$oBox->getID()}" visible=$oBox->getItems()->isActive()}
            {block name='boxes-box-filter-manufacturer-include-manufacturer'}
                    {include file='snippets/filter/manufacturer.tpl' filter=$oBox->getItems()}
            {/block}
            {/collapse}
            {block name='boxes-box-filter-manufacturer-hr'}
                <hr class="my-2">
            {/block}
        </div>
    {/if}
{/block}
