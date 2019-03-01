<fieldset id="payment_debit">
   <legend>{lang key='paymentOptionDebitDesc' section='shipping payment'}</legend>
    <div class="row">   
        {if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_kontoinhaber_abfrage !== 'N'}
            <div class="col-xs-12 col-md-6">
                <div class="form-group float-label-control owner {if isset($fehlendeAngaben.inhaber) && $fehlendeAngaben.inhaber > 0} has-error{/if}">
                    <label class="control-label" for="inp_owner">{lang key='owner' section='shipping payment'}
                        {if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_kontoinhaber_abfrage !== 'Y'}
                            <span class="optional"> - {lang key='optional'}</span>
                        {/if}
                    </label>
                    <input class="form-control" id="inp_owner" type="text" name="inhaber" maxlength="32" size="32" value="{if isset($ZahlungsInfo->cInhaber) && $ZahlungsInfo->cInhaber|count_characters > 0}{$ZahlungsInfo->cInhaber}{elseif isset($oKundenKontodaten->cInhaber)}{$oKundenKontodaten->cInhaber}{/if}"{if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_kontoinhaber_abfrage === 'Y'} required{/if}>
                    {if isset($fehlendeAngaben.inhaber) && $fehlendeAngaben.inhaber > 0}<div class="alert alert-danger">{lang key='fillOut' section='global'}</div>{/if}
                </div>
            </div>
        {/if}
        {if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_kreditinstitut_abfrage !== 'N'}
            <div class="col-xs-12 col-md-6">
                <div class="form-group float-label-control bankname{if isset($fehlendeAngaben.bankname) && $fehlendeAngaben.bankname > 0} has-error{/if}">
                    <label class="control-label" for="inp_bankname">{lang key='bankname' section='shipping payment'}
                        {if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_kreditinstitut_abfrage !== 'Y'}
                            <span class="optional"> - {lang key='optional'}</span>
                        {/if}
                    </label>
                    <input class="form-control" id="inp_bankname" type="text" name="bankname" size="20" maxlength="90" value="{if isset($ZahlungsInfo->cBankName) && $ZahlungsInfo->cBankName|count_characters > 0}{$ZahlungsInfo->cBankName}{elseif isset($oKundenKontodaten->cBankName)}{$oKundenKontodaten->cBankName}{/if}"{if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_kreditinstitut_abfrage === 'Y'} required{/if}>
                   {if isset($fehlendeAngaben.bankname) && $fehlendeAngaben.bankname > 0}<div class="alert alert-danger">{lang key='fillOut' section='global'}</div>{/if}
                </div>
            </div>
        {/if}
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="form-group float-label-control iban{if isset($fehlendeAngaben.iban) && $fehlendeAngaben.iban > 0} has-error{/if}">
                <label class="control-label" for="inp_iban">{lang key='iban' section='shipping payment'}</label>
                <input class="form-control" id="inp_iban" type="text" name="iban" maxlength="32" value="{if isset($ZahlungsInfo->cIBAN) && $ZahlungsInfo->cIBAN|count_characters > 0}{$ZahlungsInfo->cIBAN}{elseif isset($oKundenKontodaten->cIBAN)}{$oKundenKontodaten->cIBAN}{/if}" size="20" required>
                {if isset($fehlendeAngaben.iban)}
                    <div class="alert alert-danger">
                        {if $fehlendeAngaben.iban === 1}{lang key='fillOut' section='global'}{/if}
                        {if $fehlendeAngaben.iban === 2}{lang key='wrongIban' section='checkout'}{/if}
                    </div>
                {/if}
            </div>
        </div>
        {if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_bic_abfrage !== 'N'}
            <div class="col-xs-12 col-md-6">
                <div class="form-group float-label-control bic{if isset($fehlendeAngaben.bic) && $fehlendeAngaben.bic > 0} has-error{/if}">
                    <label class="control-label" for="inp_bic">{lang key='bic' section='shipping payment'}
                        {if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_bic_abfrage !== 'Y'}
                            <span class="optional"> - {lang key='optional'}</span>
                        {/if}
                    </label>
                    <input class="form-control" id="inp_bic" type="text" name="bic" maxlength="32" size="20" value="{if isset($ZahlungsInfo->cBIC) && $ZahlungsInfo->cBIC|count_characters > 0}{$ZahlungsInfo->cBIC}{elseif isset($oKundenKontodaten->cBIC)}{$oKundenKontodaten->cBIC}{/if}"{if $Einstellungen.zahlungsarten.zahlungsart_lastschrift_bic_abfrage === 'Y'} required{/if}>
                    {if isset($fehlendeAngaben.bic)}
                        <div class="alert alert-danger">
                            {if $fehlendeAngaben.bic === 1}{lang key='fillOut' section='global'}{/if}
                            {if $fehlendeAngaben.bic === 2}{lang key='wrongBIC' section='checkout'}{/if}
                        </div>
                    {/if}
                </div>
            </div>
        {/if}
    </div>
</fieldset>