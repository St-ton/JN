{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{navitem
    id="shop-nav-compare"
    tag="div"
    router-tag="a"
    router-class="link_to_comparelist{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"
    href="{get_static_route id='vergleichsliste.php'}"
    target="{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'}_blank{/if}"
    title="{lang key='compare'}"
    class="{if $nSeitenTyp === $smarty.const.PAGE_VERGLEICHSLISTE && !empty($smarty.session.Vergleichsliste->oArtikel_arr)} active{/if}"
}
    {if !empty($smarty.session.Vergleichsliste->oArtikel_arr)}
        <span class="fas fa-tasks"></span>
        <sup>
            {badge pill=true variant="primary"}{$smarty.session.Vergleichsliste->oArtikel_arr|count}{/badge}
        </sup>
    {/if}
{/navitem}
