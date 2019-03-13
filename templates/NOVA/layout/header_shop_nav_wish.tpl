{if !empty($wishlists)}
    <div class="wishlist-icon-dropdown">
        {navitem tag="div"
        aria=['expanded' => 'false']
        data=['toggle' => 'collapse', 'target' => '#nav-wishlist-collapse']
        id='shop-nav-wish'
        class="d-none d-md-flex{if $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE} active{/if}"
        }
            <i class='fas fa-heart'></i>
            <sup>
                {badge pill=true
                variant="primary"
                class="{if empty($smarty.session.Wunschliste->CWunschlistePos_arr)} d-none{/if}"
                id="badge-wl-count"
                }
                {$smarty.session.Wunschliste->CWunschlistePos_arr|count}
                {/badge}
            </sup>
        {/navitem}
        {collapse id="nav-wishlist-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-md-2"}
            <div id="wishlist-dropdown-container" class="p-3">
                {include file='snippets/wishlist_dropdown.tpl'}
            </div>
        {/collapse}
    </div>
{/if}