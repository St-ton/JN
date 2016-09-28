{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='shoptemplate'}
{assign var="cBeschreibung" value=#shoptemplatesDesc#}
{if isset($oEinstellungenXML) && $oEinstellungenXML}
    {assign var="cTitel" value="Einstellungen: "|cat:$oTemplate->cName}
    {if !empty($oTemplate->cDokuURL)}
        {assign var="cDokuURL" value=$oTemplate->cDokuURL}
    {else}
        {assign var="cDokuURL" value=#shoptemplateURL#}
    {/if}
{else}
    {assign var="cTitel" value=#shoptemplates#}
    {assign var="cDokuURL" value=#shoptemplateURL#}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=$cBeschreibung cDokuURL=$cDokuURL}
{*workaround: no async uploads (the fileinput option uploadAsync does not work correctly... *}
<style>.fileinput-upload-button, .kv-file-upload{ldelim}display:none!important;{rdelim}</style>
<div id="content" class="container-fluid">
{if isset($oEinstellungenXML) && $oEinstellungenXML}
    <form action="shoptemplate.php" method="post" enctype="multipart/form-data">
        {$jtl_token}
        <div id="settings" class="settings">
            {if isset($oTemplate->eTyp) && ($oTemplate->eTyp === 'admin' || ($oTemplate->eTyp != 'mobil' && ($oTemplate->cOrdner === 'Evo' || $oTemplate->cParent === 'Evo')))}
                <input type="hidden" name="eTyp" value="{if !empty($oTemplate->eTyp)}{$oTemplate->eTyp}{else}standard{/if}" />
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Mobil</h3>
                    </div>
                    <div class="panel-body">
                        {if $oTemplate->eTyp === 'mobil' && ($oTemplate->cOrdner === 'Evo' || $oTemplate->cParent === 'Evo')}
                            <div class="alert alert-warning">{#warning_responsive_mobile#}</div>
                        {/if}
                        <div class="item input-group">
                            <span class="input-group-addon">
                                <label for="eTyp">Standard-Template f&uuml;r mobile Endger&auml;te?</label>
                            </span>
                            <span class="input-group-wrap">
                                <select class="form-control" name="eTyp" id="eTyp">
                                    <option value="standard" {if $oTemplate->eTyp === 'standard'}selected="selected"{/if}>Nein
                                        (optimiert f&uuml;r Standard-Browser)
                                    </option>
                                    <option value="mobil" {if $oTemplate->eTyp === 'mobil'}selected="selected"{/if}>Ja (optimiert
                                        f&uuml;r mobile Endger&auml;te)
                                    </option>
                                </select>
                            </span>
                        </div>
                    </div>
                </div>
            {/if}

            {foreach from=$oEinstellungenXML item=oSection}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{$oSection->cName}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">                        
                            {foreach name="tplOptions" from=$oSection->oSettings_arr item=oSetting}
                                {if $oSetting->cKey === 'theme_default' && isset($themePreviews) && $themePreviews !== null}
                                    <div class="col-xs-12">
                                        <div class="item input-group" id="theme-preview-wrap" style="display: none;">
                                            <span class="input-group-addon"><strong>Vorschau</strong></span>
                                            <img id="theme-preview" alt="" />
                                        </div>
                                        <script type="text/javascript">
                                            var previewJSON = {$themePreviewsJSON};
                                            {literal}
                                            setPreviewImage = function () {
                                                var currentTheme = $('#theme-theme_default').val(),
                                                    previewImage = $('#theme-preview'),
                                                    previewImageWrap = $('#theme-preview-wrap');
                                                if (typeof previewJSON[currentTheme] !== 'undefined') {
                                                    previewImage.attr('src', previewJSON[currentTheme]);
                                                    previewImageWrap.show();
                                                } else {
                                                    previewImageWrap.hide();
                                                }
                                            };
                                            $(document).ready(function () {
                                                setPreviewImage();
                                                $('#theme-theme_default').change(function () {
                                                    setPreviewImage();
                                                });
                                            });
                                            {/literal}
                                        </script>
                                    </div>
                                {/if}
                                <div class="col-xs-12 col-md-12">
                                    <input type="hidden" name="cSektion[]" value="{$oSection->cKey}" />
                                    <input type="hidden" name="cName[]" value="{$oSetting->cKey}" />
                                    <div class="item input-group">
                                        {if $oSetting->bEditable}
                                            <span class="input-group-addon">
                                                <label for="{$oSection->cKey}-{$oSetting->cKey}">{$oSetting->cName}</label>
                                            </span>
                                            <span class="input-group-wrap">
                                                {if $oSetting->cType === 'select'}
                                                    <select class="form-control" name="cWert[]" id="{$oSection->cKey}-{$oSetting->cKey}">
                                                        {foreach from=$oSetting->oOptions_arr item=oOption}
                                                            <option value="{$oOption->cValue}" {if $oOption->cValue == $oSetting->cValue}selected="selected"{/if}>{$oOption->cName}</option>
                                                        {/foreach}
                                                    </select>
                                                {elseif $oSetting->cType === 'optgroup'}
                                                    <select class="form-control" name="cWert[]" id="{$oSection->cKey}-{$oSetting->cKey}">
                                                        {foreach from=$oSetting->oOptgroup_arr item=oOptgroup}
                                                            <optgroup label="{$oOptgroup->cName}">
                                                            {foreach from=$oOptgroup->oValues_arr item=oOption}
                                                                <option value="{$oOption->cValue}" {if $oOption->cValue == $oSetting->cValue}selected="selected"{/if}>{$oOption->cName}</option>
                                                            {/foreach}
                                                            </optgroup>
                                                        {/foreach}
                                                    </select>
                                                {elseif $oSetting->cType === 'number'}
                                                    <input class="form-control" type="number" name="cWert[]" id="{$oSection->cKey}-{$oSetting->cKey}" value="{$oSetting->cValue|escape:"html"}" placeholder="{$oSetting->cPlaceholder}" />
                                                {elseif $oSetting->cType === 'text' || $oSetting->cType === 'float'}
                                                    <input class="form-control" type="text" name="cWert[]" id="{$oSection->cKey}-{$oSetting->cKey}" value="{$oSetting->cValue|escape:"html"}" placeholder="{$oSetting->cPlaceholder}" />
                                                {elseif $oSetting->cType === 'upload' && isset($oSetting->rawAttributes.target)}
                                                    <div class="template-favicon-upload">
                                                        <input name="upload-{$smarty.foreach.tplOptions.index}"
                                                               id="tpl-upload-{$smarty.foreach.tplOptions.index}" type="file"
                                                               class="file"
                                                               accept="{if !empty($oSetting->rawAttributes.accept)}{$oSetting->rawAttributes.accept}{else}image/*{/if}">
                                                    </div>
                                                    <input type="hidden" name="cWert[]" value="upload-{$smarty.foreach.tplOptions.index}" />
                                                    <script>
                                                        $('#tpl-upload-{$smarty.foreach.tplOptions.index}').fileinput({ldelim}
                                                            uploadAsync: false,
                                                            uploadExtraData: {ldelim}id:1{rdelim},
                                                            uploadUrl: '{$shopURL}/{$PFAD_ADMIN}shoptemplate.php?token={$smarty.session.jtl_token}',
                                                            allowedFileExtensions : {if !empty($oSetting->rawAttributes.allowedFileExtensions)}{$oSetting->rawAttributes.allowedFileExtensions}{else}['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp']{/if},
                                                            overwriteInitial: true,
                                                            deleteUrl: '{$shopURL}/{$PFAD_ADMIN}shoptemplate.php?token={$smarty.session.jtl_token}',
                                                            initialPreviewCount: 1,
                                                            showPreview: true,
                                                            language: 'de',
                                                            maxFileSize: {if !empty($oSetting->rawAttributes.maxFileSize)}{$oSetting->rawAttributes.maxFileSize}{else}1000{/if},
                                                            maxFilesNum: 1{if !empty($oSetting->cValue)}, initialPreview: [
                                                                '<img src="{$shopURL}/templates/{$oTemplate->cOrdner}/{$oSetting->rawAttributes.target}{$oSetting->cValue}?v={$smarty.now}" class="file-preview-image" alt="" title="" />'
                                                            ]{/if},
                                                            initialPreviewConfig: [
                                                                {ldelim}
                                                                    url: '{$shopURL}/{$PFAD_ADMIN}shoptemplate.php',
                                                                    extra: {ldelim}upload: '{$oTemplate->cOrdner}/{$oSetting->rawAttributes.target}{$oSetting->cValue}', id: 'upload-{$smarty.foreach.tplOptions.index}', token : '{$smarty.session.jtl_token}'{rdelim}
                                                                    {rdelim}
                                                            ]
                                                        {rdelim}).on('fileuploaded', function(event, data) {ldelim}
                                                            if (data.response.status === 'OK') {ldelim}
                                                                $('#logo-upload-success').show().removeClass('hidden');
                                                                $('.kv-upload-progress').addClass('hide');
                                                            {rdelim} else {ldelim}
                                                                $('#logo-upload-error').show().removeClass('hidden');
                                                            {rdelim}
                                                        {rdelim});
                                                    </script>
                                                {/if}
                                            </span>
                                        {else}
                                            <input type="hidden" name="cWert[]" value="{$oSetting->cValue|escape:"html"}" />
                                        {/if}
                                    </div>
                                </div>
                            {/foreach}
                       </div>{* /row *}
                    </div>
                </div>
            {/foreach}
            <div class="save_wrapper">
                {if isset($smarty.get.activate)}<input type="hidden" name="activate" value="1" />{/if}
                <input type="hidden" name="type" value="settings" />
                <input type="hidden" name="ordner" value="{$oTemplate->cOrdner}" />
                <input type="hidden" name="admin" value="{$admin}" />
                <button type="submit" class="btn btn-primary">{if isset($smarty.get.activate)}<i class="fa fa-share"></i> Template aktivieren{else}<i class="fa fa-save"></i> Speichern{/if}</button>
            </div>
        </div>
    </form>
{else}
    <table class="table">
        <thead>
        <tr>
            <th></th>
            <th></th>
            <th class="text-center">Status</th>
            <th class="text-center">Version</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach name="template" from=$oTemplate_arr item=oTemplate}
            <tr>
                <td class="text-vcenter text-center" width="140">
                    <div class="thumb-box thumb-sm">
                        <div class="thumb" style="background-image:url({if $oTemplate->cPreview|strlen > 0}{$shopURL}/templates/{$oTemplate->cOrdner}/{$oTemplate->cPreview}{else}{$shopURL}/gfx/keinBild.gif{/if})"></div>
                    </div>
                </td>
                <td>
                    <ul class="list-unstyled">
                        <li>
                            <h3 style="margin:0">{$oTemplate->cName}</h3>
                            {if !empty($oTemplate->cDescription)}
                                <small class="text-muted">{$oTemplate->cDescription}</small>
                            {/if}
                        </li>
                        <li>
                        <!--
                        {if !empty($oTemplate->cURL)}<a href="{$oTemplate->cURL}">{/if}
                            {$oTemplate->cAuthor}
                            {if !empty($oTemplate->cURL)}</a>
                        {/if}
                        -->
                        </li>
                        <li>
                            {if $oTemplate->bChild === true}<span class="label label-danger"><abbr title="Vererbt von {$oTemplate->cParent}">{$oTemplate->cParent}</abbr></span>{/if}

                            {if isset($oStoredTemplate_arr[$oTemplate->cOrdner])}
                                {foreach $oStoredTemplate_arr[$oTemplate->cOrdner] as $oStored}
                                    <span class="label label-warning"><abbr title="Originalversion {$oStored->cVersion} vorhanden">{$oStored->cVersion}</abbr></span>
                                {/foreach}
                            {/if}
                        </li>
                    </ul>
                </td>
                <td class="text-vcenter text-center">
                    {if !empty($oTemplate->bHasError) && $oTemplate->bHasError === true}
                        <h4 class="label-wrap">
                            <span class="label label-danger">Fehlerhaft</span>
                        </h4>
                    {elseif $oTemplate->bAktiv}
                        <h4 class="label-wrap">
                            <span class="label label-success">Aktiviert {if $oTemplate->eTyp === 'mobil'}(Mobile Endger&auml;te){/if}</span>
                        </h4>
                    {/if}
                </td>
                <td class="text-vcenter text-center">
                    <h4 class="label-wrap">
                        <span class="label label-default"><abbr title="Verzeichnis: {$oTemplate->cOrdner}">{$oTemplate->cVersion}</abbr></span>
                    </h4>
                </td>
                <td class="text-vcenter text-center">
                    {if !empty($oTemplate->bHasError) && $oTemplate->bHasError === true}
                        <span class="error"><strong>Achtung:</strong><br />Parent-Template fehlt.</span>
                    {else}
                        {if !$oTemplate->bAktiv}
                            {if $oTemplate->bEinstellungen}
                                <a class="btn btn-primary" href="shoptemplate.php?settings={$oTemplate->cOrdner}&activate=1&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> Aktivieren</a>
                            {else}
                                <a class="btn btn-primary" href="shoptemplate.php?switch={$oTemplate->cOrdner}&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> Aktivieren</a>
                            {/if}
                        {else}
                            {if $oTemplate->bEinstellungen}
                                <a class="btn btn-default" href="shoptemplate.php?settings={$oTemplate->cOrdner}&token={$smarty.session.jtl_token}"><i class="fa fa-edit"></i> Einstellungen</a>
                            {/if}
                        {/if}
                    {/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/if}
</div>
{include file='tpl_inc/footer.tpl'}