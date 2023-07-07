{block name='account-rma-summary-items'}
    {if isset($rma)}
        {assign var=positions value=$rma->groupPositions('order')}
        {foreach $positions as $orderNo => $order}
            <div class="card limit-rows">
                <div class="card-header limit-rows-toggle">
                    <a href="#" class="w-100">
                        {lang key='order'} {$orderNo}
                    </a>
                </div>
                <div class="card-body limit-rows-row">
                    <div class="row py-1 font-weight-bold text-nowrap">
                        <div class="col-auto d-none d-md-block">
                            <div class="w-45">
                                Bild
                            </div>
                        </div>
                        <div class="col">Name</div>
                        <div class="col col-2 col-sm-2 col-md-2 text-right d-none d-sm-block">Anzahl</div>
                        <div class="col col-3 col-sm-2 col-md-2 text-right d-none d-sm-block">Preis</div>
                        <div class="col col-4 col-sm-3 col-md-2 text-right">Gesamt</div>
                    </div>
                    {foreach $order as $pos}
                        <div class="row py-1 text-nowrap">
                            <div class="col-auto d-none d-md-block">
                                <div class="mw-45">
                                    {if !empty($pos->getProduct()->cVorschaubildURL)}
                                        {include file='snippets/image.tpl' item=$pos->getProduct() square=false srcSize='xs'}
                                    {/if}
                                </div>
                            </div>
                            <div class="col">
                                <span class="line-clamp">{$pos->getName()}</span>
                                <small class="">
                                    {if $pos->getProperty()->name !== ''
                                    && $pos->getProperty()->value !== ''}
                                        {$pos->getProperty()->name}: {$pos->getProperty()->value}<br>
                                    {/if}
                                </small>
                            </div>
                            <div class="col col-2 col-sm-2 col-md-2 text-right d-none d-sm-block">
                                <span class="text-nowrap">{$pos->getQuantity()}{$pos->getUnit()}</span>
                            </div>
                            <div class="col col-3 col-sm-2 col-md-2 text-right d-none d-sm-block">
                                <span class="text-nowrap">{$pos->getUnitPriceLocalized()}</span>
                            </div>
                            <div class="col col-4 col-sm-3 col-md-2 text-right">
                                <span class="text-nowrap">{$pos->getPriceLocalized()}</span>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        {/foreach}
        <div class="row mt-3">
            <div class="col col-6">
                <div class="text-left">
                    <a href="#" id="goBackOneStep">
                        <-- Go back
                    </a>
                </div>
            </div>
            <div class="col col-6">
                <div class="text-right font-weight-bold">
                    {lang key='total'}: {$rma->getPriceLocalized()}
                </div>
            </div>
        </div>
    {/if}
{/block}

{inline_script}
<script>
    $(document).ready(function () {
        $('.limit-rows-toggle').on('click', function (e) {
            e.preventDefault();
            $(this).closest('.limit-rows').toggleClass('open');
        });
    });
</script>
{/inline_script}
