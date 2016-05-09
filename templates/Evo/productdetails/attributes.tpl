{assign var="showProductWeight" value=false}
{if isset($Artikel->cArtikelgewicht)  && $Artikel->fArtikelgewicht > 0
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

{assign var="showAttributesTable" value=false}
{if    $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y' && !empty($Artikel->oMerkmale_arr)
    || $showProductWeight
    || $showShippingWeight
    || isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0  && $Artikel->cTeilbar != 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1)
    || isset($Artikel->fBreite) && isset($Artikel->fHoehe) && isset($Artikel->fLaenge) && $Artikel->fBreite > 0 && $Artikel->fHoehe > 0 && $Artikel->fLaenge > 0
    || $Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y' || $Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN] == 1 && !empty($Artikel->Attribute)
}
    {assign var="showAttributesTable" value=true}
{/if}

{if $showAttributesTable}
<hr>
<div class="product-attributes">
    {block name="productdetails-attributes"}
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <tbody>
                {if $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'}
                    {foreach from=$Artikel->oMerkmale_arr item=oMerkmal}
                        <tr class="attr-characteristic">
                            <td class="attr-label">
                                {$oMerkmal->cName}:
{*                              ******* images as labels dont look well here *******
                                {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'T'}
                                    {$oMerkmal->cName}:
                                {elseif $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'B' && !empty($oMerkmal->cBildpfadKlein)}
                                    <img src="{$oMerkmal->cBildpfadKlein}" title="{$oMerkmal->cName}" />
                                {elseif $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'BT'}
                                    {if isset($oMerkmal->cBildpfadKlein)}<img src="{$oMerkmal->cBildpfadKlein}" alt="{$oMerkmal->cName}" title="{$oMerkmal->cName}" class="vmiddle" /> {/if}{$oMerkmal->cName}:
                                {/if}
*}
                            </td>
                             <td class="attr-value">{strip}
                                {foreach name="attr_characteristics" from=$oMerkmal->oMerkmalWert_arr item=oMerkmalWert}
                                    {if $oMerkmal->cTyp === 'TEXT' || $oMerkmal->cTyp === 'SELECTBOX' || $oMerkmal->cTyp === ''}
                                        <span class="value"><a href="{$oMerkmalWert->cURL}" class="label label-primary">{$oMerkmalWert->cWert}</a> </span>
                                    {else}
                                        <span class="value">
                                            <a href="{$oMerkmalWert->cURL}" data-toggle="tooltip" data-placement="top" title="{$oMerkmalWert->cWert|escape:"html"}">
                                                {if $oMerkmalWert->cBildpfadKlein !== 'gfx/keinBild_kl.gif'}
                                                <img src="{$oMerkmalWert->cBildpfadKlein}" title="{$oMerkmalWert->cWert}" alt="{$oMerkmalWert->cWert}" />
                                                {/if}
                                            </a>
                                        </span>
                                    {/if}
                                {/foreach}
                                {/strip}
                            </td>
                        </tr>
                    {/foreach}
                {/if}

                {if $showShippingWeight}
                    <tr class="attr-weight">
                        <td class="attr-label">{lang key="shippingWeight" section="global"}: </td>
                        <td class="attr-value weight-unit">{$Artikel->cGewicht} {lang key="weightUnit" section="global"}</td>
                    </tr>
                {/if}

                {if $showProductWeight}
                    <tr class="attr-weight">
                        <td class="attr-label">{lang key="productWeight" section="global"}: </td>
                        <td class="attr-value weight-unit">{$Artikel->cArtikelgewicht} {lang key="weightUnit" section="global"}</td>
                    </tr>
                {/if}

                {if isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0 && $Artikel->cTeilbar != 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1) && isset($Artikel->cMassMenge)}
                    <tr class="attr-contents">
                        <td class="attr-label">{lang key="contents" section="productDetails"}: </td>
                        <td class="attr-value">{$Artikel->cMassMenge} {$Artikel->cMasseinheitName}</td>
                    </tr>
                {/if}

                {if isset($Artikel->fBreite) && isset($Artikel->fHoehe) && isset($Artikel->fLaenge) && isset($Artikel->cBreite) && isset($Artikel->cHoehe) && isset($Artikel->cLaenge) && $Artikel->fBreite > 0 && $Artikel->fHoehe > 0 && $Artikel->fLaenge > 0}
                    <tr class="attr-dimensions">
                        <td class="attr-label">{lang key="dimensions" section="productDetails"}: </td>
                        <td class="attr-value">{$Artikel->cLaenge} &times; {$Artikel->cBreite} &times; {$Artikel->cHoehe} cm</td>
                    </tr>
                {/if}

                {if $Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y' || $Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN] == 1}
                    {foreach name=Attribute from=$Artikel->Attribute item=Attribut}
                        <tr class="attr-custom">
                            <td class="attr-label">{$Attribut->cName}: </td><td class="attr-value">{$Attribut->cWert}</td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>{* /attr-group *}
        </table>
    </div>
    {/block}
</div>
{/if}