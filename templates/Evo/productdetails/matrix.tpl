{* the matrix *}
{if $showMatrix}
    <hr>
    <div id="product-var-matrix" class="product-matrix panel-wrap">
        {if $Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_anzeigeformat === 'L' && $Artikel->nIstVater == 1 && $Artikel->oVariationKombiKinderAssoc_arr|count > 0}
            {include file="productdetails/matrix_list.tpl"}
        {else}
            {include file="productdetails/matrix_classic.tpl"}
        {/if}
     </div>
{/if}