{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-modules-billpay-zusatzschritt'}
    {block name='checkout-modules-billpay-zusatzschritt-script-billpay'}
        <script type="application/javascript">
            {literal}
            var bpyReq = {},
                appPath = '//widgetcdn.billpay.de/checkout/1.x.x/';
            (function(win, doc, appPath, objectName) {
                bpyReq = {
                    "deps": ['main'],
                    "baseUrl": appPath,
                    "skipDataMain": true,
                    "callback": function() {}
                };
                win['BillPayCheckout'] = objectName;
                win[objectName] = win[objectName] || function() {
                    (win[objectName].queue = win[objectName].queue || []).push(arguments)
                };
                var requireJs = doc.createElement('script');
                requireJs.src = appPath + 'require.js';
                doc.getElementsByTagName('head')[0].appendChild(requireJs);
            })(window, document, appPath, 'billpayCheckout');
            {/literal}
        </script>
    {/block}

    {block name='checkout-modules-billpay-zusatzschritt-alert'}
        {if $billpay_message}
            {alert variant="danger" class="box_{$billpay_message->cType}"}{$billpay_message->cCustomerMessage}{/alert}
        {/if}

        {if isset($cMissing_arr) && $cMissing_arr|@count > 0}
            {alert variant="danger"}
                <p>{lang key='fillOut'}</p>
            {/alert}
        {/if}
    {/block}

    {block name='checkout-modules-billpay-zusatzschritt-paypal-container'}
        <div id="paypal_container" bpy-pm="{$widgetType}">
            <noscript>Bitte aktivieren Sie Javascript</noscript>
        </div>

        <br />
    {/block}

    {if $widgetOptionsJSON}
        {block name='checkout-modules-billpay-zusatzschritt-script-billpay-checkout'}
            <script type="text/javascript">
                billpayCheckout('options', {$widgetOptionsJSON});
                billpayCheckout('run', {ldelim} "container": "#paypal_container" {rdelim});
            </script>
        {/block}
    {/if}
{/block}
