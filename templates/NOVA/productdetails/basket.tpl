{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-basket'}
    {if ($Artikel->inWarenkorbLegbar == 1 || $Artikel->nErscheinendesProdukt == 1) || $Artikel->Variationen}
        <div id="add-to-cart" class="mt-5 d-print-none product-buy{if $Artikel->nErscheinendesProdukt} coming_soon{/if}">
            {if $Artikel->nErscheinendesProdukt}
                {block name='productdetails-basket-coming-soon'}
                    <div class="{if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'}alert alert-warning coming_soon{/if} text-center">
                        {lang key='productAvailableFrom' section='global'}: <strong>{$Artikel->Erscheinungsdatum_de}</strong>
                        {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $Artikel->inWarenkorbLegbar == 1}
                            ({lang key='preorderPossible' section='global'})
                        {/if}
                    </div>
                {/block}
            {/if}
            {if $Artikel->nIstVater && $Artikel->kVaterArtikel == 0}
                {block name='productdetails-basket-alert-choose'}
                    {alert variation="info" class="choose-variations"}
                        {lang key='chooseVariations' section='messages'}
                    {/alert}
                {/block}
            {elseif $Artikel->inWarenkorbLegbar == 1 }
                {if !$showMatrix}
                    {block name='productdetails-basket-form-inline'}
                        {row class="align-items-center"}
                            {col cols=12 sm=6 class="mb-3 mb-sm-0"}
                                {inputgroup id="quantity-grp" class="choose_quantity"}
                                    {input type="{if $Artikel->cTeilbar === 'Y' && $Artikel->fAbnahmeintervall == 0}text{else}number{/if}"
                                        min="{if $Artikel->fMindestbestellmenge}{$Artikel->fMindestbestellmenge}{else}0{/if}"
                                        required=($Artikel->fAbnahmeintervall > 0)
                                        step="{if $Artikel->fAbnahmeintervall > 0}{$Artikel->fAbnahmeintervall}{/if}"
                                        id="quantity" class="quantity" name="anzahl"
                                        aria=["label"=>"{lang key='quantity'}"]
                                        value="{if $Artikel->fAbnahmeintervall > 0 || $Artikel->fMindestbestellmenge > 1}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}"
                                        data=["decimals"=>{getDecimalLength quantity=$Artikel->fAbnahmeintervall}]
                                    }
                                    {if $Artikel->cEinheit}
                                        {inputgroupappend}
                                            {inputgrouptext class="unit form-control"}
                                                {$Artikel->cEinheit}
                                            {/inputgrouptext}
                                        {/inputgroupappend}
                                    {/if}
                                {/inputgroup}
                            {/col}
                            {col cols=12 sm=6}
                                {button aria=["label"=>"{lang key='addToCart'}"] block=true name="inWarenkorb" type="submit" value="{lang key='addToCart'}" variant="primary"}
                                    <span class="btn-basket-check">
                                        <span class="d-none d-sm-inline-block mr-1">{lang key='addToCart'}</span> <i class="fas fa-shopping-cart"></i>
                                    </span>
                                    <svg x="0px" y="0px" width="32px" height="32px" viewBox="0 0 32 32">
                                        <path stroke-dasharray="19.79 19.79" stroke-dashoffset="19.79" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="square" stroke-miterlimit="10" d="M9,17l3.9,3.9c0.1,0.1,0.2,0.1,0.3,0L23,11"/>
                                    </svg>
                                {/button}
                            {/col}
                        {/row}
                    {/block}
                {/if}
            {/if}
            {if $Artikel->inWarenkorbLegbar == 1
            && ($Artikel->fMindestbestellmenge > 1
                || ($Artikel->fMindestbestellmenge > 0 && $Artikel->cTeilbar === 'Y')
                || $Artikel->fAbnahmeintervall > 0
                || $Artikel->cTeilbar === 'Y'
                || $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|default:0 > 0)}
                {block name='productdetails-basket-alert-purchase-info'}
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
                        {if $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|default:0 > 0}
                            {lang key='maximalPurchase' section='productDetails' assign='maximalPurchase'}
                            <p>{$maximalPurchase|replace:"%d":$Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|replace:"%s":$units}</p>
                        {/if}
                    {/alert}
                {/block}
            {/if}
        </div>
    {/if}
{/block}
