<li class="hidden-xs wish-list-menu">
    <a href="{get_static_route id='wunschliste.php'}" title="{lang key="goToWishlist" sektion="global"}"
       class="link_to_wishlist{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"><span
                class="fa fa-heart"></span><sup
                class="badge"><em>{$smarty.session.Wunschliste->CWunschlistePos_arr|count}</em></sup></a>
</li>
