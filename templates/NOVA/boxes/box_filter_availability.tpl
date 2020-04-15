{block name='boxes-box-filter-availability'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
    && !($isMobile || $Einstellungen.template.productlist.filter_placement === 'modal')}
        <div class="box box-filter-availability d-none d-lg-block" id="sidebox{$oBox->getID()}">
            {button
            variant="link"
            class="text-decoration-none px-0 text-left dropdown-toggle"
            block=true
            role="button"
            data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
            }
                {$oBox->getTitle()}
            {/button}
            {collapse id="cllps-box{$oBox->getID()}" visible=$oBox->getItems()->isActive()}
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
