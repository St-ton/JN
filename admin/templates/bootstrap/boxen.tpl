{config_load file="$lang.conf" section="boxen"}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=#boxen# cBeschreibung=#boxenDesc# cDokuURL=#boxenURL#}

<script type="text/javascript">
    $(function() {
        $('#boxFilterModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget),
                    filter = button.data('filter'),
                    boxTitle = button.data('box-title'),
                    boxID = button.data('box-id'),
                    modal = $(this);
            modal.find('#myModalLabel').text('{#showBoxOnlyFor#}'.replace('%s', boxTitle));
            modal.find('#filter-target').val(filter);
            modal.find('#filter-target-id').val(boxID);
            $('#selected-items').append($('#box-active-filters-' + boxID).find('.selected-item').clone());
        }).on('hide.bs.modal', function (event) {
            $('#boxFilterModal .selected-item').remove(); //cleanup selected items
            $('#boxFilterModal .filter-input').val(''); //cleanup input
        });
        {if $nPage == 1}
            enableTypeahead('#products', 'getProducts', 'cName', 'kArtikel', function (item) { onSelect(item, '#selected-items', '#products'); });
        {elseif $nPage == 31}
            enableTypeahead('#pages', 'getPages', 'cName', 'kLink', function (item) { onSelect(item, '#selected-items', '#pages'); });
        {elseif $nPage == 2}
            enableTypeahead('#categories', 'getCategories', 'cName', 'kKategorie', function (item) { onSelect(item, '#selected-items', '#categories'); });
        {elseif $nPage == 24}
            enableTypeahead('#manufacturers', 'getManufacturers', 'cName', 'kHersteller', function (item) { onSelect(item, '#selected-items', '#manufacturers'); });
        {/if}

        $('#modal-save').click(function () {
            var idList = $('#modal-filter-form .new-filter'),
                    numElements = idList.length,
                    boxID = $('#filter-target-id').val(),
                    target,
                    targetSelector = $('#filter-target').val();

            if (targetSelector) {
                $('#box-active-filters-' + boxID).empty().append($('#boxFilterModal .selected-item'));
                $('#boxFilterModal').modal('hide'); //hide modal
            }
        });

        $('#modal-cancel').click(function () {
            $('#boxFilterModal').modal('hide'); //hide modal
        });

        $('#boxFilterModal .selected-items').on('click', 'a', function (e) {
            e.preventDefault();
            $('#elem-' + $(this).attr('data-ref')).remove();
            return false;
        });
    });

    function onSelect (item, selectorAdd, selectorRemove) {
        if (item.value > 0) {
            var button = $('<a />'),
                input = $('<input />'),
                element = $('<li />'),
                boxID = $('#filter-target-id').val();
            input.attr({ 'class': 'new-filter', type: 'hidden', name: 'box-filter-' + boxID + '[]', value: item.value });
            element.attr({ 'class': 'selected-item', id: 'elem-' + item.value });
            button.attr({ 'class': 'btn btn-default btn-xs', href: '#', 'data-ref': item.value }).html('<i class="fa fa-trash"></i>');
            element.append(button).append(' ' + item.text).append(input);
            $(selectorAdd).append(element);
        }
    }

    function confirmDelete(cName) {
        return confirm('{#confirmDeleteBox#}'.replace('%s', cName));
    }

    function onFocus(obj) {
       obj.id = obj.value;
       obj.value = '';
    }

    function onBlur(obj) {
       if (obj.value.length === 0) {
           obj.value = obj.id;
       }
    }
</script>
<div class="modal fade" id="boxFilterModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"></h4>
            </div>
            <div class="modal-body">
                <form id="modal-filter-form">
                    {$jtl_token}
                    <input id="filter-target" type="hidden" />
                    <input id="filter-target-id" type="hidden" />
                    {if $nPage == 1}
                        <input id="products" type="text" class="filter-input form-control" placeholder="{#products#}..." autocomplete="off" />
                        <ul id="selected-products" class="selected-items"></ul>
                    {elseif $nPage == 31}
                        <input id="pages" type="text" class="filter-input form-control" placeholder="{#pages#}..." autocomplete="off" />
                        <ul id="selected-pages" class="selected-items"></ul>
                    {elseif $nPage == 2}
                        <input id="categories" type="text" class="filter-input form-control" placeholder="{#categories#}..." autocomplete="off" />
                        <ul id="selected-categories" class="selected-items"></ul>
                    {elseif $nPage == 24}
                        <input id="manufacturers" type="text" class="filter-input form-control" placeholder="{#manufacturers#}..." autocomplete="off" />
                        <ul id="selected-manufacturers" class="selected-items"></ul>
                    {/if}
                    <ul id="selected-items" class="selected-items"></ul>
                </form>
            </div>
            <div class="modal-footer">
                <span class="btn-group">
                    <button type="button" class="btn btn-default" id="modal-cancel"><i class="fa fa-times"></i> {#cancel#}</button>
                    <button type="button" class="btn btn-primary" id="modal-save"><i class="fa fa-save"></i> {#apply#}</button>
                </span>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->

<div id="content">
    {if $invisibleBoxes|count > 0}
        <div class="alert alert-danger">{#warningInvisibleBoxes#}</div>
        <form action="boxen.php" method="post">
            {$jtl_token}
            <div class="panel panel-default editorInner">
                <div class="panel-heading">
                    <h3 class="panel-title">{#invisibleBoxes#}</h3>
                </div>
                <table class="table">
                    <tr class="boxRow">
                        <th class="check">&nbsp;</th>
                        <th>
                            <strong>{#boxTitle#}</strong>
                        </th>
                        <th>
                            <strong>{#boxLabel#}</strong>
                        </th>
                        <th>
                            <strong>{#boxTemplate#}</strong>
                        </th>
                        <th>
                            <strong>{#boxPosition#}</strong>
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
                        <td colspan="4" class="tleft"><label for="ALLMSGS">{#globalSelectAll#}</label></td>
                    </tr>
                </table>
                <div class="panel-footer">
                    <button name="action" type="submit" class="btn btn-danger" value="delete-invisible"><i class="fa fa-trash"></i> {#deleteSelected#}</button>
                </div>
            </div>
        </form>
    {/if}
    {if !is_array($oBoxenContainer) || $oBoxenContainer|@count == 0}
        <div class="alert alert-danger">{#noTemplateConfig#}</div>
    {elseif !$oBoxenContainer.left && !$oBoxenContainer.right && !$oBoxenContainer.top && !$oBoxenContainer.bottom}
        <div class="alert alert-danger">{#noBoxActivated#}</div>
    {else}
        {if isset($oEditBox) && $oEditBox}
            <div id="editor" class="editor">
                <form action="boxen.php" method="post">
                    {$jtl_token}
                    <div class="panel panel-default editorInner">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#boxEdit#}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="boxtitle">{#boxTitle#}:</label>
                                </span>
                                <input class="form-control" id="boxtitle" type="text" name="boxtitle" value="{$oEditBox->cTitel}" />
                            </div>
                            {if $oEditBox->eTyp === 'text'}
                                {foreach name="sprachen" from=$oSprachen_arr item=oSprache}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="title-{$oSprache->cISO}">{#boxTitle#} {$oSprache->cNameDeutsch}</label>
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
                                        <label for="linkID">{#catBoxNum#}</label>
                                    </span>
                                    <input class="form-control" id="linkID" type="text" name="linkID" value="{$oEditBox->kCustomID}" size="3">
                                    <span class="input-group-addon">
                                        <button type="button" class="btn-tooltip btn btn-info btn-heading"
                                                data-html="true" data-toggle="tooltip" data-placement="left" title=""
                                                data-original-title="{#catBoxNumTooltip#}">
                                            <i class="fa fa-question"></i>
                                        </button>
                                    </span>
                                </div>
                                {foreach name="sprachen" from=$oSprachen_arr item=oSprache}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="title-{$oSprache->cISO}">{#boxTitle#} {$oSprache->cNameDeutsch}:</label>
                                        </span>
                                        <input class="form-control" id="title-{$oSprache->cISO}" type="text"
                                               name="title[{$oSprache->cISO}]"
                                               value="{foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}">
                                    </div>
                                {/foreach}
                            {elseif $oEditBox->eTyp === 'link'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="linkID">{#linkgroup#}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select class="form-control" id="linkID" name="linkID">
                                            {foreach from=$oLink_arr item=oLink}
                                                <option value="{$oLink->kLinkgruppe}" {if $oLink->kLinkgruppe == $oEditBox->kCustomID}selected="selected"{/if}>{$oLink->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                </div>
                            {/if}
                            <input type="hidden" name="item" id="editor_id" value="{$oEditBox->kBox}" />
                            <input type="hidden" name="action" value="edit" />
                            <input type="hidden" name="typ" value="{$oEditBox->eTyp}" />
                            <input type="hidden" name="page" value="{$nPage}" />
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                                <button type="button" onclick="window.location.href='boxen.php'" class="btn btn-default"><i class="fa fa-angle-double-left"></i> {#cancel#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        {else}
            <div class="block">
                <form name="boxen" method="post" action="boxen.php">
                    {$jtl_token}
                    <div class="input-group p25 left">
                        <span class="input-group-addon">
                            <label for="{#page#}">{#page#}:</label>
                        </span>
                        <span class="input-group-wrap last">
                            <select name="page" class="selectBox form-control" id="{#page#}" onchange="document.boxen.submit();">
                                {include file="tpl_inc/seiten_liste.tpl"}
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

<script type="text/javascript">
</script>
{include file='tpl_inc/footer.tpl'}