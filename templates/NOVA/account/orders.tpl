{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-orders'}
    {block name='heading'}
        <div class="h1">{lang key='yourOrders' section='login'}</div>
    {/block}
    {block name='account-orders-content'}
        {if $Bestellungen|@count > 0}
            {assign var=bDownloads value=false}
            {foreach $Bestellungen as $order}
                {if isset($order->bDownload) && $order->bDownload > 0}
                    {assign var=bDownloads value=true}
                {/if}
            {/foreach}
            {block name='account-orders-orders'}
                {foreach $orderPagination->getPageItems() as $order}
                        {card no-body=true class='mb-3'}
                            {cardheader class="bg-info"}
                                {row}
                                    {col md=4}
                                        <strong><i class="far fa-calendar-alt"></i> {$order->dBestelldatum}</strong>
                                    {/col}
                                    {col md=3}
                                        {$order->cBestellwertLocalized}
                                    {/col}
                                    {col md=4}
                                        <span class="{if $order->cStatus == BESTELLUNG_STATUS_IN_BEARBEITUNG}text-success{/if}">
                                            {lang key='orderStatus' section='login'}: {$order->Status}
                                        </span>
                                    {/col}
                                    {col md=1}
                                        {link href="{get_static_route id='jtl.php'}?bestellung={$order->kBestellung}"
                                            title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}"
                                            data=["toggle" => "tooltip", "placement" => "bottom"]}
                                            <i class="fa fa-eye"></i>
                                        {/link}
                                    {/col}
                                {/row}
                            {/cardheader}
                        {/card}
                {/foreach}
            {/block}
            {block name='account-orders-include-pagination'}
                {include file='snippets/pagination.tpl' oPagination=$orderPagination cThisUrl='jtl.php' cParam_arr=['bestellungen' => 1] parts=['pagi', 'label']}
            {/block}
        {else}
            {block name='account-orders-alert'}
                {alert variant="info"}{lang key='noEntriesAvailable'}{/alert}
            {/block}
        {/if}
        {block name='account-orders-actions'}
            {link class="btn btn-primary" href="{get_static_route id='jtl.php'}"}
                {lang key='back'}
            {/link}
        {/block}
    {/block}
{/block}
