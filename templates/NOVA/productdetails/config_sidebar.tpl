{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div id="product-configuration-sidebar" class="sticky-top mb-4 d-none d-md-block">
    {button variant="link" class="cfg-toggle" block=true data=["toggle"=>"collapse", "target"=>"#configuration-table"]}
        <div class="h5 text-left">{lang key='yourConfiguration'}</div>
    {/button}
    <table id="configuration-table" class="table table-striped collapse">
        <tbody class="summary"></tbody>
        <tfoot>
        <tr>
            <td colspan="3" class="text-right word-break">
                <strong class="price"></strong>
            </td>
        </tr>
        </tfoot>
    </table>
    {*{cardfooter}
    {if $Artikel->inWarenkorbLegbar == 1}
        {inputgroup id="quantity-grp" class="choose_quantity"}
        {input type="number"
        required=$Artikel->fAbnahmeintervall > 0
        step="{if $Artikel->fAbnahmeintervall}{$Artikel->fAbnahmeintervall}{else}1{/if}"
        id="quantity"
        class="quantity text-right"
        name="anzahl"
        value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{elseif isset($fAnzahl)}{$fAnzahl}{else}1{/if}"
        }
        {inputgroupaddon append=true}
        {button name="inWarenkorb" type="submit" value="{lang key='addToCart'}"
        variant="primary"
        title="{if isset($kEditKonfig)}{lang key='applyChanges'}{else}{lang key='addToCart'}{/if}"
        }
        {if isset($kEditKonfig)}
            <i class="fas fa-sync"></i>
        {else}
            <i class="fas fa-shopping-cart"></i>
        {/if}
        {/button}
        {/inputgroupaddon}
        {/inputgroup}
        {if $Artikel->kVariKindArtikel > 0}
            {input type="hidden" name="a2" value=$Artikel->kVariKindArtikel}
        {/if}
        {if isset($kEditKonfig)}
            {input type="hidden" name="kEditKonfig" value=$kEditKonfig}
        {/if}
    {/if}
    {/cardfooter}*}
</div>
