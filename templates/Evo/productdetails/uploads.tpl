{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if !empty($oUploadSchema_arr)}
    <script type="text/javascript" src="{$currentTemplateDir}js/fileinput.min.js"></script>
    {assign var=availableLocale value=array('ar', 'bg', 'cr', 'cz', 'da', 'de', 'el', 'es', 'fa', 'fr', 'hu', 'lt', 'nl', 'pl', 'pt', 'sk', 'uk')}
    {if isset($smarty.session.currentLanguage->cISO639) && $smarty.session.currentLanguage->cISO639|in_array:$availableLocale}
        {assign var=uploaderLang value=$smarty.session.currentLanguage->cISO639}
    {else}
        {assign var=uploaderLang value='en'}
    {/if}
    <script type="text/javascript" src="{$currentTemplateDir}js/fileinput_locale_{$uploaderLang}.js"></script>

    <link href="{$currentTemplateDir}themes/base/fileinput.min.css" rel="stylesheet" type="text/css">
    <h3 class="section-heading">{lang key="uploadHeadline"}</h3>
    <div class="alert alert-info">{lang key="maxUploadSize"}: <strong>{$cMaxUploadSize}</strong></div>
    {foreach from=$oUploadSchema_arr item=oUploadSchema name=schema}
        <table class="table table-stripped table-bordered">
            <thead>
            <tr>
                <th colspan="3" class="tleft">{$oUploadSchema->cName}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$oUploadSchema->oUpload_arr item=oUpload name=upload}
                <tr>
                    {if !empty($oUpload->cName) || !empty($oUpload->cBeschreibung)}
                        <td>
                            {if !empty($oUpload->cName)}
                                <p class="upload_title">{$oUpload->cName}</p>
                            {/if}
                            {if !empty($oUpload->cBeschreibung)}
                                <p class="upload_desc">{$oUpload->cBeschreibung}</p>
                            {/if}
                        </td>
                    {/if}
                    <td id="queue{$smarty.foreach.schema.index}{$smarty.foreach.upload.index}" class="uploadifyMsg {if isset($smarty.get.fillOut) && $smarty.get.fillOut == 12 && ($oUpload->nPflicht && !$oUpload->bVorhanden)}alert-danger{/if}{if $oUpload->bVorhanden}alert-success{/if}">
                        {if isset($smarty.get.fillOut) && $smarty.get.fillOut == 12 && ($oUpload->nPflicht && !$oUpload->bVorhanden)}
                            {lang key="selectUpdateFile"}
                        {/if}
                        <span class="current-upload">
                            {if $oUpload->bVorhanden}
                                {$oUpload->cDateiname} ({$oUpload->cDateigroesse})
                            {/if}
                        </span>
                    </td>
                    <td class="text-center" style="width:60%;" id="upload-{$smarty.foreach.schema.index}{$smarty.foreach.upload.index}">
                        <input id="fileinput{$smarty.foreach.schema.index}{$smarty.foreach.upload.index}" type="file" multiple class="file-upload file-loading" />
                        <div id="kv-error-{$smarty.foreach.schema.index}{$smarty.foreach.upload.index}" style="margin-top:10px; display:none;"></div>
                    </td>
                </tr>
                <script type="text/javascript">
                    $(function () {ldelim}
                        $('#fileinput{$smarty.foreach.schema.index}{$smarty.foreach.upload.index}').fileinput({
                            uploadUrl:             '{$ShopURL}/{$PFAD_UPLOAD_CALLBACK}',
                            uploadAsync:           true,
                            showPreview:           false,
                            showRemove:            false,
                            allowedFileExtensions: [{$oUpload->cDateiListe|replace:'*.':'\''|replace:';':'\','|cat:'\''}],
                            language:              '{$uploaderLang}',
                            uploadExtraData:       {
                                sid:        "{$cSessionID}",
                                jtl_token:  "{$smarty.session.jtl_token}",
                                uniquename: "{$oUpload->cUnique}",
                                uploader:   "4.00"
                            },
                            maxFileSize:           {$nMaxUploadSize/1024},
                            elErrorContainer:      '#kv-error-{$smarty.foreach.schema.index}{$smarty.foreach.upload.index}',
                            maxFilesNum:           1
                        }).on('fileuploaded', function(event, data) {
                            var ip = $('#fileinput{$smarty.foreach.schema.index}{$smarty.foreach.upload.index}'),
                                msgField = $('#queue{$smarty.foreach.schema.index}{$smarty.foreach.upload.index} .current-upload');
                            if (typeof data.response !== 'undefined' && typeof data.response.cName !== 'undefined') {
                                msgField.html(data.response.cName + ' (' + data.response.cKB + ' KB)');
                            } else {
                                msgField.html('{lang key="uploadError"}');
                            }
                            ip.fileinput('reset');
                            ip.fileinput('refresh');
                            ip.fileinput('clear');
                            ip.fileinput('enable');
                        }).on('fileuploaderror', function() {
                            $('#upload-{$smarty.foreach.schema.index}{$smarty.foreach.upload.index} .fileinput-upload').addClass('disabled');
                        }).on('fileloaded', function() {
                            $('#upload-{$smarty.foreach.schema.index}{$smarty.foreach.upload.index} .fileinput-upload').removeClass('disabled');
                        });
                    {rdelim});
                </script>
            {/foreach}
            </tbody>
        </table>
    {/foreach}
{/if}
