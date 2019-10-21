{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-wishlist-dropdown'}
    {block name='snippets-wishlist-dropdown-wishlists'}
        <div class="table-responsive max-h-sm lg-max-h">
            <table class="table table-vertical-middle table-striped">
                <tbody>
                    {foreach $wishlists as $wishlist}
                        <tr>
                            <td>
                                {block name='snippets-wishlist-dropdown-link'}
                                    {link href="{get_static_route id='wunschliste.php'}?wl={$wishlist->kWunschliste}"}{$wishlist->cName}{/link}<br />
                                {/block}
                                {block name='snippets-wishlist-dropdown-punlic'}
                                    <span data-switch-label-state="public-{$wishlist->kWunschliste}" class="small {if $wishlist->nOeffentlich != 1}d-none{/if}">
                                        {lang key='public'}
                                    </span>
                                {/block}
                                {block name='snippets-wishlist-dropdown-private'}
                                    <span data-switch-label-state="private-{$wishlist->kWunschliste}" class="small {if $wishlist->nOeffentlich == 1}d-none{/if}">
                                        {lang key='private'}
                                    </span>
                                {/block}
                            </td>
                            {block name='snippets-wishlist-dropdown-count'}
                                <td class="text-right text-nowrap">
                                    {$wishlist->productCount} {lang key='products'}
                                </td>
                            {/block}
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {/block}
    {block name='snippets-wishlist-dropdown-new-wl'}
        <div class="dropdown-body">
            {row}
                {col class='col-lg-auto ml-auto'}
                    {block name='snippets-wishlist-dropdown-new-wl-link'}
                        {link class='btn btn-primary btn-block' href="{get_static_route id='wunschliste.php'}?newWL=1"}
                            {lang key='addNew' section='wishlist'}
                        {/link}
                    {/block}
                {/col}
            {/row}
        </div>
    {/block}
{/block}
