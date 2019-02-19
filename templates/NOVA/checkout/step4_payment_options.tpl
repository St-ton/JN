{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 * This file is for compatibility in 3-step checkout (content will be replaced by payment plugins if this file is loaded)
 * @deprecated since 4.06
 *}
{row class="mb-3 form-group"}
    {include file='checkout/inc_payment_methods.tpl'}
{/row}
{include file='checkout/inc_payment_trustedshops.tpl'}
