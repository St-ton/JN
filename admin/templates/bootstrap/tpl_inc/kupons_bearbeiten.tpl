{if $action == 'erstellen'}
    {assign var=cTitel value=#newCoupon#}
{else}
    {assign var=cTitel value=#modifyCoupon#}
{/if}

{if $oKupon->cKuponTyp == 'standard'}
    {assign var=cTitel value="$cTitel : Standardkupon"}
{elseif $oKupon->cKuponTyp == 'versandkupon'}
    {assign var=cTitel value="$cTitel : Versandkostenfrei-Kupon"}
{elseif $oKupon->cKuponTyp == 'neukundenkupon'}
    {assign var=cTitel value="$cTitel : Neukunden-/Begr&uuml;&szlig;ungskupon"}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#couponsDesc# cDokuURL=#couponsURL#}

<script>
    {literal}
        $(function () {
            ['fWert', 'fMindestbestellwert'].forEach(makeCurrencyTooltip);
            $('#dGueltigAb').keyup(calcRelativeValidity);
            $('#dGueltigBis').keyup(calcRelativeValidity);
            $('#dDauerTage').keyup(calcValidityEnd);
            $('#bEwig').change(onEternalCheckboxChange);
            $('#btnValidFromNow').click(function () {
                var date = new Date();
                var dateString = dateToString(date);
                $('#dGueltigAb').val(dateString);
            });
            calcRelativeValidity();
            onEternalCheckboxChange();
        });

        function pad (num, size) {
            var res = num + '';
            while (res.length < size) {
                res = '0' + res;
            }
            return res;
        }

        function dateToString (date) {
            return pad(date.getDate(), 2) + '.' + pad(date.getMonth() + 1, 2) + '.' + date.getFullYear() +
                ' ' + pad(date.getHours(), 2) + ':' + pad(date.getMinutes(), 2);
        }

        function stringToDate (str) {
            var date = new Date();
            var strMatch = str.match(/^ *([0-9]{0,2})\.([0-9]{0,2})\.([0-9]{0,4}) +([0-9]{0,2}):([0-9]{0,2}) *$/);
            if (strMatch !== null) {
                date.setFullYear(parseInt(strMatch[3]), parseInt(strMatch[2]) - 1, parseInt(strMatch[1]));
                date.setHours(parseInt(strMatch[4]), parseInt(strMatch[5]), 0);
            } else {
                var strMatch = str.match(/^ *([0-9]{0,2})\.([0-9]{0,2})\.([0-9]{0,4}) *$/);
                if (strMatch !== null) {
                    date.setFullYear(parseInt(strMatch[3]), parseInt(strMatch[2]) - 1, parseInt(strMatch[1]));
                    date.setHours(0, 0, 0);
                }
            }
            return date;
        }

        function calcRelativeValidity () {
            if ($('#bEwig').prop('checked')) {
                $('#dDauerTage').val('ewig');
            } else {
                var validStartDate = stringToDate($('#dGueltigAb').val());
                var validEndDate   = stringToDate($('#dGueltigBis').val());
                var deltaDays      = Math.floor((validEndDate - validStartDate) / (1000 * 60 * 60 * 24)) || 0;
                $('#dDauerTage').val(deltaDays);
            }
        }

        function calcValidityEnd () {
            if ($('#bEwig').prop('checked')) {
                $('#dGueltigBis').val('');
            } else {
                var date = stringToDate($('#dGueltigAb').val());
                var validDays = parseInt($('#dDauerTage').val()) || 0;
                date.setTime(date.getTime() + validDays * 24 * 60 * 60 * 1000);
                var endDateString = dateToString(date);
                $('#dGueltigBis').val(endDateString);
            }
        }

        function onEternalCheckboxChange () {
            var elem = $('#bEwig');
            var bEwig = elem[0].checked;
            $('#dGueltigBis').prop('disabled', bEwig);
            $('#dDauerTage').prop('disabled', bEwig);
            calcRelativeValidity ();
            calcValidityEnd ();
        }
    {/literal}
</script>

<div id="content" class="container-fluid">
    <form method="post" action="kupons.php">
        {$jtl_token}
        <input type="hidden" name="kKuponBearbeiten" value="{$oKupon->kKupon}">
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
                        <span class="input-group-wrap">
                            <select name="cWertTyp" id="cWertTyp" class="form-control combo">
                                <option value="festpreis"{if $oKupon->cWertTyp === 'festpreis'} selected{/if}>
                                    Betrag
                                </option>
                                <option value="prozent"{if $oKupon->cWertTyp === 'prozent'} selected{/if}>
                                    %
                                </option>
                            </select>
                        </span>
                        <span class="input-group-addon">
                            {getCurrencyConversionTooltipButton inputId='fWert'}
                        </span>
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
                    <span class="input-group-addon">
                        {getCurrencyConversionTooltipButton inputId='fMindestbestellwert'}
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
                <h3 class="panel-title">G&uuml;ltigkeitszeitraum</h3>
            </div>
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="dGueltigAb">{#validity#} {#from#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="dGueltigAb" id="dGueltigAb" value="{$oKupon->cGueltigAbLong}">
                    </span>
                    <span class="input-group-addon">
                        <button type="button" class="btn btn-info btn-xs btn-tooltip" id="btnValidFromNow" data-html="true"
                                data-toggle="tooltip" data-placement="left" data-original-title="ab jetzt">
                            <i class="fa fa-calendar-plus-o"></i>
                        </button>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="dGueltigBis">{#validity#} {#to#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="dGueltigBis" id="dGueltigBis" value="{$oKupon->cGueltigBisLong}">
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="dDauerTage">G&uuml;tigkeitsdauer (Tage)</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="dDauerTage" id="dDauerTage" value="">
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="bEwig">Ewig g&uuml;ltig</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="checkbox" class="checkfield" name="bEwig" id="bEwig" value="Y"{if $oKupon->bEwig} checked{/if}>
                    </span>
                </div>
            </div>
        </div>
        <div class="panel panel-default settings">
            <div class="panel-heading">
                <h3 class="panel-title">Einschr&auml;nkungen</h3>
            </div>
            <div class="panel-body">
                <div id="ajax_list_picker" class="ajax_list_picker article">{include file="tpl_inc/popup_artikelsuche.tpl"}</div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="assign_article_list">{#productRestrictions#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="cArtikel" id="assign_article_list" value="{$oKupon->cArtikel}">
                    </span>
                    <span class="input-group-addon">
                        <button class="btn btn-info btn-xs btn-tooltip" id="show_article_list" data-html="true"
                                data-toggle="tooltip" data-placement="left" data-original-title="Artikel verwalten">
                            <i class="fa fa-edit"></i>
                        </button>
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
                    <span class="input-group-addon">{getHelpDesc cDesc=#multipleChoice#}</span>
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
                        <span class="input-group-addon">{getHelpDesc cDesc=#multipleChoice#}</span>
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