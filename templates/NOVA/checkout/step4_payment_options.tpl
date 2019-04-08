{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 * This file is for compatibility in 3-step checkout (content will be replaced by payment plugins if this file is loaded)
 * @deprecated since 4.06
 *}
{block name='checkout-step4-payment-options'}
    {row class="mb-3 form-group"}
        {block name='checkout-step4-payment-options-include-inc-payment-methods'}
            {include file='checkout/inc_payment_methods.tpl'}
        {/block}
    {/row}
    {block name='checkout-step4-payment-options-include-inc-payment-trustedshops'}
        {include file='checkout/inc_payment_trustedshops.tpl'}
    {/block}
{/block}
