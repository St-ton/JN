{assign var="showProductWeight" value=false}
{if isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0
&& ($Einstellungen.artikeldetails.artikeldetails_artikelgewicht_anzeigen === 'Y' && $tplscope === 'details'
||  $Einstellungen.artikeluebersicht.artikeluebersicht_artikelgewicht_anzeigen === 'Y' && $tplscope === 'productlist')}
    {assign var="showProductWeight" value=true}
{/if}

{assign var="showShippingWeight" value=false}
{if isset($Artikel->cGewicht) && $Artikel->fGewicht > 0
&& ($Einstellungen.artikeldetails.artikeldetails_gewicht_anzeigen === 'Y' && $tplscope === 'details'
||  $Einstellungen.artikeluebersicht.artikeluebersicht_gewicht_anzeigen === 'Y' && $tplscope === 'productlist')}
    {assign var="showShippingWeight" value=true}
{/if}

{assign var="dimension" value=$Artikel->getDimension()}

{assign var="showAttributesTable" value=false}
{if $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y' && !empty($Artikel->oMerkmale_arr)
|| $showProductWeight
|| $showShippingWeight
|| $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y' && (!empty($dimension['length']) || !empty($dimension['width']) || !empty($dimension['height']))
|| isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0  && $Artikel->cTeilbar !== 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1)
|| ($Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y'
|| (isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN]) && $Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN] == 1)) && !empty($Artikel->Attribute)
}
    {assign var="showAttributesTable" value=true}
{/if}

{if $showAttributesTable}
<hr>
<div class="product-attributes">
    {block name="productdetails-attributes"}
        <div class="list-group list-group-striped">
            {if $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'}
                {block name="productdetails-attributes-characteristics"}
                    {foreach from=$Artikel->oMerkmale_arr item=oMerkmal}
                        <div class="list-group-item">
                            <div class="list-group-item-heading">{$oMerkmal->cName}:</div>
                            <div class="list-group-item-text attr-characteristic">
                                {strip}
                                    {foreach name="attr_characteristics" from=$oMerkmal->oMerkmalWert_arr item=oMerkmalWert}
                                        {if $oMerkmal->cTyp === 'TEXT' || $oMerkmal->cTyp === 'SELECTBOX' || $oMerkmal->cTyp === ''}
                                            <span class="value"><a href="{$oMerkmalWert->cURLFull}" class="label label-primary">{$oMerkmalWert->cWert|escape:'html'}</a> </span>
                                        {else}
                                            <span class="value">
                                            <a href="{$oMerkmalWert->cURLFull}" data-toggle="tooltip" data-placement="top" title="{$oMerkmalWert->cWert|escape:'html'}">
                                                {if $oMerkmalWert->cBildpfadKlein !== 'gfx/keinBild_kl.gif'}
                                                    <img src="{$oMerkmalWert->cBildURLKlein}" title="{$oMerkmalWert->cWert|escape:'html'}" alt="{$oMerkmalWert->cWert|escape:'html'}" />
                                                {else}
                                                    <span class="value"><a href="{$oMerkmalWert->cURLFull}" class="label label-primary">{$oMerkmalWert->cWert|escape:'html'}</a> </span>
                                                {/if}
                                            </a>
                                        </span>
                                        {/if}
                                    {/foreach}
                                {/strip}
                            </div>
                        </div>
                    {/foreach}
                {/block}
            {/if}

            {if $showShippingWeight}
                {block name="productdetails-attributes-shipping-weight"}
                    <div class="list-group-item">
                        <div class="list-group-item-heading">{lang key="shippingWeight" section="global"}:</div>
                        <div class="list-group-item-text weight-unit">
                            {$Artikel->cGewicht} {lang key="weightUnit" section="global"}
                        </div>
                    </div>
                {/block}
            {/if}

            {if $showProductWeight}
                {block name="productdetails-attributes-product-weight"}
                    <div class="list-group-item attr-weight">
                        <div class="list-group-item-heading">{lang key="productWeight" section="global"}:</div>
                        <div class="list-group-item-text weight-unit" itemprop="weight" itemscope itemtype="http://schema.org/QuantitativeValue">
                            <span itemprop="value">{$Artikel->cArtikelgewicht}</span> <span itemprop="unitText">{lang key="weightUnit" section="global"}
                        </div>
                    </div>
                {/block}
            {/if}

            {if isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0 && $Artikel->cTeilbar !== 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1) && isset($Artikel->cMassMenge)}
                {block name="productdetails-attributes-unit"}
                    <div class="list-group-item attr-contents">
                        <div class="list-group-item-heading">{lang key="contents" section="productDetails"}: </div>
                        <div class="list-group-item-text attr-value">
                            {$Artikel->cMassMenge} {$Artikel->cMasseinheitName}
                        </div>
                    </div>
                {/block}
            {/if}

            {if $dimension && $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y'}
                {block name="productdetails-attributes-dimensions"}
                    {assign var=dimensionArr value=$Artikel->getDimensionLocalized()}
                    {if $dimensionArr|count > 0}
                        <div class="list-group-item attr-dimensions">
                            <div class="list-group-item-heading">{lang key="dimensions" section="productDetails"}
                                ({foreach name=DimensionKey from=$dimensionArr key=dimkey item=dim}
                                {$dimkey}{if $smarty.foreach.DimensionKey.last}{else} &times; {/if}
                                {/foreach}):
                            </div>
                            <div class="list-group-item-text attr-value">
                                {foreach name=Dimension from=$dimensionArr item=dim}
                                    {$dim}{if $smarty.foreach.Dimension.last} cm {else} &times; {/if}
                                {/foreach}
                            </div>
                        </div>
                    {/if}
                {/block}
            {/if}

            {if $Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y' || (isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN]) && $Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN] == 1)}
                {block name="productdetails-attributes-shop-attributes"}
                    {foreach name=Attribute from=$Artikel->Attribute item=Attribut}
                        <div class="list-group-item attr-custom">
                            <div class="list-group-item-heading">{$Attribut->cName}: </div>
                            <div class="list-group-item-text attr-value">{$Attribut->cWert}</div>
                        </div>
                    {/foreach}
                {/block}
            {/if}
            </tbody> {* /attr-group *}
        </div>
    {/block}
</div>
{/if}
