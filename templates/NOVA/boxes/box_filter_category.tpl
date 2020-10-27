{block name='boxes-box-filter-category'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
        && !($isMobile || $Einstellungen.template.productlist.filter_placement === 'modal')}
        <div id="sidebox{$oBox->getID()}" class="box box-filter-category d-none d-lg-block">
            {button
                variant="link"
                class="text-decoration-none px-0 text-left-util dropdown-toggle"
                block=true
                role="button"
                data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
            }
                <span class="text-truncate">
                    {$oBox->getTitle()}
                </span>
            {/button}
            {collapse id="cllps-box{$oBox->getID()}"
                visible=$oBox->getItems()->isActive() || $Einstellungen.template.productlist.filter_items_always_visible === 'Y'}
                {block name='boxes-box-filter-category-content'}
                    {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
                {/block}
            {/collapse}
            {block name='boxes-box-filter-category-hr'}
                <hr class="my-2">
            {/block}
        </div>
    {/if}
{/block}
