{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-comparelist-dropdown'}
    {block name='snippets-comparelist-dropdown-products'}
        {col cols=12}
            {if !empty($smarty.session.Vergleichsliste->oArtikel_arr)}
                {foreach $smarty.session.Vergleichsliste->oArtikel_arr as $product}
                    {row class="py-2{if $product@iteration %2} bg-info{/if}"}
                        {col cols=9}
                            {link href=$product->cURLFull}{$product->cName}{/link}
                        {/col}
                        {col cols=3 class='text-right '}
                            {link href="?vlplo={$product->kArtikel}" class="remove float-right"
                                title="{lang section="comparelist" key="removeFromCompareList"}"
                                data=["name"=>"Vergleichsliste.remove",
                                    "toggle"=>"product-actions",
                                    "value"=>"{ldelim}{'"a"'|escape:'html'}:{$product->kArtikel}{rdelim}"
                                ]
                            }
                                &times;
                            {/link}
                        {/col}
                    {/row}
                {/foreach}
            {/if}
        {/col}
    {/block}
    {block name='snippets-comparelist-dropdown-hint'}
        {col cols=12 class="mt-2"}
            {if !empty($smarty.session.Vergleichsliste->oArtikel_arr) && $smarty.session.Vergleichsliste->oArtikel_arr|@count <= 1}
                {lang key='productNumberHint' section='comparelist'}
            {else}
                {link
                    class="btn btn-sm btn-primary float-right"
                    id='nav-comparelist-goto'
                    href="{get_static_route id='vergleichsliste.php'}"
                }
                    {lang key='gotToCompare'}
                {/link}
            {/if}
        {/col}
    {/block}
{/block}
