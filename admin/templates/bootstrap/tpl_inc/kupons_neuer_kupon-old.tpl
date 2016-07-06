{if !isset($Kupon->kKupon) || !$Kupon->kKupon}
    {assign var=cTitel value=#newCoupon#}
{else}
    {assign var=cTitel value=#modifyCoupon#}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#newCouponDesc#}
<div id="content" class="container-fluid">
    <div class="ocontainer">
        <form name="kupon_neu" method="post" action="kupons.php">
            {$jtl_token}
            <input type="hidden" name="neuerKupon" value="1" />
            <input type="hidden" name="cKuponTyp" value="{$Kupon->cKuponTyp}" />
            <input type="hidden" name="kKupon" value="{if isset($Kupon->kKupon)}{$Kupon->kKupon}{/if}" />
            <div class="settings">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Namen</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="jtl-list-group">
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="cName">{#name#}</label>
                                </span>
                                <input class="form-control" type="text" name="cName" id="cName" value="{if isset($Kupon->cName)}{$Kupon->cName}{/if}" tabindex="1" />
                            </li>
                            {foreach name=sprachen from=$sprachen item=sprache}
                                {assign var="cISO" value=$sprache->cISO}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
                                    </span>
                                    <input class="form-control" type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($Kuponname[$cISO])}{$Kuponname[$cISO]}{/if}" tabindex="2" />
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Allgemein</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="jtl-list-group">
                            <li class="input-group">
                                {if $Kupon->cKuponTyp === 'standard' || $Kupon->cKuponTyp === 'neukundenkupon'}
                                <span class="input-group-addon">
                                    <label for="fWert">{#value#} ({#gross#})</label>
                                </span>
                                <input class="form-control" type="text" name="fWert" id="fWert" value="{if isset($Kupon->fWert)}{$Kupon->fWert}{/if}" tabindex="3" onKeyUp="setzePreisAjax(false, 'WertAjax', this)" />
                                <span class="input-group-wrap">
                                <select name="cWertTyp" id="cWertTyp" class="form-control combo">
                                    <option value="festpreis" {if isset($Kupon->cWertTyp) && $Kupon->cWertTyp === 'festpreis'}selected{/if}>
                                        Betrag
                                    </option>
                                    <option value="prozent" {if isset($Kupon->cWertTyp) && $Kupon->cWertTyp === 'prozent'}selected{/if}>
                                        %
                                    </option>
                                </select>
                                </span>
                                <span class="input-group-addon"><span id="WertAjax"></span></span>
                            </li>
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="nGanzenWKRabattieren">{#wholeWKDiscount#}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="nGanzenWKRabattieren" id="nGanzenWKRabattieren" class="form-control combo">
                                        <option value="1"{if isset($Kupon->nGanzenWKRabattieren) && $Kupon->nGanzenWKRabattieren == 1} selected{/if}>
                                            Ja
                                        </option>
                                        <option value="0"{if isset($Kupon->nGanzenWKRabattieren) && $Kupon->nGanzenWKRabattieren == 0} selected{/if}>
                                            Nein
                                        </option>
                                    </select>
                                </span>
                            </li>
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="kSteuerklasse">{#taxClass#}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="kSteuerklasse" id="kSteuerklasse" class="form-control combo">
                                        {foreach name=steuer from=$steuerklassen item=steuerklasse}
                                            <option value="{if isset($steuerklasse->kSteuerklasse)}{$steuerklasse->kSteuerklasse}{/if}" {if isset($Kupon->kSteuerklasse) && $Kupon->kSteuerklasse==$steuerklasse->kSteuerklasse}selected{/if}>{$steuerklasse->cName}</option>
                                        {/foreach}
                                    </select>
                                {elseif $Kupon->cKuponTyp === 'versandkupon'}
                                    <span class="input-group-addon">
                                        <label for="cZusatzgebuehren">{#additionalShippingCosts#}</label>
                                    </span>
                                    <div class="input-group-wrap">
                                        <input type="checkbox" name="cZusatzgebuehren" id="cZusatzgebuehren" class="checkfield" value="Y" {if isset($Kupon->cZusatzgebuehren) && $Kupon->cZusatzgebuehren === 'Y'}checked{/if} />
                                    </div>
                                    <span class="input-group-addon">
                                        {getHelpDesc cDesc=#additionalShippingCostsHint#}
                                    </span>
                                {/if}
                            </li>
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="fMindestbestellwert">{#minOrderValue#} ({#gross#})</label>
                                </span>
                                <input class="form-control" type="text" name="fMindestbestellwert" id="fMindestbestellwert" value="{if isset($Kupon->fMindestbestellwert)}{$Kupon->fMindestbestellwert}{/if}" tabindex="4" onKeyUp="setzePreisAjax(false, 'MindestWertAjax', this)" />
                                <span class="input-group-addon"><span id="MindestWertAjax"></span></span>
                            </li>
                            {if isset($Kupon->cKuponTyp) && ($Kupon->cKuponTyp === 'standard' || $Kupon->cKuponTyp === 'versandkupon')}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cCode">{#code#}</label>
                                    </span>
                                    <input class="form-control" type="text" name="cCode" id="cCode" value="{if isset($Kupon->cCode)}{$Kupon->cCode}{/if}" tabindex="7" />
                                </li>
                            {/if}
                            {if isset($Kupon->cKuponTyp) && $Kupon->cKuponTyp === 'versandkupon'}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cLieferlaender">{#shippingCountries#}</label>
                                    </span>
                                    <input class="form-control" type="text" name="cLieferlaender" id="cLieferlaender" value="{if isset($Kupon->cLieferlaender)}{$Kupon->cLieferlaender}{/if}" tabindex="8" />
                                    <span class="input-group-addon">{getHelpDesc cDesc=#shippingCountriesHint#}</span>
                                </li>
                            {/if}
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="nVerwendungen">{#uses#}</label>
                                </span>
                                <input class="form-control" type="text" name="nVerwendungen" id="nVerwendungen" value="{if isset($Kupon->nVerwendungen)}{$Kupon->nVerwendungen}{/if}" tabindex="9" />
                            </li>

                            {if $Kupon->cKuponTyp === 'standard' || $Kupon->cKuponTyp === 'versandkupon'}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="nVerwendungenProKunde">{#usesPerCustomer#}</label>
                                    </span>
                                    <input class="form-control" type="text" name="nVerwendungenProKunde" id="nVerwendungenProKunde" value="{if isset($Kupon->nVerwendungenProKunde)}{$Kupon->nVerwendungenProKunde}{/if}" tabindex="10" />
                                </li>
                            {/if}
                        </ul>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Einschr&auml;nkungen</h3>
                    </div>
                    <div class="panel-body">
                        <div id="ajax_list_picker" class="ajax_list_picker article">{include file="tpl_inc/popup_artikelsuche.tpl"}</div>
                        <ul class="jtl-list-group">
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="assign_article_list">{#productRestrictions#}</label>
                                </span>
                                <input class="form-control" type="text" name="cArtikel" id="assign_article_list" value="{if isset($Kupon->cArtikel)}{$Kupon->cArtikel}{/if}" tabindex="10" />
                                <span class="input-group-addon">
                                    <a href="#" class="btn btn-default btn-xs button edit" id="show_article_list">Artikel verwalten</a>
                                </span>

                            </li>
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="kKundengruppe">{#restrictionToCustomerGroup#}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="kKundengruppe" id="kKundengruppe" class="form-control combo">
                                        <option value="-1" {if isset($Kupon->kKundengruppe) && isset($kundengruppe->kKundengruppe) && $Kupon->kKundengruppe==$kundengruppe->kKundengruppe}selected{/if}>
                                            Alle Kundengruppen
                                        </option>
                                        {foreach name=kundengruppen from=$kundengruppen item=kundengruppe}
                                            <option value="{$kundengruppe->kKundengruppe}" {if isset($Kupon->kKundengruppe) && $Kupon->kKundengruppe==$kundengruppe->kKundengruppe}selected{/if}>{$kundengruppe->cName}</option>
                                        {/foreach}
                                    </select>
                                </span>
                            </li>
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="dGueltigAb">{#validity#} {#from#}</label>
                                </span>
                                <input class="form-control" type="text" name="dGueltigAb" id="dGueltigAb" value="{if isset($Kupon->dGueltigAb)}{$Kupon->dGueltigAb}{else}{$smarty.now|date_format:"%d.%m.%Y %H:%M"}{/if}" tabindex="11" />
                            </li>

                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="dGueltigBis">{#validity#} {#to#}</label>
                                </span>
                                <input class="form-control" type="text" name="dGueltigBis" id="dGueltigBis" value="{if isset($Kupon->dGueltigBis)}{$Kupon->dGueltigBis}{/if}" tabindex="10" />
                            </li>

                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="cAktiv">{#active#}</label>
                                </span>
                                <div class="input-group-wrap">
                                    <input type="checkbox" name="cAktiv" id="cAktiv" class="checkfield" value="Y" {if (isset($Kupon->cAktiv) && $Kupon->cAktiv === 'Y') || !isset($Kupon->kKupon) || !$Kupon->kKupon}checked{/if} />
                                </div>
                            </li>

                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="kKategorien">{#restrictedToCategories#}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="kKategorien[]" multiple size="10" id="kKategorien" class="form-control combo">
                                        <option value="0" {if ((isset($Kupon->cKategorien) && $Kupon->cKategorien == '-1') || !isset($Kupon->kKupon) || !$Kupon->kKupon) && !$kategoriebaum_selected}selected{/if}>{#allCategories#}</option>
                                        {foreach name=kategorie from=$kategoriebaum item=kat}
                                            <option value="{$kat->kKategorie}" {if $kat->selected==1}selected{/if}>{$kat->cName}</option>
                                        {/foreach}
                                    </select>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#multipleChoice#}</span>
                            </li>
                            {if isset($Kupon->cKuponTyp) && ($Kupon->cKuponTyp === 'standard' || $Kupon->cKuponTyp === 'versandkupon')}
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="kKunden">{#restrictedToCustomers#}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select name="kKunden[]" multiple size="10" id="kKunden" class="form-control combo">
                                            <option value="0" {if ((isset($Kupon->cKunden) && $Kupon->cKunden == '-1') || !isset($Kupon->kKupon) || !$Kupon->kKupon) && !$kunden_selected}selected{/if}>{#allCustomers#}</option>
                                            {foreach name=kunden from=$kunden item=kunde}
                                                <option value="{$kunde->kKunde}" {if $kunde->selected==1}selected{/if}>
                                                    {$kunde->cNachname}, {$kunde->cVorname} {if isset($kunde->cFirma) && $kunde->cFirma|strlen > 0}({$kunde->cFirma}){/if}
                                                </option>
                                            {/foreach}
                                        </select>
                                    </span>
                                    <span class="input-group-addon">{getHelpDesc cDesc=#multipleChoice#}</span>
                                </li>
                                <li class="input-group">
                                    <span class="input-group-addon">
                                        <label for="informieren">{#informCustomers#}</label>
                                    </span>
                                    <div class="input-group-wrap">
                                        <input type="checkbox" name="informieren" id="informieren" class="checkfield" value="Y" />
                                    </div>
                                </li>
                            {/if}
                        </ul>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
            <p class="submit">
                <button type="submit" value="{if !isset($Kupon->kKupon) || !$Kupon->kKupon}{#newCoupon#}{else}{#modifyCoupon#}{/if}" class="btn btn-primary">
                    {if !isset($Kupon->kKupon) || !$Kupon->kKupon}<i class="fa fa-share"></i> {#newCoupon#}{else}{#modifyCoupon#}{/if}
                </button>
            </p>
        </form>
    </div>
    <script type="text/javascript">
        xajax_getCurrencyConversionAjax(0, document.getElementById('fWert').value, 'WertAjax');
        xajax_getCurrencyConversionAjax(0, document.getElementById('fMindestbestellwert').value, 'MindestWertAjax');
    </script>
</div>