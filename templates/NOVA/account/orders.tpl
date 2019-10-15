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
                            {cardheader}
                                {link href="{get_static_route id='jtl.php'}?bestellung={$order->kBestellung}"
                                    title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}"
                                    data=["toggle" => "tooltip", "placement" => "bottom"]
                                    class="no-deco"}
                                    {row}
                                        {col cols=6 md=3 order=1}
                                            <strong><i class="far fa-calendar-alt"></i> {$order->dBestelldatum}</strong>
                                        {/col}
                                        {col cols=6 md=2 order=4 order-md=2}
                                            {$order->cBestellwertLocalized}
                                        {/col}
                                        {col cols=4 md=2 order=2 order-md=3}
                                            {$order->cBestellNr}
                                        {/col}
                                        {col cols=6 md=4 order=5 order-md=4}
                                            {lang key='orderStatus' section='login'}: {$order->Status}
                                        {/col}
                                        {col cols=2 md=1 order=3 order-md=5 class="text-right"}
                                            <i class="fa fa-eye"></i>
                                        {/col}
                                    {/row}
                                {/link}
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
            {row}
                {col md=3 cols=12}
                    {link class="btn btn-secondary btn-block" href="{get_static_route id='jtl.php'}"}
                        {lang key='back'}
                    {/link}
                {/col}
            {/row}
        {/block}
    {/block}
{/block}
