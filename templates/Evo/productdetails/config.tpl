{if isset($Artikel->oKonfig_arr) && $Artikel->oKonfig_arr|@count > 0}

<div class="col-xs-12">
    <div class="product-configuration top10 row">
        <hr>
        <div class="col-sm-12 col-lg-8">
            <div id="cfg-container">
                {foreach from=$Artikel->oKonfig_arr item=oGruppe}
                    {if $oGruppe->getItemCount() > 0}
                        {assign var=oSprache value=$oGruppe->getSprache()}
                        {assign var=cBildPfad value=$oGruppe->getBildPfad()}
                        {assign var=kKonfiggruppe value=$oGruppe->getKonfiggruppe()}
                        <div class="cfg-group panel panel-default{if $oGruppe->getMin() > 0} required{/if}" data-id="{$oGruppe->getKonfiggruppe()}">
                            <div class="panel-heading">
                                <h5 class="panel-title">{$oSprache->getName()}</h5>
                            </div>
                            <div class="group panel-body">
                                <div class="group-description">
                                    {if !empty($aKonfigerror_arr[$kKonfiggruppe])}
                                        <div class="actions">
                                            <div class="alert alert-danger">{$aKonfigerror_arr[$kKonfiggruppe]}</div>
                                        </div>
                                    {/if}
                                    {if $oSprache->hatBeschreibung()}
                                        <p class="desc">{$oSprache->getBeschreibung()}</p>
                                    {/if}
                                </div>
                                <div class="row">
                                    {if !empty($cBildPfad)}
                                        <div class="col-md-2 visible-md-block visible-lg-block group-image">
                                            <img src="{$oGruppe->getBildPfad()}" alt="{$oSprache->getName()}" id="img{$oGruppe->getKonfiggruppe()}" class="img-responsive" />
                                        </div>
                                    {/if}
                                    <div class="col-sm-12 col-md-{if empty($cBildPfad)}12{else}10{/if} group-items">
                                        <ul class="list-group">
                                            {foreach from=$oGruppe->oItem_arr item=oItem name=konfigitem}
                                                <li class="list-group-item {if $oItem->getEmpfohlen()}alert-info{/if}" data-id="{$oItem->getKonfigitem()}">
                                                    {assign var=kKonfigitem value=$oItem->getKonfigitem()}
                                                    {if $oItem->getArtikelKey() == 0}
                                                        {assign var=cKurzBeschreibung value=$oItem->getBeschreibung()}
                                                    {else}
                                                        {assign var=cKurzBeschreibung value=$oItem->getKurzBeschreibung()}
                                                    {/if}
                                                    {if $smarty.foreach.konfigitem.first}
                                                        {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}
                                                        <div class="item clearfix{if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]} error{/if}">
                                                            <select class="form-control" name="item[{$oGruppe->getKonfiggruppe()}][]"
                                                                {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI} multiple="multiple" size="4"{/if} ref="{$oGruppe->getKonfiggruppe()}"{if $oGruppe->getMin() > 0} required{/if}>
                                                                {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN}
                                                                    <option value="">{lang key="pleaseChoose"}</option>
                                                                {/if}
                                                        {/if}
                                                    {/if}
                                                    {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_CHECKBOX || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_RADIO}
                                                        <div class="radio checkbox">
                                                            <label class="btn-block"
                                                                   {if !empty($cKurzBeschreibung)}title="{$cKurzBeschreibung|escape:'html'}"{/if}
                                                                   data-trigger="hover" data-placement="left"
                                                                   data-container="#tooltip{$oItem->getKonfigitem()}">
                                                                <input type="{if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_CHECKBOX}checkbox{else}radio{/if}"
                                                                       name="item[{$oGruppe->getKonfiggruppe()}][]"
                                                                       id="item{$oItem->getKonfigitem()}"
                                                                       value="{$oItem->getKonfigitem()}"
                                                                       {if isset($nKonfigitem_arr)} data-selected="{if in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}true{else}false{/if}"{else}
                                                                       {if (!empty($aKonfigerror_arr) && isset($smarty.post.item) && isset($smarty.post.item[$oGruppe->getKonfiggruppe()]) && $oItem->getKonfigitem()|in_array:$smarty.post.item[$oGruppe->getKonfiggruppe()]) || ($oItem->getSelektiert() && (!isset($aKonfigerror_arr) || !$aKonfigerror_arr))} checked="checked"{/if}{/if} />
                                                                {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_CHECKBOX}{$oItem->getInitial()}x {/if}
                                                                {$oItem->getName()}
                                                                <span class="badge pull-right">{if $oItem->hasRabatt() && $oItem->showRabatt()}
                                                                    <span class="discount">{$oItem->getRabattLocalized()} {lang key="discount"}</span>{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}
                                                                    <span class="additional">{$oItem->getZuschlagLocalized()} {lang key="additionalCharge"}</span>{/if}{$oItem->getPreisLocalized()}</span>
                                                            </label>
                                                        </div>
                                                    {elseif $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}
                                                        <option value="{$oItem->getKonfigitem()}"
                                                                id="item{$oItem->getKonfigitem()}"
                                                                {if !empty($cKurzBeschreibung)}title="{$cKurzBeschreibung|escape:'html'}"{/if}
                                                                {if isset($nKonfigitem_arr)} data-selected="{if in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}true{else}false{/if}"
                                                                {else}{if $oItem->getSelektiert() && (!isset($aKonfigerror_arr) || !$aKonfigerror_arr)}selected="selected"{/if}{/if}>
                                                            {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}{$oItem->getInitial()} &times; {/if}
                                                            {$oItem->getName()}
                                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                                            {if $oItem->hasRabatt() && $oItem->showRabatt()}({$oItem->getRabattLocalized()} {lang key="discount"})&nbsp;{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}({$oItem->getZuschlagLocalized()} {lang key="additionalCharge"})&nbsp;{/if}
                                                            {$oItem->getPreisLocalized()}
                                                        </option>
                                                    {/if}
                                                    {if $smarty.foreach.konfigitem.last}
                                                        {if $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}
                                                            </select>
                                                        </div>
                                                        {/if}
                                                    {/if}
                                                    {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                        <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                    {/if}
                                                </li>
                                            {/foreach}
                                        </ul>
                                        {if ($oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_RADIO || $oGruppe->getAnzeigeTyp() == $KONFIG_ANZEIGE_TYP_DROPDOWN)}
                                            {assign var=quantity value=$oGruppe->getInitQuantity()}
                                            {if isset($nKonfiggruppeAnzahl_arr) && array_key_exists($oGruppe->getKonfiggruppe(), $nKonfiggruppeAnzahl_arr)}
                                                {assign var=quantity value=$nKonfiggruppeAnzahl_arr[$oGruppe->getKonfiggruppe()]}
                                            {/if}

                                            {if !$oGruppe->quantityEquals()}
                                                <div class="quantity form-inline" data-id="{$oGruppe->getKonfiggruppe()}" style="display:none">
                                                    <label for="quantity{$oGruppe->getKonfiggruppe()}">{lang key="quantity" section="global"}:</label>
            
                                                    <div class="input-group">
                                                        <input class="form-control" size="2" type="number"
                                                               id="quantity{$oGruppe->getKonfiggruppe()}"
                                                               name="quantity[{$oGruppe->getKonfiggruppe()}]"
                                                               value="{$quantity}" autocomplete="off"
                                                               min="{$oGruppe->getMin()}" max="{$oGruppe->getMax()}" />
                                                    </div>
                                                </div>
                                            {else}
                                                <input type="hidden" id="quantity{$oGruppe->getKonfiggruppe()}"
                                                       name="quantity[{$oGruppe->getKonfiggruppe()}]"
                                                       value="{$quantity}" />
                                            {/if}
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </div>
        </div>
        <div class="col-sm-12 col-lg-4">
            <div id="product-configuration-sidebar">
                <div class="panel panel-primary no-margin">
                    <div class="panel-heading">
                        <h5 class="panel-title">{lang key="yourConfiguration" section="global"}</h5>
                    </div>
                    <div class="panel-body">
                        <div class="summary"></div>                        
                        <p class="text-center">
                            <strong class="price"></strong>
                        </p>
                        {if $Artikel->inWarenkorbLegbar == 1}
                            <div id="quantity-grp" class="choose_quantity input-group">
                                <input type="number"{if $Artikel->fAbnahmeintervall > 0} required
                                       step="{$Artikel->fAbnahmeintervall > 0}"{/if}" id="quantity" class="quantity form-control
                                text-right" name="anzahl"
                                value="{if $Artikel->fAbnahmeintervall > 0}{$Artikel->fAbnahmeintervall}{else}{if isset($fAnzahl)}{$fAnzahl}{else}1{/if}{/if}" />
                                <span class="input-group-btn">
                                    <button name="inWarenkorb" type="submit" value="{lang key="addToCart" section="global"}"
                                            class="submit btn btn-primary">
                                        {if isset($kEditKonfig)}
                                            {lang key="applyChanges" section="global"}
                                        {else}
                                            {lang key="addToCart" section="global"}
                                        {/if}
                                    </button>
                                </span>
                            </div>
                            {if $Artikel->kVariKindArtikel > 0}
                                <input type="hidden" name="a2" value="{$Artikel->kVariKindArtikel}"/>
                            {/if}
                            {if isset($kEditKonfig)}
                                <input type="hidden" name="kEditKonfig" value="{$kEditKonfig}"/>
                            {/if}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}