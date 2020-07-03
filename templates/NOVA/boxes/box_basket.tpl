{block name='boxes-box-basket'}
    {card class="box box-basket mb-4" id="sidebox{$oBox->getID()}"}
        <div class="box-body text-center">
            {block name='boxes-box-basket-content'}
                {block name='boxes-box-basket-title'}
                    <div class="productlist-filter-headline align-items-center d-flex">
                        {lang key='yourBasket'}
                    </div>
                {/block}
                {block name='boxes-box-basket-link'}
                    {link href="{get_static_route id='warenkorb.php'}" class="basket"}
                        <span class="d-block">{$Warenkorbtext}</span>
                        <span class="basket_link"><i class="fas fa-shopping-cart"></i> {lang key='gotoBasket'}</span>
                    {/link}
                {/block}
            {/block}
        </div>
    {/card}
{/block}
