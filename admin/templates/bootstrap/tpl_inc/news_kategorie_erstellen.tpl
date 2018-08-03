<script type="text/javascript">
    var file2large = false;
    {literal}

    $(document).ready(function () {
        $('#lang').change(function () {
            var iso = $('#lang option:selected').val();
            $('.iso_wrapper').slideUp();
            $('#iso_' + iso).slideDown();
            return false;
        });
        $('form input[type=file]').change(function(e){
            $('form div.alert').slideUp();
            var filesize= this.files[0].size;
            {/literal}
            var maxsize = {$nMaxFileSize};
            {literal}
            if (filesize >= maxsize) {
                $(this).after('<div class="alert alert-danger"><i class="fa fa-warning"></i> Die Datei ist gr&ouml;&szlig;er als das Uploadlimit des Servers.</div>').slideDown();
                file2large = true;
            } else {
                $(this).closest('div.alert').slideUp();
                file2large = false;
            }
        });

    });

    function checkfile(e){
        e.preventDefault();
        if (!file2large){
            document.news.submit();
        }
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#newsCat#}
<div id="content">
    <form name="news" method="post" action="news.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="news_kategorie_speichern" value="1" />
        <input type="hidden" name="tab" value="kategorien" />
        {if $oNewsKategorie->getID() > 0}
            <input type="hidden" name="newskategorie_edit_speichern" value="1" />
            <input type="hidden" name="kNewsKategorie" value="{$oNewsKategorie->getID()}" />
            {if isset($cSeite)}
                <input type="hidden" name="s3" value="{$cSeite}" />
            {/if}
        {/if}
        <div class="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{if $oNewsKategorie->getID() > 0}{#newsCatNew#}{else}{#newsCatAdd#}{/if}</h3>
                </div>
                <div class="table-responsive">
                    <div class="panel-body" id="formtable">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kParent">{#newsCatParent#}</label>
                            </span>
                            <select class="form-control" id="kParent" name="kParent">
                                <option value="0"> - Hauptkategorie - </option>
                                {if $oNewsKategorie->getParentID()}
                                    {assign var='selectedCat' value=$oNewsKategorie->getParentID()}
                                {else}
                                    {assign var='selectedCat' value=0}
                                {/if}
                                {include file='snippets/newscategories_recursive.tpl' i=0 selectedCat=$selectedCat}
                            </select>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nSort">{#newsCatSort#}</label>
                            </span>
                            <input class="form-control{if !empty($cPlausiValue_arr.nSort)} error{/if}" id="nSort" name="nSort" type="text" value="{if isset($cPostVar_arr.nSort)}{$cPostVar_arr.nSort}{else}{$oNewsKategorie->getSort()}{/if}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAktiv">{#newsActive#}</label>
                            </span>
                            <select class="form-control" id="nAktiv" name="nAktiv">
                                <option value="1"{if (isset($cPostVar_arr.nAktiv) && $cPostVar_arr.nAktiv == "1") || ($oNewsKategorie->getIsActive() === true)} selected{/if}>
                                    Ja
                                </option>
                                <option value="0"{if (isset($cPostVar_arr.nAktiv) && $cPostVar_arr.nAktiv == "0") || ($oNewsKategorie->getIsActive() === false)} selected{/if}>
                                    Nein
                                </option>
                            </select>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="previewImage">{#newsPreview#}</label>
                            </span>
                            {if !empty($oNewsKategorie->cPreviewImage)}
                                <img src="{$shopURL}/{$oNewsKategorie->cPreviewImage}" alt="" height="20" width="20" class="preview-image left" style="margin-right: 10px;" />
                            {/if}
                            <input id="previewImage" name="previewImage" type="file" maxlength="2097152" accept="image/*" />
                            <input name="previewImage" type="hidden" value="{if !empty($oNewsKategorie->cPreviewImage)}{$oNewsKategorie->cPreviewImage}{/if}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label>{#newsPics#}</label>
                            </span>
                            {if isset($oDatei_arr) && $oDatei_arr|@count > 0}
                                <div>
                                    {foreach name=bilder from=$oDatei_arr item=oDatei}
                                        <div class="well col-xs-3">
                                            <div class="thumbnail">{$oDatei->cURL}</div>
                                            <label>Link: </label>
                                            <div class="input-group">
                                                <input class="form-control" type="text" disabled="disabled" value="$#{$oDatei->cName}#$">
                                                <div class="input-group-addon">
                                                    <a href="news.php?news=1&newskategorie_editieren=1&kNewsKategorie={$oNewsKategorie->getID()}&delpic={$oDatei->cName}&token={$smarty.session.jtl_token}" title="{#delete#}"><i class="fa fa-trash"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            {else}
                                <div></div>
                            {/if}
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="lang">Sprache</label>
                            </span>
                            <span class="input-group-wrap">
                                <select class="form-control" name="cISO" id="lang">
                                    {foreach $sprachen as $sprache}
                                        <option value="{$sprache->cISO}" {if $sprache->cShopStandard === 'Y'}selected="selected"{/if}>{$sprache->cNameDeutsch} {if $sprache->cShopStandard === 'Y'}(Standard){/if}</option>
                                    {/foreach}
                                </select>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            {foreach name=sprachen from=$sprachen item=sprache}
                {assign var='cISO' value=$sprache->cISO}
                {assign var='langID' value=$sprache->kSprache}
                <input type="hidden" name="lang_{$cISO}" value="{$sprache->kSprache}">
                <div id="iso_{$cISO}" class="iso_wrapper{if $sprache->cShopStandard !== 'Y'} hidden-soft{/if}">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Meta/Seo ({$sprache->cNameDeutsch})</h3>
                        </div>
                        <div class="table-responsive">
                            <div class="panel-body" id="formtable">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cName_{$cISO}">{#newsCatName#}</label>
                                    </span>
                                    <input class="form-control{if !empty($cPlausiValue_arr.cName)} error{/if}" id="cName_{$cISO}" name="cName_{$cISO}" type="text" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif $oNewsKategorie->getName($langID) !== ''}{$oNewsKategorie->getName($langID)}{/if}" />{if isset($cPlausiValue_arr.cName) && $cPlausiValue_arr.cName == 2} {#newsAlreadyExists#}{/if}
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cSeo_{$cISO}">{#newsSeo#}</label>
                                    </span>
                                    <input class="form-control{if !empty($cPlausiValue_arr.cSeo)} error{/if}" id="cSeo_{$cISO}" name="cSeo_{$cISO}" type="text" value="{if isset($cPostVar_arr.cSeo)}{$cPostVar_arr.cSeo}{elseif $oNewsKategorie->getSEO($langID) !== ''}{$oNewsKategorie->getSEO($langID)}{/if}" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cMetaTitle_{$cISO}">{#newsMetaTitle#}</label>
                                    </span>
                                    <input class="form-control{if !empty($cPlausiValue_arr.cMetaTitle)} error{/if}" id="cMetaTitle_{$cISO}" name="cMetaTitle_{$cISO}" type="text" value="{if isset($cPostVar_arr.cMetaTitle)}{$cPostVar_arr.cMetaTitle}{else}{$oNewsKategorie->getMetaTitle($langID)}{/if}" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cMetaDescription_{$cISO}">{#newsMetaDescription#}</label>
                                    </span>
                                    <input class="form-control{if !empty($cPlausiValue_arr.cMetaDescription)} error{/if}" id="cMetaDescription_{$cISO}" name="cMetaDescription_{$cISO}" type="text" value="{if isset($cPostVar_arr.cMetaDescription)}{$cPostVar_arr.cMetaDescription}{else}{$oNewsKategorie->getMetaDescription($langID)}{/if}" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="previewImage">{#newsPreview#}</label>
                                    </span>
                                    {if !empty($oNewsKategorie->cPreviewImage)}
                                        <img src="{$shopURL}/{$oNewsKategorie->cPreviewImage}" alt="" height="20" width="20" class="preview-image left" style="margin-right: 10px;" />
                                    {/if}
                                    <input id="previewImage" name="previewImage" type="file" maxlength="2097152" accept="image/*" />
                                    <input name="previewImage" type="hidden" value="{$oNewsKategorie->getPreviewImage($langID)}" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cBeschreibung_{$cISO}">{#newsCatDesc#}</label>
                                    </span>
                                    <textarea id="cBeschreibung_{$cISO}" class="ckeditor" name="cBeschreibung_{$cISO}" rows="15" cols="60">{if isset($cPostVar_arr.cBeschreibung)}{$cPostVar_arr.cBeschreibung}{else}{$oNewsKategorie->getDescription($langID)}{/if}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <span class="btn-group">
                                <button name="speichern" type="button" value="{#newsSave#}" onclick="document.news.submit();" class="btn btn-primary"><i class="fa fa-save"></i> {#newsSave#}</button>
                                <a class="btn btn-danger" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-exclamation"></i> Abbrechen</a>
                            </span>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </form>
</div>