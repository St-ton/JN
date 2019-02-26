{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->getItems()|count > 0}
    {card class="box box-wishlist mb-7" id="sidebox{$oBox->getID()}" title="<i class='fa fa-heart'></i> {lang key='wishlist'}"}
        <hr class="mt-0 mb-4">
        {listgroup}
            {assign var=maxItems value=$oBox->getItemCount()}
            {foreach $oBox->getItems() as $oWunschlistePos}
                {listgroupitem data-id=$oWunschlistePos->kArtikel class="border-0"}
                    {if $oWunschlistePos@iteration > $maxItems}{break}{/if}
                    {link class="remove float-right"
                        href=$oWunschlistePos->cURL
                        data=["name"=>"Wunschliste.remove",
                            "toggle"=>"product-actions",
                            "value"=>"{ldelim}'a':{$oWunschlistePos->kWunschlistePos}{rdelim}"]
                        aria=["label"=>"{lang section='login' key='wishlistremoveItem'}"]
                    }
                        <span class="fa fa-trash"></span>
                    {/link}
                    {link href=$oWunschlistePos->Artikel->cURLFull title=$oWunschlistePos->cArtikelName|escape:'quotes'}
                        {if $oBox->getShowImages()}
                            {image alt=$oWunschlistePos->cArtikelName|escape:'quotes' src=$oWunschlistePos->Artikel->Bilder[0]->cURLMini class="img-xs"}
                        {/if}
                        {$oWunschlistePos->fAnzahl|replace_delim} &times; {$oWunschlistePos->cArtikelName|truncate:25:"..."}
                    {/link}
                {/listgroupitem}
            {/foreach}
        {/listgroup}
        <hr>
        {link href="{get_static_route id='jtl.php'}?wl={$oBox->getWishListID()}" class="btn btn-secondary btn-block btn-sm"}
            {lang key='goToWishlist'}
        {/link}
    {/card}
{else}
    <section class="d-none box-wishlist" id="sidebox{$oBox->getID()}"></section>
{/if}
