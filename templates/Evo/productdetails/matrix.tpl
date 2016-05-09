{* the matrix *}
{if $showMatrix}
    <hr>
    <div class="product-matrix well panel-wrap">
        <div class="panel panel-default">
            <div class="panel-body">
                {if $Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_anzeigeformat === 'L' && $Artikel->nIstVater == 1 && $Artikel->oVariationKombiKinderAssoc_arr|count > 0}
                    {include file="productdetails/matrix_list.tpl"}
                {else}
                    {include file="productdetails/matrix_classic.tpl"}
                {/if}
             </div>
         </div>
     </div>
{/if}