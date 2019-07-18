{include file='tpl_inc/seite_header.tpl' cTitel=__('newsletteroverview') cBeschreibung=__('newsletterdesc') cDokuURL=__('newsletterURL')}
<div id="content" class="container-fluid">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' action='newsletter.php'}
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if !isset($cTab) || $cTab === 'inaktiveabonnenten'} active{/if}" data-toggle="tab" role="tab" href="#inaktiveabonnenten">
                        {__('newsletterSubscripterNotActive')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'alleabonnenten'} active{/if}" data-toggle="tab" role="tab" href="#alleabonnenten">
                        {__('newsletterAllSubscriber')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'neuerabonnenten'} active{/if}" data-toggle="tab" role="tab" href="#neuerabonnenten">
                        {__('newsletterNewSubscriber')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'newsletterqueue'} active{/if}" data-toggle="tab" role="tab" href="#newsletterqueue">
                        {__('newsletterqueue')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'newslettervorlagen'} active{/if}" data-toggle="tab" role="tab" href="#newslettervorlagen">
                        {__('newsletterdraft')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'newslettervorlagenstd'} active{/if}" data-toggle="tab" role="tab" href="#newslettervorlagenstd">
                        {__('newsletterdraftStd')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'newsletterhistory'} active{/if}" data-toggle="tab" role="tab" href="#newsletterhistory">
                        {__('newsletterhistory')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="inaktiveabonnenten" class="tab-pane fade{if !isset($cTab) || $cTab === 'inaktiveabonnenten'} active show{/if}">
                {if isset($oNewsletterEmpfaenger_arr) && $oNewsletterEmpfaenger_arr|@count > 0}
                    <form name="suche" method="post" action="newsletter.php">
                        {$jtl_token}
                        <input type="hidden" name="inaktiveabonnenten" value="1" />
                        <input type="hidden" name="tab" value="inaktiveabonnenten" />
                        {if isset($cSucheInaktiv) && $cSucheInaktiv|strlen > 0}
                            <input type="hidden" name="cSucheInaktiv" value="{$cSucheInaktiv}" />
                        {/if}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cSucheInaktiv">{__('newslettersubscriberSearch')}:</label>
                            </span>
                            <input class="form-control" id="cSucheInaktiv" name="cSucheInaktiv" type="text" value="{if isset($cSucheInaktiv) && $cSucheInaktiv|strlen > 0}{$cSucheInaktiv}{/if}" />
                            <span class="input-group-btn">
                                <button name="submitInaktiveAbonnentenSuche" type="submit" class="btn btn-primary" value="{__('newsletterSearchBTN')}">
                                    <i class="fal fa-search"></i> {__('newsletterSearchBTN')}
                                </button>
                            </span>
                        </div>
                    </form>
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiInaktiveAbos cAnchor='inaktiveabonnenten'}
                    <div id="newsletter-inactive-content">
                        <form name="inaktiveabonnentenForm" method="post" action="newsletter.php">
                            {$jtl_token}
                            <input type="hidden" name="inaktiveabonnenten" value="1" />
                            <input type="hidden" name="tab" value="inaktiveabonnenten" />
                            {if isset($cSucheInaktiv) && $cSucheInaktiv|strlen > 0}
                                <input type="hidden" name="cSucheInaktiv" value="{$cSucheInaktiv}" />
                            {/if}
                            <div>
                                <div class="subheading1">{__('newsletterSubscripterNotActive')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">&nbsp;</th>
                                                <th class="tleft">{__('firstName')}</th>
                                                <th class="tleft">{__('lastName')}</th>
                                                <th class="tleft">{__('customerGroup')}</th>
                                                <th class="tleft">{__('email')}</th>
                                                <th class="tcenter">{__('newslettersubscriberdate')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger}
                                            <tr>
                                                <td class="tleft">
                                                    <input name="kNewsletterEmpfaenger[]" type="checkbox" value="{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}">
                                                </td>
                                                <td class="tleft">{if $oNewsletterEmpfaenger->cVorname != ""}{$oNewsletterEmpfaenger->cVorname}{else}{$oNewsletterEmpfaenger->newsVorname}{/if}</td>
                                                <td class="tleft">{if $oNewsletterEmpfaenger->cNachname != ""}{$oNewsletterEmpfaenger->cNachname}{else}{$oNewsletterEmpfaenger->newsNachname}{/if}</td>
                                                <td class="tleft">{if isset($oNewsletterEmpfaenger->cName) && $oNewsletterEmpfaenger->cName|strlen > 0}{$oNewsletterEmpfaenger->cName}{else}{__('NotAvailable')}{/if}</td>
                                                <td class="tleft">{$oNewsletterEmpfaenger->cEmail}{if $oNewsletterEmpfaenger->nAktiv == 0} *{/if}</td>
                                                <td class="tcenter">{$oNewsletterEmpfaenger->Datum}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>
                                                    <input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                                                </td>
                                                <td colspan="6"><label for="ALLMSGS2">{__('globalSelectAll')}</label></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <button class="btn btn-danger" name="abonnentloeschenSubmit" type="submit" value="{__('delete')}"><i class="fas fa-trash-alt"></i> {__('marked')} {__('delete')}</button>
                                    <button name="abonnentfreischaltenSubmit" type="submit" value="{__('newsletterUnlock')}" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> {__('newsletterUnlock')}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="alleabonnenten" class="tab-pane fade{if isset($cTab) && $cTab === 'alleabonnenten'} active show{/if}">
                {if isset($oAbonnenten_arr) && $oAbonnenten_arr|@count > 0}
                    <form name="suche" method="post" action="newsletter.php">
                        {$jtl_token}
                        <input type="hidden" name="Suche" value="1" />
                        <input type="hidden" name="tab" value="alleabonnenten" />
                        {if isset($cSucheAktiv) && $cSucheAktiv|strlen > 0}
                            <input type="hidden" name="cSucheAktiv" value="{$cSucheAktiv}" />
                        {/if}
                        <div id="newsletter-all-search">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="cSucheAktiv">{__('newslettersubscriberSearch')}</label>
                                </span>
                                <input id="cSucheAktiv" name="cSucheAktiv" class="form-control" type="text" value="{if isset($cSucheAktiv) && $cSucheAktiv|strlen > 0}{$cSucheAktiv}{/if}" />
                                <span class="input-group-btn">
                                    <button name="submitSuche" type="submit" value="{__('newsletterSearchBTN')}" class="btn btn-primary">
                                        <i class="fal fa-search"></i> {__('newsletterSearchBTN')}
                                    </button>
                                </span>
                            </div>
                        </div>
                    </form>
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiAlleAbos cAnchor='alleabonnenten'}
                    <!-- Uebersicht Newsletterhistory -->
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newsletterabonnent_loeschen" type="hidden" value="1">
                        <input type="hidden" name="tab" value="alleabonnenten">
                        <div id="newsletter-all-content">
                            <div>
                                <div class="subheading1">{__('newsletterAllSubscriber')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">&nbsp;</th>
                                                <th class="tleft">{__('newslettersubscribername')}</th>
                                                <th class="tleft">{__('customerGroup')}</th>
                                                <th class="tleft">{__('email')}</th>
                                                <th class="tcenter">{__('newslettersubscriberdate')}</th>
                                                <th class="tcenter">{__('newslettersubscriberLastNewsletter')}</th>
                                                <th class="tleft">{__('newsletterOptInIp')}</th>
                                                <th class="tcenter">{__('newsletterOptInDate')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oAbonnenten_arr as $oAbonnenten}
                                            <tr>
                                                <td class="tleft">
                                                    <input name="kNewsletterEmpfaenger[]" type="checkbox" value="{$oAbonnenten->kNewsletterEmpfaenger}" />
                                                </td>
                                                <td class="tleft">{$oAbonnenten->cVorname} {$oAbonnenten->cNachname}</td>
                                                <td class="tleft">{$oAbonnenten->cName}</td>
                                                <td class="tleft">{$oAbonnenten->cEmail}</td>
                                                <td class="tcenter">{$oAbonnenten->dEingetragen_de}</td>
                                                <td class="tcenter">{$oAbonnenten->dLetzterNewsletter_de}</td>
                                                <td class="tleft">{$oAbonnenten->cOptIp}</td>
                                                <td class="tcenter">{$oAbonnenten->optInDate}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>
                                                    <input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);">
                                                </td>
                                                <td colspan="7"><label for="ALLMSGS3">{__('globalSelectAll')}</label></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <button name="loeschen" type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {if isset($cSucheAktiv) && $cSucheAktiv|strlen > 0}
                        <form method="post" action="newsletter.php">
                            {$jtl_token}
                            <input name="tab" type="hidden" value="alleabonnenten" />
                            <input name="submitAbo" type="submit" value="{__('newsletterNewSearch')}" class="btn btn-primary" />
                        </form>
                    {/if}
                {/if}
            </div>
            <div id="neuerabonnenten" class="tab-pane fade{if isset($cTab) && $cTab === 'neuerabonnenten'} active show{/if}">
                <form method="post" action="newsletter.php">
                    {$jtl_token}
                    <input type="hidden" name="newsletterabonnent_neu" value="1">
                    <input name="tab" type="hidden" value="neuerabonnenten">
                    <div class="settings">
                        <div class="subheading1">{__('newsletterNewSubscriber')}</div>
                        <hr class="mb-3">
                        <div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cVorname">{__('firstName')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cVorname" id="cVorname" value="{if isset($oNewsletter->cVorname)}{$oNewsletter->cVorname}{/if}" />
                                </div>
                            </div>

                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cNachname">{__('lastName')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cNachname" id="cNachname" value="{if isset($oNewsletter->cNachname)}{$oNewsletter->cNachname}{/if}" />
                                </div>
                            </div>

                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cEmail">{__('email')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cEmail" id="cEmail" value="{if isset($oNewsletter->cEmail)}{$oNewsletter->cEmail}{/if}" />
                                </div>
                            </div>

                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="kSprache">{__('language')}:</label>
                                <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <select class="custom-select" name="kSprache" id="kSprache">
                                        {foreach $sprachen as $language}
                                            <option value="{$language->getId()}">{$language->getLocalizedName()}</option>
                                        {/foreach}
                                    </select>
                                </span>
                            </div>
                        </div>
                        <div class="card-footer save-wrapper">
                            <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="newsletterqueue" class="tab-pane fade{if isset($cTab) && $cTab === 'newsletterqueue'} active show{/if}">
                {if isset($oNewsletterQueue_arr) && $oNewsletterQueue_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiWarteschlange cAnchor='newsletterqueue'}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newsletterqueue" type="hidden" value="1">
                        <input name="tab" type="hidden" value="newsletterqueue">
                        <div id="newsletter-queue-content">
                            <div>
                                <div class="subheading1">{__('newsletterqueue')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1" style="width: 4%;">&nbsp;</th>
                                                <th class="th-2" style="width: 40%;">{__('subject')}</th>
                                                <th class="th-3" style="width: 30%;">{__('newsletterqueuedate')}</th>
                                                <th class="th-4" style="width: 26%;">{__('newsletterqueueimprovement')}</th>
                                                <th class="th-5" style="width: 26%;">{__('newsletterqueuecount')}</th>
                                                <th class="th-6" style="width: 26%;">{__('newsletterqueuecustomergrp')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewsletterQueue_arr as $oNewsletterQueue}
                                            {if isset($oNewsletterQueue->nAnzahlEmpfaenger) && $oNewsletterQueue->nAnzahlEmpfaenger > 0}
                                                <tr>
                                                    <td>
                                                        <input name="kNewsletterQueue[]" type="checkbox" value="{$oNewsletterQueue->kNewsletterQueue}">
                                                    </td>
                                                    <td>{$oNewsletterQueue->cBetreff}</td>
                                                    <td>{$oNewsletterQueue->Datum}</td>
                                                    <td>{$oNewsletterQueue->nLimitN}</td>
                                                    <td>{$oNewsletterQueue->nAnzahlEmpfaenger}</td>
                                                    <td>
                                                        {foreach $oNewsletterQueue->cKundengruppe_arr as $cKundengruppe}
                                                            {if $cKundengruppe == '0'}{__('newsletterNoAccount')}{if !$cKundengruppe@last}, {/if}{/if}
                                                            {foreach $oKundengruppe_arr as $oKundengruppe}
                                                                {if $cKundengruppe == $oKundengruppe->kKundengruppe}{$oKundengruppe->cName}{if !$oKundengruppe@last}, {/if}{/if}
                                                            {/foreach}
                                                        {/foreach}
                                                    </td>
                                                </tr>
                                            {/if}
                                        {/foreach}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>
                                                    <input name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);">
                                                </td>
                                                <td colspan="6"><label for="ALLMSGS4">{__('globalSelectAll')}</label></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="newslettervorlagen" class="tab-pane fade{if isset($cTab) && $cTab === 'newslettervorlagen'} active show{/if}">
                {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiVorlagen cAnchor='newslettervorlagen'}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newslettervorlagen" type="hidden" value="1">
                        <input name="tab" type="hidden" value="newslettervorlagen">
                        <div id="newsletter-vorlagen-content">
                            <div>
                                <div class="subheading1">{__('marked')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">&nbsp;</th>
                                                <th class="th-2">{__('newsletterdraftname')}</th>
                                                <th class="th-3">{__('subject')}</th>
                                                <th class="th-4">{__('newsletterdraftStdShort')}</th>
                                                <th class="th-5" style="width: 385px;">{__('options')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewsletterVorlage_arr as $oNewsletterVorlage}
                                            <tr>
                                                <td>
                                                    <input name="kNewsletterVorlage[]" type="checkbox" value="{$oNewsletterVorlage->kNewsletterVorlage}">
                                                </td>
                                                <td>{$oNewsletterVorlage->cName}</td>
                                                <td>{$oNewsletterVorlage->cBetreff}</td>
                                                <td>
                                                    {if $oNewsletterVorlage->kNewslettervorlageStd > 0}
                                                        {__('yes')}
                                                    {else}
                                                        {__('no')}
                                                    {/if}
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a class="btn btn-default"
                                                           href="newsletter.php?&vorschau={$oNewsletterVorlage->kNewsletterVorlage}&iframe=1&tab=newslettervorlagen&token={$smarty.session.jtl_token}"
                                                           title="{__('preview')}"><i class="fa fa-eye"></i></a>
                                                        {if $oNewsletterVorlage->kNewslettervorlageStd > 0}
                                                            <a class="btn btn-default"
                                                               href="newsletter.php?newslettervorlagenstd=1&editieren={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}"
                                                               title="{__('modify')}"><i class="fal fa-edit"></i></a>
                                                        {else}
                                                            <a class="btn btn-default"
                                                               href="newsletter.php?newslettervorlagen=1&editieren={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}"
                                                               title="{__('modify')}"><i class="fal fa-edit"></i></a>
                                                        {/if}
                                                        <a class="btn btn-default"
                                                           href="newsletter.php?newslettervorlagen=1&vorbereiten={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}"
                                                           title="{__('newsletterprepare')}">{__('newsletterprepare')}</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>
                                                    <input name="ALLMSGS" id="ALLMSGS5" type="checkbox" onclick="AllMessages(this.form);">
                                                </td>
                                                <td colspan="6"><label for="ALLMSGS5">{__('globalSelectAll')}</label></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                                        <button class="btn btn-danger" name="loeschen" type="submit" value="{__('delete')}"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                    {/if}
                                    <button name="vorlage_erstellen" class="btn btn-primary" type="submit">{__('newsletterdraftcreate')}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newslettervorlagen" type="hidden" value="1">
                        <input name="tab" type="hidden" value="newslettervorlagen">
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                            <div class="submit {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}btn-group{/if}">
                                <button name="vorlage_erstellen" class="btn btn-primary" type="submit">{__('newsletterdraftcreate')}</button>
                                {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                                    <button class="btn btn-danger" name="loeschen" type="submit" value="{__('delete')}"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                {/if}
                            </div>
                    </form>
                {/if}
            </div>
            <div id="newslettervorlagenstd" class="tab-pane fade{if isset($cTab) && $cTab === 'newslettervorlagenstd'} active show{/if}">
                {if isset($oNewslettervorlageStd_arr) && $oNewslettervorlageStd_arr|@count > 0}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newslettervorlagenstd" type="hidden" value="1" />
                        <input name="vorlage_std_erstellen" type="hidden" value="1" />
                        <input name="tab" type="hidden" value="newslettervorlagenstd" />

                        <div id="newsletter-vorlage-std-content">
                            <div>
                                <div class="subheading1">{__('newsletterdraftStd')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">{__('newsletterdraftname')}</th>
                                                <th class="th-2">{__('preview')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewslettervorlageStd_arr as $oNewslettervorlageStd}
                                            <tr>
                                                <td>
                                                    <input name="kNewsletterVorlageStd" id="knvls-{$oNewslettervorlageStd@iteration}" type="radio" value="{$oNewslettervorlageStd->kNewslettervorlageStd}" /> <label for="knvls-{$oNewslettervorlageStd@iteration}">{$oNewslettervorlageStd->cName}</label>
                                                </td>
                                                <td valign="top">{$oNewslettervorlageStd->cBild}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <button name="submitVorlageStd" type="submit" value="{__('newsletterdraftStdUse')}" class="btn btn-primary"><i class="fa fa-share"></i> {__('newsletterdraftStdUse')}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="newsletterhistory" class="tab-pane fade{if isset($cTab) && $cTab === 'newsletterhistory'} active show{/if}">
                {if isset($oNewsletterHistory_arr) && $oNewsletterHistory_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiHistory cAnchor='newsletterhistory'}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newsletterhistory" type="hidden" value="1">
                        <input name="tab" type="hidden" value="newsletterhistory">
                        <div id="newsletter-history-content">
                            <div>
                                <div class="subheading1">{__('newsletterhistory')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">&nbsp;</th>
                                                <th class="tleft">{__('newsletterhistorysubject')}</th>
                                                <th class="tleft">{__('newsletterhistorycount')}</th>
                                                <th class="tleft">{__('newsletterqueuecustomergrp')}</th>
                                                <th class="tcenter">{__('newsletterhistorydate')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewsletterHistory_arr as $oNewsletterHistory}
                                            <tr>
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
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>
                                                    <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                                </td>
                                                <td colspan="6"><label for="ALLMSGS">{__('globalSelectAll')}</label></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <button name="loeschen" type="submit" class="btn btn-danger" value="{__('delete')}"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="einstellungen" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='newsletter.php' buttonCaption=__('save') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    </div><!-- .tab-content-->
</div><!-- #content -->
