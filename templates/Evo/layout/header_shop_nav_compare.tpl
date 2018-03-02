<li class="hidden-xs compare-list-menu">
    {if !empty($smarty.session.Vergleichsliste->oArtikel_arr) && $smarty.session.Vergleichsliste->oArtikel_arr|count > 1}
    <a href="{get_static_route id='vergleichsliste.php'}"
       title="{lang key="compare"}"{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'} target="_blank"{/if}
       class="link_to_comparelist{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"><span
                class="fa fa-tasks"></span><sup class="badge">
            <em>{$smarty.session.Vergleichsliste->oArtikel_arr|count}</em></sup></a>
    {elseif !empty($smarty.session.Vergleichsliste->oArtikel_arr)}
        <a class="link_to_comparelist">
            <span class="fa fa-tasks"></span>
            <sup class="badge">
                <em>{$smarty.session.Vergleichsliste->oArtikel_arr|count}</em>
            </sup>
        </a>
    {/if}
</li>
