<link type="text/css" rel="stylesheet" href="{$shopURL}/{$PFAD_ADMIN}{$currentTemplateDir}js/js_calender/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css" media="screen" />
<script type="text/javascript" src="{$shopURL}/{$PFAD_ADMIN}{$currentTemplateDir}js/js_calender/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js"></script>
<script type="text/javascript">
    var fields = 0;

    function neu() {ldelim}
        if (fields != 10) {ldelim}
            document.getElementById('ArtNr').innerHTML += "<input name='cArtNr[]' type='text' class='field'>";
            fields += 1;
        {rdelim} else {ldelim}
            document.getElementById('ArtNr').innerHTML += "";
            document.form.add.disabled = true;
        {rdelim}
    {rdelim}

    function checkNewsletterSend() {ldelim}
        var bCheck = confirm("{#newsletterSendAuthentication#}");

        if (bCheck) {ldelim}
            var input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = 'speichern_und_senden';
            input1.value = '1';
            document.getElementById('formnewslettervorlage').appendChild(input1);
            document.formnewslettervorlage.submit();
        {rdelim}
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#newsletterdraft# cBeschreibung=#newsletterdraftdesc#}
<div id="content" class="container-fluid">
    {if !empty($cPlausiValue_arr)}
        <div class="alert alert-danger">
            <p>Bitte f&uuml;llen Sie alle Pflichtfelder aus.</p>
        </div>
    {/if}
    <form name="formnewslettervorlagestd" id="formnewslettervorlagestd" method="post" action="newsletter.php" enctype="multipart/form-data">
        {$jtl_token}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($oNewslettervorlageStd->cName)}{$oNewslettervorlageStd->cName}{/if} bearbeiten</h3>
            </div>
            <div class="panel-body">
                {$jtl_token}
                <input name="newslettervorlagenstd" type="hidden" value="1">
                <input name="vorlage_std_speichern" type="hidden" value="1">
                <input name="tab" type="hidden" value="newslettervorlagenstd">

                {if isset($oNewslettervorlageStd->kNewslettervorlageStd) && $oNewslettervorlageStd->kNewslettervorlageStd > 0}
                    <input name="kNewslettervorlageStd" type="hidden" value="{$oNewslettervorlageStd->kNewslettervorlageStd}">
                {elseif isset($cPostVar_arr.kNewslettervorlageStd) && $cPostVar_arr.kNewslettervorlageStd > 0}
                    <input name="kNewslettervorlageStd" type="hidden" value="{$cPostVar_arr.kNewslettervorlageStd}">
                {/if}
                {if isset($oNewslettervorlageStd->kNewsletterVorlage) && $oNewslettervorlageStd->kNewsletterVorlage > 0}
                    <input name="kNewsletterVorlage" type="hidden" value="{$oNewslettervorlageStd->kNewsletterVorlage}">
                {elseif isset($cPostVar_arr.kNewslettervorlage) && $cPostVar_arr.kNewslettervorlage > 0}
                    <input name="kNewsletterVorlage" type="hidden" value="{$cPostVar_arr.kNewslettervorlage}">
                {/if}

                <div class="input-group{if isset($cPlausiValue_arr.cName)} error{/if}">
                    <div class="input-group-addon">
                        <label for="cName">{#newsletterdraftname#}</label>
                    </div>
                    <input{if isset($cPlausiValue_arr.cName)} placeholder="{#newsletterdraftFillOut#}"{/if} id="cName" name="cName" type="text" class="form-control {if isset($cPlausiValue_arr.cName)}fieldfillout{else}field{/if}" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($oNewslettervorlageStd->cName)}{$oNewslettervorlageStd->cName}{/if}">
                </div>

                <div class="input-group{if isset($cPlausiValue_arr.cBetreff)} error{/if}">
                    <div class="input-group-addon">
                        <label for="cBetreff">{#newsletterdraftsubject#}</label>
                    </div>
                    <input{if isset($cPlausiValue_arr.cBetreff)} placeholder="{#newsletterdraftFillOut#}"{/if} id="cBetreff" name="cBetreff" type="text" class="form-control {if isset($cPlausiValue_arr.cBetreff)}fieldfillout{else}field{/if}" value="{if isset($cPostVar_arr.cBetreff)}{$cPostVar_arr.cBetreff}{elseif isset($oNewslettervorlageStd->cBetreff)}{$oNewslettervorlageStd->cBetreff}{/if}">
                </div>
                <div class="input-group{if isset($cPlausiValue_arr.kKundengruppe_arr)} error{/if}">
                    <div class="input-group-addon">
                        <label for="kKundengruppeSelect">{#newslettercustomergrp#}</label>
                    </div>
                    <div class="input-group-wrap">
                        <select id="kKundengruppeSelect" name="kKundengruppe[]" multiple="multiple" class="form-control {if isset($cPlausiValue_arr.kKundengruppe_arr)}fieldfillout{else}combo{/if}">
                            <option value="0"
                                {if isset($kKundengruppe_arr)}
                                    {foreach name=kkundengruppen from=$kKundengruppe_arr item=kKundengruppe}
                                        {if $kKundengruppe == "0"}selected{/if}
                                    {/foreach}
                                {elseif isset($cPostVar_arr.kKundengruppe)}
                                    {foreach name=kkundengruppen from=$cPostVar_arr.kKundengruppe item=kKundengruppe}
                                        {if $kKundengruppe == "0"}selected{/if}
                                    {/foreach}
                                {/if}>Newsletterempf&auml;nger ohne Kundenkonto
                            </option>
                            {foreach name=kundengruppen from=$oKundengruppe_arr item=oKundengruppe}
                                <option value="{$oKundengruppe->kKundengruppe}"
                                    {if isset($kKundengruppe_arr)}
                                        {foreach name=kkundengruppen from=$kKundengruppe_arr item=kKundengruppe}
                                            {if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
                                        {/foreach}
                                    {elseif isset($cPostVar_arr.kKundengruppe)}
                                        {foreach name=kkundengruppen from=$cPostVar_arr.kKundengruppe item=kKundengruppe}
                                            {if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
                                        {/foreach}
                                    {/if}>{$oKundengruppe->cName}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-group-addon">
                        <label for="cArt">{#newsletterdraftcharacter#}</label>
                    </div>
                    <div class="input-group-wrap">
                        <select id="cArt" name="cArt" class="form-control combo">
                            <option {if isset($oNewslettervorlageStd->cArt) && $oNewslettervorlageStd->cArt === 'text/html'}selected{/if}>text/html</option>
                            <option {if isset($oNewslettervorlageStd->cArt) && $oNewslettervorlageStd->cArt === 'text'}selected{/if}>text</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-group-addon">
                        <label for="dTag">{#newsletterdraftdate#}</label>
                    </div>
                    <div class="input-group-wrap">
                        <select id="dTag" name="dTag" class="form-control combo">
                            {section name=dTag start=1 loop=32 step=1}
                                {if $smarty.section.dTag.index < 10}
                                    <option value="0{$smarty.section.dTag.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[0] == $smarty.section.dTag.index} selected{/if}{else}{if $smarty.now|date_format:"%d" == $smarty.section.dTag.index} selected{/if}{/if}>
                                        0{$smarty.section.dTag.index}
                                    </option>
                                {else}
                                    <option value="{$smarty.section.dTag.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[0] == $smarty.section.dTag.index} selected{/if}{else}{if $smarty.now|date_format:"%d" == $smarty.section.dTag.index} selected{/if}{/if}>
                                        {$smarty.section.dTag.index}
                                    </option>
                                {/if}
                            {/section}
                        </select>
                    </div>
                    <div class="input-group-addon">
                        <label for="dMonat">.</label>
                    </div>
                    <div class="input-group-wrap">
                        <select id="dMonat" name="dMonat" class="form-control combo">
                            {section name=dMonat start=1 loop=13 step=1}
                                {if $smarty.section.dMonat.index < 10}
                                    <option value="0{$smarty.section.dMonat.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[1] == $smarty.section.dMonat.index} selected{/if}{else}{if $smarty.now|date_format:"%m" == $smarty.section.dMonat.index} selected{/if}{/if}>
                                        0{$smarty.section.dMonat.index}
                                    </option>
                                {else}
                                    <option value="{$smarty.section.dMonat.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[1] == $smarty.section.dMonat.index} selected{/if}{else}{if $smarty.now|date_format:"%m" == $smarty.section.dMonat.index} selected{/if}{/if}>
                                        {$smarty.section.dMonat.index}
                                    </option>
                                {/if}
                            {/section}
                        </select>
                    </div>
                    <div class="input-group-addon">
                        <label for="dJahr">.</label>
                    </div>
                    <div class="input-group-wrap">
                        <select id="dJahr" name="dJahr" class="form-control combo">
                            {$Y = $smarty.now|date_format:"%Y"}
                            {section name=dJahr start=$Y loop=($Y+2) step=1}
                                <option value="{$smarty.section.dJahr.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[2] == $smarty.section.dJahr.index} selected{/if}{else}{if $smarty.now|date_format:"%Y" == $smarty.section.dJahr.index} selected{/if}{/if}>
                                    {$smarty.section.dJahr.index}
                                </option>
                            {/section}
                        </select>
                    </div>
                    <div class="input-group-addon">
                        <label for="dStunde">-</label>
                    </div>
                    <div class="input-group-wrap">
                        <select id="dStunde" name="dStunde" class="form-control combo">
                            {section name=dStunde start=0 loop=24 step=1}
                                {if $smarty.section.dStunde.index < 10}
                                    <option value="0{$smarty.section.dStunde.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[3] == $smarty.section.dStunde.index} selected{/if}{else}{if $smarty.now|date_format:"%H" == $smarty.section.dStunde.index} selected{/if}{/if}>
                                        0{$smarty.section.dStunde.index}
                                    </option>
                                {else}
                                    <option value="{$smarty.section.dStunde.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[3] == $smarty.section.dStunde.index} selected{/if}{else}{if $smarty.now|date_format:"%H" == $smarty.section.dStunde.index} selected{/if}{/if}>
                                        {$smarty.section.dStunde.index}
                                    </option>
                                {/if}
                            {/section}
                        </select>
                    </div>
                    <div class="input-group-addon">
                        <label for="dMinute">:</label>
                    </div>
                    <div class="input-group-wrap">
                        <select id="dMinute" name="dMinute" class="form-control combo">
                            {section name=dMinute start=0 loop=60 step=1}
                                {if $smarty.section.dMinute.index < 10}
                                    <option value="0{$smarty.section.dMinute.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[4] == $smarty.section.dMinute.index} selected{/if}{else}{if $smarty.now|date_format:"%M" == $smarty.section.dMinute.index} selected{/if}{/if}>
                                        0{$smarty.section.dMinute.index}
                                    </option>
                                {else}
                                    <option value="{$smarty.section.dMinute.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[4] == $smarty.section.dMinute.index} selected{/if}{else}{if $smarty.now|date_format:"%M" == $smarty.section.dMinute.index} selected{/if}{/if}>
                                        {$smarty.section.dMinute.index}
                                    </option>
                                {/if}
                            {/section}
                        </select>
                    </div>
                    <div class="input-group-addon">{#newsletterdraftformat#}</div>
                </div>

                <div class="input-group">
                    <div class="input-group-addon">
                        <label for="kKampagneselect">{#newslettercampaign#}</label>
                    </div>
                    <div class="input-group-wrap">
                        <select id="kKampagneselect" name="kKampagne" class="form-control">
                            <option value="0"></option>
                            {foreach name="" from=$oKampagne_arr item=oKampagne}
                                <option value="{$oKampagne->kKampagne}"{if (isset($oKampagne->kKampagne) && isset($oNewslettervorlageStd->kKampagn) && $oKampagne->kKampagne == $oNewslettervorlageStd->kKampagne) || (isset($cPostVar_arr.kKampagne) && isset($oKampagne->kKampagne) && $cPostVar_arr.kKampagne == $oKampagne->kKampagne)} selected{/if}>
                                    {$oKampagne->cName}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-group-addon">
                        <label for="assign_article_list">{#newsletterartnr#}</label>
                    </div>
                    <input class="form-control" name="cArtikel" id="assign_article_list" type="text" value="{if !empty($cPostVar_arr.cArtikel)}{$cPostVar_arr.cArtikel}{elseif isset($oNewslettervorlageStd->cArtikel)}{$oNewslettervorlageStd->cArtikel}{/if}" />
                    <div class="input-group-btn">
                        <a href="#" class="btn btn-success" id="show_article_list"><i class="fa fa-search"></i> Artikel verwalten</a>
                    </div>
                </div>
                <div id="ajax_list_picker1" class="ajax_list_picker article">{include file="tpl_inc/popup_artikelsuche.tpl"}</div>
                <div class="input-group">
                    <div class="input-group-addon">
                        <label for="assign_manufacturer_list">{#newslettermanufacturer#}</label>
                    </div>
                    <input class="form-control" id="assign_manufacturer_list" name="cHersteller" type="text" value="{if !empty($cPostVar_arr.cHersteller)}{$cPostVar_arr.cHersteller}{elseif isset($oNewslettervorlageStd->cHersteller)}{$oNewslettervorlageStd->cHersteller}{/if}">
                    <div class="input-group-btn">
                        <a href="#" class="btn btn-success" id="show_manufacturer_list"><i class="fa fa-search"></i> Hersteller verwalten</a>
                    </div>
                </div>
                <div id="ajax_list_picker2" class="ajax_list_picker manufacturer">{include file="tpl_inc/popup_herstellersuche.tpl"}</div>

                <div class="input-group">
                    <div class="input-group-addon">
                        <label for="assign_categories_list">{#newslettercategory#}</label>
                    </div>
                    <input class="form-control" id="assign_categories_list" name="cKategorie" type="text" value="{if !empty($cPostVar_arr.cKategorie)}{$cPostVar_arr.cKategorie}{elseif isset($oNewslettervorlageStd->cKategorie)}{$oNewslettervorlageStd->cKategorie}{/if}" />
                    <div class="input-group-btn">
                        <a href="#" class="btn btn-success" id="show_categories_list"><i class="fa fa-search"></i> Kategorien verwalten</a>
                    </div>
                </div>
                <div id="ajax_list_picker3" class="ajax_list_picker categories">{include file="tpl_inc/popup_kategoriesuche.tpl"}</div>
                {if isset($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) && $oNewslettervorlageStd->oNewslettervorlageStdVar_arr|@count > 0}
                    {foreach name=newslettervorlagestdvar from=$oNewslettervorlageStd->oNewslettervorlageStdVar_arr item=oNewslettervorlageStdVar}
                        {if $oNewslettervorlageStdVar->cTyp === 'BILD'}
                            {if isset($oNewslettervorlageStdVar->cInhalt) && $oNewslettervorlageStdVar->cInhalt|strlen > 0}
                                <img src="{$oNewslettervorlageStdVar->cInhalt}?={$nRand}" /><br /><br class="clear" />
                            {/if}
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <label for="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}">{$oNewslettervorlageStdVar->cName}</label>
                                </div>
                                <div class="input-group-wrap">
                                    <input id="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" name="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" type="file" accept="image/*" />
                                </div>
                            </div>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <label for="cLinkURL">{#newsletterPicLink#}</label>
                                </div>
                                <input id="cLinkURL" name="cLinkURL" type="text" class="form-control" value="{if !empty($cPostVar_arr.cLinkURL)}{$cPostVar_arr.cLinkURL}{elseif !empty($oNewslettervorlageStdVar->cLinkURL)}{$oNewslettervorlageStdVar->cLinkURL}{/if}" />
                            </div>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <label for="cAltTag">{#newsletterAltTag#}</label>
                                </div>
                                <input class="form-control" id="cAltTag" name="cAltTag" type="text" value="{if !empty($cPostVar_arr.cAltTag)}{$cPostVar_arr.cAltTag}{elseif !empty($oNewslettervorlageStdVar->cAltTag)}{$oNewslettervorlageStdVar->cAltTag}{/if}" />
                            </div>
                        {elseif $oNewslettervorlageStdVar->cTyp === 'TEXT'}
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <label for="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}">{$oNewslettervorlageStdVar->cName}</label>
                                </div>
                                <textarea id="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" class="form-control codemirror smarty" name="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" style="width: 500px; height: 400px;">{if isset($oNewslettervorlageStdVar->cInhalt) && $oNewslettervorlageStdVar->cInhalt|strlen > 0}{$oNewslettervorlageStdVar->cInhalt}{/if}</textarea>
                            </div>
                        {/if}
                    {/foreach}
                {/if}
            </div>
            <div class="panel-footer">
                <div class="btn-group">
                    {if (isset($oNewslettervorlageStd->kNewsletterVorlage) && $oNewslettervorlageStd->kNewsletterVorlage > 0) || (isset($cPostVar_arr.kNewslettervorlage) && $cPostVar_arr.kNewslettervorlage > 0)}
                        <a class="btn btn-default" href="newsletter.php?tab=newslettervorlagen&token={$smarty.session.jtl_token}"><i class="fa fa-angle-double-left"></i> {#newsletterback#}</a>
                    {else}
                        <a class="btn btn-default" href="newsletter.php?tab=newslettervorlagenstd&token={$smarty.session.jtl_token}"><i class="fa fa-angle-double-left"></i> {#newsletterback#}</a>
                    {/if}
                    <button class="btn btn-primary" name="speichern" type="submit" value="{#newsletterdraftsave#}"><i class="fa fa-save"></i> {#newsletterdraftsave#}</button>
                </div>
            </div>
        </div>
    </form>
</div>
