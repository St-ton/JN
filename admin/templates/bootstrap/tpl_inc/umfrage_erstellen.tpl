<script type="text/javascript">
$(document).ready(function () {ldelim}
    if(document.getElementById("kupon").selectedIndex === 0) {ldelim}
        document.getElementById('fGuthaben').disabled = false;
        document.getElementById('nBonuspunkte').disabled = false;
    {rdelim} else {ldelim}
        document.getElementById('fGuthaben').disabled = true;
        document.getElementById('nBonuspunkte').disabled = true;
    {rdelim}
{rdelim});
function selectCheck(selectBox) {ldelim}
    if(selectBox.selectedIndex === 0) {ldelim}
        document.getElementById('fGuthaben').disabled = false;
        document.getElementById('nBonuspunkte').disabled = false;
        document.getElementById('fGuthaben').value = '';
        document.getElementById('nBonuspunkte').value = '';
    {rdelim} else {ldelim}
        document.getElementById('fGuthaben').disabled = true;
        document.getElementById('nBonuspunkte').disabled = true;
        document.getElementById('fGuthaben').value = '';
        document.getElementById('nBonuspunkte').value = '';
    {rdelim}
{rdelim}

function checkInput(inputField, cFeld) {ldelim}
    document.getElementById('kupon').disabled = true;
    document.getElementById('kupon').selectedIndex = 0;
    if(cFeld === 'fGuthaben') {ldelim}
        document.getElementById('nBonuspunkte').disabled = true;
    {rdelim} else {ldelim}
        document.getElementById('fGuthaben').disabled = true;
        inputField.disabled = false;
    {rdelim}
{rdelim}

function clearInput(inputField) {ldelim}
    if(inputField.value.length === 0)  {ldelim}
        document.getElementById('kupon').disabled = false;
        document.getElementById('fGuthaben').disabled = false;
        document.getElementById('nBonuspunkte').disabled = false;
    {rdelim}
{rdelim}
</script>

<div id="page">
    <div id="content">
        <div id="welcome" class="card post">
            <div class="card-header">
                <div class="subheading1">{__('umfrageEnter')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <form name="umfrage" method="post" action="umfrage.php">
                    {$jtl_token}
                    <input type="hidden" name="umfrage" value="1" />
                    <input type="hidden" name="umfrage_speichern" value="1" />
                    <input type="hidden" name="tab" value="umfrage" />
                    <input type="hidden" name="s1" value="{if !empty($s1)}{$s1}{else}0{/if}" />
                    {if isset($oUmfrage->kUmfrage) && $oUmfrage->kUmfrage > 0}
                        <input type="hidden" name="umfrage_edit_speichern" value="1" />
                        <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}" />
                    {/if}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cName">{__('name')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" id="cName" name="cName" type="text"  value="{if isset($oUmfrage->cName)}{$oUmfrage->cName}{/if}" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cSeo">{__('seo')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" id="cSeo" name="cSeo" type="text"  value="{if isset($oUmfrage->cSeo)}{$oUmfrage->cSeo}{/if}" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="kKundengruppe">{__('customerGroup')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="kKundengruppe"
                                    name="kKundengruppe[]"
                                    multiple="multiple"
                                    class="selectpicker custom-select"
                                    data-selected-text-format="count > 2"
                                    data-size="7">
                                <option value="-1" {if isset($oUmfrage->kKundengruppe_arr)}{foreach $oUmfrage->kKundengruppe_arr as $kKundengruppe}{if $kKundengruppe == '-1'}selected{/if}{/foreach}{/if}>{__('all')}</option>
                                <option data-divider="true"></option>
                                {foreach $customerGroups as $customerGroup}
                                    <option value="{$customerGroup->getID()}" {if isset($oUmfrage->kKundengruppe_arr)}{foreach $oUmfrage->kKundengruppe_arr as $kKundengruppe}{if $customerGroup->getID() === (int)$kKundengruppe}selected{/if}{/foreach}{/if}>{$customerGroup->getName()}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="dGueltigVon">{__('umfrageValidation')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" id="dGueltigVon" name="dGueltigVon" type="text"  value="{if isset($oUmfrage->dGueltigVon_de) && $oUmfrage->dGueltigVon_de|strlen > 0}{$oUmfrage->dGueltigVon_de}{else}{$smarty.now|date_format:'%d.%m.%Y %H:%M'}{/if}" />
                        </div>
                        <div class="col-1 pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <label for="dGueltigBis">{__('to')}</label>
                        </div>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" id="dGueltigBis" name="dGueltigBis" type="text"  value="{if isset($oUmfrage->dGueltigBis_de)}{$oUmfrage->dGueltigBis_de}{/if}"/>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="nAktiv">{__('active')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="nAktiv" name="nAktiv" class="combo custom-select">
                                <option value="1"{if isset($oUmfrage->nAktiv) && $oUmfrage->nAktiv == 1} selected{/if}>{__('yes')}</option>
                                <option value="0"{if isset($oUmfrage->nAktiv) && $oUmfrage->nAktiv == 0} selected{/if}>{__('no')}</option>
                            </select>
                        </div>
                    </div>
                    {if $oKupon_arr|@count > 0 && $oKupon_arr}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-3 col-form-label text-sm-right" for="kupon">{__('coupon')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="kupon" name="kKupon" class="combo custom-select" onchange="selectCheck(this);">
                                    <option value="0"{if isset($oUmfrage->kKupon) && $oUmfrage->kKupon == 0} selected{/if} index=0>{__('umfrageNoCoupon')}</option>
                                    {foreach $oKupon_arr as $oKupon}
                                        <option value="{$oKupon->kKupon}"{if isset($oUmfrage->kKupon) && $oKupon->kKupon == $oUmfrage->kKupon} selected{/if}>{$oKupon->cName}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    {/if}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="fGuthaben">{__('credit')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" name="fGuthaben" id="fGuthaben" type="text"  value="{if isset($oUmfrage->fGuthaben)}{$oUmfrage->fGuthaben}{/if}" onclick="checkInput(this,'fGuthaben');" onblur="clearInput(this);" />
                            <input id="nBonuspunkte" type="hidden" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cBeschreibung">{__('description')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cBeschreibung" class="ckeditor" name="cBeschreibung" rows="15" cols="60">{if isset($oUmfrage->cBeschreibung)}{$oUmfrage->cBeschreibung}{/if}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="umfrage.php">
                            <i class="fa fa-angle-double-left"></i> {__('goBack')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button class="btn btn-primary btn-block" name="speichern" type="button" value="{__('save')}" onclick="document.umfrage.submit();">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
