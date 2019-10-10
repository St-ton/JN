{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
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
                        <i class='fa fa-tasks mr-2'></i>
                        <span>{lang key='compare'}</span>
                    </div>
                {/block}
                {block name='boxes-box-comparelist-collapse'}
                    {collapse
                        class="d-md-block"
                        visible=false
                        id="crd-cllps-{$oBox->getID()}"
                        aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                        {block name='boxes-box-comparelist-products'}
                            {listgroup}
                                {foreach $oBox->getProducts() as $oArtikel}
                                    {if $oArtikel@iteration > $maxItems}
                                        {break}
                                    {/if}
                                    {$id = '"a"'}
                                    {listgroupitem data-id=$oArtikel->kArtikel class="border-0"}
                                        {link href=$oArtikel->cURLDEL class="remove float-right"
                                            title="{lang section="comparelist" key="removeFromCompareList"}"
                                            data=["name"=>"Vergleichsliste.remove",
                                                "toggle"=>"product-actions",
                                                "value"=>"{ldelim}{$id|escape:'html'}:{$oArtikel->kArtikel}{rdelim}"]
                                            aria=["label"=>{lang section="comparelist" key="removeFromCompareList"}]
                                        }
                                            <span class="fas fa-times"></span>
                                        {/link}
                                        {link href=$oArtikel->cURLFull}
                                            {image src=$oArtikel->Bilder[0]->cURLMini
                                                 alt=$oArtikel->cName|strip_tags|truncate:60|escape:'html' class="img-xs mr-2"}
                                            {$oArtikel->cName|truncate:25:'...'}
                                        {/link}
                                    {/listgroupitem}
                                {/foreach}
                            {/listgroup}
                        {/block}
                        {if $itemCount > 1}
                            {block name='boxes-box-comparelist-link'}
                                <hr class="my-4">
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
