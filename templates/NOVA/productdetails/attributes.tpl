{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-attributes'}
{if $showAttributesTable}
    <div class="product-attributes mt-3">
    {block name='productdetails-attributes-table'}
        <table class="table table-condensed table-striped">
            {if $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'}
                {block name='productdetails-attributes-characteristics'}
                    {foreach $Artikel->oMerkmale_arr as $oMerkmal}
                        <tr>
                            <td class="h6">{$oMerkmal->cName}:</td>
                            <td class="attr-characteristic">
                                {strip}
                                    {foreach $oMerkmal->oMerkmalWert_arr as $oMerkmalWert}
                                        {if $oMerkmal->cTyp === 'TEXT' || $oMerkmal->cTyp === 'SELECTBOX' || $oMerkmal->cTyp === ''}
                                            <span class="value">{link href=$oMerkmalWert->cURLFull class="badge badge-light"}{$oMerkmalWert->cWert|escape:'html'}{/link} </span>
                                        {else}
                                            <span class="value">
                                            {link href=$oMerkmalWert->cURLFull
                                                class="text-decoration-none"
                                                data=['toggle'=>'tooltip', 'placement'=>'top']
                                                title=$oMerkmalWert->cWert|escape:'html'
                                                aria=["label"=>$oMerkmalWert->cWert|escape:'html']
                                            }
                                                {if $oMerkmalWert->cBildpfadKlein !== 'gfx/keinBild_kl.gif'}
                                                    {image src=$oMerkmalWert->cBildURLKlein title=$oMerkmalWert->cWert|escape:'html' alt=$oMerkmalWert->cWert|escape:'html'}
                                                {else}
                                                    {badge variant="light"}{$oMerkmalWert->cWert|escape:'html'}{/badge}
                                                {/if}
                                            {/link}
                                            </span>
                                        {/if}
                                    {/foreach}
                                {/strip}
                            </td>
                        </tr>
                    {/foreach}
                {/block}
            {/if}

            {if $showShippingWeight}
                {block name='productdetails-attributes-shipping-weight'}
                    <tr>
                        <td class="h6">{lang key='shippingWeight'}:</td>
                        <td class="weight-unit">
                            {$Artikel->cGewicht} {lang key='weightUnit'}
                        </td>
                    </tr>
                {/block}
            {/if}

            {if $showProductWeight}
                {block name='productdetails-attributes-product-weight'}
                    <tr class="attr-weight">
                        <td class="h6">{lang key='productWeight'}:</td>
                        <td class="weight-unit" itemprop="weight" itemscope itemtype="http://schema.org/QuantitativeValue">
                            <span itemprop="value">{$Artikel->cArtikelgewicht}</span> <span itemprop="unitText">{lang key='weightUnit'}
                        </td>
                    </tr>
                {/block}
            {/if}

            {if isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0 && $Artikel->cTeilbar !== 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1) && isset($Artikel->cMassMenge)}
                {block name='productdetails-attributes-unit'}
                    <tr class="attr-contents">
                        <td class="h6">{lang key='contents' section='productDetails'}: </td>
                        <td class="attr-value">
                            {$Artikel->cMassMenge} {$Artikel->cMasseinheitName}
                        </td>
                    </tr>
                {/block}
            {/if}

            {if $dimension && $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y'}
                {block name='productdetails-attributes-dimensions'}
                    {assign var=dimensionArr value=$Artikel->getDimensionLocalized()}
                    {if $dimensionArr|count > 0}
                        <tr class="attr-dimensions">
                            <td class="h6">{lang key='dimensions' section='productDetails'}
                                ({foreach $dimensionArr as $dimkey => $dim}
                                {$dimkey}{if $dim@last}{else} &times; {/if}
                                {/foreach}):
                            </td>
                            <td class="attr-value">
                                {foreach $dimensionArr as $dim}
                                    {$dim}{if $dim@last} cm {else} &times; {/if}
                                {/foreach}
                            </td>
                        </tr>
                    {/if}
                {/block}
            {/if}

            {if $Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y' || (isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN]) && $Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN] == 1)}
                {block name='productdetails-attributes-shop-attributes'}
                    {foreach $Artikel->Attribute as $Attribut}
                        <tr class="attr-custom">
                            <td class="h6">{$Attribut->cName}: </td>
                            <td class="attr-value">{$Attribut->cWert}</td>
                        </tr>
                    {/foreach}
                {/block}
            {/if}
        </table>
    {/block}
    </div>
{/if}
{/block}
