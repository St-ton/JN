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
        $('#lang').on('change', function () {
            var iso = $('#lang option:selected').val();
            $('.iso_wrapper').slideUp();
            $('#iso_' + iso).slideDown();
            return false;
        });

        $('input[name="nLinkart"]').on('change', function () {
            var lnk = $('input[name="nLinkart"]:checked').val();
            if (lnk == '1') {
                $('#option_isActive').slideDown("slow");
            } else {
                $('#option_isActive').slideUp("slow");
                $('#option_isActive select').val(1);
            }
        }).trigger('change');
        $('form input[type=file]').on('change', function(e){
            $('form div.alert').slideUp();
            var filesize= this.files[0].size;
            {/literal}
            var maxsize = {$nMaxFileSize};
            {literal}
            if (filesize >= maxsize) {
                $(this).after('<div class="alert alert-danger"><i class="fa fa-warning"></i>{/literal}{__('errorUploadSizeLimit')}{literal}</div>').slideDown();
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
{include file='tpl_inc/seite_header.tpl' cTitel=__('news') cBeschreibung=__('newsDesc')}
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
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{if $oNews->getID() > 0}{__('edit')} (ID {$oNews->getID()}){else}{__('newAdd')}{/if}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive">
                    <div id="formtable" class="card-body">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="kkundengruppe">{__('customerGroup')} *:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="kkundengruppe" name="kKundengruppe[]" multiple="multiple" class="custom-select{if !empty($cPlausiValue_arr.kKundengruppe_arr)} error{/if}">
                                    <option value="-1"
                                        {if isset($cPostVar_arr.kKundengruppe)}
                                            {foreach $cPostVar_arr.kKundengruppe as $kKundengruppe}
                                                {if $kKundengruppe == '-1'}selected{/if}
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
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="kNewsKategorie">{__('category')} *:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="kNewsKategorie" class="custom-select{if !empty($cPlausiValue_arr.kNewsKategorie_arr)} error{/if}" name="kNewsKategorie[]" multiple="multiple">
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
                                        {foreach $category->getChildren() as $category}
                                            <option value="{$category->getID()}"
                                                {if isset($cPostVar_arr.kNewsKategorie)}
                                                    {foreach $cPostVar_arr.kNewsKategorie as $kNewsKategorieNews}
                                                        {if $category->getID() == $kNewsKategorieNews}selected{/if}
                                                    {/foreach}
                                                {else}
                                                    {foreach $oNews->getCategoryIDs() as $categoryID}
                                                        {if $category->getID() === $categoryID}selected{/if}
                                                    {/foreach}
                                                {/if}>&nbsp;&nbsp;&nbsp;{$category->getName()}</option>
                                        {/foreach}
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="dGueltigVon">{__('newsValidation')} *:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="dGueltigVon" name="dGueltigVon" type="text" value="{if isset($cPostVar_arr.dGueltigVon) && $cPostVar_arr.dGueltigVon}{$cPostVar_arr.dGueltigVon}{else}{$oNews->getDateValidFrom()->format('d.m.Y H:i')}{/if}" />
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('active')} *:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="nAktiv" name="nAktiv">
                                    <option value="1"{if isset($cPostVar_arr.nAktiv)}{if $cPostVar_arr.nAktiv == 1} selected{/if}{elseif $oNews->getIsActive() === true} selected{/if}>{__('yes')}</option>
                                    <option value="0"{if isset($cPostVar_arr.nAktiv)}{if $cPostVar_arr.nAktiv == 0} selected{/if}{elseif $oNews->getIsActive() === false} selected{/if}>{__('no')}
                                    </option>
                                </select>
                            </div>
                        </div>
                        {if $oPossibleAuthors_arr|count > 0}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right"for="kAuthor">{__('newsAuthor')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="kAuthor" name="kAuthor">
                                    <option value="0">Autor auswählen</option>
                                    {foreach $oPossibleAuthors_arr as $oPossibleAuthor}
                                        <option value="{$oPossibleAuthor->kAdminlogin}"{if isset($cPostVar_arr.nAuthor)}{if isset($cPostVar_arr.nAuthor) && $cPostVar_arr.nAuthor == $oPossibleAuthor->kAdminlogin} selected="selected"{/if}{elseif isset($oAuthor->kAdminlogin) && $oAuthor->kAdminlogin == $oPossibleAuthor->kAdminlogin} selected="selected"{/if}>{$oPossibleAuthor->cName}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        {/if}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right"for="previewImage">{__('preview')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {if !empty($oNews->getPreviewImage())}
                                    <img src="{$shopURL}/{$oNews->getPreviewImage()}" alt="" height="20" width="20" class="preview-image left" style="margin: 0 10px;" />
                                {/if}
                                <input id="previewImage" name="previewImage" type="file" maxlength="2097152" accept="image/*" />
                                <input name="previewImage" type="hidden" value="{if !empty($oNews->getPreviewImage())}{$oNews->getPreviewImage()}{/if}" />
                            </div>
                        </div>
                        {if isset($oDatei_arr) && $oDatei_arr|@count > 0}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right">{__('newsPics')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {foreach $oDatei_arr as $oDatei}
                                    <div class="well col-xs-3">
                                        <div class="thumbnail"><img src="{$oDatei->cURLFull}" alt=""></div>
                                        <label>Link: :</label>
                                        <div class="input-group">
                                            <input class="form-control" type="text" disabled="disabled" value="$#{$oDatei->cName}#$">
                                            <div class="input-group-addon">
                                                <a href="news.php?news=1&news_editieren=1&kNews={$oNews->getID()}&delpic={$oDatei->cName}&token={$smarty.session.jtl_token}" title="{__('delete')}"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        {/if}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="lang">{__('language')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" name="cISO" id="lang">
                                    {foreach $sprachen as $language}
                                        <option value="{$language->getIso()}" {if $language->getShopDefault() === 'Y'}selected="selected"{/if}>{$language->getLocalizedName()} {if $language->getShopDefault() === 'Y'}({__('standard')}){/if}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {foreach $sprachen as $language}
                {assign var=cISO value=$language->getIso()}
                {assign var=langID value=$language->getId()}
                <input type="hidden" name="lang_{$cISO}" value="{$langID}">
                <div id="iso_{$cISO}" class="iso_wrapper{if !$language->isShopDefault()} hidden-soft{/if}">
                    <div class="card">
                        <div class="card-header">
                            <div class="subheading1">{__('metaSeo')} ({$language->getLocalizedName()})</div>
                            <hr class="mb-n3">
                        </div>
                        <div class="card-body">
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cName_{$cISO}">{__('headline')} *:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control{if !empty($cPlausiValue_arr.cBetreff)} error{/if}" id="cName_{$cISO}" type="text" name="cName_{$cISO}" value="{if isset($cPostVar_arr.betreff) && $cPostVar_arr.betreff}{$cPostVar_arr.betreff}{else}{$oNews->getTitle($langID)}{/if}" />
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cSeo_{$cISO}">{__('newsSeo')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input id="cSeo_{$cISO}" name="cSeo_{$cISO}" class="form-control" type="text" value="{if isset($cPostVar_arr.seo) && $cPostVar_arr.seo}{$cPostVar_arr.seo}{else}{$oNews->getSEO($langID)}{/if}" />
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cMetaTitle_{$cISO}">{__('newsMetaTitle')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" id="cMetaTitle_{$cISO}" name="cMetaTitle_{$cISO}" type="text" value="{if isset($cPostVar_arr.cMetaTitle) && $cPostVar_arr.cMetaTitle}{$cPostVar_arr.cMetaTitle}{else}{$oNews->getMetaTitle($langID)}{/if}" />
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cMetaDescription_{$cISO}">{__('newsMetaDescription')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input id="cMetaDescription_{$cISO}" class="form-control" name="cMetaDescription_{$cISO}" type="text" value="{if isset($cPostVar_arr.cMetaDescription) && $cPostVar_arr.cMetaDescription}{$cPostVar_arr.cMetaDescription}{else}{$oNews->getMetaDescription($langID)}{/if}" />
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cMetaKeywords_{$cISO}">{__('newsMetaKeywords')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" id="cMetaKeywords_{$cISO}" name="cMetaKeywords_{$cISO}" type="text" value="{if isset($cPostVar_arr.cMetaKeywords) && $cPostVar_arr.cMetaKeywords}{$cPostVar_arr.cMetaKeywords}{else}{$oNews->getMetaKeyword($langID)}{/if}" />
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="newstext_{$cISO}">{__('text')} *:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <textarea id="newstext_{$cISO}" class="ckeditor" name="text_{$cISO}" rows="15" cols="60">{if isset($cPostVar_arr.text) && $cPostVar_arr.text}{$cPostVar_arr.text}{else}{$oNews->getContent($langID)}{/if}</textarea>
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="previewtext_{$cISO}">{__('newsPreviewText')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <textarea id="previewtext_{$cISO}" class="ckeditor" name="cVorschauText_{$cISO}" rows="15" cols="60">{if isset($cPostVar_arr.cVorschauText) && $cPostVar_arr.cVorschauText}{$cPostVar_arr.cVorschauText}{else}{$oNews->getPreview($langID)}{/if}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info">{__('newsMandatoryFields')}</div>
                    </div>
                </div>
            {/foreach}
            <div class="card-footer save_wrapper">
                <div class="btn-group">
                    <button name="speichern" type="button" value="{__('save')}" onclick="checkfile(event);" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                    {if $oNews->getID() > 0}
                        <button type="submit" name="continue" value="1" class="btn btn-default" id="save-and-continue">{__('save')} {__('goOnEdit')}</button>
                    {/if}
                    <a class="btn btn-danger" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-exclamation"></i> {__('Cancel')}</a>
                </div>
            </div>
        </div>
    </form>
    {if $oNews->getID() > 0}
        {getRevisions type='news' key=$oNews->getID() show=['content'] secondary=true data=$oNews->getData()}
    {/if}
</div>
