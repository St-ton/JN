{block name='productdetails-config-item-description'}
    {row class="cfg-item-description"}
        {col cols=12 lg=9 class="d-flex align-items-center"}
            {include file='snippets/image.tpl' class="mr-2" item=$oItem->getArtikel() square=false fluid=false width=60 height='auto' srcSize='sm' sizes="15vw" alt=$oItem->getName()}
            <dl>
                <dt>{if !empty($oItem->getArtikelKey())}
                        {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                        {if JTL\Session\Frontend::getCustomerGroup()->mayViewPrices()}
                            {badge variant="light" class="border-primary"}
                                {if $oItem->hasRabatt() && $oItem->showRabatt()}
                                    <span class="discount">{$oItem->getRabattLocalized()} {lang key='discount'}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                    <span class="additional">{$oItem->getZuschlagLocalized()} {lang key='additionalCharge'}</span>
                                {/if}
                                {$oItem->getPreisLocalized()}
                            {/badge}
                        {/if}
                    {else}
                        {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                        {if JTL\Session\Frontend::getCustomerGroup()->mayViewPrices()}
                            {badge variant="light" class="border-primary"}
                                {if $oItem->hasRabatt() && $oItem->showRabatt()}
                                    <span class="discount">{$oItem->getRabattLocalized()} {lang key='discount'}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                    <span class="additional">{$oItem->getZuschlagLocalized()} {lang key='additionalCharge'}</span>
                                {/if}
                                {$oItem->getPreisLocalized()}
                            {/badge}
                        {/if}
                    {/if}
                </dt>
                <dd class="text-muted-util">
                    {if !empty($oItem->getArtikelKey())}
                        {$Artikel = $oItem->getArtikel()}
                        <div class="price-note">
                            {* Grundpreis *}
                            {if !$oItem->hasRabatt() && !empty($Artikel->cLocalizedVPE)}
                                {$Artikel->cLocalizedVPE[$NettoPreise]}
                            {/if}
                        </div>
                    {/if}
                    {if !empty($cBeschreibung)}
                        <div class="cfg-item-description-description">
                            {$cBeschreibung}
                        </div>
                    {/if}
                    {block name='productdetails-config-item-description-detail-button'}
                        {if !empty($oItem->getArtikelKey())}
                            <div class="cfg-item-detail-button">
                                {badge variant="light" class="circle-small configpreview"
                                    data=["src"=>"{$oItem->getArtikel()->cURLFull}", "title"=>$oItem->getName()]
                                    title="{lang section='productDownloads' key='downloadPreview'} - {$oItem->getName()}"}
                                    <i class="fas fa-info-circle"></i> {lang section='productDetails' key='articledetails'}
                                {/badge}
                            </div>
                        {/if}
                    {/block}
                </dd>
            </dl>
        {/col}
        {col cols=12 lg=3 class="cfg-item-qty"}
            {if $oItem->getMin() == $oItem->getMax()}
                {lang key='quantity'}: {$oItem->getInitial()}
            {else}
                {inputgroup class="form-counter"}
                    {inputgroupprepend}
                        {button variant=""
                            data=["count-down"=>""]
                            size="{if $device->isMobile()}sm{/if}"
                            aria=["label"=>{lang key='decreaseQuantity' section='aria'}]
                            disabled=empty($bSelectable)
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
                        name="item_quantity[{$oItem->getKonfigitem()}]"
                        autocomplete="off"
                        value="{if !empty($nKonfigitemAnzahl_arr[$kKonfigitem])}{$nKonfigitemAnzahl_arr[$kKonfigitem]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}{if ($oItem->getInitial()>0)}{$oItem->getInitial()}{else}{$oItem->getMin()}{/if}{/if}{/if}"
                        disabled=empty($bSelectable)
                    }
                    {inputgroupappend}
                        {button variant=""
                            data=["count-up"=>""]
                            size="{if $device->isMobile()}sm{/if}"
                            aria=["label"=>{lang key='increaseQuantity' section='aria'}]
                            disabled=empty($bSelectable)
                        }
                            <span class="fas fa-plus"></span>
                        {/button}
                    {/inputgroupappend}
                {/inputgroup}
            {/if}
        {/col}
    {/row}
{/block}