{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($smarty.session.Vergleichsliste->oArtikel_arr)}
{foreach $smarty.session.Vergleichsliste->oArtikel_arr as $product}
    {row}
        {col md=8}
            <small>{link href=$product->cURLFull}{$product->cName}{/link}</small>
        {/col}
        {col md=4 class='text-right'}
            {link href="?vlplo={$product->kArtikel}" class="remove float-right"
            title="{lang section="comparelist" key="removeFromCompareList"}"
            data=["name"=>"Vergleichsliste.remove",
            "toggle"=>"product-actions",
            "value"=>"{ldelim}{'"a"'|escape:'html'}:{$product->kArtikel}{rdelim}"]
            }
                &times;
            {/link}
        {/col}
    {/row}
{/foreach}
{/if}