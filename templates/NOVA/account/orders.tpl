{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-orders'}
    <h1>{block name='account-orders-title'}{lang key='yourOrders' section='login'}{/block}</h1>

    {if $Bestellungen|@count > 0}
        {block name='account-orders-body'}
            {assign var=bDownloads value=false}
            {foreach $Bestellungen as $order}
                {if isset($order->bDownload) && $order->bDownload > 0}
                    {assign var=bDownloads value=true}
                {/if}
            {/foreach}

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{lang key='orderNo' section='login'}</th>
                    <th>{lang key='value' section='login'}</th>
                    <th>{lang key='orderDate' section='login'}</th>
                    <th class="d-none d-sm-table-cell">{lang key='orderStatus' section='login'}</th>
                    {if $bDownloads}
                        <th class="d-none d-sm-table-cell">{lang key='downloads'}</th>
                    {/if}
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody class="small">
                {foreach $orderPagination->getPageItems() as $order}
                    <tr>
                        <td>{$order->cBestellNr}</td>
                        <td>{$order->cBestellwertLocalized}</td>
                        <td>{$order->dBestelldatum}</td>
                        <td class="d-none d-sm-table-cell">{$order->Status}</td>
                        {if $bDownloads}
                            <td class="d-none d-sm-table-cell text-center">
                                {if isset($order->bDownload) && $order->bDownload > 0}
                                    <i class="fas fa-check"></i>
                                {/if}
                            </td>
                        {/if}
                        <td class="text-right">
                            {link href="{get_static_route id='jtl.php'}?bestellung={$order->kBestellung}" title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}"}
                                <span class="fa fa-list-alt"></span> <span class="d-none d-sm-inline-block">{lang key='showOrder' section='login'}</span>
                            {/link}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>

            {include file='snippets/pagination.tpl' oPagination=$orderPagination cThisUrl='jtl.php' cParam_arr=['bestellungen' => 1] parts=['pagi', 'label']}
        {/block}
    {else}
        {alert variant="info"}{lang key='noEntriesAvailable'}{/alert}
    {/if}
{/block}
