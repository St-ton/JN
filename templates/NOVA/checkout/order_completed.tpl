{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-order-completed'}
    {block name='checkout-order-completed-include-header'}
        {include file='layout/header.tpl'}
    {/block}
    {block name='checkout-order-completed-content'}
        {block name='checkout-order-completed-heading'}
            {if isset($smarty.session.Zahlungsart->nWaehrendBestellung) && $smarty.session.Zahlungsart->nWaehrendBestellung == 1}
                <h1>{lang key='orderCompletedPre' section='checkout'}</h1>
            {elseif $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'}
                <h1>{lang key='orderCompletedPost' section='checkout'}</h1>
            {/if}
        {/block}
        {block name='checkout-order-completed-include-extension'}
            {include file='snippets/extension.tpl'}
        {/block}
        <div class="order-completed">
            {block name='checkout-order-completed-include-inc-paymentmodules'}
                {include file='checkout/inc_paymentmodules.tpl'}
            {/block}
            {block name='checkout-order-completed-order-completed'}
                {if isset($abschlussseite)}
                    {block name='checkout-order-completed-include-inc-order-completed'}
                        {include file='checkout/inc_order_completed.tpl'}
                    {/block}
                {/if}
            {/block}
        </div>
    {/block}
    {block name='checkout-order-completed-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
