{block name='account-order-return'}
    {block name='account-order-return-script-location'}
        <script>
            if (top.location !== self.location) {
                top.location = self.location.href;
            }
        </script>
    {/block}
    {block name='account-order-return-heading'}
        <h1>{lang key='rma' section='rma'}</h1>
    {/block}
    {block name='account-order-return-order-return-data'}
        {card no-body=true class='order-return'}
            {cardheader}
            {block name='account-order-return-order-heading'}
                {row class='align-items-center-util'}
                    {block name='account-order-return-order-heading-date'}
                        {col cols=12 sm=12 md=4 lg='auto'}
                            <div class="order-return-date">
                                <span class="far fa-calendar mr-2"></span>{$Bestellung->dErstelldatum_de}
                            </div>
                        {/col}
                    {/block}
                    {col cols=12 sm=6 md=4 lg='auto'}
                        {lang key='yourOrderId' section='checkout'}: {$Bestellung->cBestellNr}
                    {/col}
                    {col cols=12 sm=6 md=4 lg='auto' class='order-return-status text-sm-right'}
                        {lang key='orderStatus' section='login'}: {$Bestellung->Status}
                    {/col}
                {/row}
            {/block}
            {/cardheader}
            {if isset($Kunde) && $Kunde->kKunde > 0}
                {cardbody}
                {block name='account-order-return-order-body'}
                    {row}
                        {col cols=12 lg=12}
                            {block name='account-order-return-order-subheading-basket'}
                                <span class="subheadline">
                                    {lang key='rma_helptext' section='rma'}
                                </span>
                            {/block}
                            {block name='account-order-return-include-order-item'}
                                {form method="post" action="{get_static_route params=['returnOrder' => $Bestellung->kBestellung]}" class="jtl-validate" slide=true}
                                    {include file='account/order_item_return.tpl' tplscope='confirmation'}
                                    {input type="hidden" name="returnOrder" value="{$Bestellung->kBestellung}"}
                                    {row class='btn-row'}
                                        {col cols=12 class="text-right"}
                                            {button type="submit" value="1" variant="primary"}
                                                {lang key='rma_ruecksenden' section='rma'}
                                            {/button}
                                        {/col}
                                    {/row}
                                {/form}
                            {/block}
                        {/col}
                    {/row}
                    {/block}
                {/cardbody}
            {else}
                {cardbody class="order-return-request-plz"}
                    {block name='account-order-return-request-plz'}
                        {row}
                            {col cols=12 md=12}
                            {lang key='rma_login' section='rma'}	
                            {/col}
                        {/row}
                    {/block}
                {/cardbody}
            {/if}
        {/card}
    {/block}
{/block}