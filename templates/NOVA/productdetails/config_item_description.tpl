{block name='productdetails-config-item-description'}
    <div class="cfg-item-description">
        <div class="d-flex align-items-center">
            {include file='snippets/image.tpl' class="mr-2" item=$oItem->getArtikel() square=false fluid=false width=60 height='auto' srcSize='sm' sizes="15vw" alt=$oItem->getName()}
            <dl>
                <dt>{$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                    {if JTL\Session\Frontend::getCustomerGroup()->mayViewPrices()}
                        {badge variant="light"}
                            {if $oItem->hasRabatt() && $oItem->showRabatt()}
                                <span class="discount">{$oItem->getRabattLocalized()} {lang key='discount'}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                <span class="additional">{$oItem->getZuschlagLocalized()} {lang key='additionalCharge'}</span>
                            {/if}
                            {$oItem->getPreisLocalized()}
                        {/badge}
                    {/if}
                </dt>
                <dd class="text-muted-util">
                    {if !empty($cBeschreibung)}
                        {$cBeschreibung}
                    {/if}
                </dd>
            </dl>
        </div>
        {if $oItem->getMin() == $oItem->getMax()}
            {lang key='quantity'}: {$oItem->getInitial()}
        {else}
            {inputgroup class="form-counter"}
                {inputgroupprepend}
                    {button variant=""
                        data=["count-down"=>""]
                        size="{if $device->isMobile()}sm{/if}"
                        aria=["label"=>{lang key='decreaseQuantity' section='aria'}]
                    }
                        <span class="fas fa-minus"></span>
                    {/button}
                {/inputgroupprepend}
                {input
                    type="number"
                    min="{$oItem->getMin()}"
                    max="{$oItem->getMax()}"
                    step="{if $oItem->getArtikel()->cTeilbar === 'Y' && $oItem->getArtikel()->fAbnahmeintervall == 0}any{elseif $oItem->getArtikel()->fAbnahmeintervall > 0}{$oItem->getArtikel()->fAbnahmeintervall}{else}1{/if}"
                    id="quantity{$oItem->getKonfigitem()}"
                    class="quantity"
                    name="item_quantity[{$kKonfigitem}]"
                    autocomplete="off"
                    value="{if !empty($nKonfigitemAnzahl_arr[$kKonfigitem])}{$nKonfigitemAnzahl_arr[$kKonfigitem]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}{if ($oItem->getInitial()>0)}{$oItem->getInitial()}{else}{$oItem->getMin()}{/if}{/if}{/if}"
                }
                {inputgroupappend}
                    {button variant=""
                        data=["count-up"=>""]
                        size="{if $device->isMobile()}sm{/if}"
                        aria=["label"=>{lang key='increaseQuantity' section='aria'}]
                    }
                        <span class="fas fa-plus"></span>
                    {/button}
                {/inputgroupappend}
            {/inputgroup}
        {/if}
    </div>
{/block}