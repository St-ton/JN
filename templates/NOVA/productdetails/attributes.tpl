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
                    {foreach $Artikel->oMerkmale_arr as $characteristic}
                        <tr>
                            <td class="h6">{$characteristic->cName}:</td>
                            <td class="attr-characteristic">
                                {strip}
                                    {foreach $characteristic->oMerkmalWert_arr as $characteristicValue}
                                        {if $characteristic->cTyp === 'TEXT' || $characteristic->cTyp === 'SELECTBOX' || $characteristic->cTyp === ''}
                                            <span class="value">{link href=$characteristicValue->cURLFull class="badge badge-light"}{$characteristicValue->cWert|escape:'html'}{/link} </span>
                                        {else}
                                            <span class="value">
                                            {link href=$characteristicValue->cURLFull
                                                class="text-decoration-none"
                                                data=['toggle'=>'tooltip', 'placement'=>'top', 'boundary'=>'window']
                                                title=$characteristicValue->cWert|escape:'html'
                                                aria=["label"=>$characteristicValue->cWert|escape:'html']
                                            }
                                                {$img = $characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)}
                                                {if $img !== null && $img|strpos:$smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN === false
                                                && $img|strpos:$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN === false}
                                                    {image fluid=true webp=true lazy=true
                                                        src=$img
                                                        srcset="{$characteristicValue->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_merkmalwert_mini_breite}w,
                                                            {$characteristicValue->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_merkmalwert_klein_breite}w,
                                                            {$characteristicValue->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_merkmalwert_normal_breite}w"
                                                        sizes="40px"
                                                        alt=$characteristicValue->cWert|escape:'html'
                                                    }
                                                {else}
                                                    {badge variant="light"}{$characteristicValue->cWert|escape:'html'}{/badge}
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

            {if $Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y'
            || (isset($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_ATTRIBUTEANHAENGEN])
                && $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_ATTRIBUTEANHAENGEN] == 1)}
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
