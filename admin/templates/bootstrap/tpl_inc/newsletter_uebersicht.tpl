{include file='tpl_inc/seite_header.tpl' cTitel=#newsletteroverview# cBeschreibung=#newsletterdesc# cDokuURL=#newsletterURL#}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="newsletter.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="{#changeLanguage#}">{#changeLanguage#}:</label>
                </span>
                <span class="input-group-wrap last">
                    <select id="{#changeLanguage#}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'inaktiveabonnenten'} active{/if}">
            <a data-toggle="tab" role="tab" href="#inaktiveabonnenten">{#newsletterSubscripterNotActive#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'alleabonnenten'} active{/if}">
            <a data-toggle="tab" role="tab" href="#alleabonnenten">{#newsletterAllSubscriber#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'neuerabonnenten'} active{/if}">
            <a data-toggle="tab" role="tab" href="#neuerabonnenten">{#newsletterNewSubscriber#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'newsletterqueue'} active{/if}">
            <a data-toggle="tab" role="tab" href="#newsletterqueue">{#newsletterqueue#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'newslettervorlagen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#newslettervorlagen">{#newsletterdraft#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'newslettervorlagenstd'} active{/if}">
            <a data-toggle="tab" role="tab" href="#newslettervorlagenstd">{#newsletterdraftStd#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'newsletterhistory'} active{/if}">
            <a data-toggle="tab" role="tab" href="#newsletterhistory">{#newsletterhistory#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#newsletterconfig#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="inaktiveabonnenten" class="tab-pane fade{if !isset($cTab) || $cTab === 'inaktiveabonnenten'} active in{/if}">
            {if isset($oNewsletterEmpfaenger_arr) && $oNewsletterEmpfaenger_arr|@count > 0}
                <form name="suche" method="post" action="newsletter.php">
                    {$jtl_token}
                    <input type="hidden" name="inaktiveabonnenten" value="1" />
                    <input type="hidden" name="tab" value="inaktiveabonnenten" />
                    {if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}
                        <input type="hidden" name="cSucheInaktiv" value="{$cSucheInaktiv}" />
                    {/if}
                    <input type="hidden" name="s1" value="{$oBlaetterNaviInaktiveAbonnenten->nAktuelleSeite}" />

                    <div id="newsletter-inactive-search">
                        <table class="table2">
                            <tr>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="cSucheInaktiv">{#newslettersubscriberSearch#}:</label>
                                        </span>
                                        <input class="form-control" id="cSucheInaktiv" name="cSucheInaktiv" type="text" value="{if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}{$cSucheInaktiv}{/if}" />
                                        <span class="input-group-btn">
                                            <button name="submitInaktiveAbonnentenSuche" type="submit" class="btn btn-primary" value="{#newsletterSearchBTN#}"><i class="fa fa-search"></i> {#newsletterSearchBTN#}</button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
                {include file='pagination.tpl' cSite=1 cUrl='newsletter.php' oBlaetterNavi=$oBlaetterNaviInaktiveAbonnenten hash='#inaktiveabonnenten'}
                <div id="newsletter-inactive-content">
                    <form name="inaktiveabonnentenForm" method="post" action="newsletter.php">
                        {$jtl_token}
                        <input type="hidden" name="inaktiveabonnenten" value="1" />
                        <input type="hidden" name="tab" value="inaktiveabonnenten" />
                        {if isset($cSucheInaktiv) && $cSucheInaktiv|count_characters > 0}
                            <input type="hidden" name="cSucheInaktiv" value="{$cSucheInaktiv}" />
                        {/if}
                        <input type="hidden" name="s1" value="{$oBlaetterNaviInaktiveAbonnenten->nAktuelleSeite}" />
                        <div class="panel panel-default">
                            <table class="table">
                                <tr>
                                    <th class="th-1">&nbsp;</th>
                                    <th class="tleft">{#newslettersubscriberfirstname#}</th>
                                    <th class="tleft">{#newslettersubscriberlastname#}</th>
                                    <th class="tleft">{#newslettersubscriberCustomerGrp#}</th>
                                    <th class="tleft">{#newslettersubscriberemail#}</th>
                                    <th class="tcenter">{#newslettersubscriberdate#}</th>
                                </tr>
                                {foreach name=newsletterletztenempfaenger from=$oNewsletterEmpfaenger_arr item=oNewsletterEmpfaenger}
                                    <tr class="tab_bg{$smarty.foreach.newsletterletztenempfaenger.iteration%2}">
                                        <td class="tleft">
                                            <input name="kNewsletterEmpfaenger[]" type="checkbox" value="{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}">
                                        </td>
                                        <td class="tleft">{if $oNewsletterEmpfaenger->cVorname != ""}{$oNewsletterEmpfaenger->cVorname}{else}{$oNewsletterEmpfaenger->newsVorname}{/if}</td>
                                        <td class="tleft">{if $oNewsletterEmpfaenger->cNachname != ""}{$oNewsletterEmpfaenger->cNachname}{else}{$oNewsletterEmpfaenger->newsNachname}{/if}</td>
                                        <td class="tleft">{if isset($oNewsletterEmpfaenger->cName) && $oNewsletterEmpfaenger->cName|count_characters > 0}{$oNewsletterEmpfaenger->cName}{else}{#NotAvailable#}{/if}</td>
                                        <td class="tleft">{$oNewsletterEmpfaenger->cEmail}{if $oNewsletterEmpfaenger->nAktiv == 0} *{/if}</td>
                                        <td class="tcenter">{$oNewsletterEmpfaenger->Datum}</td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td class="TD1">
                                        <input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                                    </td>
                                    <td colspan="6" class="TD7"><label for="ALLMSGS2">{#globalSelectAll#}</label></td>
                                </tr>
                            </table>
                            <div class="panel-footer">
                                <div class="btn-group">
                                    <button name="abonnentfreischaltenSubmit" type="submit" value="{#newsletterUnlock#}" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> {#newsletterUnlock#}</button>
                                    <button class="btn btn-danger" name="abonnentloeschenSubmit" type="submit" value="{#newsletterdelete#}"><i class="fa fa-trash"></i> markierte {#newsletterdelete#}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="alleabonnenten" class="tab-pane fade{if isset($cTab) && $cTab === 'alleabonnenten'} active in{/if}">
            {if isset($oAbonnenten_arr) && $oAbonnenten_arr|@count > 0}
                <form name="suche" method="post" action="newsletter.php">
                    {$jtl_token}
                    <input type="hidden" name="Suche" value="1" />
                    <input type="hidden" name="tab" value="alleabonnenten" />
                    <input type="hidden" name="s5" value="{$oBlaetterNaviAlleAbonnenten->nAktuelleSeite}" />
                    {if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}
                        <input type="hidden" name="cSucheAktiv" value="{$cSucheAktiv}" />
                    {/if}
                    <div id="newsletter-all-search">
                        <table class="table2">
                            <tr>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="cSucheAktiv">{#newslettersubscriberSearch#}</label>
                                        </span>
                                        <input id="cSucheAktiv" name="cSucheAktiv" class="form-control" type="text" value="{if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}{$cSucheAktiv}{/if}" />
                                        <span class="input-group-btn">
                                            <button name="submitSuche" type="submit" value="{#newsletterSearchBTN#}" class="btn btn-info"><i class="fa fa-search"></i> {#newsletterSearchBTN#}</button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <br />
                    </div>
                </form>
                {include file='pagination.tpl' cSite=5 cUrl='newsletter.php' oBlaetterNavi=$oBlaetterNaviAlleAbonnenten hash='#alleabonnenten'}
                <!-- Uebersicht Newsletterhistory -->
                <form method="post" action="newsletter.php">
                    {$jtl_token}
                    <input name="newsletterabonnent_loeschen" type="hidden" value="1">
                    <input type="hidden" name="tab" value="alleabonnenten">
                    <div id="newsletter-all-content">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{#newsletterAllSubscriber#}</h3>
                            </div>
                            <table class="table">
                                <tr>
                                    <th class="th-1">&nbsp;</th>
                                    <th class="tleft">{#newslettersubscribername#}</th>
                                    <th class="tleft">{#newslettersubscriberCustomerGrp#}</th>
                                    <th class="tleft">{#newslettersubscriberemail#}</th>
                                    <th class="tcenter">{#newslettersubscriberdate#}</th>
                                    <th class="tcenter">{#newslettersubscriberLastNewsletter#}</th>
                                </tr>
                                {foreach name=newsletterabonnenten from=$oAbonnenten_arr item=oAbonnenten}
                                    <tr class="tab_bg{$smarty.foreach.newsletterabonnenten.iteration%2}">
                                        <td class="tleft">
                                            <input name="kNewsletterEmpfaenger[]" type="checkbox" value="{$oAbonnenten->kNewsletterEmpfaenger}" />
                                        </td>
                                        <td class="tleft">{$oAbonnenten->cVorname} {$oAbonnenten->cNachname}</td>
                                        <td class="tleft">{$oAbonnenten->cName}</td>
                                        <td class="tleft">{$oAbonnenten->cEmail}</td>
                                        <td class="tcenter">{$oAbonnenten->dEingetragen_de}</td>
                                        <td class="tcenter">{$oAbonnenten->dLetzterNewsletter_de}</td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td class="TD1">
                                        <input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);">
                                    </td>
                                    <td colspan="6" class="TD7"><label for="ALLMSGS3">{#globalSelectAll#}</label></td>
                                </tr>
                            </table>
                            <div class="panel-footer">
                                <button name="loeschen" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> markierte l&ouml;schen</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {if isset($cSucheAktiv) && $cSucheAktiv|count_characters > 0}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="tab" type="hidden" value="alleabonnenten" />
                        <input name="submitAbo" type="submit" value="{#newsletterNewSearch#}" class="btn btn-primary" />
                    </form>
                {/if}
            {/if}
        </div>
        <div id="neuerabonnenten" class="tab-pane fade{if isset($cTab) && $cTab == 'neuerabonnenten'} active in{/if}">
            <form method="post" action="newsletter.php">
                {$jtl_token}
                <input type="hidden" name="newsletterabonnent_neu" value="1">
                <input name="tab" type="hidden" value="neuerabonnenten">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#newsletterNewSubscriber#}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cAnrede">{#newslettersubscriberanrede#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select class="form-control" name="cAnrede" id="cAnrede">
                                    <option value="m">Herr</option>
                                    <option value="w">Frau</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cVorname">{#newslettersubscriberfirstname#}</label>
                            </span>
                            <input class="form-control" type="text" name="cVorname" id="cVorname" value="{if isset($oNewsletter->cVorname)}{$oNewsletter->cVorname}{/if}" />
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cNachname">{#newslettersubscriberlastname#}</label>
                            </span>
                            <input class="form-control" type="text" name="cNachname" id="cNachname" value="{if isset($oNewsletter->cNachname)}{$oNewsletter->cNachname}{/if}" />
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cEmail">{#newslettersubscriberemail#}</label>
                            </span>
                            <input class="form-control" type="text" name="cEmail" id="cEmail" value="{if isset($oNewsletter->cEmail)}{$oNewsletter->cEmail}{/if}" />
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kSprache">{#newslettersubscriberlang#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select class="form-control" name="kSprache" id="kSprache">
                                    {foreach from=$Sprachen item=oSprache}
                                        <option value="{$oSprache->kSprache}">{$oSprache->cNameDeutsch}</option>
                                    {/foreach}
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button name="speichern" type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="newsletterqueue" class="tab-pane fade{if isset($cTab) && $cTab === 'newsletterqueue'} active in{/if}">
            {if isset($oNewsletterQueue_arr) && $oNewsletterQueue_arr|@count > 0}
                <form method="post" action="newsletter.php">
                    {$jtl_token}
                    <input name="newsletterqueue" type="hidden" value="1">
                    <input name="tab" type="hidden" value="newsletterqueue">
                    <input name="s2" type="hidden" value="{$oBlaetterNaviNLWarteschlage->nAktuelleSeite}">
                    {include file='pagination.tpl' cSite=2 cUrl='newsletter.php' oBlaetterNavi=$oBlaetterNaviNLWarteschlage hash='#newsletterqueue'}
                    <div id="newsletter-queue-content">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{#newsletterqueue#}</h3>
                            </div>
                            <table class="table">
                                <tr>
                                    <th class="th-1" style="width: 4%;">&nbsp;</th>
                                    <th class="th-2" style="width: 40%;">{#newsletterqueuesubject#}</th>
                                    <th class="th-3" style="width: 30%;">{#newsletterqueuedate#}</th>
                                    <th class="th-4" style="width: 26%;">{#newsletterqueueimprovement#}</th>
                                    <th class="th-5" style="width: 26%;">{#newsletterqueuecount#}</th>
                                    <th class="th-6" style="width: 26%;">{#newsletterqueuecustomergrp#}</th>
                                </tr>
                                {foreach name=newsletterqueue from=$oNewsletterQueue_arr item=oNewsletterQueue}
                                    {if isset($oNewsletterQueue->nAnzahlEmpfaenger) && $oNewsletterQueue->nAnzahlEmpfaenger > 0}
                                        <tr class="tab_bg{$smarty.foreach.newsletterqueue.iteration%2}">
                                            <td class="TD1">
                                                <input name="kNewsletterQueue[]" type="checkbox" value="{$oNewsletterQueue->kNewsletterQueue}">
                                            </td>
                                            <td class="TD2">{$oNewsletterQueue->cBetreff}</td>
                                            <td class="TD3">{$oNewsletterQueue->Datum}</td>
                                            <td class="TD4">{$oNewsletterQueue->nLimitN}</td>
                                            <td class="TD5">{$oNewsletterQueue->nAnzahlEmpfaenger}</td>
                                            <td class="TD6">
                                                {foreach name=kundengruppen from=$oNewsletterQueue->cKundengruppe_arr item=cKundengruppe}
                                                    {if $cKundengruppe == "0"}Newsletterempf&auml;nger ohne Kundenkonto{if !$smarty.foreach.kundengruppen.last}, {/if}{/if}
                                                    {foreach name=kundengruppe from=$oKundengruppe_arr item=oKundengruppe}
                                                        {if $cKundengruppe == $oKundengruppe->kKundengruppe}{$oKundengruppe->cName}{if !$smarty.foreach.kundengruppen.last}, {/if}{/if}
                                                    {/foreach}
                                                {/foreach}
                                            </td>
                                        </tr>
                                    {/if}
                                {/foreach}
                                <tr>
                                    <td class="TD1">
                                        <input name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);">
                                    </td>
                                    <td colspan="6" class="TD7"><label for="ALLMSGS4">{#globalSelectAll#}</label></td>
                                </tr>
                            </table>
                            <div class="panel-footer">
                                <button name="loeschen" type="submit" value="{#newsletterdelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#newsletterdelete#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="newslettervorlagen" class="tab-pane fade{if isset($cTab) && $cTab == 'newslettervorlagen'} active in{/if}">
            <form method="post" action="newsletter.php">
                {$jtl_token}
                <input name="newslettervorlagen" type="hidden" value="1">
                <input name="tab" type="hidden" value="newslettervorlagen">
                <input name="s3" type="hidden" value="{$oBlaetterNaviNLVorlagen->nAktuelleSeite}">

                {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                    {include file='pagination.tpl' cSite=3 cUrl='newsletter.php' oBlaetterNavi=$oBlaetterNaviNLVorlagen hash='#newslettervorlagen'}
                {/if}

                {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                    <div id="newsletter-vorlagen-content">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Vorhandene Vorlagen</h3>
                            </div>
                            <table class="table">
                                <tr>
                                    <th class="th-1">&nbsp;</th>
                                    <th class="th-2">{#newsletterdraftname#}</th>
                                    <th class="th-3">{#newsletterdraftsubject#}</th>
                                    <th class="th-4">{#newsletterdraftStdShort#}</th>
                                    <th class="th-5" style="width: 385px;">{#newsletterdraftoptions#}</th>
                                </tr>
                                {foreach name=newslettervorlage from=$oNewsletterVorlage_arr item=oNewsletterVorlage}
                                    <tr class="tab_bg{$smarty.foreach.newslettervorlage.iteration%2}">
                                        <td class="TD1">
                                            <input name="kNewsletterVorlage[]" type="checkbox" value="{$oNewsletterVorlage->kNewsletterVorlage}">
                                        </td>
                                        <td class="TD2">{$oNewsletterVorlage->cName}</td>
                                        <td class="TD3">{$oNewsletterVorlage->cBetreff}</td>
                                        <td class="TD4">
                                            {if $oNewsletterVorlage->kNewslettervorlageStd > 0}
                                                {#yes#}
                                            {else}
                                                {#no#}
                                            {/if}
                                        </td>
                                        <td class="TD5">
                                            <div class="btn-group">
                                                <a class="btn btn-default" href="newsletter.php?&vorschau={$oNewsletterVorlage->kNewsletterVorlage}&iframe=1&tab=newslettervorlagen&token={$smarty.session.jtl_token}" title="{#newsletterPreview#}"><i class="fa fa-eye"></i></a>
                                                {if $oNewsletterVorlage->kNewslettervorlageStd > 0}
                                                    <a class="btn btn-default" href="newsletter.php?newslettervorlagenstd=1&editieren={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}" title="Bearbeiten"><i class="fa fa-edit"></i></a>
                                                {else}
                                                    <a class="btn btn-default" href="newsletter.php?newslettervorlagen=1&editieren={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}" title="Bearbeiten"><i class="fa fa-edit"></i></a>
                                                {/if}
                                                <a class="btn btn-default" href="newsletter.php?newslettervorlagen=1&vorbereiten={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}" title="{#newsletterprepare#}">{#newsletterprepare#}</a>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td class="TD1">
                                        <input name="ALLMSGS" id="ALLMSGS5" type="checkbox" onclick="AllMessages(this.form);">
                                    </td>
                                    <td colspan="6" class="TD7"><label for="ALLMSGS5">{#globalSelectAll#}</label></td>
                                </tr>
                            </table>
                            <div class="panel-footer">
                                <div class="{if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}btn-group{/if}">
                                    <button name="vorlage_erstellen" class="btn btn-primary" type="submit">{#newsletterdraftcreate#}</button>
                                    {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                                        <button class="btn btn-danger" name="loeschen" type="submit" value="{#newsletterdelete#}"><i class="fa fa-trash"></i> {#newsletterdelete#}</button>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                    <div class="submit {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}btn-group{/if}">
                        <button name="vorlage_erstellen" class="btn btn-primary" type="submit">{#newsletterdraftcreate#}</button>
                        {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                            <button class="btn btn-danger" name="loeschen" type="submit" value="{#newsletterdelete#}"><i class="fa fa-trash"></i> {#newsletterdelete#}</button>
                        {/if}
                    </div>
                {/if}
            </form>
        </div>
        <div id="newslettervorlagenstd" class="tab-pane fade{if isset($cTab) && $cTab == 'newslettervorlagenstd'} active in{/if}">
            {if isset($oNewslettervorlageStd_arr) && $oNewslettervorlageStd_arr|@count > 0}
                <form method="post" action="newsletter.php">
                    {$jtl_token}
                    <input name="newslettervorlagenstd" type="hidden" value="1" />
                    <input name="vorlage_std_erstellen" type="hidden" value="1" />
                    <input name="tab" type="hidden" value="newslettervorlagenstd" />
                    <input name="s6" type="hidden" value="{$oBlaetterNaviNLVorlagen->nAktuelleSeite}" />

                    <div id="newsletter-vorlage-std-content">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{#newsletterdraftStd#}</h3>
                            </div>
                            <table class="table">
                                <tr>
                                    <th class="th-1">{#newsletterdraftname#}</th>
                                    <th class="th-2">{#newsletterdraftStdPicture#}</th>
                                </tr>
                                {foreach name=newslettervorlagestsd from=$oNewslettervorlageStd_arr item=oNewslettervorlageStd}
                                    <tr class="tab_bg{$smarty.foreach.newslettervorlagestsd.iteration%2}">
                                        <td class="TD1">
                                            <input name="kNewsletterVorlageStd" id="knvls-{$smarty.foreach.newslettervorlagestsd.iteration}" type="radio" value="{$oNewslettervorlageStd->kNewslettervorlageStd}" /> <label for="knvls-{$smarty.foreach.newslettervorlagestsd.iteration}">{$oNewslettervorlageStd->cName}</label>
                                        </td>
                                        <td class="TD2" valign="top">{$oNewslettervorlageStd->cBild}</td>
                                    </tr>
                                {/foreach}
                            </table>
                            <div class="panel-footer">
                                <button name="submitVorlageStd" type="submit" value="{#newsletterdraftStdUse#}" class="btn btn-default">{#newsletterdraftStdUse#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="newsletterhistory" class="tab-pane fade{if isset($cTab) && $cTab === 'newsletterhistory'} active in{/if}">
            {if isset($oNewsletterHistory_arr) && $oNewsletterHistory_arr|@count > 0}
                <form method="post" action="newsletter.php">
                    {$jtl_token}
                    <input name="newsletterhistory" type="hidden" value="1">
                    <input name="tab" type="hidden" value="newsletterhistory">
                    <input name="s4" type="hidden" value="{$oBlaetterNaviNLHistory->nAktuelleSeite}">
                    {include file='pagination.tpl' cSite=4 cUrl='newsletter.php' oBlaetterNavi=$oBlaetterNaviNLHistory hash='#newsletterhistory'}
                    <div id="newsletter-history-content">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{#newsletterhistory#}</h3>
                            </div>
                            <table class="table">
                                <tr>
                                    <th class="th-1">&nbsp;</th>
                                    <th class="tleft">{#newsletterhistorysubject#}</th>
                                    <th class="tleft">{#newsletterhistorycount#}</th>
                                    <th class="tleft">{#newsletterqueuecustomergrp#}</th>
                                    <th class="tcenter">{#newsletterhistorydate#}</th>
                                </tr>
                                {foreach name=newsletterhistory from=$oNewsletterHistory_arr item=oNewsletterHistory}
                                    <tr class="tab_bg{$smarty.foreach.newsletterhistory.iteration%2}">
                                        <td class="tleft">
                                            <input name="kNewsletterHistory[]" type="checkbox" value="{$oNewsletterHistory->kNewsletterHistory}">
                                        </td>
                                        <td class="tleft">
                                            <a href="newsletter.php?newsletterhistory=1&anzeigen={$oNewsletterHistory->kNewsletterHistory}&tab=newsletterhistory&token={$smarty.session.jtl_token}">{$oNewsletterHistory->cBetreff}</a>
                                        </td>
                                        <td class="tleft">{$oNewsletterHistory->nAnzahl}</td>
                                        <td class="tleft">{$oNewsletterHistory->cKundengruppe}</td>
                                        <td class="tcenter">{$oNewsletterHistory->Datum}</td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td class="TD1">
                                        <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                    </td>
                                    <td colspan="6" class="TD7"><label for="ALLMSGS">{#globalSelectAll#}</label></td>
                                </tr>
                            </table>
                            <div class="panel-footer">
                                <button name="loeschen" type="submit" class="btn btn-danger" value="{#newsletterdelete#}"><i class="fa fa-trash"></i> {#newsletterdelete#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='newsletter.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div><!-- .tab-content-->
</div><!-- #content -->
