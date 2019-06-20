{if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
    {assign var=cTitel value=__('auswahlassistent')|cat:' - '|cat:__('aaGroupEdit')}
{else}
    {assign var=cTitel value=__('auswahlassistent')|cat:' - '|cat:__('aaGroup')}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('auswahlassistentDesc') cDokuURL=__('auswahlassistentURL')}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <form class="settings" method="post" action="auswahlassistent.php">
            {$jtl_token}
            <input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
            <input name="tab" type="hidden" value="gruppe">
            <input name="a" type="hidden" value="addGrp">
            {if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
                <input class="form-control" name="kAuswahlAssistentGruppe" type="hidden"
                       value="{if isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0}{$kAuswahlAssistentGruppe}{else}{$oGruppe->kAuswahlAssistentGruppe}{/if}">
            {/if}
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cName">{__('name')}{if isset($cPlausi_arr.cName)} <span class="fillout">{__('FillOut')}</span>{/if}</label>
                    </span>
                        <input name="cName" id="cName" type="text"
                               class="form-control{if isset($cPlausi_arr.cName)} fieldfillout{/if}"
                               value="{if isset($cPost_arr.cName)}{$cPost_arr.cName}{elseif isset($oGruppe->cName)}{$oGruppe->cName}{/if}">
                        <span class="input-group-addon">{getHelpDesc cDesc="{__('hintName')}"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cBeschreibung">{__('description')}</label>
                        </span>
                        <textarea id="cBeschreibung" name="cBeschreibung"
                                  class="form-control description">{if isset($cPost_arr.cBeschreibung)}{$cPost_arr.cBeschreibung}{elseif isset($oGruppe->cBeschreibung)}{$oGruppe->cBeschreibung}{/if}</textarea>
                        <span class="input-group-addon">{getHelpDesc cDesc="{__('hintDesc')}"}</span>
                    </div>

                    {include file='tpl_inc/searchpicker_modal.tpl'
                        searchPickerName='categoryPicker'
                        modalTitle="{__('titleChooseCategory')}"
                        searchInputLabel="{__('labelSearchCategory')}"
                    }
                    <script>
                        $(function () {
                            categoryPicker = new SearchPicker({
                                searchPickerName:  'categoryPicker',
                                getDataIoFuncName: 'getCategories',
                                keyName:           'kKategorie',
                                renderItemCb:      renderCategoryItem,
                                onApply:           onApplySelectedCategories,
                                selectedKeysInit:  $('#assign_categories_list').val().split(';').filter(function (i) { return i !== ''; })
                            });
                        });
                        function renderCategoryItem(item)
                        {
                            return '<p class="list-group-item-text">' + item.cName + '</p>';
                        }
                        function onApplySelectedCategories(selected)
                        {
                            $('#assign_categories_list').val(selected.join(';'));
                        }
                    </script>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="assign_categories_list">{__('category')}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{__('FillOut')}</span>{/if}
                                {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie != 3} <span class="fillout">{__('aaKatSyntax')}</span>{/if}
                                {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie == 3} <span class="fillout">{__('aaKatTaken')}</span>{/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            <input name="cKategorie" id="assign_categories_list" type="text"
                                   class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}"
                                   value="{if isset($cPost_arr.cKategorie)}{$cPost_arr.cKategorie}{elseif isset($oGruppe->cKategorie)}{$oGruppe->cKategorie}{/if}">
                        </span>
                        <span class="input-group-addon">
                            <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                    data-target="#categoryPicker-modal"
                                    title="{__('questionCatInGroup')}">
                                <i class="fa fa-edit"></i>
                            </button>
                        </span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kLink_arr">{__('aaSpecialSite')}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{__('FillOut')}</span>{/if}
                                {if isset($cPlausi_arr.kLink_arr)} <span class="fillout">{__('aaLinkTaken')}</span>{/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            {if $oLink_arr|count > 0}
                                <select id="kLink_arr" name="kLink_arr[]"  class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}" multiple>
                                    {foreach $oLink_arr as $oLink}
                                        {assign var=bAOSelect value=false}
                                        {if isset($oGruppe->oAuswahlAssistentOrt_arr) && $oGruppe->oAuswahlAssistentOrt_arr|@count > 0}
                                            {foreach $oGruppe->oAuswahlAssistentOrt_arr as $oAuswahlAssistentOrt}
                                                {if $oLink->kLink == $oAuswahlAssistentOrt->kKey && $oAuswahlAssistentOrt->cKey == $smarty.const.AUSWAHLASSISTENT_ORT_LINK}
                                                    {assign var=bAOSelect value=true}
                                                {/if}
                                            {/foreach}
                                        {elseif isset($cPost_arr.kLink_arr) && $cPost_arr.kLink_arr|@count > 0}
                                            {foreach $cPost_arr.kLink_arr as $kLink}
                                                {if $kLink == $oLink->kLink}
                                                    {assign var=bAOSelect value=true}
                                                {/if}
                                            {/foreach}
                                        {/if}
                                        <option value="{$oLink->kLink}"{if $bAOSelect} selected{/if}>{$oLink->cName}</option>
                                    {/foreach}
                                </select>
                            {else}
                                <input type="text" disabled value="{__('noSpecialPageAvailable')}" class="form-control" />
                            {/if}
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc="{__('hintSpecialPage')}"}
                        </span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nStartseite">{__('startPage')}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{__('FillOut')}</span>{/if}
                                {if isset($cPlausi_arr.nStartseite)} <span class="fillout">{__('aaStartseiteTaken')}</span>{/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nStartseite" name="nStartseite"  class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}">
                                <option value="0"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 0) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 0)} selected{/if}>
                                    Nein
                                </option>
                                <option value="1"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 1) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 1)} selected{/if}>
                                    Ja
                                </option>
                            </select>
                        </span>
                        <span class="input-group-addon">{getHelpDesc cDesc="{__('hintGroupOnHome')}"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nAktiv">{__('active')}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nAktiv" class="form-control" name="nAktiv">
                                <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 1)} selected{/if}>
                                    Ja
                                </option>
                                <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 0)} selected{/if}>
                                    Nein
                                </option>
                            </select>
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc="{__('hintShowCheckbox')}"}
                        </span>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="btn-group">
                        <button name="speicherGruppe" type="submit" value="save" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                        <a href="auswahlassistent.php" class="btn btn-danger">{__('goBack')}</a>
                    </div>
                </div>
            </div>
            <div id="ajax_list_picker" class="ajax_list_picker categories">{include file='tpl_inc/popup_kategoriesuche.tpl'}</div>
        </form>
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}
