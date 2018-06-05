{if isset($smarty.session.Kunde->kKunde) && isset($oBox->CWunschlistePos_arr) && $oBox->CWunschlistePos_arr|@count > 0}
    {assign var=wishlistItems value=$oBox->CWunschlistePos_arr}
{elseif isset($smarty.session.Kunde->kKunde) && isset($Boxen.Wunschliste->CWunschlistePos_arr) && $Boxen.Wunschliste->CWunschlistePos_arr|@count > 0}
    {assign var=wishlistItems value=$Boxen.Wunschliste->CWunschlistePos_arr}
{/if}

{if isset($wishlistItems)}
    <section class="panel panel-default box box-wishlist" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title"><i class="fa fa-heart"></i> {lang key='wishlist'}</div>
        </div>
        <div class="box-body panel-body">
            <ul class="list-unstyled">
                {if isset($Boxen.Wunschliste->nAnzeigen)}
                    {assign var=maxItems value=$Boxen.Wunschliste->nAnzeigen}
                {else}
                    {assign var=maxItems value=$oBox->nAnzeigen}
                {/if}
                {foreach name=wunschzettel from=$wishlistItems item=oWunschlistePos}
                    <li data-id="{$oWunschlistePos->kArtikel}">
                        <a class="remove pull-right" href="{$oWunschlistePos->cURL}" data-name="Wunschliste.remove"
                           data-toggle="product-actions" data-value='{ldelim}"a":{$oWunschlistePos->kWunschlistePos}{rdelim}'>
                            <span class="fa fa-trash-o"></span>
                        </a>
                        <a href="{$oWunschlistePos->Artikel->cURLFull}" title="{$oWunschlistePos->cArtikelName|escape:'quotes'}">
                            {if (isset($Boxen.Wunschliste->nBilderAnzeigen) && $Boxen.Wunschliste->nBilderAnzeigen === 'Y') || (isset($oBox) && $oBox->nBilderAnzeigen === 'Y')}
                                <img alt="" src="{$oWunschlistePos->Artikel->Bilder[0]->cURLMini}" class="img-xs">
                            {/if}
                            {$oWunschlistePos->fAnzahl|replace_delim} &times; {$oWunschlistePos->cArtikelName|truncate:25:"..."}
                        </a>
                    </li>
                {/foreach}
            </ul>
            <hr>
            <a href="{get_static_route id='jtl.php'}?wl={if isset($Boxen.Wunschliste->CWunschlistePos_arr)}{$Boxen.Wunschliste->CWunschlistePos_arr[0]->kWunschliste}{else}{$oBox->CWunschlistePos_arr[0]->kWunschliste}{/if}" class="btn btn-default btn-block btn-sm">
                {lang key='goToWishlist'}
            </a>
        </div>
    </section>
{else}
    <section class="hidden box-wishlist" id="sidebox{$oBox->getID()}"></section>
{/if}