<li class="list-group-item boxRow">

    <div class="col-xs-3{if $oBox->bAktiv == 0} inactive text-muted{/if}">
        {$oBox->cTitel}
    </div>
    <div class="col-xs-2{if $oBox->bAktiv == 0} inactive text-muted{/if}">
        {$oBox->eTyp|ucfirst}
    </div>
    <div class="col-xs-2{if $oBox->bAktiv == 0} inactive text-muted{/if}">
        {$oBox->cName}
    </div>
    <div class="col-xs-3">
        <input type="hidden" name="box[]" value="{$oBox->kBox}" />
        {if $nPage == 0}
        {if $oBox->bAktiv == 1}
        <input type="hidden" name="aktiv[]" value="{$oBox->kBox}" />
        {/if}
        {else}
        <input class="left" style="margin-right: 5px;" type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
        {/if}
        <input class="form-control text-right" type="number" size="3" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" />
    </div>
    <div class="col-xs-2">
        {if $nPage == 0}
            {if $oBox->bAktiv == 0}
                <a href="boxen.php?action=activate&position={$position}&item={$oBox->kBox}&value=1&token={$smarty.session.jtl_token}" title="Auf jeder Seite aktivieren"><i class="fa fa-lg fa-eye"></i></a>
            {else}
                <a href="boxen.php?action=activate&position={$position}&item={$oBox->kBox}&value=0&token={$smarty.session.jtl_token}" title="Auf jeder Seite deaktivieren"><i class="fa fa-lg fa-eye-slash"></i></a>
            {/if}
        {/if}
        {if $oBox->eTyp === 'text' || $oBox->eTyp === 'link' || $oBox->eTyp === 'catbox'}
            <a href="boxen.php?action=edit_mode&page={$nPage}&position={$position}&item={$oBox->kBox}&token={$smarty.session.jtl_token}" title="{#edit#}"><i class="fa fa-lg fa-edit"></i></a>
        {/if}
        <a href="boxen.php?action=del&page={$nPage}&position={$position}&item={$oBox->kBox}&token={$smarty.session.jtl_token}" onclick="return confirmDelete('{$oBox->cTitel}');" title="Aus allen Seiten entfernen">
            <i class="fa fa-lg fa-trash"></i>
        </a>
        {if $nPage == 0}
            <a href="#" title="Sichtbar auf folgenden Seiten: {$oBox->cVisibleOn}"><i class="fa fa-lg fa-info"></i></a>
        {/if}
        {if $nPage == 1 || $nPage == 2 || $nPage == 24 || $nPage == 31}
            <a href="#" data-filter="box-filter-{$oBox->kBox}" data-box-id="{$oBox->kBox}" data-box-title="{$oBox->cTitel}" class="box-filter" data-toggle="modal" data-target="#boxFilterModal">
                <i class="fa fa-filter"></i>
            </a>
        {/if}
    </div>
    <div class="xol-xs-12 clear-both">
        <ul class="box-active-filters" id="box-active-filters-{$oBox->kBox}">
            {if !empty($oBox->cFilter)}
                {foreach name="filters" from=$oBox->cFilter item=filter}
                    {if $filter !== ''}
                        <li class="selected-item" id="elem-{$filter.id}">
                            <a href="#" data-ref="{$filter.id}">
                                <i class="fa fa-trash"></i>
                            </a>
                            <span>{$filter.name}</span>
                            <input class="new-filter" type="hidden" name="box-filter-{$oBox->kBox}[]" value="{$filter.id}" />
                        </li>
                    {/if}
                {/foreach}
            {/if}
        </ul>
    </div>
</li>