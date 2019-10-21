{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-comparelist-dropdown'}
    {block name='snippets-comparelist-dropdown-products'}
        <div class="table-responsive max-h-sm lg-max-h">
            {if !empty($smarty.session.Vergleichsliste->oArtikel_arr)}
                <table class="table table-vertical-middle table-striped">
                    <tbody>
                    {foreach $smarty.session.Vergleichsliste->oArtikel_arr as $product}
                        {block name='snippets-comparelist-dropdown-products-body'}
                            <tr>
                                <td>
                                    {block name='snippets-comparelist-dropdown-products-title'}
                                        {link href=$product->cURLFull}{$product->cName}{/link}
                                    {/block}
                                </td>
                                <td  class="text-right text-nowrap">
                                    {block name='snippets-comparelist-dropdown-products-remove'}
                                        {link href="?vlplo={$product->kArtikel}" class="remove float-right"
                                            title="{lang section="comparelist" key="removeFromCompareList"}"
                                            data=["name"=>"Vergleichsliste.remove",
                                                "toggle"=>"product-actions",
                                                "value"=>"{ldelim}{'"a"'|escape:'html'}:{$product->kArtikel}{rdelim}"
                                            ]
                                        }
                                            <i class="fas fa-times"></i>
                                        {/link}
                                    {/block}
                                </td>
                            </tr>
                        {/block}
                    {/foreach}
                    </tbody>
                </table>
            {/if}
        </div>
    {/block}
    {block name='snippets-comparelist-dropdown-hint'}
        <div class="dropdown-body">
            {if !empty($smarty.session.Vergleichsliste->oArtikel_arr) && $smarty.session.Vergleichsliste->oArtikel_arr|@count <= 1}
                {block name='snippets-comparelist-dropdown-more-than-one'}
                    {lang key='productNumberHint' section='comparelist'}
                {/block}
            {else}
                {block name='snippets-comparelist-dropdown-hint-to-compare'}
                    {row}
                        {col class='col-lg-auto ml-auto'}
                            {link class="btn btn-block btn-primary btn-sm"
                                id='nav-comparelist-goto'
                                href="{get_static_route id='vergleichsliste.php'}"
                            }
                                {lang key='gotToCompare'}
                            {/link}
                        {/col}
                    {/row}
                {/block}
            {/if}
        </div>
    {/block}
{/block}
