{block name='account-orders'}
    {block name='heading'}
        <div class="h1">{lang key='yourOrders' section='login'}</div>
    {/block}
    {block name='account-orders-content'}
        {if $Bestellungen|count > 0}
            {block name='account-orders-orders'}
                {get_static_route id='jtl.php' assign='ordersURL'}
                {foreach $orderPagination->getPageItems() as $order}
                    {row}
                        {col class=""}
                            {card no-body=true class='account-orders-item'}
                                {cardheader}
                                    {row}
                                        {col cols=4 md=4 lg=2 order=1}
                                            <strong><i class="far fa-calendar-alt"></i> {$order->dBestelldatum}</strong>
                                        {/col}
                                        {col cols=4 md=3 lg=2 order=4 order-md=2}
                                            {$order->cBestellwertLocalized}
                                        {/col}
                                        {col cols=4 md=3 lg=2 order=2 order-md=3}
                                            {$order->cBestellNr}
                                        {/col}
                                        {col cols=8 md=8 lg=4 order=5 order-md=5}
                                            {lang key='orderStatus' section='login'}: {$order->Status}
                                        {/col}
                                        {col cols=4 md=2 lg=2 order=3 order-md=4 order-lg=5 class="text-right-util"}
                                            {link href="{$ordersURL}?bestellung={$order->kBestellung}"
                                                title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}"
                                                data=["toggle" => "tooltip", "placement" => "bottom"]
                                            }
                                                <i class="fa fa-eye"></i>
                                            {/link}
                                            {link href="{$ordersURL}?return={$order->kBestellung}"
                                                title="{lang key='rma' section='rma'}: {lang key='rma_artikelwahl' section='rma'}"
                                                data=["toggle" => "tooltip", "placement" => "bottom"]
                                                class="mr-2 me-2"
                                            }
                                                <i class="fa fa-retweet ml-2 ms-2"></i>
                                            {/link}
                                        {/col}
                                    {/row}
                                {/cardheader}
                            {/card}
                        {/col}
                    {/row}
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
                    {link class="btn btn-outline-primary btn-block" href="{get_static_route id='jtl.php'}"}
                        {lang key='back'}
                    {/link}
                {/col}
            {/row}
        {/block}
    {/block}
{/block}
