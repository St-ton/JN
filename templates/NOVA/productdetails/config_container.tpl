{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-config-container'}
    {block name='productdetails-config-container-main'}
        {col cols=12}
            <div id="cfg-container" class="mb-5">
                <div id="cfg-top"></div>
                {block name='productdetails-config-container-groups'}
                {foreach $Artikel->oKonfig_arr as $oGruppe}
                    {if $oGruppe->getItemCount() > 0}
                        {$oSprache = $oGruppe->getSprache()}
                        {$cBildPfad = $oGruppe->getBildPfad()}
                        {$kKonfiggruppe = $oGruppe->getKonfiggruppe()}
                        <div class="cfg-group mb-4" data-id="{$kKonfiggruppe}">
                            <div class="hr-sect mt-5 mb-0">
                                {button
                                    id="crd-hdr-{$oGruppe@iteration}"
                                    variant="link"
                                    data=["toggle"=>"collapse","target"=>"#cfg-grp-cllps-{$kKonfiggruppe}"]
                                    class="text-left text-decoration-none"}
                                    {$oSprache->getName()}{if $oGruppe->getMin() == 0}<span class="optional"> - {lang key='optional'}</span>{/if}
                                {/button}
                            </div>

                            {collapse visible=true id="cfg-grp-cllps-{$kKonfiggruppe}" aria=["labelledby"=>"crd-hdr-{$oGruppe@iteration}"]}
                                <div class="text-center mb-5">
                                    {badge variant="light"}
                                        {if $oGruppe->getMin() === 1 && $oGruppe->getMax() === 1}
                                            {lang key='configChooseOneComponent' section='productDetails'}
                                        {else}
                                            {if !empty($oGruppe->getMin())}{lang key='configChooseMinComponents' section='productDetails' printf=$oGruppe->getMin()}{/if}{if $oGruppe->getMax()<$oGruppe->getItemCount()}, {lang key='configChooseMaxComponents' section='productDetails' printf=$oGruppe->getMax()}{/if}
                                        {/if}
                                    {/badge}
                                </div>
                                {block name='productdetails-config-container-group-description'}
                                    {row class="group-description mb-3"}
                                        {if !empty($aKonfigerror_arr[$kKonfiggruppe])}
                                            {col cols=12}
                                                {alert variant="danger"}
                                                   {$aKonfigerror_arr[$kKonfiggruppe]}
                                                {/alert}
                                            {/col}
                                        {/if}
                                        {if $oSprache->hatBeschreibung()}
                                            {col cols=12 lg="{if !empty($cBildPfad)}8{else}12{/if}" order=1 order-lg=0}
                                                <p class="desc">{$oSprache->getBeschreibung()}</p>
                                            {/col}
                                        {/if}
                                        {if !empty($cBildPfad)}
                                            {col cols=12 lg="{if $oSprache->hatBeschreibung()}4{else}12{/if}" order=0 order-lg=1}
                                                {image id="img{$kKonfiggruppe}" fluid=true fluid-grow=true lazy=true
                                                    src=$cBildPfad
                                                    alt=$oSprache->getName()
                                                }
                                            {/col}
                                        {/if}
                                    {/row}
                                {/block}

                                {block name='productdetails-config-container-group-items'}
                                    {row class="form-group"}
                                        {if $oGruppe->getAnzeigeTyp() == $smarty.const.KONFIG_ANZEIGE_TYP_CHECKBOX
                                        || $oGruppe->getAnzeigeTyp() == $smarty.const.KONFIG_ANZEIGE_TYP_RADIO
                                        || $oGruppe->getAnzeigeTyp() == $smarty.const.KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}
                                            {block name='productdetails-config-container-group-item-type-swatch'}
                                                {foreach $oGruppe->oItem_arr as $oItem}
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

                                                        {if $oGruppe->getAnzeigeTyp() == $smarty.const.KONFIG_ANZEIGE_TYP_RADIO}
                                                            {radio name="item[{$kKonfiggruppe}][]"
                                                                value=$oItem->getKonfigitem()
                                                                disabled=empty($bSelectable)
                                                                data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                                checked=$checkboxActive
                                                                id="item{$oItem->getKonfigitem()}"
                                                                class="cfg-swatch"
                                                                required=$oItem@first && $oGruppe->getMin() > 0
                                                            }
                                                                <div data-id="{$oItem->getKonfigitem()}" class="config-item text-center mb-5{if $oItem->getEmpfohlen()} bg-info{/if}{if empty($bSelectable)} disabled{/if}{if $checkboxActive} active{/if}">

                                                                    {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                                        <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                                    {/if}
                                                                    {badge class="badge-circle"}<i class="fas fa-check mx-auto"></i>{/badge}
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
                                                                    <p class="mb-2">
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

                                                                    {if !empty($cBeschreibung)}
                                                                        <div class="mb-2">
                                                                            {button
                                                                                id="desc_link_{$kKonfigitem}"
                                                                                variant="link"
                                                                                data=["toggle"=>"collapse","target"=>"#desc_{$kKonfigitem}"]
                                                                                size="sm"
                                                                                role="button"
                                                                                aria=["expanded"=>"false", "controls"=>"desc_{$kKonfigitem}"]
                                                                            }
                                                                                {lang key='showDescription'}
                                                                            {/button}
                                                                            {collapse visible=false id="desc_{$kKonfigitem}" aria=["labelledby"=>"#desc_link_{$kKonfigitem}"]}
                                                                                {$cBeschreibung}
                                                                            {/collapse}
                                                                        </div>
                                                                    {/if}

                                                                    {if $oItem->getMin() == $oItem->getMax()}
                                                                        {lang key='quantity'}: {$oItem->getInitial()}
                                                                    {else}
                                                                        {input
                                                                            type="{if $oItem->getArtikel()->cTeilbar === 'Y' && $oItem->getArtikel()->fAbnahmeintervall == 0}text{else}number{/if}"
                                                                            min="{$oItem->getMin()}"
                                                                            max="{$oItem->getMax()}"
                                                                            step="{if $oItem->getArtikel()->fAbnahmeintervall > 0}{$oItem->getArtikel()->fAbnahmeintervall}{/if}"
                                                                            id="quantity{$oItem->getKonfigitem()}"
                                                                            class="quantity"
                                                                            name="item_quantity[{$kKonfigitem}]"
                                                                            autocomplete="off"
                                                                            value="{if !empty($nKonfigitemAnzahl_arr[$kKonfigitem])}{$nKonfigitemAnzahl_arr[$kKonfigitem]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}1{/if}{/if}"
                                                                        }
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
                                                                <div data-id="$oItem->getKonfigitem()" class="config-item text-center mb-5{if $oItem->getEmpfohlen()} bg-info{/if}{if empty($bSelectable)} disabled{/if}{if $checkboxActive} active{/if}">
                                                                    {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                                        <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                                    {/if}
                                                                    {badge class="badge-circle"}<i class="fas fa-check mx-auto"></i>{/badge}
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
                                                                    <p class="mb-2">
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

                                                                    {if !empty($cBeschreibung)}
                                                                        <div class="mb-2">
                                                                            {button
                                                                                id="desc_link_{$kKonfigitem}"
                                                                                variant="link"
                                                                                data=["toggle"=>"collapse","target"=>"#desc_{$kKonfigitem}"]
                                                                                size="sm"
                                                                                role="button"
                                                                                aria=["expanded"=>"false", "controls"=>"desc_{$kKonfigitem}"]
                                                                            }
                                                                                {lang key='showDescription'}
                                                                            {/button}
                                                                            {collapse visible=false id="desc_{$kKonfigitem}" aria=["labelledby"=>"#desc_link_{$kKonfigitem}"]}
                                                                                {$cBeschreibung}
                                                                            {/collapse}
                                                                        </div>
                                                                    {/if}

                                                                    {if $oItem->getMin() == $oItem->getMax()}
                                                                        {lang key='quantity'}: {$oItem->getInitial()}
                                                                    {else}
                                                                        {input
                                                                            type="{if $oItem->getArtikel()->cTeilbar === 'Y' && $oItem->getArtikel()->fAbnahmeintervall == 0}text{else}number{/if}"
                                                                            min="{$oItem->getMin()}"
                                                                            max="{$oItem->getMax()}"
                                                                            step="{if $oItem->getArtikel()->fAbnahmeintervall > 0}{$oItem->getArtikel()->fAbnahmeintervall}{/if}"
                                                                            id="quantity{$oItem->getKonfigitem()}"
                                                                            class="quantity"
                                                                            name="item_quantity[{$kKonfigitem}]"
                                                                            autocomplete="off"
                                                                            value="{if !empty($nKonfigitemAnzahl_arr[$kKonfigitem])}{$nKonfigitemAnzahl_arr[$kKonfigitem]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}1{/if}{/if}"
                                                                        }
                                                                    {/if}
                                                                </div>
                                                            {/checkbox}
                                                        {/if}
                                                    {/col}
                                                {/foreach}
                                            {/block}
                                        {elseif $oGruppe->getAnzeigeTyp() == $smarty.const.KONFIG_ANZEIGE_TYP_DROPDOWN}
                                            {block name='productdetails-config-container-group-item-type-dropdown'}
                                                {col cols=12 md=3 data=["id"=>$kKonfiggruppe] class="mb-3"}
                                                    {formgroup}
                                                        {select name="item[{$kKonfiggruppe}][]"
                                                            data=["ref"=>$kKonfiggruppe]
                                                            required=$oGruppe->getMin() > 0
                                                            aria=["label"=>$oSprache->getName()]
                                                            class='custom-select'
                                                        }
                                                            <option value="">{lang key='pleaseChoose'}</option>
                                                            {foreach $oGruppe->oItem_arr as $oItem}
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
                                                    {foreach $oGruppe->oItem_arr as $oItem}
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
                                                            <p class="mb-2 d-none d-md-block">
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
                                                                        {input
                                                                            type="{if $oItem->getArtikel()->cTeilbar === 'Y' && $oItem->getArtikel()->fAbnahmeintervall == 0}text{else}number{/if}"
                                                                            min="{$oItem->getMin()}"
                                                                            max="{$oItem->getMax()}"
                                                                            step="{if $oItem->getArtikel()->fAbnahmeintervall > 0}{$oItem->getArtikel()->fAbnahmeintervall}{/if}"
                                                                            id="quantity{$oItem->getKonfigitem()}"
                                                                            class="quantity"
                                                                            name="item_quantity[{$oItem->getKonfigitem()}]"
                                                                            autocomplete="off"
                                                                            value="{if !empty($nKonfigitemAnzahl_arr[$oItem->getKonfigitem()])}{$nKonfigitemAnzahl_arr[$oItem->getKonfigitem()]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}1{/if}{/if}"
                                                                        }
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
                            {/collapse}
                        </div>
                    {/if}
                {/foreach}
                {/block}
            </div>
            {link variant="light" href="#cfg-top" class="float-right m-2 btn btn-link"
                title="{lang key='goTop'}"
                aria=["label"=>{lang key='goTop'}]
            }
                <i class="fas fa-angle-double-up"></i>
            {/link}
        {/col}
    {/block}
    {block name='productdetails-config-container-sticky-sidebar'}
        {col cols=12 class="mb-6"}
            <div id="cfg-sticky-sidebar" class="mb-4">
                {if $Artikel->bHasKonfig}
                    {block name='productdetails-config-container-include-config-sidebar'}
                        {include file='productdetails/config_sidebar.tpl'}
                    {/block}
                {/if}
            </div>
            {row}
                {col cols=12 md=6 offset-md=6}
                    {block name='productdetails-config-container-include-basket'}
                        <div class="mt-3">
                            {include file='productdetails/basket.tpl'}
                        </div>
                    {/block}
                {/col}
            {/row}
        {/col}
    {/block}
{/block}
