{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=maxItems value=$oBox->getItemCount()}
{assign var=itemCount value=count($oBox->getProducts())}
{if $itemCount > 0}
    {card
        class="box box-compare mb-7"
        id="sidebox{$oBox->getID()}"
        title="<i class='fa fa-tasks'></i> {lang key='compare'}"
    }
        <hr class="mt-0 mb-4">
        {listgroup}
            {foreach $oBox->getProducts() as $oArtikel}
                {if $oArtikel@iteration > $maxItems}
                    {break}
                {/if}
                {$id = '"a"'}
                {listgroupitem data-id=$oArtikel->kArtikel}
                    {link href=$oArtikel->cURLDEL class="remove float-right"
                        title="{lang section="comparelist" key="removeFromCompareList"}"
                        data=["name"=>"Vergleichsliste.remove",
                            "toggle"=>"product-actions",
                            "value"=>"{ldelim}{$id|escape:'html'}:{$oArtikel->kArtikel}{rdelim}"]
                    }
                        <span class="fa fa-trash"></span>
                    {/link}
                    {link href=$oArtikel->cURLFull}
                        {image src=$oArtikel->Bilder[0]->cURLMini
                             alt=$oArtikel->cName|strip_tags|truncate:60|escape:'html' class="img-xs"}
                        {$oArtikel->cName|truncate:25:'...'}
                    {/link}
                {/listgroupitem}
            {/foreach}
        {/listgroup}
        {if $itemCount > 1}
            <hr>
            {link
                class="btn btn-secondary btn-sm btn-block{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"
                href="{get_static_route id='vergleichsliste.php'}"
                target="{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'}_blank{else}_self{/if}"
            }
               {lang key='gotToCompare'}
            {/link}
        {/if}
    {/card}
{else}
    <section class="d-none box-compare" id="sidebox{$oBox->getID()}"></section>
{/if}
