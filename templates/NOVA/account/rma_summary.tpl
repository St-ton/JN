{block name='account-rma-summary-items'}
    {if isset($rma)}
        {row class="account-rma-summary-items"}
            <!-- ToDo: Check for positions length and if more than 100, change layout to 'compact' -->
            {foreach $rma->getPositions() as $pos}
                {col cols=6 sm=4 md=4 lg=3 xl=2 class="account-rma-summary-item py-3"}
                    <div class="card h-100">
                        {if !empty($pos->getProduct()->cVorschaubildURL)}
                            {include file='snippets/image.tpl' item=$pos->getProduct() square=false srcSize='sm'
                            class='card-img-top'}
                        {/if}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title flex-grow-1 line-clamp line-clamp-2">
                                {$pos->name}
                            </h5>
                            <p class="card-text">
                                {$pos->getQuantity()}{$pos->getUnit()} x {$pos->getUnitPriceLocalized()}
                            </p>
                            <small class="font-quote">
                                {$pos->getComment()}
                            </small>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">{$pos->getReason()->title}</small>
                        </div>
                    </div>
                {/col}
            {/foreach}
        {/row}
    {/if}
{/block}
