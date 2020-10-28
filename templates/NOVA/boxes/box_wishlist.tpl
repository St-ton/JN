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
                        class="text-decoration-none-util font-weight-bold-util mb-2 d-md-none dropdown-toggle"}
                        {lang key='wishlist'}
                    {/link}
                {/block}
                {block name='boxes-box-wishlist-title'}
                    <div class="productlist-filter-headline align-items-center-util d-none d-md-flex">
                        <i class='fa fa-heart mr-2'></i>
                        {lang key='wishlist'}
                    </div>
                {/block}
                {block name='boxes-box-wishlist-collapse'}
                    {collapse
                        class="d-md-block"
                        visible=false
                        id="crd-cllps-{$oBox->getID()}"
                        aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                            {assign var=maxItems value=$oBox->getItemCount()}
                        <table class="table table-vertical-middle table-striped table-img">
                            <tbody>
                                {block name='boxes-box-wishlist-wishlist-items'}
                                {foreach $oBox->getItems() as $wishlistItem}
                                        {if $wishlistItem@iteration > $maxItems}{break}{/if}
                                    <tr>
                                        <td class="w-100" data-id={$wishlistItem->kArtikel}>
                                            {block name='boxes-box-wishlist-dropdown-products-image-title'}
                                                {formrow class="align-items-center-util"}
                                                    {if $oBox->getShowImages()}
                                                        {col class="col-auto"}
                                                            {block name='boxes-box-wishlist-dropdown-products-image'}
                                                                {link href=$wishlistItem->Artikel->cURLFull title=$wishlistItem->cArtikelName|escape:'quotes'}
                                                                    {image fluid=true webp=true lazy=true
                                                                        src=$wishlistItem->Artikel->Bilder[0]->cURLMini
                                                                        srcset="{$wishlistItem->Artikel->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                                                {$wishlistItem->Artikel->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                                                {$wishlistItem->Artikel->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                                                        sizes="24px"
                                                                        alt=$wishlistItem->Artikel->cName|strip_tags|escape:'html'}
                                                                {/link}
                                                            {/block}
                                                        {/col}
                                                    {/if}
                                                    {col}
                                                        {block name='boxes-box-wishlist-dropdown-products-title'}
                                                            {link href=$wishlistItem->Artikel->cURLFull title=$wishlistItem->cArtikelName|escape:'quotes'}
                                                                {$wishlistItem->fAnzahl|replace_delim} &times; {$wishlistItem->cArtikelName|truncate:40:"..."}
                                                            {/link}
                                                        {/block}
                                                    {/col}
                                                {/formrow}
                                            {/block}
                                        </td>
                                        <td class="text-right-util text-nowrap-util">
                                            {block name='snippets-wishlist-dropdown-products-remove'}
                                                {link class="remove float-right"
                                                    href=$wishlistItem->cURL
                                                    data=["name"=>"Wunschliste.remove",
                                                    "toggle"=>"product-actions",
                                                    "value"=>['a'=>$wishlistItem->kWunschlistePos]|json_encode|escape:'html'
                                                    ]
                                                    aria=["label"=>"{lang section='login' key='wishlistremoveItem'}"]}
                                                    <span class="fas fa-times"></span>
                                                {/link}
                                            {/block}
                                        </td>
                                {/foreach}
                                {/block}
                            </tbody>
                        </table>
                        {block name='boxes-box-wishlist-actions'}
                            <hr class="mt-0 mb-3">
                            {link href="{get_static_route id='wunschliste.php'}?wl={$oBox->getWishListID()}" class="btn btn-outline-primary btn-block btn-sm"}
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
