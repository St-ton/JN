{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-wishlist-dropdown'}
    {block name='snippets-wishlist-dropdown-wischlists'}
        <div class="table-responsive max-h-sm lg-max-h">
            <table class="table table-vertical-middle table-striped">
                <tbody>
                    {foreach $wishlists as $wishlist}
                        <tr>
                            <td>
                                {link href="{get_static_route id='wunschliste.php'}?wl={$wishlist->kWunschliste}"}{$wishlist->cName}{/link}<br />
                                <span data-switch-label-state="public-{$wishlist->kWunschliste}" class="small {if $wishlist->nOeffentlich != 1}d-none{/if}">
                                    {lang key='public'}
                                </span>
                                <span data-switch-label-state="private-{$wishlist->kWunschliste}" class="small {if $wishlist->nOeffentlich == 1}d-none{/if}">
                                    {lang key='private'}
                                </span>
                            </td>
                            <td class="text-right text-nowrap">
                                {$wishlist->productCount} {lang key='products'}
                            </td>
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
                    {link class='btn btn-primary btn-block' href="{get_static_route id='wunschliste.php'}?newWL=1"}
                        {lang key='addNew' section='wishlist'}
                    {/link}
                {/col}
            {/row}
        </div>
    {/block}
{/block}
