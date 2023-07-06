{block name='account-rma-summary-items'}
    {if isset($rma)}
        {row class="account-rma-summary-items"}
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th scope="col">Bild</th>
                            <th scope="col">Position</th>
                            <th scope="col">Preis</th>
                            <th scope="col">Gesamt</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $rma->getPositions() as $pos}
                            <tr>
                                <td>
                                    {if !empty($pos->getProduct()->cVorschaubildURL)}
                                        {include file='snippets/image.tpl' item=$pos->getProduct() square=true srcSize='xs'}
                                    {/if}
                                </td>
                                <td>
                                    <span class="line-clamp">{$pos->getName()}</span>
                                </td>
                                <td>
                                    {$pos->getQuantity()}{$pos->getUnit()} x {$pos->getUnitPriceLocalized()}
                                </td>
                                <td>
                                    {$pos->getPriceLocalized()}
                                </td>
                            </tr>
                        {/foreach}
                        <tr>
                            <td colspan="4" class="text-right font-weight-bold">Gesamt: {$rma->getPriceLocalized()}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {col cols=12}
                <a href="#" id="goBackOneStep">
                    <-- Go back
                </a>
            {/col}
        {/row}
    {/if}
{/block}
