<li class="list-group-item {if $oBox->getContainerID() > 0}boxRowContainer{/if}">
    <div class="row">
        {assign var=isActive value=$oBox->getFilter($nPage) === true || is_array($oBox->getFilter($nPage))}
        {if $oBox->getBaseType() === $smarty.const.BOX_CONTAINER}
            <div class="col-sm-6 col-xs-12{if !$isActive} inactive text-muted{/if}">
                <b>Container #{$oBox->getID()}</b>
            </div>
        {else}
            <div class="col-sm-2 col-xs-4{if !$isActive} inactive text-muted{/if}{if $oBox->getContainerID() > 0} boxSubName{/if}">
                {$oBox->getTitle()}
            </div>
            <div class="col-sm-1 col-xs-3{if !$isActive} inactive text-muted{/if}">
                {$oBox->getType()|ucfirst}
            </div>
            <div class="col-sm-3 col-xs-4{if !$isActive} inactive text-muted{/if}">
                {$oBox->getName()}
            </div>
        {/if}
        <div class="col-sm-2">
            {if $nPage === 0}
                {if \Functional\true($oBox->getFilter())}
                    {__('visibleOnAllPages')}
                {elseif \Functional\false($oBox->getFilter())}
                    {__('deactivatedOnAllPages')}
                {else}
                    {__('visibleOnSomePages')}
                {/if}
            {else}
                <ul class="box-active-filters" id="box-active-filters-{$oBox->getID()}">
                    {if $oBox->getContainerID() === 0 && is_array($oBox->getFilter($nPage))}
                        {foreach $oBox->getFilter($nPage) as $pageID}
                            <li class="selected-item"><i class="fa fa-filter"></i> {$filterMapping[$pageID]}</li>
                        {/foreach}
                    {/if}
                </ul>
            {/if}
        </div>
        <div class="col-sm-2 col-xs-6{if $oBox->getContainerID() > 0} boxSubName{/if}">
            <input class="left{if ($nPage !== 0 && is_array($oBox->getFilter($nPage))) || ($nPage === 0 && !\Functional\true($oBox->getFilter()) && !\Functional\false($oBox->getFilter()))} tristate{/if}"
                   style="margin-right: 5px;"
                   type="checkbox"
                   name="aktiv[]"
                   {if ($nPage !== 0 && $oBox->isVisibleOnPage($nPage)) || ($nPage === 0 && \Functional\true($oBox->getFilter()))}checked="checked"{/if} value="{$oBox->getID()}">
            <input type="hidden" name="box[]" value="{$oBox->getID()}">
            {*prevents overwriting specific visibility when indeterminate checkbox is set on 'all pages' view*}
            {if $nPage === 0 && !\Functional\true($oBox->getFilter()) && !\Functional\false($oBox->getFilter())}
                <input type="hidden" name="ignore[]" value="{$oBox->getID()}">
            {/if}
            <input class="form-control text-right" type="number" size="3" name="sort[]" value="{$oBox->getSort()}"
                   autocomplete="off" id="{$oBox->getSort()}">
        </div>
        <div class="col-sm-2 col-xs-6 btn-group">
            <a href="boxen.php?action=del&page={$nPage}&position={$position}&item={$oBox->getID()}&token={$smarty.session.jtl_token}"
               onclick="return confirmDelete('{if $oBox->getBaseType() === $smarty.const.BOX_CONTAINER}Container #{$oBox->getID()}{else}{$oBox->getTitle()}{/if}');"
               title="{__('remove')}"
               class="btn btn-danger">
                <i class="fa fa-trash"></i>
            </a>
            <a href="boxen.php?action=edit_mode&page={$nPage}&position={$position}&item={$oBox->getID()}&token={$smarty.session.jtl_token}"
               title="{__('edit')}"
               class="btn btn-default{if empty($oBox->getType()) || ($oBox->getType() !== \JTL\Boxes\Type::TEXT && $oBox->getType() !== \JTL\Boxes\Type::LINK && $oBox->getType() !== \JTL\Boxes\Type::CATBOX)} disabled{/if}">
                <i class="fa fa-edit"></i>
            </a>
            {if $oBox->getContainerID() === 0}
                {if $nPage === $smarty.const.PAGE_ARTIKEL || $nPage === $smarty.const.PAGE_ARTIKELLISTE || $nPage === $smarty.const.PAGE_HERSTELLER || $nPage === $smarty.const.PAGE_EIGENE}
                    {if $nPage === $smarty.const.PAGE_ARTIKEL}
                        {assign var=picker value='articlePicker'}
                    {elseif $nPage === $smarty.const.PAGE_ARTIKELLISTE}
                        {assign var=picker value='categoryPicker'}
                    {elseif $nPage === $smarty.const.PAGE_HERSTELLER}
                        {assign var=picker value='manufacturerPicker'}
                    {elseif $nPage === $smarty.const.PAGE_EIGENE}
                        {assign var=picker value='pagePicker'}
                    {/if}
                    {if !is_array($oBox->getFilter($nPage)) || \Functional\true($oBox->getFilter())}
                        <input type="hidden" id="box-filter-{$oBox->getID()}" name="box-filter-{$oBox->getID()}" value="">
                    {else}
                        <input type="hidden" id="box-filter-{$oBox->getID()}" name="box-filter-{$oBox->getID()}"
                               value="{foreach $oBox->getFilter($nPage) as $pageID}{if !empty($pageID)}{$pageID}{/if}{if !$pageID@last},{/if}{/foreach}">
                    {/if}
                    <button type="button" class="btn btn-default"
                            onclick="openFilterPicker({$picker}, {$oBox->getID()})">
                        <i class="fa fa-filter"></i>
                    </button>
                {/if}
            {/if}
        </div>
    </div>
</li>
