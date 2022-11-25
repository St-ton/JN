{block name='productdetails-config-options'}
    {row class="cfg-group-gallery-wrapper"}
        {col cols=3 class="d-none d-md-block"}
            <nav id="cfg-nav" class="navbar navbar-light bg-light sticky-top">
                <nav class="nav nav-pills flex-column">
                    {foreach $Artikel->oKonfig_arr as $configGroup}
                        {$configLocalization = $configGroup->getSprache()}
                        <a id="cfg-nav-{$configGroup->getID()}" class="nav-link" href="#cfg-grp-{$configGroup->getID()}">{$configLocalization->getName()} <span class="d-none js-group-checked"><i class="fas fa-check text-success"></i></span></a>
                    {/foreach}
                </nav>
            </nav>
        {/col}
        {col cols=12 md=9 data=["spy"=>"scroll", "target"=>"#cfg-nav", "offset"=>"15"] class="cfg-options"}
            {foreach $Artikel->oKonfig_arr as $configGroup}
                {if $configGroup->getItemCount() > 0}
                    {$configLocalization = $configGroup->getSprache()}
                    {$configGroupHasImage = (strpos($configGroup->getImage(\JTL\Media\Image::SIZE_MD), $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN)) === false}
                    {$kKonfiggruppe = $configGroup->getKonfiggruppe()}
                    <div id="cfg-grp-{$configGroup->getID()}" class="cfg-group js-cfg-group mb-3 {if $configGroup@first}visited{/if}"
                         data-id="{$configGroup->getID()}">
                        {block name='productdetails-config-options-heading'}
                            <div class="hr-sect h3">
                                {$configLocalization->getName()}
                            </div>
                        {/block}
                        {block name='productdetails-config-options-collapse'}


                            {block name='productdetails-config-options-collapse-top'}
                                <div class="cfg-group-info sticky-top">
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
                            {/block}
                            {block name='productdetails-config-container-group-description'}
                                {alert variant="danger" class="js-cfg-group-error" data=["id"=>"{$kKonfiggruppe}"]}{/alert}
                            {/block}
                            {block name='productdetails-config-container-group-description'}
                                {row class="group-description"}
                                {if !empty($aKonfigerror_arr[$kKonfiggruppe])}
                                    {col cols=12}
                                        {alert variant="danger"}
                                            {$aKonfigerror_arr[$kKonfiggruppe]}
                                        {/alert}
                                    {/col}
                                {/if}
                                {if $configLocalization->hatBeschreibung()}
                                    {col cols=12 lg="{if $configGroupHasImage}8{else}12{/if}" order=1 order-lg=0}
                                        {$configLocalization->getBeschreibung()}
                                    {/col}
                                {/if}
                                {if $configGroupHasImage}
                                    {col cols=12 lg=4 offset-lg="{if $configLocalization->hatBeschreibung()}0{else}5{/if}" order=0 order-lg=1}
                                        {include file='snippets/image.tpl' item=$configGroup square=false}
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
                                            {$checkboxActive = (isset($nKonfigitem_arr)
                                                && in_array($oItem->getKonfigitem(), $nKonfigitem_arr))
                                                || (!empty($aKonfigerror_arr)
                                                && isset($smarty.post.item)
                                                && isset($smarty.post.item[$kKonfiggruppe])
                                                && in_array($oItem->getKonfigitem(), $smarty.post.item[$kKonfiggruppe]))
                                                || ($oItem->getSelektiert()
                                                && !isset($kEditKonfig)
                                                && (!isset($aKonfigerror_arr)
                                                || !$aKonfigerror_arr))}
                                            {$cKurzBeschreibung = $oItem->getKurzBeschreibung()}
                                            {$cBeschreibung = $oItem->getBeschreibung()}
                                            {if !empty($cKurzBeschreibung)}
                                                {$cBeschreibung = $cKurzBeschreibung}
                                            {/if}
                                            {block name='productdetails-config-container-group-item-type-swatch-option'}
                                                {if $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_RADIO}
                                                    {block name='productdetails-config-container-group-item-type-swatch-option-radio'}
                                                        {radio name="item[{$kKonfiggruppe}][]"
                                                            value=$oItem->getKonfigitem()
                                                            disabled=empty($bSelectable)
                                                            data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                            checked=$checkboxActive
                                                            id="item{$oItem->getKonfigitem()}"
                                                            class="cfg-swatch"
                                                            required=$oItem@first && $configGroup->getMin() > 0
                                                        }
                                                            <div data-id="{$oItem->getKonfigitem()}" class="config-item {if $oItem->getEmpfohlen()} bg-recommendation{/if}{if empty($bSelectable)} disabled{/if}{if $checkboxActive} active{/if}">
                                                                {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                                    <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                                {/if}
                                                                {badge class="badge-circle circle-small"}<i class="fas fa-check"></i>{/badge}
                                                                {include file='snippets/image.tpl' item=$oItem->getArtikel() srcSize='sm' alt=$oItem->getName()}
                                                                <div class="cfg-item-description">
                                                                    {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                                                                    {if JTL\Session\Frontend::getCustomerGroup()->mayViewPrices()}
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
                                                        {/radio}
                                                    {/block}
                                                {else}
                                                    {block name='productdetails-config-container-group-item-type-swatch-option-checkbox'}
                                                        {checkbox name="item[{$kKonfiggruppe}][]"
                                                            value=$oItem->getKonfigitem()
                                                            disabled=empty($bSelectable)
                                                            data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                            checked=$checkboxActive
                                                            id="item{$oItem->getKonfigitem()}"
                                                            class="cfg-swatch"
                                                        }
                                                            <div data-id="$oItem->getKonfigitem()" class="config-item {if $oItem->getEmpfohlen()} bg-recommendation{/if}{if empty($bSelectable)} disabled{/if}{if $checkboxActive} active{/if}">
                                                                {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                                    <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                                {/if}
                                                                {badge class="badge-circle circle-small"}<i class="fas fa-check"></i>{/badge}
                                                                {include file='snippets/image.tpl' item=$oItem->getArtikel() srcSize='sm' alt=$oItem->getName()}
                                                                <div class="cfg-item-description">
                                                                    {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                                                                    {if JTL\Session\Frontend::getCustomerGroup()->mayViewPrices()}
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
                                                                </div>

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
                                                                            type="number"
                                                                            min="{$oItem->getMin()}"
                                                                            max="{$oItem->getMax()}"
                                                                            step="{if $oItem->getArtikel()->cTeilbar === 'Y' && $oItem->getArtikel()->fAbnahmeintervall == 0}any{elseif $oItem->getArtikel()->fAbnahmeintervall > 0}{$oItem->getArtikel()->fAbnahmeintervall}{else}1{/if}"
                                                                            id="quantity{$oItem->getKonfigitem()}"
                                                                            class="quantity"
                                                                            name="item_quantity[{$kKonfigitem}]"
                                                                            autocomplete="off"
                                                                        value="{if !empty($nKonfigitemAnzahl_arr[$kKonfigitem])}{$nKonfigitemAnzahl_arr[$kKonfigitem]}{else}{if $oItem->getArtikel()->fAbnahmeintervall > 0}{if $oItem->getArtikel()->fMindestbestellmenge > $oItem->getArtikel()->fAbnahmeintervall}{$oItem->getArtikel()->fMindestbestellmenge}{else}{$oItem->getArtikel()->fAbnahmeintervall}{/if}{else}{if ($oItem->getInitial()>0)}{$oItem->getInitial()}{else}{$oItem->getMin()}{/if}{/if}{/if}"
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
                                                    {/block}
                                                {/if}
                                            {/block}
                                            {/col}
                                        {/foreach}
                                    {/block}
                                {elseif $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_DROPDOWN}
                                    {block name='productdetails-config-container-group-item-type-dropdown'}
                                        {col cols=12 data=["id"=>$kKonfiggruppe] class="config-option-dropdown"}
                                            {block name='productdetails-config-container-group-item-type-dropdown-select'}
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
                                                        {if JTL\Session\Frontend::getCustomerGroup()->mayViewPrices()}
                                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                                            {if $oItem->hasRabatt() && $oItem->showRabatt()}({$oItem->getRabattLocalized()} {lang key='discount'})&nbsp;{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}({$oItem->getZuschlagLocalized()} {lang key='additionalCharge'})&nbsp;{/if}
                                                            {$oItem->getPreisLocalized()}
                                                        {/if}
                                                    </option>
                                                {/foreach}
                                                {/select}
                                                {/formgroup}
                                            {/block}
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
                                            {block name='productdetails-config-container-group-item-type-dropdown-collapse'}
                                                {collapse visible=isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr) id="drpdwn_qnt_{$oItem->getKonfigitem()}" class="cfg-drpdwn-item"}
                                                    {include file='productdetails/config_item_description.tpl'}
                                                {/collapse}
                                            {/block}
                                        {/foreach}
                                        {/col}
                                    {/block}
                                {/if}
                                {/row}
                            {/block}
                            {block name='productdetails-config-bottom'}
                                <div class="sticky-bottom">
                                    {if $configGroup@last}
                                        {nav}
                                            {navitem id="cfg-tab-summary-finish"
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
                                    {/if}
                                </div>
                            {/block}

                        {/block}
                    </div>
                {/if}
            {/foreach}
        {/col}
    {/row}
{/block}
