{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($smarty.session.Vergleichsliste->oArtikel_arr) && $smarty.session.Vergleichsliste->oArtikel_arr|count > 1}
    {navitem
        id="shop-nav-compare"
        tag="div"
        router-tag="a"
        router-class="link_to_comparelist{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"
        href="{get_static_route id='vergleichsliste.php'}"
        target="{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'}_blank{/if}"
        title="{lang key='compare'}"
        class="d-none d-md-flex mr-2{if $nSeitenTyp === $smarty.const.PAGE_VERGLEICHSLISTE} active{/if}"
    }
        <span class="fas fa-tasks"></span>
        <sup>
            {badge pill=true variant="primary"}{$smarty.session.Vergleichsliste->oArtikel_arr|count}{/badge}
        </sup>
    {/navitem}
{else}
    {navitem
        id="shop-nav-compare"
        tag="div"
        router-tag="a"
        router-class="link_to_comparelist"
        target="{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'}_blank{/if}"
        title="{lang key='compare'}"
        class="{if $nSeitenTyp === $smarty.const.PAGE_VERGLEICHSLISTE && !empty($smarty.session.Vergleichsliste->oArtikel_arr)} active{/if}{if empty($smarty.session.Vergleichsliste->oArtikel_arr)} d-none{/if}"
    }
        {if !empty($smarty.session.Vergleichsliste->oArtikel_arr)}
            <span class="fas fa-tasks"></span>
            <sup>
                {badge pill=true variant="primary"}{$smarty.session.Vergleichsliste->oArtikel_arr|count}{/badge}
            </sup>
        {/if}
    {/navitem}
{/if}