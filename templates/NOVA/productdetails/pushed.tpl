{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-pushed'}
    {if $nSeitenTyp !== $smarty.const.PAGE_ARTIKEL}
        {include
            file='productdetails/pushed_success.tpl'
            Artikel=$zuletztInWarenkorbGelegterArtikel
        }
    {/if}
{/block}
