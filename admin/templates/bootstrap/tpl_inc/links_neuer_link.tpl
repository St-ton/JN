<script type="text/javascript">
    function append_file_selector() {ldelim}
        var file_input = $('<input type="file" name="Bilder[]" maxlength="2097152" accept="image/*" />'),
            container = $('<p class="multi_input vmiddle"><a href="#" title="Entfernen"><img src="{$currentTemplateDir}/gfx/layout/delete.png" class="vmiddle" /></a></p>').prepend(file_input);
        $('#file_input_wrapper').append(container);
        $(container).find('img').bind('click', function () {ldelim}
            $(file_input).parent().remove();
            return false;
        {rdelim});
        $(file_input).trigger('click');
        return false;
    {rdelim}

    {literal}
    $(function () {
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
    });

    $(window).on('load', function () {
        $('select[id="nLink3"]').change(function () {
            var $self = $(this);
            ioCall('checkSpecialSite', [$self.val(), $('input[name="kLink"]').val()], function (result) {
                if (result) {
                    $('#nLink3-error').removeClass('hidden-soft');
                    $('#nSpezialseite-name').html(result.cName);
                } else {
                    $('#nLink3-error').addClass('hidden-soft');
                    $('#nSpezialseite-name').html();
                }
            });
        }).trigger('change');
    });
    {/literal}
</script>
{if $Link->getID() > 0 && !empty($Link->getName())}
    {assign var=description value=$Link->getName()|cat:' (ID '|cat:$Link->getID()|cat:')'}
{else}
    {assign var=description value=''}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=#newLinks# cBeschreibung=$description}
<div id="content" class="container-fluid">
    <div id="settings">
        <form id="create_link" name="link_erstellen" method="post" action="links.php" enctype="multipart/form-data">
            {$jtl_token}
            <input type="hidden" name="neu_link" value="1" />
            <input type="hidden" name="kLinkgruppe" value="{$Link->getLinkGroupID()}" />
            <input type="hidden" name="kLink" value="{if $Link->getID() > 0}{$Link->getID()}{/if}" />
            <input type="hidden" name="kPlugin" value="{if $Link->getPluginID() > 0}{$Link->getPluginID()}{/if}" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Allgemein</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group{if isset($xPlausiVar_arr.cName)} error{/if}">
                        <span class="input-group-addon">
                            <label for="cName">Name{if isset($xPlausiVar_arr.cName)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                        </span>
                        <input required type="text" name="cName" id="cName" class="form-control{if isset($xPlausiVar_arr.cName)} fieldfillout{/if}" value="{if isset($xPostVar_arr.cName) && $xPostVar_arr.cName}{$xPostVar_arr.cName}{elseif !empty($Link->getName())}{$Link->getName()}{/if}" tabindex="1" />
                    </div>
                    <div class="input-group{if isset($xPlausiVar_arr.nLinkart) || isset($xPlausiVar_arr.nSpezialseite)} error{/if}">
                        <span class="input-group-addon">
                            <label>{#linkType#}{if isset($xPlausiVar_arr.nLinkart)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                        </span>
                        <div class="input-group-wrap">
                        {if $Link->getPluginID() > 0}
                            <p class="multi_input">
                                <input type="hidden" name="nLinkart" value="25" />
                                <input type="radio" id="nLink3" name="nLinkart" checked="checked" disabled="disabled" />
                                <label for="nLink3">{#linkToSpecalPage#}</label>
                                <select id="nLink3" name="nSpezialseite" disabled="disabled">
                                    <option selected="selected">Plugin</option>
                                </select>
                            </p>
                        {else}
                            <p class="multi_input" style="margin-top: 10px;">
                                <input type="radio" id="nLink1" name="nLinkart" value="1" tabindex="2" {if isset($xPostVar_arr.nLinkart) && (int)$xPostVar_arr.nLinkart === 1}checked{elseif $Link->getLinkType() === 1}checked{/if} />
                                <label for="nLink1">{#linkWithOwnContent#}</label>
                            </p>
                            <p class="multi_input">
                                <input type="radio" id="nLink2" name="nLinkart" value="2" onclick="$('#nLinkInput2').val('http://')" tabindex="3" {if isset($xPostVar_arr.nLinkart) && (int)$xPostVar_arr.nLinkart === 2}checked{elseif $Link->getLinkType() === 2}checked{/if} />
                                <label for="nLink2">{#linkToExternalURL#} (anlegen unter "Suchmaschinenname")</label>
                            </p>
                            <p class="multi_input" style="margin-bottom: 10px;">
                                <input type="radio" id="nLink3" name="nLinkart" value="3" {if isset($xPostVar_arr.nLinkart) && (int)$xPostVar_arr.nLinkart === 3}checked{elseif $Link->getLinkType() > 3}checked{/if} />
                                <label for="nLink3">{#linkToSpecalPage#}</label>
                                <select id="nLink3" name="nSpezialseite">
                                    <option value="0">{#choose#}</option>
                                    {foreach name=spezialseiten from=$oSpezialseite_arr item=oSpezialseite}
                                        <option value="{$oSpezialseite->nLinkart}" {if isset($xPostVar_arr.nSpezialseite) && $xPostVar_arr.nSpezialseite === $oSpezialseite->nLinkart}selected{elseif $Link->getLinkType() == $oSpezialseite->nLinkart}selected{/if}>{$oSpezialseite->cName}</option>
                                    {/foreach}
                                </select>
                                <span id="nLink3-error" class="hidden-soft error">{#specialSiteExists#}<span id="nSpezialseite-name"></span></span>
                            </p>
                        {/if}
                        </div>
                    </div>
                    <div class="input-group{if isset($xPlausiVar_arr.cKundengruppen)} error{/if}">
                        <span class="input-group-addon">
                            <label for="cKundengruppen">{#restrictedToCustomerGroups#}{if isset($xPlausiVar_arr.cKundengruppen)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                        </span>
                        <select required name="cKundengruppen[]" class="form-control{if isset($xPlausiVar_arr.cKundengruppen)} fieldfillout{/if}" multiple="multiple" size="6" id="cKundengruppen">
                            <option value="-1"{if isset($Link->getID()) && $Link->getID() > 0 && isset($gesetzteKundengruppen[0]) && $gesetzteKundengruppen[0]} selected{elseif isset($xPostVar_arr.cKundengruppen)}
                                {foreach name=postkndgrp from=$xPostVar_arr.cKundengruppen item=cPostKndGrp}
                                    {if $cPostKndGrp|strlen > 0 && $cPostKndGrp == "-1"}selected{/if}
                                {/foreach}
                                    {elseif !$Link->getID()}selected{/if}>{#all#}</option>

                            {foreach $kundengruppen as $kundengruppe}
                                {assign var='kKundengruppe' value=$kundengruppe->kKundengruppe}
                                {assign var=postkndgrp value='0'}
                                {if isset($xPostVar_arr.cKundengruppen)}
                                    {foreach $xPostVar_arr.cKundengruppen as $cPostKndGrp}
                                        {if $cPostKndGrp == $kKundengruppe}{assign var=postkndgrp value='1'}{/if}
                                    {/foreach}
                                {/if}
                                <option value="{$kundengruppe->kKundengruppe}" {if (isset($gesetzteKundengruppen[$kKundengruppe]) && $gesetzteKundengruppen[$kKundengruppe]) || (isset($postkndgrp) && $postkndgrp == 1)}selected{/if}>{$kundengruppe->cName}</option>
                            {/foreach}
                        </select>
                        <span class="input-group-addon">{getHelpDesc cDesc=#multipleChoice#}</span>
                    </div>
                    <div class="input-group" id="option_isActive">
                        <span class="input-group-addon"><label for="bIsActive">{#active#}</label></span>
                        <div class="input-group-wrap">
                            <select class="form-control" type="selectbox" name="bIsActive" id="bIsActive">
                                <option value="1" {if $Link->getIsEnabled() || (isset($xPostVar_arr.bIsActive) && $xPostVar_arr.bIsActive === '1')}selected{/if}>Aktiviert</option>
                                <option value="0" {if !$Link->getIsEnabled() || (isset($xPostVar_arr.bIsActive) && $xPostVar_arr.bIsActive === '0')}selected{/if}>Deaktiviert</option>
                            </select>
                        </div>
                    </div>
                    {if !isset($Link->getLinkType()) || $Link->getLinkType() != LINKTYP_LOGIN}
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cSichtbarNachLogin">{#visibleAfterLogin#}</label></span>
                        <div class="input-group-wrap">
                            <input class="form-control2" type="checkbox" name="cSichtbarNachLogin" id="cSichtbarNachLogin" value="Y" {if $Link->getVisibleLoggedInOnly() === true || (isset($xPostVar_arr.cSichtbarNachLogin) && $xPostVar_arr.cSichtbarNachLogin)}checked{/if} />
                        </div>
                    </div>
                    {/if}
                    <div class="input-group">
                        <span class="input-group-addon"><label for="bSSL">SSL</label></span>
                        <span class="input-group-wrap">
                            <select id="bSSL" class="form-control" name="bSSL">
                                <option value="0"{if $Link->getSSL() === false || (isset($xPostVar_arr.bSSL) && ($xPostVar_arr.bSSL == 0 || $xPostVar_arr.bSSL == 1))} selected="selected"{/if}>standard</option>
                                <option value="2"{if $Link->getSSL() === true || (isset($xPostVar_arr.bSSL) && $xPostVar_arr.bSSL == 2)} selected="selected"{/if}>erzwungen</option>
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cNoFollow">{#noFollow#}</label></span>
                        <div class="input-group-wrap">
                            <input class="form-control2" type="checkbox" name="cNoFollow" id="cNoFollow" value="Y" {if $Link->getNoFollow() === true || (isset($xPostVar_arr.cNoFollow) && $xPostVar_arr.cNoFollow)}checked{/if} />
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="nSort">{#sortNo#}</label></span>
                        <input class="form-control" type="text" name="nSort" id="nSort" value="{if isset($xPostVar_arr.nSort) && $xPostVar_arr.nSort}{$xPostVar_arr.nSort}{elseif $Link->getSort()}{$Link->getSort()}{/if}" tabindex="6" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="Bilder_0">Bilder</label></span>
                        <span class="input-group-wrap">
                            <div id="file_input_wrapper">
                                <p class="multi_input">
                                    <input class="form-control-upload" id="Bilder_0" name="Bilder[]" type="file" maxlength="2097152" accept="image/*" />
                                </p>
                            </div>
                        </span>
                        <span class="input-group-btn input-group-addon">
                            <button type="button" title="{#linkPicAdd#}" name="hinzufuegen" value="{#linkPicAdd#}"
                                    onclick="return append_file_selector();" class="btn btn-info">
                                <i class="fa fa-plus"></i>
                            </button>
                        </span>

                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label>{#linkPics#}</label></span>
                        <div class="input-group-wrap">
                        {if isset($cDatei_arr)}
                            {foreach name=bilder from=$cDatei_arr item=cDatei}
                                <span class="block tcenter vmiddle">
                                    <a href="links.php?kLink={$Link->getID()}&token={$smarty.session.jtl_token}&delpic=1&cName={$cDatei->cNameFull}{if isset($Link->getPluginID()) && $Link->getPluginID() > 0}{$Link->getPluginID()}{/if}"><img src="{$currentTemplateDir}/gfx/layout/remove.png" alt="delete"></a>
                                    $#{$cDatei->cName}#$
                                    <div>{$cDatei->cURL}</div>
                                </span>
                            {/foreach}
                        {/if}
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="lang">Sprache</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="cISO" id="lang">
                                {foreach name=sprachen from=$sprachen item=sprache}
                                    <option value="{$sprache->cISO}" {if $sprache->cShopStandard === 'Y'}selected="selected"{/if}>{$sprache->cNameDeutsch} {if $sprache->cShopStandard === 'Y'}(Standard){/if}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="bIsFluid">{#bIsFluidText#}</label></span>
                        <div class="input-group-wrap">
                            <input class="form-control2" type="checkbox" name="bIsFluid" id="bIsFluid" value="1" {if $Link->getIsFluid() === true || (isset($xPostVar_arr.bIsFluid) && $xPostVar_arr.bIsFluid === '1')}checked{/if} />
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cIdentifier">{#cIdentifierText#}</label></span>
                        <div class="input-group-wrap">
                            <input class="form-control" type="text" name="cIdentifier" id="cIdentifier" value="{if $Link->getIdentifier()}{$Link->getIdentifier()}{elseif isset($xPostVar_arr.bIsFluid)}$xPostVar_arr.bIsFluid{/if}" />
                        </div>
                    </div>
                </div>
            </div>

            {foreach name=sprachen from=$sprachen item=sprache}
                {assign var="cISO" value=$sprache->cISO}
                {assign var="langID" value=(int)$sprache->kSprache}
                <div id="iso_{$cISO}" class="iso_wrapper{if $sprache->cShopStandard !== 'Y'} hidden-soft{/if}">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Meta/Seo ({$sprache->cNameDeutsch})</h3>
                        </div>
                        <div class="panel-body">
                            <div class="input-group">
                                <span class="input-group-addon"><label for="cName_{$cISO}">{#showedName#}</label></span>
                                {assign var=cName_ISO value="cName_"|cat:$cISO}
                                <input class="form-control" type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($xPostVar_arr.$cName_ISO) && $xPostVar_arr.$cName_ISO}{$xPostVar_arr.$cName_ISO}{elseif !empty($Link->getName($langID))}{$Link->getName($langID)}{/if}" tabindex="7" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon"><label for="cSeo_{$cISO}">{#linkSeo#}</label></span>
                                {assign var=cSeo_ISO value="cSeo_"|cat:$cISO}
                                <input class="form-control" type="text" name="cSeo_{$cISO}" id="cSeo_{$cISO}" value="{if isset($xPostVar_arr.$cSeo_ISO) && $xPostVar_arr.$cSeo_ISO}{$xPostVar_arr.$cSeo_ISO}{elseif !empty($Link->getSEO($langID))}{$Link->getSEO($langID)}{/if}" tabindex="7" />
                            </div>
                            {assign var=cTitle_ISO value="cTitle_"|cat:$cISO}
                            <div class="input-group">
                                <span class="input-group-addon"><label for="cTitle_{$cISO}">{#linkTitle#}</label></span>
                                <span class="input-group-wrap">
                                    <input class="form-control" type="text" name="cTitle_{$cISO}" id="cTitle_{$cISO}" value="{if isset($xPostVar_arr.$cTitle_ISO) && $xPostVar_arr.$cTitle_ISO}{$xPostVar_arr.$cTitle_ISO}{elseif !empty($Link->getTitle($langID))}{$Link->getTitle($langID)}{/if}" tabindex="8" />
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#titleDesc#}</span>
                            </div>
                            <div class="input-group">
                                {assign var=cContent_ISO value="cContent_"|cat:$cISO}
                                <span class="input-group-addon"><label for="cContent_{$cISO}">{#linkContent#}</label></span>
                                <span class="input-group-wrap">
                                    <textarea class="form-control ckeditor" id="cContent_{$cISO}" name="cContent_{$cISO}" rows="10" cols="40">{if isset($xPostVar_arr.$cContent_ISO) && $xPostVar_arr.$cContent_ISO}{$xPostVar_arr.$cContent_ISO}{elseif !empty($Link->getContent($langID))}{$Link->getContent($langID)}{/if}</textarea>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#titleDesc#}</span>
                            </div>
                            <div class="input-group">
                                {assign var=cMetaTitle_ISO value="cMetaTitle_"|cat:$cISO}
                                <span class="input-group-addon"><label for="cMetaTitle_{$cISO}">{#metaTitle#}</label></span>
                                <span class="input-group-wrap">
                                    <input class="form-control" type="text" name="cMetaTitle_{$cISO}" id="cMetaTitle_{$cISO}" value="{if isset($xPostVar_arr.$cMetaTitle_ISO) && $xPostVar_arr.$cMetaTitle_ISO}{$xPostVar_arr.$cMetaTitle_ISO}{elseif !empty($Link->getMetaTitle($langID))}{$Link->getMetaTitle($langID)}{/if}" tabindex="9" />
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#metaTitleDesc#}</span>
                            </div>
                            <div class="input-group">
                            {assign var=cMetaKeywords_ISO value="cMetaKeywords_"|cat:$cISO}
                                <span class="input-group-addon"><label for="cMetaKeywords_{$cISO}">{#metaKeywords#}</label></span>
                                <span class="input-group-wrap">
                                    <input class="form-control" type="text" name="cMetaKeywords_{$cISO}" id="cMetaKeywords_{$cISO}" value="{if isset($xPostVar_arr.$cMetaKeywords_ISO) && $xPostVar_arr.$cMetaKeywords_ISO}{$xPostVar_arr.$cMetaKeywords_ISO}{elseif !empty($Link->getMetaKeyword($langID))}{$Link->getMetaKeyword($langID)}{/if}" tabindex="9" />
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#metaKeywordsDesc#}</span>
                            </div>
                            <div class="input-group">
                                {assign var=cMetaDescription_ISO value="cMetaDescription_"|cat:$cISO}
                                <span class="input-group-addon"><label for="cMetaDescription_{$cISO}">{#metaDescription#}</label></span>
                                <span class="input-group-wrap">
                                    <input class="form-control" type="text" name="cMetaDescription_{$cISO}" id="cMetaDescription_{$cISO}" value="{if isset($xPostVar_arr.$cMetaDescription_ISO) && $xPostVar_arr.$cMetaDescription_ISO}{$xPostVar_arr.$cMetaDescription_ISO}{elseif !empty($Link->getMetaDescription($langID))}{$Link->getMetaDescription($langID)}{/if}" tabindex="9" />
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#metaDescriptionDesc#}</span>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
            <div class="panel{if isset($Link->getID())} btn-group{/if}">
                <button type="submit" value="{#newLinksSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#newLinksSave#}</button>
                {if isset($Link->getID())}<button type="submit" name="continue" value="1" class="btn btn-default" id="save-and-continue">{#newLinksSave#} und weiter bearbeiten</button>{/if}
            </div>
        </form>
        {if isset($Link->getID())}
            {getRevisions type='link' key=$Link->getID() show=['cContent'] secondary=true data=$Link->getContent()}
        {/if}
    </div>
</div>