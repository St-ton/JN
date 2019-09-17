{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-wishlist'}
    {if $oBox->getItems()|count > 0}
        {card class="box box-wishlist mb-4" id="sidebox{$oBox->getID()}"}
            {block name='boxes-box-wishlist-content'}
                {block name='boxes-box-wishlisttitle'}
                    <div class="productlist-filter-headline align-items-center d-flex">
                        <i class='fa fa-heart mr-2'></i>
                        <span>{lang key='wishlist'}</span>
                    </div>
                {/block}
                {listgroup}
                    {assign var=maxItems value=$oBox->getItemCount()}
                    {block name='boxes-box-wishlist-wishlist-items'}
                    {foreach $oBox->getItems() as $oWunschlistePos}
                            {if $oWunschlistePos@iteration > $maxItems}{break}{/if}
                            {listgroupitem data-id=$oWunschlistePos->kArtikel class="border-0"}
                                {link class="remove float-right"
                                    href=$oWunschlistePos->cURL
                                    data=["name"=>"Wunschliste.remove",
                                        "toggle"=>"product-actions",
                                        "value"=>['a'=>$oWunschlistePos->kWunschlistePos]|json_encode|escape:'html'
                                    ]
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
                    {/block}
                {/listgroup}
                {block name='boxes-box-wishlist-actions'}
                    <hr class="my-4">
                    {link href="{get_static_route id='wunschliste.php'}?wl={$oBox->getWishListID()}" class="btn btn-secondary btn-block btn-sm"}
                        {lang key='goToWishlist'}
                    {/link}
                {/block}
            {/block}
        {/card}
    {else}
        {block name='boxes-box-wishlist-no-items'}
            <section class="d-none box-wishlist" id="sidebox{$oBox->getID()}"></section>
        {/block}
    {/if}
{/block}
