{if $oKupon->kKupon === 0}
    {assign var=cTitel value=__('buttonNewCoupon')}
{else}
    {assign var=cTitel value=__('buttonModifyCoupon')}
{/if}

{if $oKupon->cKuponTyp === $couponTypes.standard}
    {assign var=cTitel value="$cTitel : {__('standardCoupon')}"}
{elseif $oKupon->cKuponTyp === $couponTypes.shipping}
    {assign var=cTitel value="$cTitel : {__('shippingCoupon')}"}
{elseif $oKupon->cKuponTyp === $couponTypes.newCustomer}
    {assign var=cTitel value="$cTitel : {__('newCustomerCoupon')}"}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('couponsDesc') cDokuURL=__('couponsURL')}

<script>
    $(function () {
        {if $oKupon->cKuponTyp == $couponTypes.standard || $oKupon->cKuponTyp == $couponTypes.newCustomer}
            makeCurrencyTooltip('fWert');
        {/if}
        makeCurrencyTooltip('fMindestbestellwert');
        $('#bOpenEnd').on('change', onEternalCheckboxChange);
        onEternalCheckboxChange();
    });

    function onEternalCheckboxChange () {
        var elem = $('#bOpenEnd');
        var bOpenEnd = elem[0].checked;
        $('#dGueltigBis').prop('disabled', bOpenEnd);
        $('#dDauerTage').prop('disabled', bOpenEnd);
        if ($('#bOpenEnd').prop('checked')) {
            $('#dDauerTage').val('{__('openEnd')}');
            $('#dGueltigBis').val('');
        } else {
            $('#dDauerTage').val('');
        }
    }
</script>

<div id="content" class="container-fluid">
    <form method="post" action="kupons.php">
        {$jtl_token}
        <input type="hidden" name="kKuponBearbeiten" value="{$oKupon->kKupon}">
        <input type="hidden" name="cKuponTyp" value="{$oKupon->cKuponTyp}">
        <div class="card settings">
            <div class="card-header">
                <div class="subheading1">{__('names')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('name')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="text" class="form-control" name="cName" id="cName" value="{$oKupon->cName}">
                    </div>
                </div>
                {foreach $sprachen as $language}
                    {assign var=langCode value=$language->getIso()}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cName_{$langCode}">{__('showedName')} ({$language->getLocalizedName()}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input
                                type="text" class="form-control" name="cName_{$langCode}"
                                id="cName_{$langCode}"
                                value="{if isset($oKuponName_arr[$langCode])}{$oKuponName_arr[$langCode]}{/if}">
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
        {if empty($oKupon->kKupon) && isset($oKupon->cKuponTyp) && $oKupon->cKuponTyp !== $couponTypes.newCustomer}
            <div class="card settings">
                <div class="card-header">
                    <div class="subheading1">
                        <label>
                            <input type="checkbox" name="couponCreation" id="couponCreation" class="checkfield"{if isset($oKupon->massCreationCoupon->cActiv) && $oKupon->massCreationCoupon->cActiv == 1} checked{/if} value="1" />{__('couponsCreation')}
                        </label>
                    </div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body{if !isset($oKupon->massCreationCoupon)} hidden{/if}" id="massCreationCouponsBody">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="numberOfCoupons">{__('numberCouponsDesc')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="number" name="numberOfCoupons" id="numberOfCoupons" min="2" step="1" {if isset($oKupon->massCreationCoupon->numberOfCoupons)}value="{$oKupon->massCreationCoupon->numberOfCoupons}"{else}value="2"{/if}/>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="lowerCase">{__('lowerCaseDesc')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="checkbox" name="lowerCase" id="lowerCase" class="checkfield" {if isset($oKupon->massCreationCoupon->lowerCase) && $oKupon->massCreationCoupon->lowerCase == true}checked{elseif isset($oKupon->massCreationCoupon->lowerCase) && $oKupon->massCreationCoupon->lowerCase == false}unchecked{else}checked{/if} />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="upperCase">{__('upperCaseDesc')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="checkbox" name="upperCase" id="upperCase" class="checkfield" {if isset($oKupon->massCreationCoupon->upperCase) && $oKupon->massCreationCoupon->upperCase == true}checked{elseif isset($oKupon->massCreationCoupon->upperCase) && $oKupon->massCreationCoupon->upperCase == false}unchecked{else}checked{/if} />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="numbersHash">{__('numbersHashDesc')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="checkbox" name="numbersHash" id="numbersHash" class="checkfield" {if isset($oKupon->massCreationCoupon->numbersHash) && $oKupon->massCreationCoupon->numbersHash == true}checked{elseif isset($oKupon->massCreationCoupon->numbersHash) && $oKupon->massCreationCoupon->numbersHash == false}unchecked{else}checked{/if} />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="hashLength">{__('hashLengthDesc')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="number" name="hashLength" id="hashLength" min="2" max="16" step="1" {if isset($oKupon->massCreationCoupon->hashLength)}value="{$oKupon->massCreationCoupon->hashLength}"{else}value="2"{/if} />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                         <label class="col col-sm-4 col-form-label text-sm-right" for="prefixHash">{__('prefixHashDesc')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="text" name="prefixHash" id="prefixHash" placeholder="SUMMER"{if isset($oKupon->massCreationCoupon->prefixHash)} value="{$oKupon->massCreationCoupon->prefixHash}"{/if} />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                         <label class="col col-sm-4 col-form-label text-sm-right" for="suffixHash">{__('suffixHashDesc')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="text" name="suffixHash" id="suffixHash"{if isset($oKupon->massCreationCoupon->suffixHash)} value="{$oKupon->massCreationCoupon->suffixHash}"{/if} />
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        <div class="card settings">
            <div class="card-header">
                <div class="subheading1">{__('general')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                {if $oKupon->cKuponTyp === $couponTypes.standard || $oKupon->cKuponTyp === $couponTypes.newCustomer}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="fWert">{__('value')} ({__('gross')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" name="fWert" id="fWert" value="{$oKupon->fWert}">
                        </div>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="cWertTyp" id="cWertTyp" class="custom-select combo">
                                <option value="festpreis"{if $oKupon->cWertTyp === 'festpreis'} selected{/if}>
                                    {__('amount')}
                                </option>
                                <option value="prozent"{if $oKupon->cWertTyp === 'prozent'} selected{/if}>
                                    %
                                </option>
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3" {if $oKupon->cWertTyp === 'prozent'} style="display: none;"{/if}>
                            {getCurrencyConversionTooltipButton inputId='fWert'}
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nGanzenWKRabattieren">{__('wholeWKDiscount')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="nGanzenWKRabattieren" id="nGanzenWKRabattieren" class="custom-select combo">
                                <option value="1"{if $oKupon->nGanzenWKRabattieren == 1} selected{/if}>
                                    {__('yes')}
                                </option>
                                <option value="0"{if $oKupon->nGanzenWKRabattieren == 0} selected{/if}>
                                    {__('no')}
                                </option>
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('wholeWKDiscountHint')}</div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kSteuerklasse">{__('taxClass')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="kSteuerklasse" id="kSteuerklasse" class="custom-select combo">
                                {foreach $oSteuerklasse_arr as $oSteuerklasse}
                                    <option value="{$oSteuerklasse->kSteuerklasse}"{if $oKupon->kSteuerklasse == $oSteuerklasse->kSteuerklasse} selected{/if}>
                                        {$oSteuerklasse->cName}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                {/if}
                {if $oKupon->cKuponTyp === $couponTypes.shipping}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cZusatzgebuehren">{__('additionalShippingCosts')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="checkbox" class="checkfield" name="cZusatzgebuehren" id="cZusatzgebuehren" value="Y"{if $oKupon->cZusatzgebuehren === 'Y'} checked{/if}>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('additionalShippingCostsHint')}</div>
                    </div>
                {/if}
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="fMindestbestellwert">{__('minOrderValue')} ({__('gross')}):</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="text" class="form-control" name="fMindestbestellwert" id="fMindestbestellwert" value="{$oKupon->fMindestbestellwert}">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {getCurrencyConversionTooltipButton inputId='fMindestbestellwert'}
                    </div>
                </div>
                {if $oKupon->cKuponTyp === $couponTypes.standard || $oKupon->cKuponTyp === $couponTypes.shipping}
                    <div class="form-group form-row align-items-center{if isset($oKupon->massCreationCoupon)} hidden{/if}" id="singleCouponCode">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cCode">{__('code')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" name="cCode" id="cCode"{if !isset($oKupon->massCreationCoupon)} value="{$oKupon->cCode}"{/if}>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('codeHint')}</div>
                    </div>
                {/if}
                {if $oKupon->cKuponTyp === $couponTypes.shipping}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cLieferlaender">{__('shippingCountries')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" name="cLieferlaender" id="cLieferlaender" value="{$oKupon->cLieferlaender}">
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('shippingCountriesHint')}</div>
                    </div>
                {/if}
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="nVerwendungen">{__('uses')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="text" class="form-control" name="nVerwendungen" id="nVerwendungen" value="{$oKupon->nVerwendungen}">
                    </div>
                </div>
                {if $oKupon->cKuponTyp === $couponTypes.standard || $oKupon->cKuponTyp === $couponTypes.shipping}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nVerwendungenProKunde">{__('usesPerCustomer')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" name="nVerwendungenProKunde" id="nVerwendungenProKunde" value="{$oKupon->nVerwendungenProKunde}">
                        </div>
                    </div>
                {/if}
            </div>
        </div>
        <div class="card settings">
            <div class="card-header">
                <div class="subheading1">{__('validityPeriod')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="dGueltigAb">{__('validFrom')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="text" class="form-control" name="dGueltigAb" id="dGueltigAb" >
                        {include
                            file="snippets/daterange_picker.tpl"
                            datepickerID="#dGueltigAb"
                            currentDate="{$oKupon->cGueltigAbLong}"
                            format="DD.MM.YYYY"
                            separator="{__('datepickerSeparator')}"
                            single=true
                        }
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('validFromHelp')}</div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="dGueltigBis">{__('validUntil')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="datetime" class="form-control" name="dGueltigBis" id="dGueltigBis">
                        {include
                            file="snippets/daterange_picker.tpl"
                            datepickerID="#dGueltigBis"
                            currentDate="{if $oKupon->cGueltigBisLong !== 'open-end'}{$oKupon->cGueltigBisLong}{/if}"
                            format="DD.MM.YYYY"
                            separator="{__('datepickerSeparator')}"
                            single=true
                        }
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('validUntilHelp')}</div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="dDauerTage">{__('periodOfValidity')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="text" class="form-control" name="dDauerTage" id="dDauerTage">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('periodOfValidityHelp')}</div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="bOpenEnd">{__('openEnd')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="checkbox" class="checkfield" name="bOpenEnd" id="bOpenEnd" value="Y"{if $oKupon->bOpenEnd} checked{/if}>
                    </div>
                </div>
            </div>
        </div>
        <div class="card settings">
            <div class="card-header">
                <div class="subheading1">{__('restrictions')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                {include file='tpl_inc/searchpicker_modal.tpl'
                    searchPickerName='articlePicker'
                    modalTitle="{__('titleChooseProducts')}"
                    searchInputLabel="{__('labelSearchProduct')}"
                }
                <script>
                    $(function () {
                        articlePicker = new SearchPicker({
                            searchPickerName:  'articlePicker',
                            getDataIoFuncName: 'getProducts',
                            keyName:           'cArtNr',
                            renderItemCb:      function (item) {
                                return '<p class="list-group-item-text">' + item.cName + ' <em>(' + item.cArtNr + ')</em></p>';
                            },
                            onApply:           onApplySelectedArticles,
                            selectedKeysInit:  '{$oKupon->cArtikel}'.split(';').filter(function (i) { return i !== ''; })
                        });
                        onApplySelectedArticles(articlePicker.getSelection());
                    });
                    function onApplySelectedArticles(selectedArticles)
                    {
                        if (selectedArticles.length > 0) {
                            $('#articleSelectionInfo').val(selectedArticles.length + ' {__('product')}');
                            $('#cArtikel').val(selectedArticles.join(';') + ';');
                        } else {
                            $('#articleSelectionInfo').val('{__('all')}' + ' {__('products')}');
                            $('#cArtikel').val('');
                        }
                    }
                </script>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="articleSelectionInfo">{__('productRestrictions')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="text" class="form-control" readonly="readonly" id="articleSelectionInfo">
                        <input type="hidden" id="cArtikel" name="cArtikel" value="{$oKupon->cArtikel}">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                data-target="#articlePicker-modal">
                            <i class="fa fa-edit"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kHersteller">{__('restrictedToManufacturers')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select multiple size="10" name="kHersteller[]" id="kHersteller" class="custom-select combo">
                            <option value="-1"{if $oKupon->cHersteller === '-1'} selected{/if}>
                                Alle Hersteller
                            </option>
                            {foreach $oHersteller_arr as $oHersteller}
                                <option value="{$oHersteller->kHersteller}"{if $oHersteller->selected == 1} selected{/if}>
                                    {$oHersteller->cName}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('multipleChoice')}</div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('restrictionToCustomerGroup')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select name="kKundengruppe" id="kKundengruppe" class="custom-select combo">
                            <option value="-1"{if $oKupon->kKundengruppe == -1} selected{/if}>
                                {__('allCustomerGroups')}
                            </option>
                            {foreach $oKundengruppe_arr as $oKundengruppe}
                                <option value="{$oKundengruppe->kKundengruppe}"{if $oKupon->kKundengruppe == $oKundengruppe->kKundengruppe} selected{/if}>
                                    {$oKundengruppe->cName}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cAktiv">{__('active')}:</label>
                    <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="checkbox" class="checkfield" name="cAktiv" id="cAktiv" value="Y"{if $oKupon->cAktiv === 'Y'} checked{/if}>
                    </span>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kKategorien">{__('restrictedToCategories')}:</label>
                    <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select multiple size="10" name="kKategorien[]" id="kKategorien" class="custom-select combo">
                            <option value="-1"{if $oKupon->cKategorien === '-1'} selected{/if}>
                                Alle Kategorien
                            </option>
                            {foreach $oKategorie_arr as $oKategorie}
                                <option value="{$oKategorie->kKategorie}"{if $oKategorie->selected == 1} selected{/if}>
                                    {$oKategorie->cName}
                                </option>
                            {/foreach}
                        </select>
                    </span>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('multipleChoice')}</div>
                </div>
                {if $oKupon->cKuponTyp === $couponTypes.standard || $oKupon->cKuponTyp === $couponTypes.shipping}
                    {include file='tpl_inc/searchpicker_modal.tpl'
                        searchPickerName='customerPicker'
                        modalTitle="{__('chooseCustomer')}"
                        searchInputLabel="{__('searchNameZipEmail')}"
                    }
                    <script>
                        $(function () {
                            customerPicker = new SearchPicker({
                                searchPickerName:  'customerPicker',
                                getDataIoFuncName: 'getCustomers',
                                keyName:           'kKunde',
                                renderItemCb:      renderCustomerItem,
                                onApply:           onApplySelectedCustomers,
                                selectedKeysInit:  [{foreach $kKunde_arr as $kKunde}'{$kKunde}',{/foreach}]
                            });
                            onApplySelectedCustomers(customerPicker.getSelection());
                        });
                        function renderCustomerItem(item)
                        {
                            return '<p class="list-group-item-text">' +
                                item.cVorname + ' ' + item.cNachname + '<em>(' + item.cMail + ')</em></p>' +
                                '<p class="list-group-item-text">' +
                                item.cStrasse + ' ' + item.cHausnummer + ', ' + item.cPLZ + ' ' + item.cOrt + '</p>';
                        }
                        function onApplySelectedCustomers(selectedCustomers)
                        {
                            if (selectedCustomers.length > 0) {
                                $('#customerSelectionInfo').val(selectedCustomers.length + ' {__('customers')}');
                                $('#cKunden').val(selectedCustomers.join(';'));
                            } else {
                                $('#customerSelectionInfo').val('{__('all')}' + ' {__('customer')}');
                                $('#cKunden').val('-1');
                            }
                        }
                    </script>
                    <div class="form-group form-row align-items-center{if isset($oKupon->massCreationCoupon)} hidden{/if}" id="limitedByCustomers">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="customerSelectionInfo">{__('restrictedToCustomers')}:</label>
                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" readonly="readonly" id="customerSelectionInfo">
                            <input type="hidden" id="cKunden" name="cKunden" value="{$oKupon->cKunden}">
                        </span>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                    data-target="#customerPicker-modal">
                                <i class="fa fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center{if isset($oKupon->massCreationCoupon)} hidden{/if}" id="informCustomers">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="informieren">{__('informCustomers')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="checkbox" class="checkfield" name="informieren" id="informieren" value="Y">
                        </div>
                    </div>
                {/if}
            </div>
        </div>
        <button type="submit" class="btn btn-primary" name="action" value="speichern">
            <i class="fa fa-share"></i> {__('save')}
        </button>
    </form>
</div>
