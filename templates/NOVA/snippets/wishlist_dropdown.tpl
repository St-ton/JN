{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($wishlists)}
    {foreach $wishlists as $wishlist}
        {row class="py-2{if $wishlist@iteration %2} bg-info{/if}"}
            {col md=6}
                <p>{link href="{get_static_route id='wunschliste.php'}?wl={$wishlist->kWunschliste}"}{$wishlist->cName}{/link}<br />
                    <small>{if (int)$wishlist->nOeffentlich === 1}{lang key='public'}{else}{lang key='private'}{/if}</small>
                </p>
            {/col}
            {col md=6 class='text-right'}
                {$wishlist->productCount} {lang key='products'}
            {/col}
        {/row}
    {/foreach}
    {row}
        {col}
            {link class='btn btn-primary float-right mt-3' href="{get_static_route id='wunschliste.php'}?newWL=1"}
                {lang key='addNew' section='wishlist'}
            {/link}
        {/col}
    {/row}
{/if}
