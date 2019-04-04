{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-modules-billpay-raten'}
    <div id="billpay_wrapper">
        <div id="billpay_rate_selection">
            {block name='checkout-modules-billpay-raten-rate-selection'}
                {input type="hidden" name="billpay_rate_total" value=$oRate->fBase}
                <div class="input-group">
                    {select name="billpay_rate"}
                    {foreach $nRate_arr as $nRate}
                        <option value="{$nRate}" {if $oRate->nRate == $nRate}selected="selected"{/if}>{$nRate} Raten</option>
                    {/foreach}
                    {/select}
                    <span class="input-group-btn">
                    <button class="btn btn-primary" type="button" id="billpay_calc_rate" onclick="$.billpay.push();">Raten berechnen</button>
                 </span>
                </div>
                <br/>
                <div id="billpay_links" class="btn-group">
                    {link class="btn btn-secondary popup" href=$cBillpayTermsURL target="_blank"}AGB Ratenkauf{/link}
                    {link class="btn btn-secondary popup" href=$cBillpayPrivacyURL target="_blank"}Datenschutzbestimmungen{/link}
                    {link class="btn btn-secondary popup" href=$cBillpayTermsPaymentURL target="_blank"}Zahlungsbedingungen{/link}
                </div>
            {/block}
        </div>

        <div id="billpay_rate_info">
            {block name='checkout-modules-billpay-raten-subheading'}
                <div class="h2">Ihre Teilzahlung in {$oRate->nRate} Monatsraten</div>
            {/block}
            {block name='checkout-modules-billpay-raten-rates'}
                <table class="rates table table-striped">
                    <tbody>
                    <tr>
                        <td>Warenkorbwert</td>
                        <td class="text-right">=</td>
                        <td class="text-right">{$oRate->fBaseFmt}</td>
                    </tr>

                    <tr>
                        <td>Zinsaufschlag</td>
                        <td class="text-right">+</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>({$oRate->fBaseFmt} x {$oRate->fInterest} x {$oRate->nRate}) / 100</td>
                        <td class="text-right">=</td>
                        <td class="text-right">{$oRate->fSurchargeFmt}</td>
                    </tr>

                    <tr>
                        <td>Bearbeitungsgebühr</td>
                        <td class="text-right">+</td>
                        <td class="text-right">{$oRate->fFeeFmt}</td>
                    </tr>

                    <tr>
                        <td>weitere Gebühren (z.B. Versandgebühr)</td>
                        <td class="text-right">+</td>
                        <td class="text-right">{$oRate->fOtherSurchargeFmt}</td>
                    </tr>

                    <tr class="special">
                        <td>Gesamtsumme</td>
                        <td class="text-right">=</td>
                        <td class="text-right">{$oRate->fTotalFmt}</td>
                    </tr>

                    <tr>
                        <td>Geteilt durch die Anzahl der Raten</td>
                        <td class="text-right"></td>
                        <td class="text-right">{$oRate->nRate} Raten</td>
                    </tr>

                    <tr>
                        <td>Die erste Rate inkl. Gebühren beträgt</td>
                        <td></td>
                        <td class="text-right">{$oRate->oDues_arr[0]->fAmountFmt}</td>
                    </tr>

                    <tr>
                        <td>Jede folgende Rate beträgt</td>
                        <td></td>
                        <td class="text-right">{$oRate->oDues_arr[1]->fAmountFmt}</td>
                    </tr>

                    <tr class="special">
                        <td>Effektiver Jahreszins</td>
                        <td class="text-right">=</td>
                        <td class="text-right">{$oRate->fAnual|replace:'.':','} %</td>
                    </tr>
                    </tbody>
                </table>
            {/block}
        </div>
    </div>
{/block}
