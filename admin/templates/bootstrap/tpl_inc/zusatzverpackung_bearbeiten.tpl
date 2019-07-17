<form name="zusatzverpackung" method="post" action="zusatzverpackung.php">
    {$jtl_token}
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="kVerpackung" value="{if isset($kVerpackung)}{$kVerpackung}{/if}" />
    <div class="card">
        <div class="card-header">
            <div class="subheading1">
                {if isset($kVerpackung) && $kVerpackung > 0}{__('zusatzverpackungEdit')}{else}{__('zusatzverpackungCreate')}{/if}
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="kundenfeld table">
                {foreach $sprachen as $key => $language}
                    {assign var=cISO value=$language->getIso()}
                    <tr>
                        <td><label for="cName_{$cISO}">{__('name')} ({$language->getLocalizedName()})</label></td>
                        <td>
                            <input class="form-control" id="cName_{$cISO}" name="cName_{$cISO}" type="text" value="{if isset($oVerpackungEdit->oSprach_arr[$cISO]->cName)}{$oVerpackungEdit->oSprach_arr[$cISO]->cName}{/if}" {if $key === 0}required{/if}>
                        </td>
                    </tr>
                {/foreach}
                <tr>
                    <td><label for="fBrutto">{__('price')} ({__('gross')})</label></td>
                    <td>
                        <input class="form-control" name="fBrutto" id="fBrutto" type="text" value="{if isset($oVerpackungEdit->fBrutto)}{$oVerpackungEdit->fBrutto}{/if}" onKeyUp="setzePreisAjax(false, 'WertAjax', this)" required/>
                        <span id="WertAjax">{if isset($oVerpackungEdit->fBrutto)}{getCurrencyConversionSmarty fPreisBrutto=$oVerpackungEdit->fBrutto}{/if}</span>
                    </td>
                </tr>
                <tr>
                    <td><label for="fMindestbestellwert">{__('minOrderValue')} ({__('gross')})</label></td>
                    <td>
                        <input class="form-control" name="fMindestbestellwert" id="fMindestbestellwert" type="text" value="{if isset($oVerpackungEdit->fMindestbestellwert)}{$oVerpackungEdit->fMindestbestellwert}{/if}" onKeyUp="setzePreisAjax(false, 'MindestWertAjax', this)" required/>
                        <span id="MindestWertAjax">{if isset($oVerpackungEdit->fMindestbestellwert)}{getCurrencyConversionSmarty fPreisBrutto=$oVerpackungEdit->fMindestbestellwert}{/if}</span>
                    </td>
                </tr>
                <tr>
                    <td><label for="fKostenfrei">{__('zusatzverpackungExemptFromCharge')} ({__('gross')})</label></td>
                    <td>
                        <input class="form-control" name="fKostenfrei" id="fKostenfrei" type="text" value="{if isset($oVerpackungEdit->fKostenfrei)}{$oVerpackungEdit->fKostenfrei}{/if}" onKeyUp="setzePreisAjax(false, 'KostenfreiAjax', this)" required/>
                        <span id="KostenfreiAjax">{if isset($oVerpackungEdit->fKostenfrei)}{getCurrencyConversionSmarty fPreisBrutto=$oVerpackungEdit->fKostenfrei}{/if}</span>
                    </td>
                </tr>
                {foreach $sprachen as $language}
                    {assign var=cISO value=$language->getIso()}
                    <tr>
                        <td><label for="cBeschreibung_{$cISO}">{__('description')} ({$language->getLocalizedName()})</label></td>
                        <td>
                            <textarea id="cBeschreibung_{$cISO}" name="cBeschreibung_{$cISO}" rows="5" cols="35" class="form-control combo">{if isset($oVerpackungEdit->oSprach_arr[$cISO]->cBeschreibung)}{$oVerpackungEdit->oSprach_arr[$cISO]->cBeschreibung}{/if}</textarea>
                        </td>
                    </tr>
                {/foreach}
                <tr>
                    <td><label for="kSteuerklasse">{__('zusatzverpackungTaxClass')}</label></td>
                    <td>
                        <select id="kSteuerklasse" name="kSteuerklasse" class="custom-select combo">
                            <option value="-1">{__('zusatzverpackungAutoTax')}</option>
                            {foreach $oSteuerklasse_arr as $oSteuerklasse}
                                <option value="{$oSteuerklasse->kSteuerklasse}" {if isset($oVerpackungEdit) && (int)$oSteuerklasse->kSteuerklasse === (int)$oVerpackungEdit->kSteuerklasse} selected{/if}>{$oSteuerklasse->cName}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="kKundengruppe">{__('customerGroup')}</label></td>
                    <td>
                        <select id="kKundengruppe" name="kKundengruppe[]" multiple="multiple" class="custom-select combo" required>
                            <option value="-1"{if isset($oVerpackungEdit) && $oVerpackungEdit->cKundengruppe == '-1'} selected{/if}>{__('all')}</option>
                            {foreach $oKundengruppe_arr as $oKundengruppe}
                                {if (isset($oVerpackungEdit->cKundengruppe) && $oVerpackungEdit->cKundengruppe == '-1') || !isset($oVerpackungEdit) || !$oVerpackungEdit}
                                    <option value="{$oKundengruppe->kKundengruppe}">{$oKundengruppe->cName}</option>
                                {else}
                                    <option value="{$oKundengruppe->kKundengruppe}"
                                        {foreach $oVerpackungEdit->kKundengruppe_arr as $kKundengruppe}
                                            {if isset($oKundengruppe->kKundengruppe) && $oKundengruppe->kKundengruppe == $kKundengruppe} selected{/if}
                                        {/foreach}>
                                        {$oKundengruppe->cName}
                                    </option>
                                {/if}
                            {/foreach}
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="nAktiv">{__('active')}</label></td>
                    <td>
                        <select id="nAktiv" name="nAktiv" class="custom-select combo">
                            <option value="1"{if isset($oVerpackungEdit) && (int)$oVerpackungEdit->nAktiv === 1} selected{/if}>{__('yes')}</option>
                            <option value="0"{if isset($oVerpackungEdit) && (int)$oVerpackungEdit->nAktiv === 0} selected{/if}>{__('no')}</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div class="card-footer save-wrapper">
            <button class="btn btn-primary" name="speichern" type="submit">
                <i class="fa fa-save"></i> {__('save')}
            </button>
        </div>
    </div>
</form>
