{block name='productdetails-config-options'}
    <div id="cfg-accordion" class="accordion">
        {foreach $Artikel->oKonfig_arr as $configGroup}
            {if $configGroup->getItemCount() > 0}
                {$configLocalization = $configGroup->getSprache()}
                {$configImagePath = $configGroup->getImage(\JTL\Media\Image::SIZE_XS)}
                {$kKonfiggruppe = $configGroup->getKonfiggruppe()}
                <div class="cfg-group js-cfg-group {if $configGroup@first}visited{/if}" data-id="{$kKonfiggruppe}">
                    <div class="hr-sect mb-0">
                        <span class="d-none js-group-checked"><i class="far fa-check-square"></i></span>
                        <span><i class="far fa-square"></i></span>
                        {button
                            id="crd-hdr-{$configGroup@iteration}"
                            variant="link"
                            data=["toggle"=>"collapse","target"=>"#cfg-grp-cllps-{$configGroup@iteration}"]
                            class="text-left text-decoration-none"
                            disabled=!$configGroup@first
                        }
                            {$configLocalization->getName()}
                        {/button}
                    </div>

                    {collapse visible=$configGroup@first
                        id="cfg-grp-cllps-{$configGroup@iteration}"
                        aria=["labelledby"=>"crd-hdr-{$configGroup@iteration}"]
                        data=["parent"=>"#cfg-accordion"]
                        class="js-cfg-group-collapse"}
                        <div class="text-center mb-5 sticky-top">
                            {if !empty($configGroup->getMin()) || !empty($configGroup->getMax())}
                                {badge variant="info" class="js-group-badge-checked"}
                                    {if $configGroup->getMin() === 1 && $configGroup->getMax() === 1}
                                        {lang key='configChooseOneComponent' section='productDetails'}
                                    {elseif $configGroup->getMin() === $configGroup->getMax()}
                                        {lang key='configChooseNumberComponents' section='productDetails' printf=$configGroup->getMin()}
                                    {elseif !empty($configGroup->getMin()) && $configGroup->getMax()<$configGroup->getItemCount()}
                                        {lang key='configChooseMinMaxComponents' section='productDetails' printf=$configGroup->getMin()|cat:':::'|cat:$configGroup->getMax()}
                                    {elseif !empty($configGroup->getMin())}
                                            {lang key='configChooseMinComponents' section='productDetails' printf=$configGroup->getMin()}
                                    {elseif $configGroup->getMax()<$configGroup->getItemCount()}
                                        {lang key='configChooseMaxComponents' section='productDetails' printf=$configGroup->getMax()}
                                    {else}
                                        {lang key='optional'}
                                    {/if}
                                {/badge}
                            {elseif $configGroup->getMin() == 0}
                                {badge variant="info" class="js-group-badge-checked"}{lang key='optional'}{/badge}
                            {/if}
                        </div>
                    {block name='productdetails-config-container-group-description'}
                        {alert variant="danger" class="js-cfg-group-error" data=["id"=>"{$kKonfiggruppe}"]}{/alert}
                    {/block}
                    {block name='productdetails-config-container-group-description'}
                        {row class="group-description mb-3"}
                            {if !empty($aKonfigerror_arr[$kKonfiggruppe])}
                                {col cols=12}
                                    {alert variant="danger"}
                                        {$aKonfigerror_arr[$kKonfiggruppe]}
                                    {/alert}
                                {/col}
                            {/if}
                            {if $configLocalization->hatBeschreibung()}
                                {col cols=12 lg="{if !empty($configImagePath) && $configImagePath|strpos:$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN === false}8{else}12{/if}" order=1 order-lg=0}
                                    <p class="desc">{$configLocalization->getBeschreibung()}</p>
                                {/col}
                            {/if}
                            {if !empty($configImagePath) && $configImagePath|strpos:$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN === false}
                                {col cols=12 lg=4 offset-lg="{if $configLocalization->hatBeschreibung()}0{else}4{/if}" order=0 order-lg=1}
                                    {image fluid=true lazy=true webp=true
                                        src=$configImagePath
                                        srcset="{$configGroup->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_konfiggruppe_mini_breite}w,
                                            {$configGroup->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_konfiggruppe_klein_breite}w,
                                            {$configGroup->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_konfiggruppe_normal_breite}w,
                                            {$configGroup->getImage(\JTL\Media\Image::SIZE_LG)} {$Einstellungen.bilder.bilder_konfiggruppe_gross_breite}w"
                                        alt=$configLocalization->getName()
                                        sizes="auto"
                                    }
                                {/col}
                            {/if}
                        {/row}
                    {/block}

                    {block name='productdetails-config-container-group-items'}
                        {row class="form-group"}
                        {$viewType = $configGroup->getAnzeigeTyp()}
                        {if $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_CHECKBOX
                        || $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_RADIO
                        || $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}
                            {block name='productdetails-config-container-group-item-type-swatch'}
                                {foreach $configGroup->oItem_arr as $oItem}
                                    {col cols=6 md=4 lg=3}
                                        {$bSelectable = 0}
                                        {if $oItem->isInStock()}
                                            {$bSelectable = 1}
                                        {/if}
                                        {$kKonfigitem = $oItem->getKonfigitem()}
                                        {$checkboxActive = (!empty($aKonfigerror_arr)
                                            && isset($smarty.post.item)
                                            && isset($smarty.post.item[$kKonfiggruppe])
                                            && $oItem->getKonfigitem()|in_array:$smarty.post.item[$kKonfiggruppe])
                                            || ($oItem->getSelektiert()
                                                && (!isset($aKonfigerror_arr)
                                                    || !$aKonfigerror_arr))}
                                        {$cKurzBeschreibung = $oItem->getKurzBeschreibung()}
                                        {$cBeschreibung = $oItem->getBeschreibung()}
                                        {if !empty($cKurzBeschreibung)}
                                            {$cBeschreibung = $cKurzBeschreibung}
                                        {/if}

                                        {if $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_RADIO}
                                            {radio name="item[{$kKonfiggruppe}][]"
                                                value=$oItem->getKonfigitem()
                                                disabled=empty($bSelectable)
                                                data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                checked=$checkboxActive
                                                id="item{$oItem->getKonfigitem()}"
                                                class="cfg-swatch"
                                                required=$oItem@first && $configGroup->getMin() > 0
                                            }
                                                <div data-id="{$oItem->getKonfigitem()}" class="config-item text-center mb-5{if $oItem->getEmpfohlen()} bg-info{/if}{if empty($bSelectable)} disabled{/if}{if $checkboxActive} active{/if}">
                                                    {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                        <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                    {/if}
                                                    {badge class="badge-circle circle-small"}<i class="fas fa-check mx-auto"></i>{/badge}
                                                    {if !empty($oItem->getArtikel()->Bilder[0]->cURLNormal)}
                                                        {$productImage = $oItem->getArtikel()->Bilder[0]}
                                                        {image fluid-grow=true webp=true lazy=true
                                                            src=$productImage->cURLMini
                                                            srcset="{$productImage->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                                {$productImage->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                                {$productImage->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w,
                                                                {$productImage->cURLGross} {$Einstellungen.bilder.bilder_artikel_gross_breite}w"
                                                            sizes="255px"
                                                            alt=$oItem->getName()
                                                        }
                                                    {/if}
                                                    <p class="my-2 cfg-item-description">
                                                        {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                                                        {if $smarty.session.Kundengruppe->mayViewPrices()}
                                                            {badge variant="light"}
                                                            {if $oItem->hasRabatt() && $oItem->showRabatt()}
                                                                <span class="discount">{$oItem->getRabattLocalized()} {lang key='discount'}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                                                <span class="additional">{$oItem->getZuschlagLocalized()} {lang key='additionalCharge'}</span>
                                                            {/if}
                                                            {$oItem->getPreisLocalized()}
                                                            {/badge}
                                                        {/if}
                                                        {if !empty($cBeschreibung)}
                                                            {link title=$cBeschreibung|escape:"html" data=["toggle"=>"tooltip", "html"=>"true"]}
                                                                <i class="fas fa-question-circle"></i>
                                                            {/link}
                                                        {/if}
                                                    </p>

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
                                                                type="{if $oItem->getArtikel()->cTeilbar === 'Y' && $oItem->getArtikel()->fAbnahmeintervall == 0}text{else}number{/if}"
                                                                min="{$oItem->getMin()}"
                                                                max="{$oItem->getMax()}"
                                                                step="{if $oItem->getArtikel()->fAbnahmeintervall > 0}{$oItem->getArtikel()->fAbnahmeintervall}{/if}"
                                                                id="quantity{$oItem->getKonfigitem()}"
                                                                class="quantity"
                                                                name="item_quantity[{$kKonfigitem}]"
                                                                autocomplete="off"
                                                                value="{if !empty($nKonfigitemAnzahl_arr[$kKonfigitem])}{$nKonfigitemAnzahl_arr[$kKonfigitem]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}{$oItem->getMin()}{/if}{/if}"
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
                                            {/radio}
                                        {else}
                                            {checkbox name="item[{$kKonfiggruppe}][]"
                                                value=$oItem->getKonfigitem()
                                                disabled=empty($bSelectable)
                                                data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                checked=$checkboxActive
                                                id="item{$oItem->getKonfigitem()}"
                                                class="cfg-swatch"
                                            }
                                                <div data-id="$oItem->getKonfigitem()" class="config-item mb-5{if $oItem->getEmpfohlen()} bg-info{/if}{if empty($bSelectable)} disabled{/if}{if $checkboxActive} active{/if}">
                                                    {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                        <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                    {/if}
                                                    {badge class="badge-circle circle-small"}<i class="fas fa-check mx-auto"></i>{/badge}
                                                    {if !empty($oItem->getArtikel()->Bilder[0]->cURLNormal)}
                                                        <p>
                                                            {$productImage = $oItem->getArtikel()->Bilder[0]}
                                                            {image fluid-grow=true webp=true lazy=true
                                                                src=$productImage->cURLMini
                                                                srcset="{$productImage->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                                    {$productImage->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                                    {$productImage->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w,
                                                                    {$productImage->cURLGross} {$Einstellungen.bilder.bilder_artikel_gross_breite}w"
                                                                sizes="255px"
                                                                alt=$oItem->getName()
                                                            }
                                                        </p>
                                                    {/if}
                                                    <p class="mb-2 cfg-item-description">
                                                        {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                                                        {if $smarty.session.Kundengruppe->mayViewPrices()}
                                                            {badge variant="light"}
                                                                {if $oItem->hasRabatt() && $oItem->showRabatt()}
                                                                    <span class="discount">{$oItem->getRabattLocalized()} {lang key='discount'}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                                                    <span class="additional">{$oItem->getZuschlagLocalized()} {lang key='additionalCharge'}</span>
                                                                {/if}
                                                                {$oItem->getPreisLocalized()}
                                                            {/badge}
                                                        {/if}
                                                        {if !empty($cBeschreibung)}
                                                            {link title=$cBeschreibung|escape:"html" data=["toggle"=>"tooltip", "html"=>"true"]}
                                                                <i class="fas fa-question-circle"></i>
                                                            {/link}
                                                        {/if}
                                                    </p>

                                                    {if $oItem->getMin() == $oItem->getMax()}
                                                        {lang key='quantity'}: {$oItem->getInitial()}
                                                    {else}
                                                        {inputgroup class="form-counter"}
                                                            {inputgroupprepend}
                                                                {button variant=""
                                                                    data=["count-down"=>""]
                                                                    disabled=empty($bSelectable)
                                                                    size="{if $device->isMobile()}sm{/if}"
                                                                    aria=["label"=>{lang key='decreaseQuantity' section='aria'}]
                                                                }
                                                                    <span class="fas fa-minus"></span>
                                                                {/button}
                                                            {/inputgroupprepend}
                                                            {input
                                                                type="{if $oItem->getArtikel()->cTeilbar === 'Y' && $oItem->getArtikel()->fAbnahmeintervall == 0}text{else}number{/if}"
                                                                min="{$oItem->getMin()}"
                                                                max="{$oItem->getMax()}"
                                                                step="{if $oItem->getArtikel()->fAbnahmeintervall > 0}{$oItem->getArtikel()->fAbnahmeintervall}{/if}"
                                                                id="quantity{$oItem->getKonfigitem()}"
                                                                class="quantity"
                                                                name="item_quantity[{$kKonfigitem}]"
                                                                autocomplete="off"
                                                                value="{if !empty($nKonfigitemAnzahl_arr[$kKonfigitem])}{$nKonfigitemAnzahl_arr[$kKonfigitem]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}{$oItem->getMin()}{/if}{/if}"
                                                                disabled=empty($bSelectable)
                                                            }
                                                            {inputgroupappend}
                                                                {button variant=""
                                                                    data=["count-up"=>""]
                                                                    disabled=empty($bSelectable)
                                                                    size="{if $device->isMobile()}sm{/if}"
                                                                    aria=["label"=>{lang key='increaseQuantity' section='aria'}]
                                                                }
                                                                    <span class="fas fa-plus"></span>
                                                                {/button}
                                                            {/inputgroupappend}
                                                        {/inputgroup}
                                                    {/if}
                                                </div>
                                            {/checkbox}
                                        {/if}
                                    {/col}
                                {/foreach}
                            {/block}
                        {elseif $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_DROPDOWN}
                            {block name='productdetails-config-container-group-item-type-dropdown'}
                                {col cols=12 data=["id"=>$kKonfiggruppe] class="mb-3"}
                                    {formgroup}
                                        {select name="item[{$kKonfiggruppe}][]"
                                            data=["ref"=>$kKonfiggruppe]
                                            required=$configGroup->getMin() > 0
                                            aria=["label"=>$configLocalization->getName()]
                                            class='custom-select'
                                        }
                                            <option value="">{lang key='pleaseChoose'}</option>
                                        {foreach $configGroup->oItem_arr as $oItem}
                                            {$bSelectable = 0}
                                            {if $oItem->isInStock()}
                                                {$bSelectable = 1}
                                            {/if}
                                            <option value="{$oItem->getKonfigitem()}"
                                                    id="item{$oItem->getKonfigitem()}"
                                                    {if empty($bSelectable)} disabled{/if}
                                                    {if isset($nKonfigitem_arr)} data-selected="{if in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}true{else}false{/if}"
                                                    {else}{if $oItem->getSelektiert() && (!isset($aKonfigerror_arr) || !$aKonfigerror_arr)}selected="selected"{/if}{/if}>
                                                {$oItem->getName()}{if empty($bSelectable)} - {lang section='productDetails' key='productOutOfStock'}{/if}
                                                {if $smarty.session.Kundengruppe->mayViewPrices()}
                                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                                    {if $oItem->hasRabatt() && $oItem->showRabatt()}({$oItem->getRabattLocalized()} {lang key='discount'})&nbsp;{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}({$oItem->getZuschlagLocalized()} {lang key='additionalCharge'})&nbsp;{/if}
                                                    {$oItem->getPreisLocalized()}
                                                {/if}
                                            </option>
                                        {/foreach}
                                        {/select}
                                    {/formgroup}
                                {/col}
                                {col}
                                {foreach $configGroup->oItem_arr as $oItem}
                                    {$bSelectable = 0}
                                    {if $oItem->isInStock()}
                                        {$bSelectable = 1}
                                    {/if}
                                    {$cKurzBeschreibung = $oItem->getKurzBeschreibung()}
                                    {$cBeschreibung = $oItem->getBeschreibung()}
                                    {if !empty($cKurzBeschreibung)}
                                        {$cBeschreibung = $cKurzBeschreibung}
                                    {/if}
                                    {collapse visible=isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr) id="drpdwn_qnt_{$oItem->getKonfigitem()}" class="cfg-drpdwn-item"}
                                        {row}
                                            {col cols=4}
                                                {if !empty($oItem->getArtikel()->Bilder[0]->cURLNormal)}
                                                    <p>
                                                        {$productImage = $oItem->getArtikel()->Bilder[0]}
                                                        {image fluid-grow=true webp=true lazy=true
                                                            src=$productImage->cURLMini
                                                            srcset="{$productImage->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                                {$productImage->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                                {$productImage->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w,
                                                                {$productImage->cURLGross} {$Einstellungen.bilder.bilder_artikel_gross_breite}w"
                                                            sizes="255px"
                                                            alt=$oItem->getName()
                                                        }
                                                    </p>
                                                {/if}
                                                <p class="mb-2 cfg-item-description">
                                                    {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                                                    {if $smarty.session.Kundengruppe->mayViewPrices()}
                                                        {badge variant="light"}
                                                            {if $oItem->hasRabatt() && $oItem->showRabatt()}
                                                                <span class="discount">{$oItem->getRabattLocalized()} {lang key='discount'}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                                                <span class="additional">{$oItem->getZuschlagLocalized()} {lang key='additionalCharge'}</span>
                                                            {/if}
                                                            {$oItem->getPreisLocalized()}
                                                        {/badge}
                                                    {/if}
                                                </p>
                                            {/col}
                                            {col cols=8}
                                                {if !empty($cBeschreibung)}
                                                    <div class="mb-2">
                                                        {$cBeschreibung}
                                                    </div>
                                                {/if}

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
                                                            type="{if $oItem->getArtikel()->cTeilbar === 'Y' && $oItem->getArtikel()->fAbnahmeintervall == 0}text{else}number{/if}"
                                                            min="{$oItem->getMin()}"
                                                            max="{$oItem->getMax()}"
                                                            step="{if $oItem->getArtikel()->fAbnahmeintervall > 0}{$oItem->getArtikel()->fAbnahmeintervall}{/if}"
                                                            id="quantity{$oItem->getKonfigitem()}"
                                                            class="quantity"
                                                            name="item_quantity[{$oItem->getKonfigitem()}]"
                                                            autocomplete="off"
                                                            value="{if !empty($nKonfigitemAnzahl_arr[$oItem->getKonfigitem()])}{$nKonfigitemAnzahl_arr[$oItem->getKonfigitem()]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}{$oItem->getMin()}{/if}{/if}"
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
                                            {/col}
                                        {/row}
                                    {/collapse}
                                {/foreach}
                                {/col}
                            {/block}
                        {/if}
                        {/row}
                    {/block}
                        <div class="sticky-bottom">
                            {if $configGroup@last}
                                {nav}
                                    {navitem id="cfg-tab-summary-finish"
                                        class="m-auto"
                                        href="#cfg-tab-pane-summary"
                                        role="tab"
                                        router-data=["toggle"=>"pill"]
                                        router-aria=["controls"=>"cfg-tab-pane-summary", "selected"=>"false"]
                                        router-class="btn btn-secondary btn-sm"
                                        disabled=true
                                    }
                                        {lang key='finishConfiguration' section='productDetails'}
                                    {/navitem}
                                {/nav}
                            {else}
                                {button
                                    size="sm"
                                    variant="secondary"
                                    data=["toggle"=>"collapse","target"=>"#cfg-grp-cllps-{$configGroup@iteration + 1}"]
                                    class="m-auto js-cfg-next no-caret"}
                                    {lang key='nextConfigurationGroup' section='productDetails'} <i class="fas fa-arrow-right"></i>
                                {/button}
                            {/if}
                        </div>
                    {/collapse}
                </div>
            {/if}
        {/foreach}
    </div>
{/block}
