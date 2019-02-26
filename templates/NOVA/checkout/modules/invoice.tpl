<script type="text/javascript" src="{$currentTemplateDir}js/jtl.billpay.js"></script>

{if $cMissing_arr|@count > 0}
    {alert variant="danger"}Bitte f&uuml;llen Sie alle Pflichtfelder aus.{/alert}
{/if}

{if $billpay_message}
    {alert variant="danger" class="box_{$billpay_message->cType}"}{$billpay_message->cCustomerMessage}{/alert}
{/if}

<fieldset id="billpay_form">
    {input type="hidden" name="za_billpay_jtl" value="1"}
    {input type="hidden" name="billpay_paymenttype" value="1"}

    {if $cAdditionalCustomer_arr|@count > 0}
        <fieldset>
            <legend>Weitere Kundendaten</legend>

            <div id="invoicebusiness_information">
                {radio name="billpay_b2b" id="billpay_b2b_no" value="0" checked=($cData_arr.billpay_b2b == 0)}
                    Privatkunde<span class="optional"> - {lang key='optional'}</span>
                {/radio}

                {radio name="billpay_b2b" id="billpay_b2b_yes" value="1" checked=($cData_arr.billpay_b2b == 1)}
                    Gesch&auml;ftskunde<span class="optional"> - {lang key='optional'}</span>
                {/radio}
            </div>

            <div class="{if $cData_arr.billpay_b2b == 1}closed{/if}" id="invoicebusiness_b2c">
                {if $cAdditionalCustomer_arr.cAnrede}
                    {formgroup
                        class="{if $cMissing_arr.cAnrede > 0} has-error{/if}"
                        label="{lang key='salutation' section='account data'}"
                        label-for="salutation"
                        label-cols=4
                        horizontal=true
                    }
                        {select name="cAnrede" id="salutation" disabled=($cData_arr.billpay_b2b == 1)}
                            <option value="" selected="selected">{lang key='pleaseChoose'}</option>
                            <option value="m" {if $cData_arr.cAnrede === 'm'}selected="selected"{/if}>{lang key='salutationM'}</option>
                            <option value="w" {if $cData_arr.cAnrede === 'w'}selected="selected"{/if}>{lang key='salutationW'}</option>
                        {/select}
                        {if $cMissing_arr.cAnrede > 0}
                            {alert variant="danger"}{lang key='fillOut'}{/alert}
                        {/if}
                    {/formgroup}
                {/if}

                {if $cAdditionalCustomer_arr.dGeburtstag}
                    {formgroup
                        class="{if $cMissing_arr.dGeburtstag > 0} has-error_block{/if}"
                        label="{lang key='birthday' section='account data'}<span class='optional'> - {lang key='optional'}</span>"
                        label-for="dGeburtstag"
                        label-cols=4
                        horizontal=true
                    }
                        {input type="text" name="dGeburtstag" value=$cData_arr.dGeburtstag id="dGeburtstag" class="birthday"}
                        {if $cMissing_arr.dGeburtstag > 0}
                            {alert variant="danger"}
                                {if $cMissing_arr.dGeburtstag == 1}
                                    {lang key='fillOut'}
                                {elseif $cMissing_arr.dGeburtstag == 2}
                                    {lang key='invalidDateformat'}
                                {elseif $cMissing_arr.dGeburtstag == 3}
                                    {lang key='invalidDate'}
                                {/if}
                            {/alert}
                        {/if}
                    {/formgroup}
                {/if}

                {if $cAdditionalCustomer_arr.cTel}
                    {formgroup
                        class="{if $cMissing_arr.cTel > 0} has-error_block{/if}" id="tel"
                        label="{lang key='tel' section='account data'}"
                        label-for="cTel"
                        label-cols=4
                        horizontal=true
                    }
                        {input type="text" name="cTel" value=$cData_arr.cTel id="cTel"}
                        {if $cMissing_arr.cTel > 0}
                            {alert variant="danger"}{lang key='fillOut'}{/alert}
                        {/if}
                    {/formgroup}
                {/if}
            </div>

            <div class="{if $cData_arr.billpay_b2b == 0}closed{/if}" id="invoicebusiness_b2b">

                {row}
                    {col cols=12 md=6}
                        {formgroup
                            label="{lang key='salutation' section='account data'}<span class='optional'> - {lang key='optional'}</span>"
                            label-for="firstName"
                        }
                            {select name="cAnrede" id="salutation"}
                                <option value="" selected="selected">{lang key='pleaseChoose'}</option>
                                <option value="m" {if $cData_arr.cAnrede === 'm'}selected="selected"{/if}>{lang key='salutationM'}</option>
                                <option value="w" {if $cData_arr.cAnrede === 'w'}selected="selected"{/if}>{lang key='salutationW'}</option>
                            {/select}
                        {/formgroup}
                    {/col}
                    {col cols=12 md=6}
                        {formgroup
                            label="Inhaber"
                            label-for="cInhaber"
                        }
                            {input type="text" name="cInhaber" value=$cData_arr.cInhaber id="cInhaber" placeholder="Nachname*" required=true}
                        {/formgroup}
                    {/col}
                {/row}

                {formgroup
                    class="{if $cMissing_arr.cRechtsform > 0} has-error{/if}"
                    label="Rechtsform"
                    label-for="salutation"
                    label-cols=4
                    horizontal=true
                }
                    {select name="cRechtsform" id="salutation"}
                        <optgroup label="{lang key='pleaseChoose'}">
                            {if $cCountryISO === 'DEU'}
                                <option value="ek" {if $cData_arr.cRechtsform === 'ek'}selected="selected"{/if}>EK
                                    (eingetragener Kaufmann)
                                </option>
                                <option value="gbr" {if $cData_arr.cRechtsform === 'gbr'}selected="selected"{/if}>
                                    GbR/BGB (Gesellschaft b&uuml;rgerlichen Rechts)
                                </option>
                                <option value="gmbh_ig" {if $cData_arr.cRechtsform === 'gmbh_ig'}selected="selected"{/if}>
                                    GmbH in Gr&uuml;ndung
                                </option>
                                <option value="gmbh_co_kg" {if $cData_arr.cRechtsform === 'gmbh_co_kg'}selected="selected"{/if}>
                                    GmbH &amp; Co. KG
                                </option>
                                <option value="ltd_co_kg" {if $cData_arr.cRechtsform === 'ltd_co_kg'}selected="selected"{/if}>
                                    Limited &amp; Co. KG
                                </option>
                                <option value="ohg" {if $cData_arr.cRechtsform === 'ohg'}selected="selected"{/if}>OHG
                                    (offene Handelsgesellschaft)
                                </option>
                                <option value="ug" {if $cData_arr.cRechtsform === 'ug'}selected="selected"{/if}>UG
                                    (Unternehmensgesellschaft haftungsbeschr&auml;nkt)
                                </option>
                            {elseif $cCountryISO === 'CHE'}
                                <option value="einzel" {if $cData_arr.cRechtsform === 'einzel'}selected="selected"{/if}>
                                    Einzelfirma
                                </option>
                                <option value="e_ges" {if $cData_arr.cRechtsform === 'e_ges'}selected="selected"{/if}>
                                    Einfache Gesellschaft
                                </option>
                                <option value="inv_kk" {if $cData_arr.cRechtsform === 'inv_kk'}selected="selected"{/if}>
                                    Investmentgesellschaft f&uuml;r kollektive Kapitalanlagen
                                </option>
                                <option value="k_ges" {if $cData_arr.cRechtsform === 'k_ges'}selected="selected"{/if}>
                                    Kollektivgesellschaft
                                </option>
                            {/if}
                        </optgroup>
                        <optgroup label="Weitere Rechtsformen">
                            <option value="eg" {if $cData_arr.cRechtsform === 'eg'}selected="selected"{/if}>eG
                                (eingetragene Genossenschaft)
                            </option>
                            <option value="ag" {if $cData_arr.cRechtsform === 'ag'}selected="selected"{/if}>AG
                            </option>
                            <option value="ev" {if $cData_arr.cRechtsform === 'ev'}selected="selected"{/if}>e.V.
                                (eingetragener Verein)
                            </option>
                            <option value="freelancer" {if $cData_arr.cRechtsform === 'freelancer'}selected="selected"{/if}>
                                Freiberufler/Kleingewerbetreibender/Handelsvertreter
                            </option>
                            <option value="gmbh" {if $cData_arr.cRechtsform === 'gmbh'}selected="selected"{/if}>GmbH
                                (Gesellschaft mit beschr&auml;nkter Haftung)
                            </option>
                            <option value="kg" {if $cData_arr.cRechtsform === 'kg'}selected="selected"{/if}>KG
                                (Kommanditgesellschaft)
                            </option>
                            <option value="kgaa" {if $cData_arr.cRechtsform === 'kgaa'}selected="selected"{/if}>
                                Kommanditgesellschaft auf Aktien
                            </option>
                            <option value="ltd" {if $cData_arr.cRechtsform === 'ltd'}selected="selected"{/if}>
                                Limited
                            </option>
                            <option value="public_inst" {if $cData_arr.cRechtsform === 'public_inst'}selected="selected"{/if}>
                                &ouml;ffentliche Einrichtung
                            </option>
                            <option value="misc_capital" {if $cData_arr.cRechtsform === 'misc_capital'}selected="selected"{/if}>
                                Sonstige Kapitalgesellschaft
                            </option>
                            <option value="misc" {if $cData_arr.cRechtsform === 'misc'}selected="selected"{/if}>
                                Sonstige Personengesellschaft
                            </option>
                            <option value="foundation" {if $cData_arr.cRechtsform === 'foundation'}selected="selected"{/if}>
                                Stiftung
                            </option>
                        </optgroup>
                    {/select}
                    {if $cMissing_arr.cRechtsform > 0}
                        {alert variant="danger"}{lang key='fillOut'}{/alert}
                    {/if}
                {/formgroup}

                {formgroup
                    class="{if $cMissing_arr.cFirma > 0} has-error{/if}"
                    label="Firmenname"
                    label-for="cFirma"
                }
                    {input type="text" name="cFirma" value=$cData_arr.cFirma id="cFirma"}
                    {if $cMissing_arr.cFirma > 0}
                        {alert variant="danger"}{lang key='fillOut'}{/alert}
                    {/if}
                {/formgroup}

                {formgroup
                    class="{if $cMissing_arr.cUSTID > 0} has-error{/if}"
                    label="USt-IdNr.<span class='optional'> - {lang key='optional'}</span>"
                    label-for="cUSTID"
                }
                    {input type="text" name="cUSTID" value=$cData_arr.cUSTID id="cUSTID"}
                    {if $cMissing_arr.cUSTID > 0}
                        {alert variant="danger"}{lang key='fillOut'}{/alert}
                    {/if}
                {/formgroup}

                {formgroup
                    class="{if $cMissing_arr.cHrn > 0} has-error{/if}"
                    label="Handelsregisternummer<span class='optional'> - {lang key='optional'}</span>"
                    label-for="cHrn"
                }
                    {input type="text" name="cHrn" value=$cData_arr.cHrn id="cHrn"}
                    {if $cMissing_arr.cHrn > 0}
                        {alert variant="danger"}{lang key='fillOut'}{/alert}
                    {/if}
                {/formgroup}
            </div>
        </fieldset>
    {/if}

    <div id="billpay_agb_def">
        <fieldset>
            <legend>Weitere Informationen</legend>
            {buttongroup}
                {link class="btn btn-secondary popup" href=$cBillpayTermsURL target="_blank"}
                    {lang key="termsAndConditions" section="shipping payment"}
                {/link}
                {link class="btn btn-secondary popup" href="{$cBillpayTermsURL}#datenschutz" target="_blank"}
                    Datenschutzbestimmungen
                {/link}
            {/buttongroup}
        </fieldset>
    </div>

    <div class="checkbox{if $cMissing_arr.billpay_accepted} has-error{/if}">
        {checkbox name="billpay_accepted" id="billpay_accepted"} Mit der &Uuml;bermittlung der f&uuml;r
            die Abwicklung des Rechnungskaufs und einer Identit&auml;ts
            und Bonit&auml;tspr&uuml;fung <br />erforderlichen Daten an die
            {link href="https://billpay.de/endkunden" target="blank"} Billpay GmbH{/link} bin ich einverstanden. Es gelten die
            {link href="{$cBillpayTermsURL}#datenschutz" class="popup" target="_blank"}Datenschutzbestimmungen{/link}
            von Billpay.
            {if $cMissing_arr.billpay_accepted}
                {alert variant="danger"}Bitte best&auml;tigen{/alert}
            {/if}
        {/checkbox}
    </div>

</fieldset>
