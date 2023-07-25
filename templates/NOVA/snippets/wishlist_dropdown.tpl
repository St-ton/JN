{block name='snippets-wishlist-dropdown'}
    {get_static_route id='wunschliste.php' assign='wlslug'}
    {if $wishlists->isNotEmpty()}
        {block name='snippets-wishlist-dropdown-wishlists'}
            <div class="wishlist-dropdown-items table-responsive max-h-sm lg-max-h">
                <table class="table table-vertical-middle">
                    <tbody>
                        {foreach $wishlists as $wishlist}
                            <tr class="clickable-row cursor-pointer" data-href="{$wlslug}?wl={$wishlist->getID()}">
                                <td>
                                    {block name='snippets-wishlist-dropdown-link'}
                                        {$wishlist->getName()}<br />
                                    {/block}
                                    {block name='snippets-wishlist-dropdown-punlic'}
                                        <span data-switch-label-state="public-{$wishlist->getID()}" class="small {if $wishlist->isPublic() !== true}d-none{/if}">
                                            {lang key='public'}
                                        </span>
                                    {/block}
                                    {block name='snippets-wishlist-dropdown-private'}
                                        <span data-switch-label-state="private-{$wishlist->getID()}" class="small {if $wishlist->isPublic()}d-none{/if}">
                                            {lang key='private'}
                                        </span>
                                    {/block}
                                </td>
                                {block name='snippets-wishlist-dropdown-count'}
                                    <td class="text-right-util text-nowrap-util">
                                        {$wishlist->getProductCount()} {lang key='products'}
                                    </td>
                                {/block}
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/block}
    {/if}
    {block name='snippets-wishlist-dropdown-new-wl'}
        <div class="wishlist-dropdown-footer dropdown-body">
            {block name='snippets-wishlist-dropdown-new-wl-link'}
                {button variant="primary" type="link" block=true size="sm" href="{$wlslug}?newWL=1"}
                    {lang key='addNew' section='wishlist'}
                {/button}
            {/block}
        </div>
    {/block}
{/block}
