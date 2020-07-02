{block name='boxes-box-comparelist'}
    {assign var=maxItems value=$oBox->getItemCount()}
    {assign var=itemCount value=count($oBox->getProducts())}
    {if $itemCount > 0}
        {card class="box box-compare mb-md-4" id="sidebox{$oBox->getID()}"}
            {block name='boxes-box-comparelist-content'}
                {block name='boxes-box-comparelist-toggle-title'}
                    {link id="crd-hdr-{$oBox->getID()}"
                        href="#crd-cllps-{$oBox->getID()}"
                        data=["toggle"=>"collapse"]
                        role="button"
                        aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
                        class="text-decoration-none font-weight-bold mb-2 d-md-none dropdown-toggle"}
                        {lang key='compare'}
                    {/link}
                {/block}
                {block name='boxes-box-comparelist-title'}
                    <div class="productlist-filter-headline align-items-center d-none d-md-flex">
                        <i class='fas fa-list mr-2'></i>
                        {lang key='compare'}
                    </div>
                {/block}
                {block name='boxes-box-comparelist-collapse'}
                    {collapse
                        class="d-md-block"
                        visible=false
                        id="crd-cllps-{$oBox->getID()}"
                        aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                        {block name='boxes-box-comparelist-products'}
                            <table class="table table-vertical-middle table-striped table-img">
                                <tbody>
                                    {$id = '"a"'|escape:'html'}
                                    {foreach $oBox->getProducts() as $product}
                                        {if $product@iteration > $maxItems}
                                            {break}
                                        {/if}
                                        <tr>
                                        <td class="w-100" data-id={$product->kArtikel}>
                                            {block name='boxes-box-comparelist-dropdown-products-image-title'}
                                                {formrow class="align-items-center"}
                                                    {col class="col-auto"}
                                                        {block name='boxes-box-comparelist-dropdown-products-image'}
                                                            {link href=$product->cURLFull}
                                                                {image fluid=true webp=true lazy=true
                                                                    src=$product->Bilder[0]->cURLMini
                                                                    srcset="{$product->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                                        {$product->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                                        {$product->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                                                    sizes="45px"
                                                                    alt=$product->cName|strip_tags|escape:'html'}
                                                            {/link}
                                                        {/block}
                                                    {/col}
                                                    {col}
                                                        {block name='boxes-box-comparelist-dropdown-products-title'}
                                                            {link href=$product->cURLFull}{$product->cName|truncate:40:'...'}{/link}
                                                        {/block}
                                                    {/col}
                                                {/formrow}
                                            {/block}
                                        </td>
                                        <td class="text-right text-nowrap">
                                            {block name='boxes-box-comparelist-dropdown-products-remove'}
                                                {link href=$product->cURLDEL class="remove float-right"
                                                    title="{lang section="comparelist" key="removeFromCompareList"}"
                                                    data=["name"=>"Vergleichsliste.remove",
                                                    "toggle"=>"product-actions",
                                                    "value"=>"{ldelim}{$id}:{$product->kArtikel}{rdelim}"]
                                                    aria=["label"=>{lang section="comparelist" key="removeFromCompareList"}]}
                                                    <span class="fas fa-times"></span>
                                                {/link}
                                            {/block}
                                        </td>
                                    {/foreach}
                                </tbody>
                            </table>
                        {/block}
                        {if $itemCount > 1}
                            {block name='boxes-box-comparelist-link'}
                                <hr class="mt-n3 mb-3">
                                {link
                                    class="btn btn-outline-primary btn-sm btn-block{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"
                                    href="{get_static_route id='vergleichsliste.php'}"
                                    target="{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'}_blank{else}_self{/if}"
                                }
                                   {lang key='gotToCompare'}
                                {/link}
                            {/block}
                        {/if}
                    {/collapse}
                {/block}
            {/block}
            {block name='boxes-box-comparelist-hr-end'}
                <hr class="my-3 d-flex d-md-none">
            {/block}
        {/card}
    {else}
        {block name='blog-preview-no-items'}
            <section class="d-none box-compare" id="sidebox{$oBox->getID()}"></section>
        {/block}
    {/if}
{/block}
