{if isset($smarty.session.Kunde->kKunde) && isset($oBox->CWunschlistePos_arr) && $oBox->CWunschlistePos_arr|@count > 0}
    {assign var=wishlistItems value=$oBox->CWunschlistePos_arr}
{elseif isset($smarty.session.Kunde->kKunde) && isset($Boxen.Wunschliste->CWunschlistePos_arr) && $Boxen.Wunschliste->CWunschlistePos_arr|@count > 0}
    {assign var=wishlistItems value=$Boxen.Wunschliste->CWunschlistePos_arr}
{/if}

{if isset($wishlistItems)}
    <section class="panel panel-default box box-wishlist" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title"><i class="fa fa-heart"></i> {lang key="wishlist" section="global"}</h5>
        </div>
        <table class="table vtable">
            {if isset($Boxen.Wunschliste->nAnzeigen)}
                {assign var=maxItems value=$Boxen.Wunschliste->nAnzeigen}
            {else}
                {assign var=maxItems value=$oBox->nAnzeigen}
            {/if}
            {foreach name=wunschzettel from=$wishlistItems item=oWunschlistePos}
                <tr class="item" data-id="{$oArtikel->kArtikel}">
                    {if (isset($Boxen.Wunschliste->nBilderAnzeigen) && $Boxen.Wunschliste->nBilderAnzeigen === 'Y') || (isset($oBox) && $oBox->nBilderAnzeigen === 'Y')}
                        <td>
                            <a href="{$oWunschlistePos->Artikel->cURLFull}"
                               title="{$oWunschlistePos->cArtikelName|escape:'quotes'}">
                                <img alt="" src="{$oWunschlistePos->Artikel->Bilder[0]->cURLMini}" class="img-xs">
                            </a>
                        </td>
                    {/if}
                    <td>
                        <a href="{$oWunschlistePos->Artikel->cURLFull}" title="{$oWunschlistePos->cArtikelName|escape:'quotes'}">
                            {$oWunschlistePos->fAnzahl|replace_delim} &times; {$oWunschlistePos->cArtikelName|truncate:25:"..."}
                        </a>
                    </td>
                    <td class="text-right">
                        <a class="remove pull-right" href="{$oWunschlistePos->cURL}" data-name="Wunschliste.remove"
                           data-toggle="product-actions" data-value='{ldelim}"a":{$oWunschlistePos->kWunschlistePos}{rdelim}'>
                            <span class="fa fa-trash-o"></span>
                        </a>
                    </td>
                </tr>
            {/foreach}
        </table>
        <div class="panel-body">
            <a href="{get_static_route id='jtl.php'}?wl={if isset($Boxen.Wunschliste->CWunschlistePos_arr)}{$Boxen.Wunschliste->CWunschlistePos_arr[0]->kWunschliste}{else}{$oBox->CWunschlistePos_arr[0]->kWunschliste}{/if}" class="btn btn-default btn-block btn-sm">{lang key="goToWishlist" section="global"}</a>
        </div>
    </section>
{elseif  empty($oBox->bSingleBox)}
    <section class="hidden box-wishlist" id="sidebox{$oBox->kBox}"></section>
{/if}