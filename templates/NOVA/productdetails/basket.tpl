{block name='productdetails-basket'}
    {if ($Artikel->inWarenkorbLegbar == 1 || $Artikel->nErscheinendesProdukt == 1) || $Artikel->Variationen}
        {$interval = $Artikel->fAbnahmeintervall|default:0}
        {$mbm = $Artikel->fMindestbestellmenge|default:0}
        <div id="add-to-cart" class="product-buy{if $Artikel->nErscheinendesProdukt} coming_soon{/if}">
            {if $Artikel->nErscheinendesProdukt}
                {block name='productdetails-basket-coming-soon'}
                    <div class="{if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'}alert alert-warning coming_soon{/if} text-center-util">
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
                        {row class="basket-form-inline"}
                            {$basketColWidth = 6}
                            {if $Artikel->bHasKonfig}
                                {$basketColWidth = 12}
                            {/if}
                            {if $Artikel->Preise->fVKNetto == 0 && isset($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_VOUCHER_FLEX])}
                                {block name='productdetails-basket-voucher-flex'}
                                    {col cols=12 sm=$basketColWidth}
                                        {inputgroup}
                                            {input type="number"
                                                step=".01"
                                                value="{if isset($voucherPrice)}{$voucherPrice}{/if}"
                                                name="{$smarty.const.FKT_ATTRIBUT_VOUCHER_FLEX}Value"
                                                required=true
                                                placeholder="{lang key='voucherFlexPlaceholder' section='productDetails' printf=$smarty.session.Waehrung->getName()}"
                                                class="font-weight-bolder border-right-0"}
                                            {inputgroupappend}
                                                {inputgrouptext class="form-control font-weight-bolder bg-transparent"}
                                                    {JTL\Session\Frontend::getCurrency()->getName()}
                                                {/inputgrouptext}
                                            {/inputgroupappend}
                                        {/inputgroup}
                                    {/col}
                                    {input type="hidden" id="quantity" class="quantity" name="anzahl" value="1"}
                                {/block}
                            {else}
                            {block name='productdetails-basket-quantity'}
                                {col cols=12 sm=$basketColWidth}
                                    {inputgroup id="quantity-grp" class="form-counter choose_quantity"}
                                        {inputgroupprepend}
                                            {button variant=""
                                                data=["count-down"=>""]
                                                aria=["label"=>{lang key='decreaseQuantity' section='aria'}]}
                                                <span class="fas fa-minus"></span>
                                            {/button}
                                        {/inputgroupprepend}
                                        {$step = 1}
                                        {if $Artikel->cTeilbar === 'Y' && $interval == 0}
                                            {$step = 'any'}
                                        {elseif $interval > 0}
                                            {$step = $interval}
                                        {/if}
                                        {$inputValue = 1}
                                        {if $interval > 0 || $mbm > 1}
                                            {$inputValue = max($mbm,$interval)}
                                        {elseif isset($fAnzahl)}
                                            {$inputValue = $fAnzahl}
                                        {/if}
                                        {$pid = $Artikel->kVariKindArtikel|default:$Artikel->kArtikel}
                                        {input type="number"
                                            min=$mbm
                                            max=$Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|default:''
                                            required=($interval > 0)
                                            step=$step
                                            id="quantity" class="quantity" name="anzahl"
                                            aria=["label"=>"{lang key='quantity'}"]
                                            value=$inputValue
                                            data=[
                                                "decimals"=>{getDecimalLength quantity=$interval},
                                                "product-id"=>"{$pid}"
                                            ]
                                        }
                                        {inputgroupappend}
                                            {if $Artikel->cEinheit}
                                                {inputgrouptext class="unit form-control"}
                                                    {$Artikel->cEinheit}
                                                {/inputgrouptext}
                                            {/if}
                                            {button variant=""
                                                data=["count-up"=>""]
                                                aria=["label"=>{lang key='increaseQuantity' section='aria'}]}
                                                <span class="fas fa-plus"></span>
                                            {/button}
                                        {/inputgroupappend}
                                    {/inputgroup}
                                {/col}
                            {/block}
                            {/if}
                            {block name='productdetails-basket-add-to-cart'}
                                {col cols=12 sm=$basketColWidth}
                                    {button aria=["label"=>"{lang key='addToCart'}"]
                                        block=true name="inWarenkorb"
                                        type="submit"
                                        value="{lang key='addToCart'}"
                                        variant="primary"
                                        disabled=$Artikel->bHasKonfig && !$isConfigCorrect|default:false
                                        class="js-cfg-validate"}
                                        <span class="btn-basket-check">
                                            <span>
                                                {if isset($kEditKonfig)}
                                                    {lang key='applyChanges'}
                                                {else}
                                                    {lang key='addToCart'}
                                                {/if}
                                            </span> <i class="fas fa-shopping-cart"></i>
                                        </span>
                                        <svg x="0px" y="0px" width="32px" height="32px" viewBox="0 0 32 32">
                                            <path stroke-dasharray="19.79 19.79" stroke-dashoffset="19.79" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="square" stroke-miterlimit="10" d="M9,17l3.9,3.9c0.1,0.1,0.2,0.1,0.3,0L23,11"/>
                                        </svg>
                                    {/button}
                                    {if isset($kEditKonfig)}
                                        <input type="hidden" name="kEditKonfig" value="{$kEditKonfig}"/>
                                    {/if}
                                {/col}
                            {/block}
                        {/row}
                    {/block}
                {/if}
            {/if}
            {if $Artikel->inWarenkorbLegbar == 1
            && ($mbm > 1
                || ($interval > 0 && $Einstellungen.artikeldetails.artikeldetails_artikelintervall_anzeigen === 'Y')
                || $Artikel->cTeilbar === 'Y'
                || $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|default:0 > 0)}
                {block name='productdetails-basket-alert-purchase-info'}
                    {alert variant="info" class="purchase-info"}
                        {assign var=units value=$Artikel->cEinheit}
                        {if empty($Artikel->cEinheit) || $Artikel->cEinheit|strlen == 0}
                            <p>{lang key='units' section='productDetails' assign='units'}</p>
                        {/if}

                        {if $mbm > 1 || ($mbm > 0 && $Artikel->cTeilbar === 'Y')}
                            {lang key='minimumPurchase' section='productDetails' assign='minimumPurchase'}
                            <p>{$minimumPurchase|replace:"%d":$mbm|replace:"%s":$units}</p>
                        {/if}

                        {if $interval > 0}
                            {lang key='takeHeedOfInterval' section='productDetails' assign='takeHeedOfInterval'}
                            <p id="intervall-notice" {if $Einstellungen.artikeldetails.artikeldetails_artikelintervall_anzeigen !== 'Y'}class="d-none"{/if}>{$takeHeedOfInterval|replace:"%d":$interval|replace:"%s":$units}</p>
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
