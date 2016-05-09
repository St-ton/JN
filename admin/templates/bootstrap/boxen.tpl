{config_load file="$lang.conf" section="boxen"}
{include file='tpl_inc/header.tpl'}

<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>
<script type="text/javascript">
{literal}
function confirmDelete(cName) {
    return confirm('Sind Sie sicher, dass Sie die Box "' + cName + '" l\u00f6schen m\u00f6chten?');
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
{/literal}
</script>
<div class="modal fade" id="boxFilterModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div class="modal-body">
                <form id="modal-filter-form">
                    {$jtl_token}
                    <input id="filter-target" type="hidden" />
                    <input id="filter-target-id" type="hidden" />
                    {if $nPage == 1}
                        <input id="products" type="text" class="filter-input form-control" placeholder="Produkt..." autocomplete="off" />
                        <ul id="selected-products" class="selected-items"></ul>
                    {elseif $nPage == 31}
                        <input id="pages" type="text" class="filter-input form-control" placeholder="Seiten..." autocomplete="off" />
                        <ul id="selected-pages" class="selected-items"></ul>
                    {elseif $nPage == 2}
                        <input id="categories" type="text" class="filter-input form-control" placeholder="Kategorien..." autocomplete="off" />
                        <ul id="selected-categories" class="selected-items"></ul>
                    {elseif $nPage == 24}
                        <input id="manufacturers" type="text" class="filter-input form-control" placeholder="Hersteller..." autocomplete="off" />
                        <ul id="selected-manufacturers" class="selected-items"></ul>
                    {/if}
                    <ul id="selected-items" class="selected-items"></ul>
                </form>
            </div>
            <div class="modal-footer">
                <span class="btn-group">
                    <button type="button" class="btn btn-default" id="modal-cancel"><i class="fa fa-times"></i> abbrechen</button>
                    <button type="button" class="btn btn-primary" id="modal-save"><i class="fa fa-save"></i> speichern</button>
                </span>
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/seite_header.tpl' cTitel=#boxen# cBeschreibung=#boxenDesc# cDokuURL=#boxenURL#}
<!-- Modal -->

<div id="content">
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
                                    <label for="boxtitle">Titel:</label>
                                </span>
                                <input class="form-control" id="boxtitle" type="text" name="boxtitle" value="{$oEditBox->cTitel}" />
                            </div>
                            {if $oEditBox->eTyp === 'text'}
                                {foreach name="sprachen" from=$oSprachen_arr item=oSprache}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="title-{$oSprache->cISO}">Titel {$oSprache->cNameDeutsch}</label>
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
                                        <label for="linkID">Kategoriebox-Nummer</label>
                                    </span>
                                    <input class="form-control" id="linkID" type="text" name="linkID" value="{$oEditBox->kCustomID}" size="3" />
                                    <span class="input-group-addon">
                                        <button type="button" class="btn-tooltip btn btn-info btn-heading" data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="Listet nur die Kategorien mit dem Wawi-Kategorieattribut 'kategoriebox' und der gesetzten Nummer. Standard=0 f&uuml;r alle Kategorien mit oder ohne Funktionsattribut."><i class="fa fa-question"></i></button>
                                    </span>
                                </div>
                                {foreach name="sprachen" from=$oSprachen_arr item=oSprache}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="title-{$oSprache->cISO}">Titel {$oSprache->cNameDeutsch}:</label>
                                        </span>
                                        <input class="form-control" id="title-{$oSprache->cISO}" type="text" name="title[{$oSprache->cISO}]" value="{foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}" />
                                    </div>
                                {/foreach}
                            {elseif $oEditBox->eTyp === 'link'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="linkID">Linkgruppe</label>
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
                                <button type="button" onclick="window.location.href='boxen.php'" class="btn btn-default"><i class="fa fa-angle-double-left"></i> Abbrechen</button>
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
                {if isset($oBoxenContainer.left) && $oBoxenContainer.left === true}
                    {include file='tpl_inc/boxen_left.tpl'}
                {/if}
                {if isset($oBoxenContainer.right) && $oBoxenContainer.right === true}
                    {include file='tpl_inc/boxen_right.tpl'}
                {/if}
                {include file='tpl_inc/boxen_middle.tpl'}
            </div>
        {/if}
    {/if}
</div>

<script type="text/javascript">
    $(function() {ldelim}
        $('#boxFilterModal').on('show.bs.modal', function (event) {ldelim}
            var button = $(event.relatedTarget),
                filter = button.data('filter'),
                boxTitle = button.data('box-title'),
                boxID = button.data('box-id'),
                modal = $(this);
            modal.find('.modal-title').text('Filter Box ' + boxTitle);
            modal.find('#filter-target').val(filter);
            modal.find('#filter-target-id').val(boxID);
            $('#boxFilterModal #selected-items').append($('#box-active-filters-' + boxID).find('.selected-item').clone());
        {rdelim}).on('hide.bs.modal', function (event) {ldelim}
            $('#boxFilterModal .selected-item').remove(); //cleanup selected items
            $('#boxFilterModal .filter-input').val(''); //cleanup input
        {rdelim});

        function onSelect (item, selectorAdd, selectorRemove) {ldelim}
            if (item.value > 0) {ldelim}
                var button = $('<a />'),
                    text = $('<span />'),
                    input = $('<input />'),
                    element = $('<li />'),
                    boxID = $('#filter-target-id').val();
                input.attr('class', 'new-filter').attr('type', 'hidden').attr('name', 'box-filter-' + boxID + '[]').attr('value', item.value);
                element.addClass('selected-item').attr('id', 'elem-' + item.value);
                button.attr('href', '#').attr('data-ref', item.value).html('<i class="fa fa-trash"></i>');
                text.html(item.text);
                element.append(button).append(text).append(input);
                $(selectorAdd).append(element);
            {rdelim}
        {rdelim}
        {if $nPage == 1}
        $('#products').typeahead({ldelim}
            ajax: '{$shopURL}/{$PFAD_ADMIN}ajax.php?type=product&token={$smarty.session.jtl_token}',
            onSelect: function (item) {ldelim}
                onSelect(item, '#selected-items', '#products');
                {rdelim}
            {rdelim});
        {elseif $nPage == 31}
        $('#pages').typeahead({ldelim}
            ajax: '{$shopURL}/{$PFAD_ADMIN}ajax.php?type=page&token={$smarty.session.jtl_token}',
            onSelect: function (item) {ldelim}
                onSelect(item, '#selected-items', '#pages');
                {rdelim}
            {rdelim});
        {elseif $nPage == 2}
        $('#categories').typeahead({ldelim}
            ajax: '{$shopURL}/{$PFAD_ADMIN}ajax.php?type=category&token={$smarty.session.jtl_token}',
            onSelect: function (item) {ldelim}
                onSelect(item, '#selected-items', '#categories');
                {rdelim}
            {rdelim});
        {elseif $nPage == 24}
        $('#manufacturers').typeahead({ldelim}
            ajax: '{$shopURL}/{$PFAD_ADMIN}ajax.php?type=manufacturer&token={$smarty.session.jtl_token}',
            onSelect: function (item) {ldelim}
                onSelect(item, '#selected-items', '#manufacturers');
            {rdelim}
        {rdelim});
        {/if}

        $('#modal-save').click(function () {ldelim}
            var idList = $('#modal-filter-form .new-filter'),
                numElements = idList.length,
                boxID = $('#filter-target-id').val(),
                target,
                targetSelector = $('#filter-target').val();

            if (targetSelector) {ldelim}
                $('#box-active-filters-' + boxID).empty().append($('#boxFilterModal .selected-item'));
                $('#boxFilterModal').modal('hide'); //hide modal
            {rdelim}
        {rdelim});

        $('#modal-cancel').click(function () {ldelim}
            $('#boxFilterModal').modal('hide'); //hide modal
        {rdelim});

        $('#boxFilterModal .selected-items').on('click', 'a', function (e) {ldelim}
            e.preventDefault();
            $('#elem-' + $(this).attr('data-ref')).remove();
            return false;
        {rdelim});
    {rdelim});
</script>
{include file='tpl_inc/footer.tpl'}