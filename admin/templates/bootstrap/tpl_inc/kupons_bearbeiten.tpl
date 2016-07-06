{if $action == 'erstellen'}
    {assign var=cTitel value=#newCoupon#}
{else}
    {assign var=cTitel value=#modifyCoupon#}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#couponsDesc# cDokuURL=#couponsURL#}
<div id="content" class="container-fluid">
    <form method="post" action="kupons.php">
        {$jtl_token}
        {if isset($oKupon->kKupon)}
            <input type="hidden" name="kKupon" value="{$oKupon->kKupon}">
        {/if}
        <input type="hidden" name="cKuponTyp" value="{$oKupon->cKuponTyp}">
        <div class="panel panel-default settings">
            <div class="panel-heading">
                <h3 class="panel-title">Namen</h3>
            </div>
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cName">{#name#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="cName" id="cName" value="{$oKupon->cName}">
                    </span>
                </div>
                {foreach $oSprache_arr as $oSprache}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cName_{$oSprache->cISO}">{#showedName#} ({$oSprache->cNameDeutsch})</label>
                        </span>
                        <span class="input-group-wrap">
                            <input
                                type="text" class="form-control" name="cName_{$oSprache->cISO}"
                                id="cName_{$oSprache->cISO}"
                                value="{if isset($oKuponName_arr[$oSprache->cISO])}{$oKuponName_arr[$oSprache->cISO]}{/if}">
                        </span>
                    </div>
                {/foreach}
            </div>
        </div>
        <div class="panel panel-default settings">
            <div class="panel-heading">
                <h3 class="panel-title">Allgemein</h3>
            </div>
            <div class="panel-body">
                {if $oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'neukundenkupon'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="fWert">{#value#} ({#gross#})</label>
                        </span>
                        <span class="input-group-wrap">
                            <input type="text" class="form-control" name="fWert" id="fWert" value="{$oKupon->fWert}">
                        </span>
                        <select name="cWertTyp" id="cWertTyp" class="form-control combo">
                            <option value="festpreis"{if $oKupon->cWertTyp === 'festpreis'} selected{/if}>
                                Betrag
                            </option>
                            <option value="prozent"{if $oKupon->cWertTyp === 'prozent'} selected{/if}>
                                %
                            </option>
                        </select>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nGanzenWKRabattieren">{#wholeWKDiscount#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select name="nGanzenWKRabattieren" id="nGanzenWKRabattieren" class="form-control combo">
                                <option value="1"{if $oKupon->nGanzenWKRabattieren == 1} selected{/if}>
                                    Ja
                                </option>
                                <option value="0"{if $oKupon->nGanzenWKRabattieren == 0} selected{/if}>
                                    Nein
                                </option>
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kSteuerklasse">{#taxClass#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select name="kSteuerklasse" id="kSteuerklasse" class="form-control combo">
                                {foreach $oSteuerklasse_arr as $oSteuerklasse}
                                    <option value="{$oSteuerklasse->kSteuerklasse}"{if $oKupon->kSteuerklasse == $oSteuerklasse->kSteuerklasse} selected{/if}>
                                        {$oSteuerklasse->cName}
                                    </option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                {/if}
                {if $oKupon->cKuponTyp === 'versandkupon'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cZusatzgebuehren">{#additionalShippingCosts#}</label>
                        </span>
                        <div class="input-group-wrap">
                            <input type="checkbox" class="checkfield" name="cZusatzgebuehren" id="cZusatzgebuehren" value="Y"{if $oKupon->cZusatzgebuehren === 'Y'} checked{/if}>
                        </div>
                    </div>
                {/if}
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="fMindestbestellwert">{#minOrderValue#} ({#gross#})</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="fMindestbestellwert" id="fMindestbestellwert" value="{$oKupon->fMindestbestellwert}">
                    </span>
                </div>
                {if $oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'versandkupon'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cCode">{#code#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <input type="text" class="form-control" name="cCode" id="cCode" value="{$oKupon->cCode}">
                        </span>
                    </div>
                {/if}
                {if $oKupon->cKuponTyp === 'versandkupon'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cLieferlaender">{#shippingCountries#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <input type="text" class="form-control" name="cLieferlaender" id="cLieferlaender" value="{$oKupon->cLieferlaender}">
                        </span>
                    </div>
                {/if}
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="nVerwendungen">{#uses#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="nVerwendungen" id="nVerwendungen" value="{$oKupon->nVerwendungen}">
                    </span>
                </div>
                {if $oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'versandkupon'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nVerwendungenProKunde">{#usesPerCustomer#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <input type="text" class="form-control" name="nVerwendungenProKunde" id="nVerwendungenProKunde" value="{$oKupon->nVerwendungenProKunde}">
                        </span>
                    </div>
                {/if}
            </div>
        </div>
        <div class="panel panel-default settings">
            <div class="panel-heading">
                <h3 class="panel-title">Einschr&auml;nkungen</h3>
            </div>
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cArtikel">{#productRestrictions#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="cArtikel" id="cArtikel" value="{$oKupon->cArtikel}">
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="kKundengruppe">{#restrictionToCustomerGroup#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <select name="kKundengruppe" id="kKundengruppe" class="form-control combo">
                            <option value="-1"{if $oKupon->kKundengruppe == -1} selected{/if}>
                                Alle Kundengruppen
                            </option>
                            {foreach $oKundengruppe_arr as $oKundengruppe}
                                <option value="{$oKundengruppe->kKundengruppe}"{if $oKupon->kKundengruppe == $oKundengruppe->kKundengruppe} selected{/if}>
                                    {$oKundengruppe->cName}
                                </option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="dGueltigAb">{#validity#} {#from#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="dGueltigAb" id="dGueltigAb" value="{$oKupon->dGueltigAb}">
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="dGueltigBis">{#validity#} {#to#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="dGueltigBis" id="dGueltigBis" value="{$oKupon->dGueltigBis}">
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cAktiv">{#active#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="checkbox" class="checkfield" name="cAktiv" id="cAktiv" value="Y"{if $oKupon->cAktiv === 'Y'} checked{/if}>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="kKategorien">{#restrictedToCategories#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <select multiple size="10" name="kKategorien[]" id="kKategorien" class="form-control combo">
                            <option value="-1"{if $oKupon->cKategorien == '-1'} selected{/if}>
                                Alle Kategorien
                            </option>
                            {foreach $oKategorie_arr as $oKategorie}
                                <option value="{$oKategorie->kKategorie}"{if $oKategorie->selected == 1} selected{/if}>
                                    {$oKategorie->cName}
                                </option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                {if $oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'versandkupon'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kKunden">{#restrictedToCustomers#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select multiple name="kKunden[]" id="kKunden" class="form-control combo">
                                <option value="-1"{if $oKupon->cKunden == '-1'} selected{/if}>
                                    {#allCustomers#}
                                </option>
                                {foreach $oKunde_arr as $oKunde}
                                    <option value="{$oKunde->kKunde}"{if $oKunde->selected == 1} selected{/if}>
                                        {$oKunde->cNachname}, {$oKunde->cVorname}
                                    </option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="informieren">{#informCustomers#}</label>
                        </span>
                        <div class="input-group-wrap">
                            <input type="checkbox" class="checkfield" name="informieren" id="informieren" value="Y">
                        </div>
                    </div>
                {/if}
            </div>
        </div>
        <button type="submit" class="btn btn-primary" name="action" value="speichern">
            <i class="fa fa-share"></i> Speichern
        </button>
    </form>
</div>