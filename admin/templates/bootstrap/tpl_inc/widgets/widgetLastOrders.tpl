<div class="widget-custom-data">
    <div class="table-responsive" data-draggable-ignore>
        <table id="table_last_orders" class="table table-striped">
            <thead>
                <tr>
                    <th>{__('Order number')}</th>
                    <th>{__('Shipping method')}</th>
                    <th>{__('Payment method')}</th>
                    <th class="text-right">{__('Total')}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $orders as $order}
                <tr id="last_order_row_{$order->kBestellung}"
                    data-toggle="modal"
                    data-target="#order-modal-{$order->kBestellung}">
                    <td>
                        {$order->cBestellNr}
                    </td>
                    <td>{$order->cVersandartName}</td>
                    <td>{$order->cZahlungsartName}</td>
                    <td class="text-right">{$order->WarensummeLocalized[0]}
                        <div id="order-modal-{$order->kBestellung}" class="modal fade" role="dialog">{include file=$cDetail oBestellung=$order}</div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
