<script type="text/javascript">
    var file2large = false;
    {literal}

    $(document).ready(function () {
        $('#lang').on('change', function () {
            var iso = $('#lang option:selected').val();
            $('.iso_wrapper').slideUp();
            $('#iso_' + iso).slideDown();
            return false;
        });
        $('form input[type=file]').on('change', function(e){
            $('form div.alert').slideUp();
            var filesize= this.files[0].size;
            {/literal}
            var maxsize = {$nMaxFileSize};
            {literal}
            if (filesize >= maxsize) {
                $(this).after('<div class="alert alert-danger"><i class="fa fa-warning"></i> {/literal}{__('errorUploadSizeLimit')}{literal}</div>').slideDown();
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

{include file='tpl_inc/seite_header.tpl' cTitel=__('category')}
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
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{if $oNewsKategorie->getID() > 0}{__('newsCatEdit')} ({__('id')} {$oNewsKategorie->getID()}){else}{__('newsCatCreate')}{/if}</div>
                </div>
                <div class="table-responsive">
                    <div class="card-body" id="formtable">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kParent">{__('newsCatParent')}</label>
                            </span>
                            <select class="form-control" id="kParent" name="kParent">
                                <option value="0"> - {__('mainCategory')} - </option>
                                {if $oNewsKategorie->getParentID()}
                                    {assign var=selectedCat value=$oNewsKategorie->getParentID()}
                                {else}
                                    {assign var=selectedCat value=0}
                                {/if}
                                {include file='snippets/newscategories_recursive.tpl' i=0 selectedCat=$selectedCat}
                            </select>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nSort">{__('newsCatSort')}</label>
                            </span>
                            <input class="form-control{if !empty($cPlausiValue_arr.nSort)} error{/if}" id="nSort" name="nSort" type="text" value="{$oNewsKategorie->getSort()}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAktiv">{__('active')}</label>
                            </span>
                            <select class="form-control" id="nAktiv" name="nAktiv">
                                <option value="1"{if $oNewsKategorie->getIsActive() === true} selected{/if}>
                                    {__('yes')}
                                </option>
                                <option value="0"{if $oNewsKategorie->getIsActive() === false} selected{/if}>
                                    {__('no')}
                                </option>
                            </select>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="previewImage">{__('preview')}</label>
                            </span>
                            <div class="input-group-wrap">
                                {if !empty($oNewsKategorie->getPreviewImage())}
                                    <img src="{$shopURL}/{$oNewsKategorie->getPreviewImage()}" alt="" height="20" width="20" class="preview-image left" style="margin: 0 10px;" />
                                {/if}
                                <input id="previewImage" name="previewImage" type="file" maxlength="2097152" accept="image/*" />
                                <input name="previewImage" type="hidden" value="{if !empty($oNewsKategorie->getPreviewImage())}{$oNewsKategorie->getPreviewImage()}{/if}" />
                            </div>
                        </div>
                        {if isset($oDatei_arr) && $oDatei_arr|@count > 0}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label>{__('newsPics')}</label>
                                </span>
                                <div>
                                    {foreach $oDatei_arr as $oDatei}
                                        <div class="well col-xs-3">
                                            <div class="thumbnail"><img src="{$oDatei->cURLFull}" alt=""></div>
                                            <label>{__('link')}: </label>
                                            <div class="input-group">
                                                <input class="form-control" type="text" disabled="disabled" value="$#{$oDatei->cName}#$">
                                                <div class="input-group-addon">
                                                    <a href="news.php?news=1&newskategorie_editieren=1&kNewsKategorie={$oNewsKategorie->getID()}&delpic={$oDatei->cName}&token={$smarty.session.jtl_token}" title="{__('delete')}"><i class="fa fa-trash"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        {/if}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="lang">{__('language')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select class="form-control" name="cISO" id="lang">
                                    {foreach $sprachen as $language}
                                        <option value="{$language->getIso()}" {if $language->getShopDefault() === 'Y'}selected="selected"{/if}>{$language->getLocalizedName()} {if $language->getShopDefault() === 'Y'}({__('standard')}){/if}</option>
                                    {/foreach}
                                </select>
                            </span>
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
                            <div class="card-title">{__('metaSeo')} ({$language->getLocalizedName()})</div>
                        </div>
                        <div class="table-responsive">
                            <div class="card-body" id="formtable">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cName_{$cISO}">{__('name')}</label>
                                    </span>
                                    <input class="form-control{if !empty($cPlausiValue_arr.cName)} error{/if}" id="cName_{$cISO}" name="cName_{$cISO}" type="text" value="{if $oNewsKategorie->getName($langID) !== ''}{$oNewsKategorie->getName($langID)}{/if}" />{if isset($cPlausiValue_arr.cName) && $cPlausiValue_arr.cName == 2} {__('newsAlreadyExists')}{/if}
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cSeo_{$cISO}">{__('newsSeo')}</label>
                                    </span>
                                    <input class="form-control{if !empty($cPlausiValue_arr.cSeo)} error{/if}" id="cSeo_{$cISO}" name="cSeo_{$cISO}" type="text" value="{if $oNewsKategorie->getSEO($langID) !== ''}{$oNewsKategorie->getSEO($langID)}{/if}" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cMetaTitle_{$cISO}">{__('newsMetaTitle')}</label>
                                    </span>
                                    <input class="form-control{if !empty($cPlausiValue_arr.cMetaTitle)} error{/if}" id="cMetaTitle_{$cISO}" name="cMetaTitle_{$cISO}" type="text" value="{$oNewsKategorie->getMetaTitle($langID)}" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cMetaDescription_{$cISO}">{__('newsMetaDescription')}</label>
                                    </span>
                                    <input class="form-control{if !empty($cPlausiValue_arr.cMetaDescription)} error{/if}" id="cMetaDescription_{$cISO}" name="cMetaDescription_{$cISO}" type="text" value="{$oNewsKategorie->getMetaDescription($langID)}" />
                                </div>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cBeschreibung_{$cISO}">{__('description')}</label>
                                    </span>
                                    <textarea id="cBeschreibung_{$cISO}" class="ckeditor" name="cBeschreibung_{$cISO}" rows="15" cols="60">{$oNewsKategorie->getDescription($langID)}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="btn-group">
                                <button name="speichern" type="button" value="{__('save')}" onclick="document.news.submit();" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                                <a class="btn btn-danger" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-exclamation"></i> {__('Cancel')}</a>
                            </span>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </form>
</div>
