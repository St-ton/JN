{config_load file="$lang.conf" section='boxen'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('boxen') cBeschreibung=__('boxenDesc') cDokuURL=__('boxenURL')}

{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='articlePicker'
    modalTitle='Artikel ausw&auml;hlen'
    searchInputLabel='Suche nach Artikelnamen'
}
{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='categoryPicker'
    modalTitle='Kategorien ausw&auml;hlen'
    searchInputLabel='Suche nach Kategorienamen'
}
{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='manufacturerPicker'
    modalTitle='Hersteller ausw&auml;hlen'
    searchInputLabel='Suche nach Herstellernamen'
}
{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='pagePicker'
    modalTitle='Eigene Seiten ausw&auml;hlen'
    searchInputLabel='Suche nach Seitennamen'
}

<script>
    $(function () {
        articlePicker = new SearchPicker({
            searchPickerName:  'articlePicker',
            getDataIoFuncName: 'getProducts',
            keyName:           'kArtikel',
            renderItemCb:      renderItemName
        });
        categoryPicker = new SearchPicker({
            searchPickerName:  'categoryPicker',
            getDataIoFuncName: 'getCategories',
            keyName:           'kKategorie',
            renderItemCb:      renderItemName
        });
        manufacturerPicker = new SearchPicker({
            searchPickerName:  'manufacturerPicker',
            getDataIoFuncName: 'getManufacturers',
            keyName:           'kHersteller',
            renderItemCb:      renderItemName
        });
        pagePicker = new SearchPicker({
            searchPickerName:  'pagePicker',
            getDataIoFuncName: 'getPages',
            keyName:           'kLink',
            renderItemCb:      renderItemName
        });
    });

    function renderItemName (item)
    {
        return '<p class="list-group-item-text">' + item.cName + '</p>';
    }

    function openFilterPicker (picker, kBox)
    {
        picker
            .setOnApplyBefore(
                function () { onApplyBeforeFilterPicker(kBox) }
            )
            .setOnApply(
                function (selectedKeys, selectedItems) { onApplyFilterPicker(kBox, selectedKeys, selectedItems) }
            )
            .setSelection($('#box-filter-' + kBox).val().split(',').filter(Boolean))
            .show();
    }

    function onApplyBeforeFilterPicker (kBox)
    {
        $('#box-active-filters-' + kBox)
            .empty()
            .append(
                '<li class="selected-item"><i class="fa fa-spinner fa-pulse"></i></li>'
            );
    }

    function onApplyFilterPicker (kBox, selectedKeys, selectedItems)
    {
        var $activeFilterList = $('#box-active-filters-' + kBox);

        $('#box-filter-' + kBox).val(selectedKeys.join(','));
        $activeFilterList.empty();

        selectedItems.forEach(function (item) {
            $activeFilterList.append(
                '<li class="selected-item"><i class="fa fa-filter"></i> ' + item.cName + '</li>'
            );
        });
    }

    function confirmDelete(cName)
    {
        return confirm('{__('confirmDeleteBox')}'.replace('%s', cName));
    }
</script>

<div id="content">
    {if $invisibleBoxes|count > 0}
        <div class="alert alert-danger">{__('warningInvisibleBoxes')}</div>
        <form action="boxen.php" method="post" class="block">
            {$jtl_token}
            <div class="panel panel-default editorInner">
                <div class="panel-heading">
                    <h3 class="panel-title">{__('invisibleBoxes')}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tr class="boxRow">
                            <th class="check">&nbsp;</th>
                            <th>
                                <strong>{__('boxTitle')}</strong>
                            </th>
                            <th>
                                <strong>{__('boxLabel')}</strong>
                            </th>
                            <th>
                                <strong>{__('boxTemplate')}</strong>
                            </th>
                            <th>
                                <strong>{__('boxPosition')}</strong>
                            </th>
                        </tr>
                        {foreach from=$invisibleBoxes item=invisibleBox name=invisibleBoxList}
                            <tr>
                                <td class="check">
                                    <input name="kInvisibleBox[]" type="checkbox" value="{$invisibleBox->kBox}" id="kInvisibleBox-{$smarty.foreach.invisibleBoxList.index}">
                                </td>
                                <td>
                                    <label for="kInvisibleBox-{$smarty.foreach.invisibleBoxList.index}">{$invisibleBox->cTitel}</label>
                                </td>
                                <td>
                                    {$invisibleBox->cName}
                                </td>
                                <td>
                                    {$invisibleBox->cTemplate}
                                </td>
                                <td>
                                    {$invisibleBox->ePosition}
                                </td>
                            </tr>
                        {/foreach}
                        <tr>
                            <td class="check">
                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                            </td>
                            <td colspan="4" class="tleft"><label for="ALLMSGS">{__('globalSelectAll')}</label></td>
                        </tr>
                    </table>
                </div>
                <div class="panel-footer">
                    <button name="action" type="submit" class="btn btn-danger" value="delete-invisible"><i class="fa fa-trash"></i> {__('deleteSelected')}</button>
                </div>
            </div>
        </form>
    {/if}
    {if !is_array($oBoxenContainer) || $oBoxenContainer|@count == 0}
        <div class="alert alert-danger">{__('noTemplateConfig')}</div>
    {elseif !$oBoxenContainer.left && !$oBoxenContainer.right && !$oBoxenContainer.top && !$oBoxenContainer.bottom}
        <div class="alert alert-danger">{__('noBoxActivated')}</div>
    {else}
        {if isset($oEditBox) && $oEditBox}
            <div id="editor" class="editor">
                <form action="boxen.php" method="post">
                    {$jtl_token}
                    <div class="panel panel-default editorInner">
                        <div class="panel-heading">
                            <h3 class="panel-title">{__('boxEdit')}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="boxtitle">{__('boxTitle')}:</label>
                                </span>
                                <input class="form-control" id="boxtitle" type="text" name="boxtitle" value="{$oEditBox->cTitel}" />
                            </div>
                            {if $oEditBox->eTyp === 'text'}
                                {foreach $oSprachen_arr as $oSprache}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="title-{$oSprache->cISO}">{__('boxTitle')} {$oSprache->cNameDeutsch}</label>
                                        </span>
                                        <input class="form-control" id="title-{$oSprache->cISO}" type="text" name="title[{$oSprache->cISO}]" value="{foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}" />
                                    </div>
                                    <textarea id="text-{$oSprache->cISO}" name="text[{$oSprache->cISO}]" class="form-control ckeditor" rows="15" cols="60">
                                        {foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cInhalt}{/if}{/foreach}
                                    </textarea>
                                    <hr>
                                {/foreach}
                            {elseif $oEditBox->eTyp === 'catbox'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="linkID">{__('catBoxNum')}</label>
                                    </span>
                                    <input class="form-control" id="linkID" type="text" name="linkID" value="{$oEditBox->kCustomID}" size="3">
                                    <span class="input-group-addon">
                                        <button type="button" class="btn-tooltip btn btn-info btn-heading"
                                                data-html="true" data-toggle="tooltip" data-placement="left" title=""
                                                data-original-title="{__('catBoxNumTooltip')}">
                                            <i class="fa fa-question"></i>
                                        </button>
                                    </span>
                                </div>
                                {foreach $oSprachen_arr as $oSprache}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="title-{$oSprache->cISO}">{__('boxTitle')} {$oSprache->cNameDeutsch}:</label>
                                        </span>
                                        <input class="form-control" id="title-{$oSprache->cISO}" type="text"
                                               name="title[{$oSprache->cISO}]"
                                               value="{foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}">
                                    </div>
                                {/foreach}
                            {elseif $oEditBox->eTyp === 'link'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="linkID">{__('linkgroup')}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select class="form-control" id="linkID" name="linkID" required>
                                            <option value="" {if $oEditBox->kCustomID == 0}selected="selected"{/if}>{__('FillOut')}</option>
                                            {foreach $oLink_arr as $link}
                                                <option value="{$link->getID()}" {if $link->getID() == $oEditBox->kCustomID}selected="selected"{/if}>
                                                    {$link->getName()}
                                                </option>
                                            {/foreach}
                                        </select>
                                    </span>
                                </div>
                                {foreach $oSprachen_arr as $oSprache}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="title-{$oSprache->cISO}">{__('boxTitle')} {$oSprache->cNameDeutsch}</label>
                                        </span>
                                        <input class="form-control" id="title-{$oSprache->cISO}" type="text" name="title[{$oSprache->cISO}]" value="{foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}" />
                                    </div>
                                {/foreach}
                            {/if}
                            <input type="hidden" name="item" id="editor_id" value="{$oEditBox->kBox}" />
                            <input type="hidden" name="action" value="edit" />
                            <input type="hidden" name="typ" value="{$oEditBox->eTyp}" />
                            <input type="hidden" name="page" value="{$nPage}" />
                            {if !empty($oEditBox->kBox) && $oEditBox->supportsRevisions === true}
                                {getRevisions type='box' key=$oEditBox->kBox show=['cTitel', 'cInhalt'] secondary=true data=$revisionData}
                            {/if}
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                                <button type="button" onclick="window.location.href='boxen.php'" class="btn btn-default"><i class="fa fa-angle-double-left"></i> {__('cancel')}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        {else}
            <div class="">
                <form name="boxen" method="post" action="boxen.php">
                    {$jtl_token}
                    <div class="input-group p25 left">
                        <span class="input-group-addon">
                            <label for="{__('page')}">{__('page')}:</label>
                        </span>
                        <span class="input-group-wrap last">
                            <select name="page" class="selectBox form-control" id="{__('page')}" onchange="document.boxen.submit();">
                                {include file='tpl_inc/seiten_liste.tpl'}
                            </select>
                        </span>
                        <input type="hidden" name="boxen" value="1" />
                    </div>
                </form>
            </div>

            <div class="boxWrapper row">
                {include file='tpl_inc/boxen_side.tpl'}
                {include file='tpl_inc/boxen_middle.tpl'}
            </div>
        {/if}
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}