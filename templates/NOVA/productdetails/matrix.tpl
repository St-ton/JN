{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $showMatrix}
    <div class="product-matrix clearfix mt-3">
        {if $Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_anzeigeformat === 'L' && $Artikel->nIstVater == 1 && $Artikel->oVariationKombiKinderAssoc_arr|count > 0}
            {include file='productdetails/matrix_list.tpl'}
        {else}
            {include file='productdetails/matrix_classic.tpl'}
        {/if}
     </div>
{/if}
