{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-wishlist-dropdown'}
    {block name='snippets-wishlist-dropdown-wischlists'}
        {foreach $wishlists as $wishlist}
            {row class="py-2{if $wishlist@iteration %2} bg-info{/if}"}
                {col md=6}
                    <p>{link href="{get_static_route id='wunschliste.php'}?wl={$wishlist->kWunschliste}"}{$wishlist->cName}{/link}<br />
                        <small>{if $wishlist->nOeffentlich === 1}{lang key='public'}{else}{lang key='private'}{/if}</small>
                    </p>
                {/col}
                {col md=6 class='text-right'}
                    {$wishlist->productCount} {lang key='products'}
                {/col}
            {/row}
        {/foreach}
    {/block}
    {block name='snippets-wishlist-dropdown-new-wl'}
        {row}
            {col}
                {link class='btn btn-primary float-right mt-3' href="{get_static_route id='wunschliste.php'}?newWL=1"}
                    {lang key='addNew' section='wishlist'}
                {/link}
            {/col}
        {/row}
    {/block}
{/block}
