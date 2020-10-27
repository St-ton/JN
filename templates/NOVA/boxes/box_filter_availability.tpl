{block name='boxes-box-filter-availability'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
    && !($isMobile || $Einstellungen.template.productlist.filter_placement === 'modal')}
        <div id="sidebox{$oBox->getID()}" class="box box-filter-availability d-none d-lg-block">
            {button
                variant="link"
                class="text-decoration-none px-0 text-left-util dropdown-toggle text-truncate"
                block=true
                role="button"
                data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
            }
                <span class="text-truncate">
                    {lang key='filterAvailability'}
                </span>
            {/button}
            {collapse id="cllps-box{$oBox->getID()}"
                visible=($oBox->getItems()->isActive() || $Einstellungen.template.productlist.filter_items_always_visible === 'Y')}
                {block name='boxes-box-filter-availability-content'}
                    {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
                {/block}
            {/collapse}
            {block name='boxes-box-filter-availability-hr'}
                <hr class="my-2">
            {/block}
        </div>
    {/if}
{/block}
