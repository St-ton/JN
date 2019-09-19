{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-wishlist'}
    {if $oBox->getItems()|count > 0}
        {card class="box box-wishlist mb-md-4" id="sidebox{$oBox->getID()}"}
            {block name='boxes-box-wishlist-content'}
                {block name='boxes-box-wishlist-toggle-title'}
                    {link id="crd-hdr-{$oBox->getID()}"
                        href="#crd-cllps-{$oBox->getID()}"
                        data=["toggle"=>"collapse"]
                        role="button"
                        aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
                        class="text-decoration-none font-weight-bold mb-2 d-md-none dropdown-toggle"}
                        {lang key='wishlist'}
                    {/link}
                {/block}
                {block name='boxes-box-wishlist-title'}
                    <div class="productlist-filter-headline align-items-center d-none d-md-flex">
                        <i class='fa fa-heart mr-2'></i>
                        <span>{lang key='wishlist'}</span>
                    </div>
                {/block}
                {block name='boxes-box-wishlist-collapse'}
                    {collapse
                        class="d-md-block"
                        visible=false
                        id="crd-cllps-{$oBox->getID()}"
                        aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
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
                    {/collapse}
                {/block}
            {/block}
            {block name='boxes-box-wishlist-hr-end'}
                <hr class="my-3 d-flex d-md-none">
            {/block}
        {/card}
    {else}
        {block name='boxes-box-wishlist-no-items'}
            <section class="d-none box-wishlist" id="sidebox{$oBox->getID()}"></section>
        {/block}
    {/if}
{/block}
