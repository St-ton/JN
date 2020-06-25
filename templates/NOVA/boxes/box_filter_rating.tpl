{block name='boxes-box-filter-rating'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
        && !($isMobile || $Einstellungen.template.productlist.filter_placement === 'modal')}
        <div id="sidebox{$oBox->getID()}" class="box box-filter-rating d-none d-lg-block">
            {button
                variant="link"
                class="text-decoration-none px-0 text-left dropdown-toggle"
                block=true
                role="button"
                data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
            }
                {lang key='Votes'}
            {/button}
            {collapse id="cllps-box{$oBox->getID()}" visible=$oBox->getItems()->isActive()}
            {block name='boxes-box-filter-rating-content'}
                {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
            {/block}
            {/collapse}
            {block name='boxes-box-filter-rating-hr'}
                <hr class="my-2">
            {/block}
        </div>
    {/if}
{/block}
