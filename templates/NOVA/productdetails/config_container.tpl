{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{col cols=12 md=7}
    <div id="cfg-container">
        <div id="cfg-top"></div>
        {foreach $Artikel->oKonfig_arr as $oGruppe}
            {if $oGruppe->getItemCount() > 0}
                {assign var=oSprache value=$oGruppe->getSprache()}
                {assign var=cBildPfad value=$oGruppe->getBildPfad()}
                {assign var=kKonfiggruppe value=$oGruppe->getKonfiggruppe()}
                {card class="cfg-group mb-4" data=["id"=>"{$kKonfiggruppe}"] no-body=true}
                    {cardheader id="crd-hdr-{$oGruppe@iteration}" class="h5 mb-0 p-2"}
                        {button variant="link" block=true data=["toggle"=>"collapse","target"=>"#cfg-grp-cllps-{$kKonfiggruppe}"] class="text-left"}
                            {$oSprache->getName()}{if $oGruppe->getMin() == 0}<span class="optional"> - {lang key='optional'}</span>{/if}
                            <span class="float-right"><i class="fas fa-minus"></i></span>
                        {/button}
                    {/cardheader}
                    {collapse visible=true id="cfg-grp-cllps-{$kKonfiggruppe}" aria=["labelledby"=>"crd-hdr-{$oGruppe@iteration}"]}
                        {cardbody class="group-description"}
                            <div class="group-description">
                                {if !empty($aKonfigerror_arr[$kKonfiggruppe])}
                                    {alert variant="danger"}
                                    {$aKonfigerror_arr[$kKonfiggruppe]}
                                    {/alert}
                                {/if}
                                {if $oSprache->hatBeschreibung()}
                                    <p class="desc">{$oSprache->getBeschreibung()}</p>
                                {/if}
                            </div>
                            {row}
                                {if !empty($cBildPfad)}
                                    {col md=2 class="d-none d-md-block group-image"}
                                        {image src=$cBildPfad alt=$oSprache->getName() id="img{$kKonfiggruppe}" fluid=true }
                                    {/col}
                                {/if}
                                {col md="{if empty($cBildPfad)}12{else}10{/if}" class="group-items"}
                                    {listgroup class="form-group"}
                                        {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_CHECKBOX}
                                            {foreach $oGruppe->oItem_arr as $oItem}
                                                {if $oItem->isInStock()}
                                                    {assign var=bSelectable value=1}
                                                {else}
                                                    {assign var=bSelectable value=0}
                                                {/if}
                                                {listgroupitem data-id=$oItem->getKonfigitem() class="{if $oItem->getEmpfohlen()}list-group-item-info{/if}{if empty($bSelectable)} disabled{/if}"}
                                                    {assign var=kKonfigitem value=$oItem->getKonfigitem()}
                                                    {assign var=cKurzBeschreibung value=$oItem->getKurzBeschreibung()}
                                                    {if !empty($cKurzBeschreibung)}
                                                        {assign var=cBeschreibung value=$oItem->getKurzBeschreibung()}
                                                    {else}
                                                        {assign var=cBeschreibung value=$oItem->getBeschreibung()}
                                                    {/if}

                                                    {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                        <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                    {/if}

                                                    {checkbox name="item[{$kKonfiggruppe}][]"
                                                        value=$oItem->getKonfigitem()
                                                        disabled=empty($bSelectable)
                                                        data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                        checked=(!empty($aKonfigerror_arr)
                                                        && isset($smarty.post.item)
                                                        && isset($smarty.post.item[$kKonfiggruppe])
                                                        && $oItem->getKonfigitem()|in_array:$smarty.post.item[$kKonfiggruppe])
                                                        || ($oItem->getSelektiert()
                                                        && (!isset($aKonfigerror_arr)
                                                        || !$aKonfigerror_arr))
                                                        id="item{$oItem->getKonfigitem()}"
                                                    }
                                                        {if !empty($oItem->getArtikel()->Bilder[0]->cURLMini)}
                                                            {image src=$oItem->getArtikel()->Bilder[0]->cURLMini alt=$oItem->getName() title=$oItem->getName()}
                                                        {/if}
                                                        {if $oItem->getMin() == $oItem->getMax()}{$oItem->getInitial()}x {/if}
                                                        {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                                                        {if !empty($cBeschreibung)}
                                                            <br>
                                                            <a class="small filter-collapsible-control" data-toggle="collapse" href="#filter-collapsible_checkdio_{$oItem->getKonfigitem()}" aria-expanded="false" aria-controls="filter-collapsible">
                                                                {lang key='showDescription'} <i class="caret"></i>
                                                            </a>
                                                        {/if}
                                                    {/checkbox}

                                                    {if !empty($cBeschreibung) && $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN}
                                                        <div class="panel-collapse">
                                                            <div id="filter-collapsible_dropdown_{$kKonfiggruppe}" class="collapse top10 panel-body{if empty($cBeschreibung)} hidden{/if}">
                                                                {$cBeschreibung}
                                                            </div>
                                                        </div>
                                                    {elseif !empty($cBeschreibung) && ($oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_CHECKBOX || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_RADIO)}
                                                        <div class="panel-collapse">
                                                            <div id="filter-collapsible_checkdio_{$oItem->getKonfigitem()}" class="collapse top10 panel-body">
                                                                {$cBeschreibung}
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if $smarty.session.Kundengruppe->mayViewPrices()}
                                                        {badge variant="light" class="float-right"}
                                                            {if $oItem->hasRabatt() && $oItem->showRabatt()}
                                                                <span class="discount">{$oItem->getRabattLocalized()} {lang key='discount'}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                                                <span class="additional">{$oItem->getZuschlagLocalized()} {lang key='additionalCharge'}</span>
                                                            {/if}
                                                            {$oItem->getPreisLocalized()}
                                                        {/badge}
                                                    {/if}
                                                {/listgroupitem}
                                            {/foreach}
                                        {elseif $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_RADIO}
                                            {foreach $oGruppe->oItem_arr as $oItem}
                                                {if $oItem->isInStock()}
                                                    {assign var=bSelectable value=1}
                                                {else}
                                                    {assign var=bSelectable value=0}
                                                {/if}
                                                {listgroupitem data-id=$oItem->getKonfigitem() class="{if $oItem->getEmpfohlen()}list-group-item-info{/if}{if empty($bSelectable)} disabled{/if}"}
                                                    {assign var=kKonfigitem value=$oItem->getKonfigitem()}
                                                    {assign var=cKurzBeschreibung value=$oItem->getKurzBeschreibung()}
                                                    {if !empty($cKurzBeschreibung)}
                                                        {assign var=cBeschreibung value=$oItem->getKurzBeschreibung()}
                                                    {else}
                                                        {assign var=cBeschreibung value=$oItem->getBeschreibung()}
                                                    {/if}

                                                    {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                        <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                    {/if}
                                                    {radio name="item[{$kKonfiggruppe}][]"
                                                        class="form-control"
                                                        value=$oItem->getKonfigitem()
                                                        disabled=empty($bSelectable)
                                                        data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                        checked=(!empty($aKonfigerror_arr)
                                                        && isset($smarty.post.item)
                                                        && isset($smarty.post.item[$kKonfiggruppe])
                                                        && $oItem->getKonfigitem()|in_array:$smarty.post.item[$kKonfiggruppe])
                                                        || ($oItem->getSelektiert()
                                                        && (!isset($aKonfigerror_arr)
                                                        || !$aKonfigerror_arr))
                                                        id="item{$oItem->getKonfigitem()}"
                                                        required= $oGruppe->getMin() != 0
                                                    }
                                                        {if !empty($oItem->getArtikel()->Bilder[0]->cURLMini)}
                                                            {image src=$oItem->getArtikel()->Bilder[0]->cURLMini alt=$oItem->getName() title=$oItem->getName()}
                                                        {/if}
                                                        {if $oItem->getMin() == $oItem->getMax()}{$oItem->getInitial()}x {/if}
                                                        {$oItem->getName()}{if empty($bSelectable)} - {lang section="productDetails" key="productOutOfStock"}{/if}
                                                        {if !empty($cBeschreibung)}
                                                            <br>
                                                            <a class="small filter-collapsible-control" data-toggle="collapse" href="#filter-collapsible_checkdio_{$oItem->getKonfigitem()}" aria-expanded="false" aria-controls="filter-collapsible">
                                                                {lang key='showDescription'} <i class="caret"></i>
                                                            </a>
                                                        {/if}
                                                    {/radio}
                                                    {if !empty($cBeschreibung) && $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN}
                                                        <div class="panel-collapse">
                                                            <div id="filter-collapsible_dropdown_{$kKonfiggruppe}" class="collapse top10 panel-body{if empty($cBeschreibung)} hidden{/if}">
                                                                {$cBeschreibung}
                                                            </div>
                                                        </div>
                                                    {elseif !empty($cBeschreibung) && ($oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_CHECKBOX || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_RADIO)}
                                                        <div class="panel-collapse">
                                                            <div id="filter-collapsible_checkdio_{$oItem->getKonfigitem()}" class="collapse top10 panel-body">
                                                                {$cBeschreibung}
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    {if $smarty.session.Kundengruppe->mayViewPrices()}
                                                        {badge variant="light" class="float-right"}
                                                            {if $oItem->hasRabatt() && $oItem->showRabatt()}
                                                                <span class="discount">{$oItem->getRabattLocalized()} {lang key='discount'}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                                                <span class="additional">{$oItem->getZuschlagLocalized()} {lang key='additionalCharge'}</span>
                                                            {/if}
                                                            {$oItem->getPreisLocalized()}
                                                        {/badge}
                                                    {/if}
                                                {/listgroupitem}
                                            {/foreach}
                                        {elseif $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}
                                            {$kKonfiggruppe = $oGruppe->getKonfiggruppe()}
                                            {listgroupitem data-id=$kKonfiggruppe}
                                                {select name="item[{$kKonfiggruppe}][]"
                                                    multiple=$oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI
                                                    size="{if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}4{else}1{/if}"
                                                    data=["ref"=>$kKonfiggruppe]
                                                    required=$oGruppe->getMin() > 0
                                                }
                                                    <option value="">{lang key='pleaseChoose'}</option>
                                                    {foreach $oGruppe->oItem_arr as $oItem}
                                                        {if $oItem->isInStock()}
                                                            {assign var=bSelectable value=1}
                                                        {else}
                                                            {assign var=bSelectable value=0}
                                                        {/if}
                                                        <option value="{$oItem->getKonfigitem()}"
                                                                id="item{$oItem->getKonfigitem()}"
                                                                {if empty($bSelectable)} disabled{/if}
                                                                {if isset($nKonfigitem_arr)} data-selected="{if in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}true{else}false{/if}"
                                                                {else}{if $oItem->getSelektiert() && (!isset($aKonfigerror_arr) || !$aKonfigerror_arr)}selected="selected"{/if}{/if}>
                                                            {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}{$oItem->getInitial()} &times; {/if}
                                                            {$oItem->getName()}{if empty($bSelectable)} - {lang section='productDetails' key='productOutOfStock'}{/if}
                                                            {if $smarty.session.Kundengruppe->mayViewPrices()}
                                                                &nbsp;&nbsp;&nbsp;&nbsp;
                                                                {if $oItem->hasRabatt() && $oItem->showRabatt()}({$oItem->getRabattLocalized()} {lang key='discount'})&nbsp;{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}({$oItem->getZuschlagLocalized()} {lang key='additionalCharge'})&nbsp;{/if}
                                                                {$oItem->getPreisLocalized()}
                                                            {/if}
                                                        </option>
                                                    {/foreach}
                                                {/select}
                                            {/listgroupitem}
                                        {/if}
                                    {/listgroup}
                                    {if ($oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_RADIO || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN)}
                                        {assign var=quantity value=$oGruppe->getInitQuantity()}
                                        {if isset($nKonfiggruppeAnzahl_arr) && array_key_exists($kKonfiggruppe, $nKonfiggruppeAnzahl_arr)}
                                            {assign var=quantity value=$nKonfiggruppeAnzahl_arr[$kKonfiggruppe]}
                                        {/if}

                                        {if !$oGruppe->quantityEquals()}
                                            <div class="quantity form-inline" data-id="{$kKonfiggruppe}" style="display:none">
                                                {inputgroup}
                                                    {inputgroupaddon prepend=true}
                                                        {inputgrouptext}
                                                            {lang key='quantity'}:
                                                        {/inputgrouptext}
                                                    {/inputgroupaddon}
                                                    {input size="2" type="number"
                                                        id="quantity{$kKonfiggruppe}"
                                                        name="quantity[{$kKonfiggruppe}]"
                                                        value=$quantity autocomplete="off"
                                                        min=$oGruppe->getMin() max=$oGruppe->getMax()}
                                                {/inputgroup}
                                            </div>
                                        {else}
                                            <div class="quantity">
                                                {input type="hidden" id="quantity{$kKonfiggruppe}"
                                                    name="quantity[{$kKonfiggruppe}]"
                                                    value=$quantity}
                                            </div>
                                        {/if}
                                    {/if}
                                {/col}
                            {/row}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/if}
        {/foreach}
    </div>
    <hr>
    {link variant="light" href="#cfg-top" class="float-right m-2 btn btn-link"}
        <i class="fas fa-angle-double-up"></i>
    {/link}
{/col}
{col cols=12 md=5}
    <div id="cfg-sticky-sidebar" class="sticky-top mb-4 d-none d-md-block">
        {if $Artikel->bHasKonfig}
            {block name='productdetails-config-summary'}
                {include file='productdetails/config_sidebar.tpl'}
            {/block}
        {/if}
        <div class="mt-3">
            {include file='productdetails/basket.tpl'}
        </div>
    </div>
{/col}
