{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if ($Artikel->inWarenkorbLegbar == 1 || $Artikel->nErscheinendesProdukt == 1) || $Artikel->Variationen}
    <div id="add-to-cart" class="d-print-none product-buy text-right{if $Artikel->nErscheinendesProdukt} coming_soon{/if}">
    {block name='add-to-cart'}
        {if $Artikel->nErscheinendesProdukt}
            <div class="{if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'}alert alert-warning coming_soon{/if} text-center">
                {lang key='productAvailableFrom' section='global'}: <strong>{$Artikel->Erscheinungsdatum_de}</strong>
                {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $Artikel->inWarenkorbLegbar == 1}
                    ({lang key='preorderPossible' section='global'})
                {/if}
            </div>
        {/if}
        {if $Artikel->nIstVater && $Artikel->kVaterArtikel == 0}
            {alert variation="info" class="choose-variations"}
                {lang key='chooseVariations' section='messages'}
            {/alert}
        {elseif $Artikel->inWarenkorbLegbar == 1 }
            {if !$showMatrix}
                {block name='basket-form-inline'}
                    {inputgroup id="quantity-grp" class="choose_quantity"}
                        {input type="{if $Artikel->cTeilbar === 'Y' && $Artikel->fAbnahmeintervall == 0}text{else}number{/if}"
                            min="{if $Artikel->fMindestbestellmenge}{$Artikel->fMindestbestellmenge}{else}0{/if}"
                            required=($Artikel->fAbnahmeintervall > 0)
                            step="{if $Artikel->fAbnahmeintervall > 0}{$Artikel->fAbnahmeintervall}{/if}"
                            id="quantity" class="quantity text-right" name="anzahl"
                            aria=["label"=>"{lang key='quantity'}"]
                            value="{if $Artikel->fAbnahmeintervall > 0 || $Artikel->fMindestbestellmenge > 1}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}"
                            data=["decimals"=>"{if $Artikel->fAbnahmeintervall > 0}2{else}0{/if}"]
                        }
                        {inputgroupappend}
                            {if $Artikel->cEinheit}
                                {inputgrouptext class="unit form-control"}
                                    {$Artikel->cEinheit}
                                {/inputgrouptext}
                            {/if}
                            {button aria=["label"=>"{lang key='addToCart'}"] name="inWarenkorb" type="submit" value="{lang key='addToCart'}" class="ml-4" variant="primary"}
                                <em>
                                    <span class="fas fa-shopping-cart d-block d-sm-none"></span><span class="d-none d-sm-block">{lang key='addToCart'}</span>
                                </em>
                                <svg x="0px" y="0px" width="32px" height="32px" viewBox="0 0 32 32">
                                    <path stroke-dasharray="19.79 19.79" stroke-dashoffset="19.79" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="square" stroke-miterlimit="10" d="M9,17l3.9,3.9c0.1,0.1,0.2,0.1,0.3,0L23,11"/>
                                </svg>
                            {/button}
                        {/inputgroupappend}
                    {/inputgroup}
                {/block}
            {/if}
        {/if}
        {if $Artikel->inWarenkorbLegbar == 1 && ($Artikel->fMindestbestellmenge > 1 || ($Artikel->fMindestbestellmenge > 0 && $Artikel->cTeilbar === 'Y') || $Artikel->fAbnahmeintervall > 0 || $Artikel->cTeilbar === 'Y' || (isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_MAXBESTELLMENGE]) && $Artikel->FunktionsAttribute[$FKT_ATTRIBUT_MAXBESTELLMENGE] > 0))}
            {alert variant="info" class="mt-2 purchase-info"}
                {assign var=units value=$Artikel->cEinheit}
                {if empty($Artikel->cEinheit) || $Artikel->cEinheit|@count_characters == 0}
                    <p>{lang key='units' section='productDetails' assign='units'}</p>
                {/if}

                {if $Artikel->fMindestbestellmenge > 1 || ($Artikel->fMindestbestellmenge > 0 && $Artikel->cTeilbar === 'Y')}
                    {lang key='minimumPurchase' section='productDetails' assign='minimumPurchase'}
                    <p>{$minimumPurchase|replace:"%d":$Artikel->fMindestbestellmenge|replace:"%s":$units}</p>
                {/if}

                {if $Artikel->fAbnahmeintervall > 0 && $Einstellungen.artikeldetails.artikeldetails_artikelintervall_anzeigen === 'Y'}
                    {lang key='takeHeedOfInterval' section='productDetails' assign='takeHeedOfInterval'}
                    <p>{$takeHeedOfInterval|replace:"%d":$Artikel->fAbnahmeintervall|replace:"%s":$units}</p>
                {/if}

                {if $Artikel->cTeilbar === 'Y'}
                    <p>{lang key='integralQuantities' section='productDetails'}</p>
                {/if}

                {if isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_MAXBESTELLMENGE]) && $Artikel->FunktionsAttribute[$FKT_ATTRIBUT_MAXBESTELLMENGE] > 0}
                    {lang key='maximalPurchase' section='productDetails' assign='maximalPurchase'}
                    <p>{$maximalPurchase|replace:"%d":$Artikel->FunktionsAttribute[$FKT_ATTRIBUT_MAXBESTELLMENGE]|replace:"%s":$units}</p>
                {/if}
            {/alert}
        {/if}
    {/block}
    </div>
{/if}
