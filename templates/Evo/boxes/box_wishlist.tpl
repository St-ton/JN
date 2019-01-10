{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->getItems()|count > 0}
    <section class="panel panel-default box box-wishlist" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title"><i class="fa fa-heart"></i> {lang key='wishlist'}</div>
        </div>
        <div class="box-body panel-body">
            <ul class="list-unstyled">
                {assign var=maxItems value=$oBox->getItemCount()}
                {foreach $oBox->getItems() as $oWunschlistePos}
                    <li data-id="{$oWunschlistePos->kArtikel}">
                        {if $oWunschlistePos@iteration > $maxItems}{break}{/if}
                        <a class="remove pull-right" href="{$oWunschlistePos->cURL}" data-name="Wunschliste.remove"
                           data-toggle="product-actions" data-value='{ldelim}"a":{$oWunschlistePos->kWunschlistePos}{rdelim}'>
                            <span class="fa fa-trash-o"></span>
                        </a>
                        <a href="{$oWunschlistePos->Artikel->cURLFull}" title="{$oWunschlistePos->cArtikelName|escape:'quotes'}">
                            {if $oBox->getShowImages()}
                                <img alt="{$oWunschlistePos->cArtikelName|escape:'quotes'}" src="{$oWunschlistePos->Artikel->Bilder[0]->cURLMini}" class="img-xs">
                            {/if}
                            {$oWunschlistePos->fAnzahl|replace_delim} &times; {$oWunschlistePos->cArtikelName|truncate:25:"..."}
                        </a>
                    </li>
                {/foreach}
            </ul>
            <hr>
            <a href="{get_static_route id='jtl.php'}?wl={$oBox->getWishListID()}" class="btn btn-default btn-block btn-sm">
                {lang key='goToWishlist'}
            </a>
        </div>
    </section>
{else}
    <section class="hidden box-wishlist" id="sidebox{$oBox->getID()}"></section>
{/if}
