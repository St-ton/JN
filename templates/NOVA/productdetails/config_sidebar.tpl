{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-config-sidebar'}
    <div id="product-configuration-sidebar">
        {block name='productdetails-config-sidebar-button'}
            {button variant="link" class="cfg-toggle" block=true data=["toggle"=>"collapse", "target"=>"#configuration-table"]}
                <div class="h5 text-left">{lang key='yourConfiguration'}</div>
            {/button}
        {/block}
        {block name='productdetails-config-sidebar-table'}
            <table id="configuration-table" class="table table-striped collapse">
                <tbody class="summary"></tbody>
                <tfoot>
                <tr>
                    <td colspan="3" class="text-right word-break">
                        <strong class="price"></strong>
                        <p class="vat_info text-muted">
                            {block name='productdetails-config-sidebar-include-shipping-tax-info'}
                                <small>{include file='snippets/shipping_tax_info.tpl' taxdata=$Artikel->taxData}</small>
                            {/block}
                        </p>
                    </td>
                </tr>
                </tfoot>
            </table>
        {/block}
    </div>
{/block}