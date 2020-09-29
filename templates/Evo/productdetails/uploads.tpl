{if !empty($oUploadSchema_arr)}
    <script type="text/javascript" src="{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/fileinput.min.js"></script>
    {assign var=availableLocale value=array('ar', 'bg', 'cr', 'cz', 'da', 'de', 'el', 'es', 'fa', 'fr', 'hu', 'lt', 'nl', 'pl', 'pt', 'sk', 'uk')}
    {if isset($smarty.session.currentLanguage->cISO639) && $smarty.session.currentLanguage->cISO639|in_array:$availableLocale}
        {assign var=uploaderLang value=$smarty.session.currentLanguage->cISO639}
    {else}
        {assign var=uploaderLang value='en'}
    {/if}
    <script type="text/javascript" src="{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/fileinput_locale_{$uploaderLang}.js"></script>

    <link href="{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}themes/base/fileinput.min.css" rel="stylesheet" type="text/css">
    <h3 class="section-heading">{lang key='uploadHeadline'}</h3>
    <div class="alert alert-info">{lang key='maxUploadSize'}: <strong>{$cMaxUploadSize}</strong></div>
    {foreach $oUploadSchema_arr as $oUploadSchema}
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">
                    {$oUploadSchema->cName}
                    {if !empty($oUploadSchema->WarenkorbPosEigenschaftArr)}
                        <small>
                            {foreach name=variationen from=$oUploadSchema->WarenkorbPosEigenschaftArr item=Variation}
                                - {$Variation->cEigenschaftName|trans}: {$Variation->cEigenschaftWertName|trans}
                            {/foreach}
                        </small>
                    {/if}
                </div>
            </div>
            <div class="panel-body">
                {foreach $oUploadSchema->oUpload_arr as $oUpload}
                    <div class="row">
                        {if !empty($oUpload->cName) || !empty($oUpload->cBeschreibung)}
                            <div class="col-xs-6">
                                {if !empty($oUpload->cName)}
                                    <p class="upload_title">{$oUpload->cName}</p>
                                {/if}
                                {if !empty($oUpload->cBeschreibung)}
                                    <p class="upload_desc">{$oUpload->cBeschreibung}</p>
                                {/if}
                            </div>
                        {/if}
                        <div class="col-xs-6 word-break text-right">
                            <div id="queue{$oUploadSchema@index}{$oUpload@index}" style="margin-bottom: 15px;" class="uploadifyMsg">
                                <span class="current-upload small text-success">
                                    {if $oUpload->bVorhanden}
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                        {$oUpload->cDateiname} ({$oUpload->cDateigroesse})
                                    {/if}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="text-center
                                {if isset($smarty.get.fillOut) && $smarty.get.fillOut == 12 && ($oUpload->nPflicht
                                    && !$oUpload->bVorhanden)} upload-error{/if}"
                                 id="upload-{$oUploadSchema@index}{$oUpload@index}">
                                <input id="fileinput{$oUploadSchema@index}{$oUpload@index}"
                                    type="file" multiple class="file-upload file-loading" />
                                <div id="kv-error-{$oUploadSchema@index}{$oUpload@index}"
                                    style="margin-top:10px; display:none;"></div>
                            </div>
                            <script type="text/javascript">
                                $(function () {ldelim}
                                    $('#fileinput{$oUploadSchema@index}{$oUpload@index}').fileinput({
                                        uploadUrl:             '{$ShopURL}/{$smarty.const.PFAD_UPLOAD_CALLBACK}',
                                        uploadAsync:           true,
                                        showPreview:           false,
                                        showRemove:            false,
                                        allowedFileExtensions: [{$oUpload->cDateiListe|replace:'*.':'\''|replace:';':'\','|cat:'\''}],
                                        language:              '{$uploaderLang}',
                                        uploadExtraData:       {
                                            sid:        "{$cSessionID}",
                                            jtl_token:  "{$smarty.session.jtl_token}",
                                            uniquename: "{$oUpload->cUnique}",
                                            uploader:   "4.00",
                                            cname:      "{$oUploadSchema->cName|replace:" ":"_"}",
                                            kUploadSchema:"{$oUpload->kUploadSchema}",
                                            prodID:     "{$oUpload->prodID}"
                                            {if !empty($oUploadSchema->WarenkorbPosEigenschaftArr)},
                                            variation:  "{strip}
                                            {foreach name=variationen from=$oUploadSchema->WarenkorbPosEigenschaftArr item=Variation}_{$Variation->cEigenschaftWertName|trans|replace:" ":"_"}{/foreach}
                                                "{/strip}
                                            {/if}
                                        },
                                        maxFileSize:           {$nMaxUploadSize/1024},
                                        elErrorContainer:      '#kv-error-{$oUploadSchema@index}{$oUpload@index}',
                                        maxFilesNum:           1
                                    }).on('fileuploaded', function(event, data) {
                                        var ip = $('#fileinput{$oUploadSchema@index}{$oUpload@index}'),
                                            msgField = $('#queue{$oUploadSchema@index}{$oUpload@index} .current-upload'),
                                            uploadMsgField = $('.uploadifyMsg');
                                        if (typeof data.response !== 'undefined' && typeof data.response.cName !== 'undefined') {
                                            msgField.html('<i class="fa fa-check" aria-hidden="true"></i>' + data.response.cName + ' (' + data.response.cKB + ' KB)');
                                        } else {
                                            msgField.html('{lang key='uploadError'}');
                                        }
                                        $('#msgWarning').hide();
                                        uploadMsgField.find('.alert-danger').hide();
                                        $('#cart-form').find('.upload-error').removeClass('upload-error');
                                        ip.fileinput('reset');
                                        ip.fileinput('refresh');
                                        ip.fileinput('clear');
                                        ip.fileinput('enable');
                                    }).on('fileuploaderror', function(event, data, msg) {
                                        $('#upload-{$oUploadSchema@index}{$oUpload@index} .fileinput-upload').addClass('disabled');
                                        if(Object.keys(data.jqXHR).length > 0){
                                            switch(data.jqXHR.responseJSON.status){
                                                case 'reached_limit_per_hour':
                                                    message = '{lang key='uploadErrorReachedLimitPerHour'}';
                                                    break;
                                                case 'filetype_forbidden':
                                                    message = '{lang key='uploadErrorFiletypeForbidden'}';
                                                    break;
                                                case 'extension_not_listed':
                                                    message = '{lang key='uploadErrorExtensionNotListed'}';
                                                    break;
                                            }
                                            let errorOutput = $('#kv-error-{$oUploadSchema@index}{$oUpload@index} ul li');
                                            errorOutput.html(message);
                                        }
                                    }).on('fileloaded', function() {
                                        $('#upload-{$oUploadSchema@index}{$oUpload@index} .fileinput-upload').removeClass('disabled');
                                    });
                                    {rdelim});
                            </script>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/foreach}
{/if}
