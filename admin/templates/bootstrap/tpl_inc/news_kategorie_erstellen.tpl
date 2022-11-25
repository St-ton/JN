<script type="text/javascript">
    var file2large = false;

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
            var maxsize = {$nMaxFileSize};
            if (filesize >= maxsize) {
                $(this).after('<div class="alert alert-danger"><i class="fal fa-exclamation-triangle"></i> {__('errorUploadSizeLimit')}</div>').slideDown();
                file2large = true;
            } else {
                $(this).closest('div.alert').slideUp();
                file2large = false;
            }
        });
        {$defaultLang = 'ger'}
        {foreach $availableLanguages as $language}
            {if $language->getShopDefault() === 'Y'}
                {$defaultLang = $language->getCode()}
            {/if}
        {/foreach}
        {if isset($validation) && count($validation) > 0 && isset($validation.lang) && !in_array($defaultLang, $validation.lang, true)}
            $('#lang').val('{$validation.lang[0]}').trigger('change');
        {/if}
    });

    function checkfile(e){
        e.preventDefault();
        if (!file2large){
            document.news.submit();
        }
    }
</script>
<style>
    .form-control.error { border-width: 3px};
</style>
{include file='tpl_inc/seite_header.tpl' cTitel=__('category')}
<div id="content">
    <form name="news" method="post" action="{$adminURL}{$route}" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="news_kategorie_speichern" value="1" />
        <input type="hidden" name="tab" value="kategorien" />
        {if $category->getID() > 0}
            <input type="hidden" name="newskategorie_edit_speichern" value="1" />
            <input type="hidden" name="kNewsKategorie" value="{$category->getID()}" />
            {if isset($cSeite)}
                <input type="hidden" name="s3" value="{$cSeite}" />
            {/if}
        {/if}
        <div class="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{if $category->getID() > 0}{__('newsCatEdit')} ({__('id')} {$category->getID()}){else}{__('newsCatCreate')}{/if}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive">
                    <div class="card-body" id="formtable">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="kParent">{__('newsCatParent')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="kParent" name="kParent">
                                    <option value="0"> - {__('mainCategory')} - </option>
                                    {if $category->getParentID()}
                                        {assign var=selectedCat value=$category->getParentID()}
                                    {else}
                                        {assign var=selectedCat value=0}
                                    {/if}
                                    {include file='snippets/newscategories_recursive.tpl' i=0 selectedCat=$selectedCat}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nSort">{__('newsCatSort')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control{if !empty($validation.nSort)} error{/if}" id="nSort" name="nSort" type="text" value="{$category->getSort()}" />
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('active')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="nAktiv" name="nAktiv">
                                    <option value="1"{if $category->getIsActive() === true} selected{/if}>
                                        {__('yes')}
                                    </option>
                                    <option value="0"{if $category->getIsActive() === false} selected{/if}>
                                        {__('no')}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="previewImage">{__('preview')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {include file='tpl_inc/fileupload.tpl'
                                    fileID='previewImage'
                                    fileShowRemove=true
                                    fileMaxSize=2097152
                                    fileInitialPreview="[
                                            {if !empty($category->getPreviewImage())}
                                                '<img src=\"{$shopURL}/{$category->getPreviewImage()}\" class=\"mb-3\" />'
                                            {/if}
                                        ]"
                                }
                            </div>
                        </div>
                        {if count($files) > 0}
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right">{__('newsPics')}:</label>
                                <div>
                                    {foreach $files as $file}
                                        <div class="well col-xs-3">
                                            <div class="thumbnail"><img src="{$file->cURLFull}" alt=""></div>
                                            <label>{__('link')}: </label>
                                            <div class="input-group">
                                                <input class="form-control" type="text" disabled="disabled" value="$#{$file->cName}#$">
                                                <div class="input-group-addon">
                                                    <a href="{$adminURL}{$route}?news=1&newskategorie_editieren=1&kNewsKategorie={$category->getID()}&delpic={$file->cName}&token={$smarty.session.jtl_token}" title="{__('delete')}"><i class="fas fa-trash-alt"></i></a>
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
                                    {foreach $availableLanguages as $language}
                                        <option value="{$language->getIso()}" {if $language->getShopDefault() === 'Y'}selected="selected"{/if}>{$language->getLocalizedName()} {if $language->getShopDefault() === 'Y'}({__('standard')}){/if}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {foreach $availableLanguages as $language}
                {$cISO = $language->getIso()}
                {$langID = $language->getId()}
                <input type="hidden" name="lang_{$cISO}" value="{$langID}">
                <div id="iso_{$cISO}" class="iso_wrapper{if !$language->isShopDefault()} hidden-soft{/if}">
                    <div class="card">
                        <div class="card-header">
                            <div class=subheading1>{__('metaSeo')} ({$language->getLocalizedName()})</div>
                            <hr class="mb-n3">
                        </div>
                        <div class="table-responsive">
                            <div class="card-body" id="formtable">
                                <div class="form-group form-row align-items-center">
                                    {$name = 'cName_'|cat:$cISO}
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="{$name}">{__('name')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control{if !empty($validation[$name])} error{/if}" id="{$name}" name="{$name}" type="text" value="{if $category->getName($langID) !== ''}{$category->getName($langID)}{/if}" />{if isset($validation.cName) && $validation.cName == 2} {__('newsAlreadyExists')}{/if}
                                    </div>
                                </div>
                                <div class="form-group form-row align-items-center">
                                    {$name = 'cSeo_'|cat:$cISO}
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="{$name}">{__('newsSeo')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control{if !empty($validation[$name])} error{/if}" id="{$name}" name="{$name}" type="text" value="{if $category->getSEO($langID) !== ''}{$category->getSEO($langID)}{/if}" />
                                    </div>
                                </div>
                                <div class="form-group form-row align-items-center">
                                    {$name = 'cMetaTitle_'|cat:$cISO}
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="{$name}">{__('newsMetaTitle')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control{if !empty($validation[$name])} error{/if}" id="{$name}" name="{$name}" type="text" value="{$category->getMetaTitle($langID)}" />
                                    </div>
                                </div>
                                <div class="form-group form-row align-items-center">
                                    {$name = 'cMetaDescription_'|cat:$cISO}
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="{$name}">{__('newsMetaDescription')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control{if !empty($validation[$name])} error{/if}" id="{$name}" name="{$name}" type="text" value="{$category->getMetaDescription($langID)}" />
                                    </div>
                                </div>
                                <div class="form-group form-row align-items-center">
                                    {$name = 'cBeschreibung_'|cat:$cISO}
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="{$name}">{__('description')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <textarea id="{$name}" class="ckeditor" name="{$name}" rows="15" cols="60">{$category->getDescription($langID)}</textarea>
                                    </div>
                                    {$name = null}
                                </div>
                            </div>
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <a class="btn btn-outline-primary btn-block" href="{$adminURL}{$route}{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}">
                                        <i class="fa fa-exclamation"></i> {__('Cancel')}
                                    </a>
                                </div>
                                <div class=" col-sm-6 col-xl-auto">
                                    {include file='snippets/buttons/saveAndContinueButton.tpl' value='kategorie'}
                                </div>
                                <div class=" col-sm-6 col-xl-auto">
                                    <button name="speichern" type="button" value="{__('save')}" onclick="document.news.submit();" class="btn btn-primary btn-block">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </form>
</div>
