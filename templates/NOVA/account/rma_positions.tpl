{block name='account-rma-summary-items'}
    {if isset($rmaPositions)}
        {cardheader}
        {block name='rma-positions-header'}
            <div class="d-flex justify-content-between">
                <span class="h3 mb-0">
                    {lang key='rma_products' section='rma'}
                </span>
                <span class="badge badge-secondary badge-pill">{$rmaPositions|count}</span>
            </div>
        {/block}
        {/cardheader}

        <ul class="list-group mb-3 list-compressed" id="rma-sticky-pos-list">
            {foreach $rmaPositions as $pos}
                <li class="list-group-item justify-content-between lh-condensed">
                    <div class="pr-2">
                        <h6 class="my-0 line-clamp rmaPosOverviewTitle">{$pos->getName()}</h6>
                        <small class="text-muted rmaPosOverviewContent">
                            {$pos->getQuantity()}{$pos->getUnit()} x {$pos->getUnitPriceLocalized()}
                        </small>
                    </div>
                    <span class="text-muted text-nowrap rmaPosOverviewTotal">{$pos->getPriceLocalized()}</span>
                </li>
            {/foreach}
            <li class="list-group-item justify-content-start bg-light{if count($rmaPositions) < 6} d-none{/if}">
                <a href="#" class="w-100 d-flex justify-content-between text-decoration-none listExpander"
                   data-showall="{lang key='showAll'}" data-shownone="{lang key='showNone'}">
                    <span class="fa fa-chevron-down toggle"></span>
                </a>
            </li>
            <li class="list-group-item justify-content-between bg-light">
                <span>Total ({JTL\Session\Frontend::getCurrency()->getName()})</span>
                <strong>{$rmaTotal}</strong>
            </li>
        </ul>
    {/if}
{/block}
