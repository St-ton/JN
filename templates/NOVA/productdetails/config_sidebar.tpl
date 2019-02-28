{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div id="product-configuration-sidebar">
    {button variant="link" class="cfg-toggle" block=true data=["toggle"=>"collapse", "target"=>"#configuration-table"]}
        <div class="h5 text-left">{lang key='yourConfiguration'}</div>
    {/button}
    <table id="configuration-table" class="table table-striped collapse show">
        <tbody class="summary"></tbody>
        <tfoot>
        <tr>
            <td colspan="3" class="text-right word-break">
                <strong class="price"></strong>
                <p class="vat_info text-muted">
                    <small>{include file='snippets/shipping_tax_info.tpl' taxdata=$Artikel->taxData}</small>
                </p>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
