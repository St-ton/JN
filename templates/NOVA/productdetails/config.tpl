{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($Artikel->oKonfig_arr) && $Artikel->oKonfig_arr|@count > 0}
    {row class="product-configuration mt-2 mb-5"}
        {include file='productdetails/config_container.tpl'}
        {include file='productdetails/config_sidebar.tpl'}
    {/row}
{/if}