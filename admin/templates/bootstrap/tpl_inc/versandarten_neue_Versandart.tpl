<script type="text/javascript">
    {assign var=addOne value=1}
    var i = {if isset($VersandartStaffeln) && $VersandartStaffeln|@count > 0}Number({$VersandartStaffeln|@count}) + 1{else}2{/if};
    function addInputRow() {ldelim}
        $('#price_range tbody').append('<tr><td><div class="input-group"><span class="input-group-addon"><label>{__('upTo')}</label></span><input type="text" name="bis[]"  id="bis' + i + '" class="form-control kilogram"><span class="input-group-addon"><label>{if isset($einheit)}{$einheit}{/if}</label></span></div></td><td class="tcenter"><div class="input-group"><span class="input-group-addon"><label>{__('amount')}</label></span><input type="text" name="preis[]"  id="preis' + i + '" class="form-control price_large"></div></td></tr>');
        i += 1;
        {rdelim}

    function confirmAllCombi() {ldelim}
        return confirm('{__('shippingConfirm')}');
        {rdelim}

    {literal}
    function delInputRow() {
        i -= 1;
        $('#price_range tbody tr:last').remove();
    }

    function addShippingCombination() {
        var newCombi = '<li class=\'input-group\'>'+$('#ulVK #liVKneu').html()+'</li>';
        newCombi = newCombi.replace(/selectX/gi,'select');
        if ($("select[name='Versandklassen']").size() >= 1) {
            newCombi = newCombi.replace(/<option value="-1">/gi, '<option value="-1" disabled="disabled">');
        }

        $('#ulVK').append(newCombi);
    }

    function updateVK() {
        var val = '';
        $("select[name='Versandklassen']").each( function(index) {
            if ($(this).val()!= null) {
                val += ((val.length > 0)?' ':'') + $(this).val().toString().replace(/,/gi,'-');
            }
        });
        $("input[name='kVersandklasse']").val(val);
    }

    function checkCombination() {
        var remove = false;
        $("select[name='Versandklassen']").each(function (index) {
            if (index === 0) {
                if ($.inArray("-1", $(this).val()) != -1) {
                    if (!confirmAllCombi()) {
                        var valSelected = $(this).val();
                        valSelected.shift();
                        $(this).val(valSelected);
                        $('.select2').select2();
                        return false;
                    }
                    if ($("select[name='Versandklassen']").size() >= 1) {
                        $(this).val("-1");
                        $('#addNewShippingClassCombi').prop('disabled', true);
                        remove = true;
                    }
                    $(this).val("-1");
                    $('#addNewShippingClassCombi').prop('disabled', true);
                    $('.select2').select2();
                } else {
                    $('#addNewShippingClassCombi').prop('disabled', false);
                }
            } else {
                if (remove) {
                    $(this).parent().parent().detach();
                }
            }
        });
    }
    {/literal}
</script>

{assign var=cTitel value=__('createShippingMethod')}
{assign var=cBeschreibung value=__('createShippingMethodDesc')}

{if isset($Versandart->kVersandart) && $Versandart->kVersandart > 0}
    {assign var=cTitel value=__('modifyedShippingType')}
    {assign var=cBeschreibung value=""}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=$cBeschreibung}
<div id="content" class="container-fluid">
    <form name="versandart_neu" method="post" action="versandarten.php">
        {$jtl_token}
        <input type="hidden" name="neueVersandart" value="1" />
        <input type="hidden" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" />
        <input type="hidden" name="kVersandart" value="{if isset($Versandart->kVersandart)}{$Versandart->kVersandart}{/if}" />
        <input type="hidden" name="cModulId" value="{$versandberechnung->cModulId}" />
        <div class="row">
            <div class="col col-md-6 settings">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{__('general')}</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="jtl-list-group">
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="cName">{__('shippingMethodName')}</label>
                                </span>
                                <input class="form-control" type="text" id="cName" name="cName" value="{if isset($Versandart->cName)}{$Versandart->cName}{/if}" />
                            </li>
                            {foreach $sprachen as $sprache}
                                {assign var=cISO value=$sprache->cISO}
                                {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                    <li class="input-group">
                                        <span class="input-group-addon">
                                            <label for="cName_{$cISO}">{__('showedName')} ({$sprache->cNameDeutsch})</label>
                                        </span>
                                        <input class="form-control" type="text" id="cName_{$cISO}" name="cName_{$cISO}" value="{if isset($oVersandartSpracheAssoc_arr[$cISO]->cName)}{$oVersandartSpracheAssoc_arr[$cISO]->cName}{/if}" />
                                    </li>
                                {/if}
                            {/foreach}
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="nSort">{__('sortnr')}</label>
                                </span>
                                <input class="form-control" type="text" id="nSort" name="nSort" value="{if isset($Versandart->nSort)}{$Versandart->nSort}{/if}" />
                                <span class="input-group-addon">{getHelpDesc cDesc=__('versandartenSortDesc')}</span>
                            </li>
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="cBild">{__('pictureURL')}</label>
                                </span>
                                <input class="form-control" type="text" id="cBild" name="cBild" value="{if isset($Versandart->cBild)}{$Versandart->cBild}{/if}" />
                                <span class="input-group-addon">{getHelpDesc cDesc=__('pictureDesc')}</span>
                            </li>
                            {foreach $sprachen as $sprache}
                                {assign var=cISO value=$sprache->cISO}
                                {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                    <li class="input-group">
                                        <span class="input-group-addon">
                                            <label for="cHinweistextShop_{$cISO}">{__('shippingNoteShop')} ({$sprache->cNameDeutsch})</label>
                                        </span>
                                        <textarea id="cHinweistextShop_{$cISO}" class="form-control combo" name="cHinweistextShop_{$cISO}">{if isset($oVersandartSpracheAssoc_arr[$cISO]->cHinweistextShop)}{$oVersandartSpracheAssoc_arr[$cISO]->cHinweistextShop}{/if}</textarea>
                                    </li>
                                {/if}
                            {/foreach}

                            {foreach $sprachen as $sprache}
                                {assign var=cISO value=$sprache->cISO}
                                {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                    <li class="input-group">
                                        <span class="input-group-addon">
                                            <label for="cHinweistext_{$cISO}">{__('shippingNoteEmail')} ({$sprache->cNameDeutsch})</label>
                                        </span>
                                        <textarea id="cHinweistext_{$cISO}" class="form-control combo" name="cHinweistext_{$cISO}">{if isset($oVersandartSpracheAssoc_arr[$cISO]->cHinweistext)}{$oVersandartSpracheAssoc_arr[$cISO]->cHinweistext}{/if}</textarea>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                        <ul class="jtl-list-group">
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="nMinLiefertage">{__('minLiefertage')}</label>
                                </span>
                                <input class="form-control" type="text" id="nMinLiefertage" name="nMinLiefertage" value="{if isset($Versandart->nMinLiefertage)}{$Versandart->nMinLiefertage}{/if}" />
                            </li>
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="nMaxLiefertage">{__('maxLiefertage')}</label>
                                </span>
                                <input class="form-control" type="text" id="nMaxLiefertage" name="nMaxLiefertage" value="{if isset($Versandart->nMaxLiefertage)}{$Versandart->nMaxLiefertage}{/if}" />
                            </li>
                            {foreach $sprachen as $sprache}
                                {assign var=cISO value=$sprache->cISO}
                                {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                    <li class="input-group">
                                        <span class="input-group-addon">
                                            <label for="cLieferdauer_{$cISO}">{__('shippingTime')} ({$sprache->cNameDeutsch})</label>
                                        </span>
                                        <input class="form-control" type="text" id="cLieferdauer_{$cISO}" name="cLieferdauer_{$cISO}" value="{if isset($oVersandartSpracheAssoc_arr[$cISO]->cLieferdauer)}{$oVersandartSpracheAssoc_arr[$cISO]->cLieferdauer}{/if}" />
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                        <ul class="jtl-list-group">
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="cAnzeigen">{__('showShippingMethod')}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="cAnzeigen" id="cAnzeigen" class="form-control combo">
                                        <option value="immer" {if isset($Versandart->cAnzeigen) && $Versandart->cAnzeigen === 'immer'}selected{/if}>{__('always')}</option>
                                        <option value="guenstigste" {if isset($Versandart->cAnzeigen) && $Versandart->cAnzeigen === 'guenstigste'}selected{/if}>{__('lowest')}</option>
                                    </select>
                                </span>
                            </li>

                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="cIgnoreShippingProposal">{__('excludeShippingProposal')}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="cIgnoreShippingProposal" id="cIgnoreShippingProposal" class="form-control combo">
                                        <option value="N" {if isset($Versandart->cIgnoreShippingProposal) && $Versandart->cIgnoreShippingProposal === 'N'}selected{/if}>{__('no')}</option>
                                        <option value="Y" {if isset($Versandart->cIgnoreShippingProposal) && $Versandart->cIgnoreShippingProposal === 'Y'}selected{/if}>{__('yes')}</option>
                                    </select>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=__('excludeShippingProposalDesc')}</span>
                            </li>

                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="cNurAbhaengigeVersandart">{__('onlyForOwnShippingPrices')}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="cNurAbhaengigeVersandart" id="cNurAbhaengigeVersandart" class="combo form-control">
                                        <option value="N" {if isset($Versandart->cNurAbhaengigeVersandart) && $Versandart->cNurAbhaengigeVersandart === 'N'}selected{/if}>{__('no')}</option>
                                        <option value="Y" {if isset($Versandart->cNurAbhaengigeVersandart) && $Versandart->cNurAbhaengigeVersandart === 'Y'}selected{/if}>{__('yes')}</option>
                                    </select>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=__('ownShippingPricesDesc')}</span>
                            </li>

                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="eSteuer">{__('taxshippingcosts')}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="eSteuer" id="eSteuer" class="combo form-control">
                                        <option value="brutto" {if isset($Versandart->eSteuer) && $Versandart->eSteuer === 'brutto'}selected{/if}>{__('gross')}</option>
                                        <option value="netto" {if isset($Versandart->eSteuer) && $Versandart->eSteuer === 'netto'}selected{/if}>{__('net')}</option>
                                    </select>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=__('taxshippingcostsDesc')}</span>
                            </li>

                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="cSendConfirmationMail">{__('sendShippingNotification')}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="cSendConfirmationMail" id="cSendConfirmationMail" class="combo form-control">
                                        <option value="Y" {if isset($Versandart->cSendConfirmationMail) && $Versandart->cSendConfirmationMail === 'Y'}selected{/if}>{__('yes')}</option>
                                        <option value="N" {if isset($Versandart->cSendConfirmationMail) && $Versandart->cSendConfirmationMail === 'N'}selected{/if}>{__('no')}</option>
                                    </select>
                                </span>
                                {*<span class="input-group-addon">{getHelpDesc cDesc=''}</span>*}
                            </li>
                        </ul>
                        <ul class="jtl-list-group">
                            <li class="input-group">
                                <span class="input-group-addon">
                                    <label for="kKundengruppe">{__('customerclass')}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select name="kKundengruppe[]" id="kKundengruppe" multiple="multiple" class="combo form-control">
                                        <option value="-1" {if $gesetzteKundengruppen.alle}selected{/if}>{__('all')}</option>
                                        {foreach $kundengruppen as $oKundengruppe}
                                            {assign var=klasse value=$oKundengruppe->kKundengruppe}
                                            <option value="{$oKundengruppe->kKundengruppe}" {if isset($gesetzteKundengruppen.$klasse) && $gesetzteKundengruppen.$klasse}selected{/if}>{$oKundengruppe->cName}</option>
                                        {/foreach}
                                    </select>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=__('customerclassDesc')}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                {if $versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl' || $versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' || $versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
                    <h3 class="panel-title">{__('priceScale')}</h3>
                    <ul class="jtl-list-group">
                        <li class="input-group">
                            <table id="price_range" class="table">
                                <thead>
                                <tr>
                                    <th class="p50"></th>
                                    <th>{__('amount')}</th>
                                </tr>
                                </thead>
                                <tbody>

                                {if isset($VersandartStaffeln) && $VersandartStaffeln|@count > 0}
                                    {foreach $VersandartStaffeln as $oPreisstaffel}
                                        {if $oPreisstaffel->fBis != 999999999}
                                            <tr>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><label>{__('upTo')}</label></span>
                                                        <input type="text" id="bis{$oPreisstaffel@index}" name="bis[]" value="{if isset($VersandartStaffeln[$oPreisstaffel@index]->fBis)}{$VersandartStaffeln[$oPreisstaffel@index]->fBis}{/if}" class="form-control kilogram" />
                                                        <span class="input-group-addon"><label>{$einheit}</label></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><label>{__('amount')}:</label></span>
                                                        <input type="text" id="preis{$oPreisstaffel@index}" name="preis[]" value="{if isset($VersandartStaffeln[$oPreisstaffel@index]->fPreis)}{$VersandartStaffeln[$oPreisstaffel@index]->fPreis}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxpreisstaffel{$oPreisstaffel@index}', this)" /> <span id="ajaxpreisstaffel{$oPreisstaffel@index}"></span>*}
                                                    </div>
                                                </td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-addon"><label>{__('upTo')}</label></span>
                                                <input type="text" id="bis1" name="bis[]" value="" class="form-control kilogram" />
                                                <span class="input-group-addon"><label>{$einheit}</label></span>
                                            </div>
                                        </td>
                                        <td class="tcenter">
                                            <div class="input-group">
                                                <span class="input-group-addon"><label>{__('amount')}:</label></span>
                                                <input type="text" id="preis1" name="preis[]" value="" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxpreis1', this)" /> <span id="ajaxpreis1"></span>*}
                                            </div>
                                        </td>
                                    </tr>
                                {/if}

                                </tbody>
                            </table>
                            <div class="btn-group">
                                <button name="addRow" type="button" value="{__('addPriceScale')}" onclick="addInputRow();" class="btn btn-primary"><i class="fa fa-share"></i> {__('addPriceScale')}</button>
                                <button name="delRow" type="button" value="{__('delPriceScale')}" onclick="delInputRow();" class="btn btn-danger"><i class="fa fa-trash"></i> {__('delPriceScale')}</button>
                            </div>
                        </li>
                    </ul>
                {elseif $versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
                    <div class="panel-heading">
                        <h3 class="panel-title">{__('shippingPrice')}</h3>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right">
                            {__('shippingPrice')} {__('amount')}
                        </div>
                        <div class="col-md-8">
                            <input type="text" id="fPreisNetto" name="fPreis" value="{if isset($Versandart->fPreis)}{$Versandart->fPreis}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxfPreisNetto', this)" /> <span id="ajaxfPreisNetto"></span>*}
                        </div>
                    </div>
                {/if}
                    <div class="row">
                        <div class="col-md-4 text-right">
                            {__('freeShipping')}
                        </div>
                        <div class="col-md-3">
                            <select id="versandkostenfreiAktiv" name="versandkostenfreiAktiv" class="combo form-control">
                                <option value="0">{__('no')}</option>
                                <option value="1" {if isset($Versandart->fVersandkostenfreiAbX) && $Versandart->fVersandkostenfreiAbX > 0}selected{/if}>{__('yes')}</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" id="fVersandkostenfreiAbX" name="fVersandkostenfreiAbX" class="form-control price_large" value="{if isset($Versandart->fVersandkostenfreiAbX)}{$Versandart->fVersandkostenfreiAbX}{/if}">{* onKeyUp="setzePreisAjax(false, 'ajaxversandkostenfrei', this)" /> <span id="ajaxversandkostenfrei"></span>*}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right">
                            {__('maxCosts')}
                        </div>
                        <div class="col-md-3">
                            <select id="versanddeckelungAktiv" name="versanddeckelungAktiv" class="combo form-control">
                                <option value="0">{__('no')}</option>
                                <option value="1" {if isset($Versandart->fDeckelung) && $Versandart->fDeckelung > 0}selected{/if}>{__('yes')}</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" id="fDeckelung" name="fDeckelung" value="{if isset($Versandart->fDeckelung)}{$Versandart->fDeckelung}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxdeckelung', this)" /> <span id="ajaxdeckelung"></span>*}
                        </div>
                    </div>

                    <div class="panel-heading">
                        <h3 class="panel-title">{__('validOnShippingClasses')}</h3>
                    </div>
                    <div class="panel-body">
                        <input name="kVersandklasse" type="hidden" value="{if !empty($Versandart->cVersandklassen)}{$Versandart->cVersandklassen}{else}-1{/if}">
                        <ul id="ulVK" class="jtl-list-group">
                            <li id='liVKneu' class="input-group" style="display:none;">
                                <span class="input-group-wrap">
                                    <selectX class="selectX2 form-control" name="Versandklassen"
                                             onchange="checkCombination();updateVK();"
                                             multiple>
                                        <option value="-1">{__('allCombinations')}</option>
                                        {foreach $versandKlassen as $vk}
                                            <option value="{$vk->kVersandklasse}">{$vk->cName}</option>
                                        {/foreach}
                                    </selectX>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=__('shippingclassDesc')}</span>
                                <div class="input-group-btn">
                                    <button class="btn btn-danger" type="button"
                                            onclick="$(this).parent().parent().detach(); updateVK();">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </button>
                                </div>
                            </li>
                            {if !empty($Versandart->cVersandklassen)}
                                {$aVK = ' '|explode:$Versandart->cVersandklassen}
                                {foreach $aVK as $VK}
                                    <li class="input-group">
                                        <span class="input-group-wrap">
                                            <select class="select2 form-control" name="Versandklassen"
                                                    onchange="checkCombination();updateVK();" multiple="multiple">
                                                <option value="-1"{if $VK@iteration > 1} disabled="disabled"{/if}{if $VK === '-1'} selected{/if}>{__('allCombinations')}</option>
                                                {if $VK === '-1'}
                                                    {foreach $versandKlassen as $vclass}
                                                        <option value="{$vclass->kVersandklasse}">{$vclass->cName}</option>
                                                    {/foreach}
                                                {else}
                                                    {$vkID = '-'|explode:$VK}
                                                    {foreach $versandKlassen as $vclass}
                                                    <option value="{$vclass->kVersandklasse}"{if $vclass->kVersandklasse|in_array:$vkID} selected{/if}>{$vclass->cName}</option>
                                                {/foreach}
                                                {/if}
                                            </select>
                                        </span>
                                        <span class="input-group-addon">{getHelpDesc cDesc=__('shippingclassDesc')}</span>
                                        {if $VK@iteration != 1}
                                            <div class="input-group-btn">
                                                <button class="btn btn-danger" type="button"
                                                        onclick="$(this).parent().parent().detach(); updateVK();">
                                                    <span class="glyphicon glyphicon-remove"></span>
                                                </button>
                                            </div>
                                        {/if}
                                    </li>
                                {/foreach}
                            {else}
                                <li class="input-group">
                                    <span class="input-group-wrap">
                                        <select class="select2 form-control" name="Versandklassen"
                                                onchange="checkCombination();updateVK();" multiple="multiple">
                                            <option value="-1">{__('allCombinations')}</option>
                                            {foreach $versandKlassen as $vclass}
                                                <option value="{$vclass->kVersandklasse}">{$vclass->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                    <span class="input-group-addon">{getHelpDesc cDesc=__('shippingclassDesc')}</span>
                                </li>
                            {/if}
                        </ul>
                    </div>
                    <div class="btn-group" role="group">
                        <button id="addNewShippingClassCombi" class="btn btn-success" type="button"
                                onclick="addShippingCombination();$('.select2').select2();">
                            <span class="glyphicon glyphicon-plus"></span> {__('addShippingClass')}
                        </button>
                        {if !empty($missingShippingClassCombis)}
                            <button class="btn btn-warning" type="button" data-toggle="collapse" data-target="#collapseShippingClasses" aria-expanded="false" aria-controls="collapseShippingClasses">
                                {__('showMissingCombinations')}
                            </button>
                        {/if}
                    </div>
                    {if !empty($missingShippingClassCombis)}
                        <div class="collapse" id="collapseShippingClasses">
                            <div class="row">
                                {if $missingShippingClassCombis === -1}
                                    <div class="col-xs-12">
                                        {__('coverageShippingClassCombination')}
                                        {__('noShipClassCombiValidation')|replace:'%s':$smarty.const.SHIPPING_CLASS_MAX_VALIDATION_COUNT}
                                    </div>
                                {else}
                                    {foreach $missingShippingClassCombis as $mscc}
                                        <div class="col-xs-12 col-sm-6">[{$mscc}]</div>
                                    {/foreach}
                                {/if}
                            </div>
                        </div>
                    {/if}
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{__('acceptedPaymentMethods')}</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="jtl-list-group">

                            <li class="input-group2 table-responsive">
                                <table class="list table">
                                    <thead>
                                    <tr>
                                        <th class="check"></th>
                                        <th class="tleft">{__('paymentType')}</th>
                                        <th></th>
                                        <th>{__('amount')}</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $zahlungsarten as $zahlungsart}
                                        {assign var=kZahlungsart value=$zahlungsart->kZahlungsart}
                                        <tr>
                                            <td class="check">
                                                <input type="checkbox" id="kZahlungsart{$zahlungsart@index}" name="kZahlungsart[]" class="boxen" value="{$kZahlungsart}" {if isset($VersandartZahlungsarten[$kZahlungsart]->checked)}{$VersandartZahlungsarten[$kZahlungsart]->checked}{/if} />
                                            </td>
                                            <td>
                                                <label for="kZahlungsart{$zahlungsart@index}">
                                                    {$zahlungsart->cName}{if isset($zahlungsart->cAnbieter) && $zahlungsart->cAnbieter|strlen > 0} ({$zahlungsart->cAnbieter}){/if}
                                                </label>
                                            </td>
                                            <td>{__('discount')}</td>
                                            <td class="tcenter">
                                                <input type="text" id="Netto_{$kZahlungsart}" name="fAufpreis_{$kZahlungsart}" value="{if isset($VersandartZahlungsarten[$kZahlungsart]->fAufpreis)}{$VersandartZahlungsarten[$kZahlungsart]->fAufpreis}{/if}" class="form-control price_large"{* onKeyUp="setzePreisAjax(false, 'ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}', this)"*} />
                                            </td>
                                            <td>
                                                <select name="cAufpreisTyp_{$kZahlungsart}" id="cAufpreisTyp_{$kZahlungsart}" class="form-control">
                                                    <option value="festpreis"{if isset($VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp) && $VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp === 'festpreis'} selected{/if}>
                                                        {__('amount')}
                                                    </option>
                                                    <option value="prozent"{if isset($VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp) && $VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp === 'prozent'} selected{/if}>
                                                        %
                                                    </option>
                                                </select>
                                                <span id="ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}" class="ZahlungsartAufpreis"></span>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{__('shipToCountries')}</h3>
            </div>
            {foreach $continents as $continentKey => $continent}
                <div class="country-collapse">
                    <div class="row">
                        <div class="col-xs-5 col-md-6 collapsed" data-toggle="collapse" data-target="#collapse-continent-{$continentKey}">
                            {$continent->name}
                        </div>
                        <div class="col-xs-3 col-md-2 collapsed text-muted" data-toggle="collapse" data-target="#collapse-continent-{$continentKey}">
                            {$continent->countriesCount} {__('countries')}
                        </div>
                        <div class="col-xs-2 collapsed" data-toggle="collapse" data-target="#collapse-continent-{$continentKey}">
                            {$continent->countriesSelectedCount} {__('countriesSelected')}
                        </div>
                        <div class="col-xs-2 select-all-continent" data-continent="continent-{$continentKey}">
                            <div class="btn btn-link">
                                {__('selectAll')}
                            </div>
                        </div>
                    </div>
                    <div class="row collapse in" id="collapse-continent-{$continentKey}">
                        <div class="col-md-12">
                            <table class="table">
                                <tbody>
                                <tr>
                                    {foreach $continent->countries as $country}
                                    {if $country@index % 5 === 0}
                                    <td style="height:0;border:0 none;" colspan="2"></td>
                                </tr>
                                <tr>
                                    {/if}
                                    <td>
                                        <input type="checkbox" name="land[]" data-id="country_{$country->getISO()}" value="{$country->getISO()}" {if isset($gewaehlteLaender) && is_array($gewaehlteLaender) && in_array($country->getISO(),$gewaehlteLaender)} checked="checked"{/if} />
                                        <label for="country_{$country->getISO()}">{$country->getName()}</label>
                                    </td>
                                    {/foreach}
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>

        {literal}
            <script type="text/javascript">
                <!--
                $('.country-collapse [data-toggle="collapse"]').click(function () {
                   $(this).closest('.country-collapse').toggleClass('active');
                });
                $('.select-all-continent').click(function(){
                    $(this).toggleClass('all-selected');
                    var continent = $(this).data('continent');
                    if ($(this).hasClass('all-selected')) {
                        $('#collapse-' + continent + ' input[name="land[]"]').each(function () {
                            $('input[data-id="' + $(this).data('id') + '"]').prop('checked', true);
                        });
                    } else {
                        $('#collapse-' + continent + ' input[name="land[]"]').each(function () {
                            $('input[data-id="' + $(this).data('id') + '"]').prop('checked', false);
                        })
                    }
                });
                $('input[name="land[]"]').change(function () {
                    $('input[data-id="' + $(this).data('id') + '"]').prop('checked', $(this).prop('checked'));
                });
                //-->
            </script>
        {/literal}
        <div class="save_wrapper btn-group">
            <button type="submit" value="{if !isset($Versandart->kVersandart) || !$Versandart->kVersandart}{__('createShippingType')}{else}{__('modifyedShippingType')}{/if}"
                    class="btn btn-primary">
                {if !isset($Versandart->kVersandart) || !$Versandart->kVersandart}
                    <i class="fa fa-share"></i> {__('createShippingType')}
                {else}
                    <i class="fa fa-edit"></i> {__('modifyedShippingType')}
                {/if}
            </button>
            <a href="versandarten.php" value="{__('cancel')}" class="btn btn-danger"><i class="fa fa-exclamation"></i> {__('cancel')}</a>
        </div>
</div>
</form>
</div>
