{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $WarenkorbArtikelPositionenanzahl >= 1}
    {*{if $dXs === 'none'}
        {navitemdropdown tag="div" class="basket-dropdown" right=true text="
            <i class='fas fa-shopping-cart'></i>
            {if $WarenkorbArtikelPositionenanzahl >= 1}
                <sup>{badge pill=true variant='primary'}{$WarenkorbArtikelPositionenanzahl}{/badge}</sup>
            {/if}
                <span class='shopping-cart-label d-none d-md-inline-flex'> {$WarensummeLocalized[$NettoPreise]}</span>
        "}
        {/navitemdropdown}
    {else}*}
        {navitem tag="div" class="btn-link" aria=["expanded"=>false] data=["toggle"=>"collapse", "target"=>"#nav-cart-collapse"]}
            <i class='fas fa-shopping-cart'></i>
            {if $WarenkorbArtikelPositionenanzahl >= 1}
                <sup>{badge pill=true variant='primary'}{$WarenkorbArtikelPositionenanzahl}{/badge}</sup>
            {/if}
        {/navitem}
    {*{/if}*}
{/if}
