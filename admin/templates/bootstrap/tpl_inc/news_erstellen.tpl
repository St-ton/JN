<script type="text/javascript">
    var i = 10,
        j = 2,
        file2large = false;

    function addInputRow() {ldelim}
        var row = document.getElementById('formtable').insertRow(i),
                cell_1,
                cell_2,
                input1,
                label,
                myText;
        row.id = '' + i;
        row.valign = 'top';

        cell_1 = row.insertCell(0);
        cell_2 = row.insertCell(1);
        input1 = document.createElement('input');
        input1.type = 'file';
        input1.name = 'Bilder[]';
        input1.className = 'field';
        input1.id = 'Bilder_' + i;
        input1.maxlength = '2097152';
        input1.accept = 'image/*';
        label = document.createElement('label');
        label.setAttribute('for', 'Bilder_' + i);
        myText = document.createTextNode('Bild ' + j + ':');
        label.appendChild(myText);
        cell_1.appendChild(label);
        cell_2.appendChild(input1);
        i += 1;
        j += 1;
    {rdelim}
    {literal}

    $(document).ready(function () {
        $('#lang').change(function () {
            var iso = $('#lang option:selected').val();
            $('.iso_wrapper').slideUp();
            $('#iso_' + iso).slideDown();
            return false;
        });

        $('input[name="nLinkart"]').change(function () {
            var lnk = $('input[name="nLinkart"]:checked').val();
            if (lnk == '1') {
                $('#option_isActive').slideDown("slow");
            } else {
                $('#option_isActive').slideUp("slow");
                $('#option_isActive select').val(1);
            }
        }).trigger('change');
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

{include file='tpl_inc/seite_header.tpl' cTitel=#news# cBeschreibung=#newsDesc#}
<div id="content" class="container-fluid">
    <form name="news" method="post" action="news.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="news_speichern" value="1" />
        <input type="hidden" name="tab" value="aktiv" />
        {if $oNews->getID() > 0}
            <input type="hidden" name="news_edit_speichern" value="1" />
            <input type="hidden" name="kNews" value="{$oNews->getID()}" />
            {if isset($cSeite)}
                <input type="hidden" name="s2" value="{$cSeite}" />
            {/if}
        {/if}
        <div class="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{if $oNews->getID() > 0}{#newsEdit#}{else}{#newAdd#}{/if}</h3>
                </div>
                <div class="table-responsive">
                    <div id="formtable" class="panel-body">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kkundengruppe">{#newsCustomerGrp#} *</label>
                            </span>
                            <select id="kkundengruppe" name="kKundengruppe[]" multiple="multiple" class="form-control{if !empty($cPlausiValue_arr.kKundengruppe_arr)} error{/if}">
                                <option value="-1"
                                    {if isset($cPostVar_arr.kKundengruppe)}
                                        {foreach $cPostVar_arr.kKundengruppe as $kKundengruppe}
                                            {if $kKundengruppe == "-1"}selected{/if}
                                        {/foreach}
                                    {else}
                                        {foreach $oNews->getCustomerGroups() as $kKundengruppe}
                                            {if $kKundengruppe === -1}selected{/if}
                                        {/foreach}
                                    {/if}>
                                    Alle
                                </option>
                                {foreach $oKundengruppe_arr as $oKundengruppe}
                                    <option value="{$oKundengruppe->kKundengruppe}"
                                        {if isset($cPostVar_arr.kKundengruppe)}
                                            {foreach $cPostVar_arr.kKundengruppe as $kKundengruppe}
                                                {if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
                                            {/foreach}
                                        {else}
                                            {foreach $oNews->getCustomerGroups() as $kKundengruppe}
                                                {if $oKundengruppe->kKundengruppe === $kKundengruppe}selected{/if}
                                            {/foreach}
                                        {/if}>{$oKundengruppe->cName}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kNewsKategorie">{#newsCat#} *</label>
                            </span>
                            <select id="kNewsKategorie" class="form-control{if !empty($cPlausiValue_arr.kNewsKategorie_arr)} error{/if}" name="kNewsKategorie[]" multiple="multiple">
                                {foreach $oNewsKategorie_arr as $category}
                                    <option value="{$category->getID()}"
                                        {if isset($cPostVar_arr.kNewsKategorie)}
                                            {foreach $cPostVar_arr.kNewsKategorie as $kNewsKategorieNews}
                                                {if $category->getID() == $kNewsKategorieNews}selected{/if}
                                            {/foreach}
                                        {else}
                                            {foreach $oNews->getCategoryIDs() as $categoryID}
                                                {if $category->getID() === $categoryID}selected{/if}
                                            {/foreach}
                                        {/if}>{$category->getName()}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="dGueltigVon">{#newsValidation#} *</label>
                            </span>
                            <input class="form-control" id="dGueltigVon" name="dGueltigVon" type="text" value="{if isset($cPostVar_arr.dGueltigVon) && $cPostVar_arr.dGueltigVon}{$cPostVar_arr.dGueltigVon}{else}{$oNews->getDateValidFrom()->format('d.m.Y H:i')}{/if}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAktiv">{#newsActive#} *</label>
                            </span>
                            <select class="form-control" id="nAktiv" name="nAktiv">
                                <option value="1"{if isset($cPostVar_arr.nAktiv)}{if $cPostVar_arr.nAktiv == 1} selected{/if}{elseif $oNews->getIsActive() === true} selected{/if}>Ja</option>
                                <option value="0"{if isset($cPostVar_arr.nAktiv)}{if $cPostVar_arr.nAktiv == 0} selected{/if}{elseif $oNews->getIsActive() === false} selected{/if}>Nein
                                </option>
                            </select>
                        </div>
                        {if $oPossibleAuthors_arr|count > 0}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kAuthor">{#newsAuthor#}</label>
                            </span>
                                <select class="form-control" id="kAuthor" name="kAuthor">
                                    <option value="0">Autor auswählen</option>
                                    {foreach $oPossibleAuthors_arr as $oPossibleAuthor}
                                        <option value="{$oPossibleAuthor->kAdminlogin}"{if isset($cPostVar_arr.nAuthor)}{if isset($cPostVar_arr.nAuthor) && $cPostVar_arr.nAuthor == $oPossibleAuthor->kAdminlogin} selected="selected"{/if}{elseif isset($oAuthor->kAdminlogin) && $oAuthor->kAdminlogin == $oPossibleAuthor->kAdminlogin} selected="selected"{/if}>{$oPossibleAuthor->cName}</option>
                                    {/foreach}
                                </select>
                        </div>
                        {/if}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="previewImage">{#newsPreview#}</label>
                            </span>
                            <div>
                                {if !empty($oNews->getPreviewImage())}
                                    <img src="{$shopURL}/{$oNews->getPreviewImage()}" alt="" height="20" width="20" class="preview-image left" style="margin-right: 10px;" />
                                {/if}
                                <input id="previewImage" name="previewImage" type="file" maxlength="2097152" accept="image/*" />
                                <input name="previewImage" type="hidden" value="{if !empty($oNews->getPreviewImage())}{$oNews->getPreviewImage()}{/if}" />
                            </div>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="Bilder_0">{#newsPictures#}</label>
                            </span>
                            <input id="Bilder_0" name="Bilder[]" type="file" maxlength="2097152" accept="image/*" />
                        </div>
                        <div class="input-group">
                            <button name="hinzufuegen" type="button" value="{#newsPicAdd#}" onclick="addInputRow();" class="btn btn-primary add">{#newsPicAdd#}</button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label>{#newsPics#}</label>
                            </span>
                            {if isset($oDatei_arr) && $oDatei_arr|@count > 0}
                                {foreach name=bilder from=$oDatei_arr item=oDatei}
                                    <div class="well col-xs-3">
                                        <div class="thumbnail">{$oDatei->cURL}</div>
                                        <label>Link: </label>
                                        <div class="input-group">
                                            <input class="form-control" type="text" disabled="disabled" value="$#{$oDatei->cName}#$">
                                            <div class="input-group-addon">
                                                <a href="news.php?news=1&news_editieren=1&kNews={$oNews->getID()}&delpic={$oDatei->cName}&token={$smarty.session.jtl_token}" title="{#delete#}"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {*{else}*}
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
                        <div class="panel-body">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="betreff_{$cISO}">{#newsHeadline#} *</label>
                                </span>
                                <input class="form-control{if !empty($cPlausiValue_arr.cBetreff)} error{/if}" id="betreff_{$cISO}" type="text" name="betreff_{$cISO}" value="{if isset($cPostVar_arr.betreff) && $cPostVar_arr.betreff}{$cPostVar_arr.betreff}{else}{$oNews->getTitle($langID)}{/if}" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="seo_{$cISO}">{#newsSeo#}</label>
                                </span>
                                <input id="seo_{$cISO}" name="seo_{$cISO}" class="form-control" type="text" value="{if isset($cPostVar_arr.seo) && $cPostVar_arr.seo}{$cPostVar_arr.seo}{else}{$oNews->getSEO($langID)}{/if}" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="cMetaTitle_{$cISO}">{#newsMetaTitle#}</label>
                                </span>
                                <input class="form-control" id="cMetaTitle_{$cISO}" name="cMetaTitle_{$cISO}" type="text" value="{if isset($cPostVar_arr.cMetaTitle) && $cPostVar_arr.cMetaTitle}{$cPostVar_arr.cMetaTitle}{else}{$oNews->getMetaTitle($langID)}{/if}" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="cMetaDescription_{$cISO}">{#newsMetaDescription#}</label>
                                </span>
                                <input id="cMetaDescription_{$cISO}" class="form-control" name="cMetaDescription_{$cISO}" type="text" value="{if isset($cPostVar_arr.cMetaDescription) && $cPostVar_arr.cMetaDescription}{$cPostVar_arr.cMetaDescription}{else}{$oNews->getMetaDescription($langID)}{/if}" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="cMetaKeywords_{$cISO}">{#newsMetaKeywords#}</label>
                                </span>
                                <input class="form-control" id="cMetaKeywords_{$cISO}" name="cMetaKeywords_{$cISO}" type="text" value="{if isset($cPostVar_arr.cMetaKeywords) && $cPostVar_arr.cMetaKeywords}{$cPostVar_arr.cMetaKeywords}{else}{$oNews->getMetaKeyword($langID)}{/if}" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="newstext_{$cISO}">{#newsText#} *</label>
                                </span>
                                <textarea id="newstext_{$cISO}" class="ckeditor" name="text_{$cISO}" rows="15" cols="60">{if isset($cPostVar_arr.text) && $cPostVar_arr.text}{$cPostVar_arr.text}{else}{$oNews->getContent($langID)}{/if}</textarea>
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="previewtext_{$cISO}">{#newsPreviewText#}</label>
                                </span>
                                <textarea id="previewtext_{$cISO}" class="ckeditor" name="cVorschauText_{$cISO}" rows="15" cols="60">{if isset($cPostVar_arr.cVorschauText) && $cPostVar_arr.cVorschauText}{$cPostVar_arr.cVorschauText}{else}{$oNews->getPreview($langID)}{/if}</textarea>
                            </div>
                        </div>
                        <div class="alert alert-info">{#newsMandatoryFields#}</div>
                    </div>
                </div>
            {/foreach}
            <div class="panel btn-group">
                <button name="speichern" type="button" value="{#newsSave#}" onclick="checkfile(event);" class="btn btn-primary"><i class="fa fa-save"></i> {#newsSave#}</button>
                {if $oNews->getID() > 0}
                    <button type="submit" name="continue" value="1" class="btn btn-default" id="save-and-continue">{#newsSave#} und weiter bearbeiten</button>
                {/if}
                <a class="btn btn-danger" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-exclamation"></i> Abbrechen</a>
            </div>
        </div>
    </form>
    {if $oNews->getID() > 0}
        {getRevisions type='news' key=$oNews->getID() show=['cText'] secondary=false data=$oNews}
    {/if}
</div>
