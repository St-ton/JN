<input type="submit" name="fake" class="hidden">
<table class="table table-striped order-items">
    <thead>
        <tr>
            <th class="hidden-xs"></th>
            <th>{lang key="product" section="global"}</th>
            <th>{lang key="quantity" section="global"}</th>
            {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
                <th class="text-right hidden-xs">{lang key="pricePerUnit" section="productDetails"}</th>
            {/if}
            <th class="text-right">{lang key="price" section="global"}</th>
            {if $tplscope === 'cart'}
                <th class="delitem-col"></th>
            {/if}
        </tr>
    </thead>
    <tbody>
    {foreach name=positionen from=$smarty.session.Warenkorb->PositionenArr item=oPosition}
        {if !$oPosition->istKonfigKind()}
            <tr class="type-{$oPosition->nPosTyp}">
                <td class="img-col hidden-xs text-center">
                    {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y' && !empty($oPosition->Artikel->cVorschaubild)}
                        <a href="{$oPosition->Artikel->cURL}" title="{$oPosition->cName|trans}" class="pull-left">
                            <img src="{$oPosition->Artikel->cVorschaubild}" alt="{$oPosition->cName|trans}" class="img-responsive-height" />
                        </a>
                    {/if}
                </td>
                <td>
                    {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                        <a href="{$oPosition->Artikel->cURL}" title="{$oPosition->cName|trans}">{$oPosition->cName|trans}</a>
                        <ul class="list-unstyled text-muted small">
                            <li class="sku"><strong>{lang key="productNo" section="global"}:</strong> {$oPosition->Artikel->cArtNr}</li>
                            {if isset($oPosition->Artikel->dMHD) && isset($oPosition->Artikel->dMHD_de) && $oPosition->Artikel->dMHD_de !== null}
                                <li title="{lang key='productMHDTool' section='global'}" class="best-before">
                                    <strong>{lang key="productMHD" section="global"}:</strong> {$oPosition->Artikel->dMHD_de}
                                </li>
                            {/if}
                            {if $oPosition->Artikel->cLocalizedVPE && $oPosition->Artikel->cVPE !== 'N'}
                                <li class="baseprice"><strong>{lang key="basePrice" section="global"}:</strong> {$oPosition->Artikel->cLocalizedVPE[$NettoPreise]}</li>
                            {/if}
                            {if $Einstellungen.kaufabwicklung.warenkorb_varianten_varikombi_anzeigen === 'Y' && isset($oPosition->WarenkorbPosEigenschaftArr) && !empty($oPosition->WarenkorbPosEigenschaftArr)}
                                {foreach name=variationen from=$oPosition->WarenkorbPosEigenschaftArr item=Variation}
                                    <li class="variation">
                                        <strong>{$Variation->cEigenschaftName|trans}:</strong> {$Variation->cEigenschaftWertName|trans}
                                    </li>
                                {/foreach}
                            {/if}
                            {if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen === 'Y' && $oPosition->cLieferstatus|trans}
                                <li class="delivery-status"><strong>{lang key="deliveryStatus" section="global"}:</strong> {$oPosition->cLieferstatus|trans}</li>
                            {/if}
                            {if !empty($oPosition->cHinweis)}
                                <li class="text-info notice">{$oPosition->cHinweis}</li>
                            {/if}
                            
                            {* Buttonloesung eindeutige Merkmale *}
                            {if $oPosition->Artikel->cHersteller && $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen != "N"}
                                 <li class="manufacturer">
                                    <strong>{lang key="manufacturer" section="productDetails"}</strong>: 
                                    <span class="values">
                                       {$oPosition->Artikel->cHersteller}
                                    </span>
                                 </li>
                            {/if}
                            
                            {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelmerkmale == 'Y' && !empty($oPosition->Artikel->oMerkmale_arr)}
                                {foreach from=$oPosition->Artikel->oMerkmale_arr item="oMerkmale_arr"}
                                  <li class="characteristic">
                                     <strong>{$oMerkmale_arr->cName}</strong>:
                                     <span class="values">
                                        {foreach name="merkmalwerte" from=$oMerkmale_arr->oMerkmalWert_arr item="oWert"}
                                           {if !$smarty.foreach.merkmalwerte.first}, {/if}
                                           {$oWert->cWert}
                                        {/foreach}
                                     </span>
                                  </li>
                                {/foreach}
                            {/if}
                             
                            {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelattribute == 'Y' && !empty($oPosition->Artikel->Attribute)}
                                {foreach from=$oPosition->Artikel->Attribute item="oAttribute_arr"}
                                 <li class="attribute">
                                    <strong>{$oAttribute_arr->cName}</strong>:
                                    <span class="values">
                                       {$oAttribute_arr->cWert}
                                    </span>
                                 </li>
                                {/foreach}
                            {/if}
                             
                            {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelkurzbeschreibung == 'Y' && $oPosition->Artikel->cKurzBeschreibung|strlen > 0}
                                <li class="shortdescription">{$oPosition->Artikel->cKurzBeschreibung}</li>
                            {/if}
                        </ul>
                    {else}
                        <p>{$oPosition->cName|trans}</p>
                        {if !empty($oPosition->cHinweis)}
                            <small class="text-info notice">{$oPosition->cHinweis}</small>
                        {/if}
                    {/if}

                    {if $oPosition->istKonfigVater()}
                        <ul class="config-items text-muted small">
                            {foreach from=$smarty.session.Warenkorb->PositionenArr item=KonfigPos name=konfigposition}
                                {if $oPosition->cUnique == $KonfigPos->cUnique && $KonfigPos->kKonfigitem > 0}
                                    <li>
                                        <span class="qty">{if !$KonfigPos->istKonfigVater()}{$KonfigPos->nAnzahlEinzel}{else}1{/if}x</span>
                                        {$KonfigPos->cName|trans} &raquo;
                                        <span class="price_value">{$KonfigPos->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}</span>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    {/if}
                </td>

                <td class="qty-col">
                    {if $tplscope === 'cart'}
                        {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                            {if !isset($Einstellungen.template.theme.qty_modify_dropdown) || $Einstellungen.template.theme.qty_modify_dropdown === 'Y'}
                                <div class="qty-wrapper dropdown modify">
                                    {$oPosition->nAnzahl|replace_delim} {if !empty($oPosition->Artikel->cEinheit)}{$oPosition->Artikel->cEinheit}{/if}
                                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                                        <span class="caret"></span>
                                    </button>
                                    <div id="cartitem-dropdown-menu{$smarty.foreach.positionen.index}" class="dropdown-menu dropdown-menu-right keepopen">
                                        <div class="panel-body text-center">
                                            {if $oPosition->istKonfigVater()}
                                                <input name="anzahl[{$smarty.foreach.positionen.index}]" type="hidden" class="form-control" value="{$oPosition->nAnzahl}" />
                                                <a class="btn btn-default configurepos" href="index.php?a={$oPosition->kArtikel}&ek={$smarty.foreach.positionen.index}">{lang key="configure" section="global"}</a>
                                            {else}
                                                <div class="form-inline">
                                                    <label for="quantity{$smarty.foreach.positionen.index}">{lang key="quantity" section="global"}
                                                        {if $oPosition->Artikel->cEinheit}
                                                            ({$oPosition->Artikel->cEinheit})
                                                        {/if}
                                                    </label>:
                                                    <div id="quantity-grp" class="choose_quantity input-group">
                                                        <input name="anzahl[{$smarty.foreach.positionen.index}]" id="quantity{$smarty.foreach.positionen.index}" class="form-control quantity form-control text-right" size="3" value="{$oPosition->nAnzahl}" />
                                                        <span class="input-group-btn">
                                                            <button type="submit" class="btn btn-default" title="{lang key='refresh' section='checkout'}"><i class="fa fa-refresh"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {else}
                                <div class="qty-wrapper modify">
                                    {if $oPosition->istKonfigVater()}
                                        <input name="anzahl[{$smarty.foreach.positionen.index}]" type="hidden" class="form-control" value="{$oPosition->nAnzahl}" />
                                        <span class="btn-group">
                                            <a class="btn btn-default configurepos" href="index.php?a={$oPosition->kArtikel}&ek={$smarty.foreach.positionen.index}">{lang key="configure" section="global"}</a>
                                        </span>
                                    {else}
                                        <div class="form-inline">
                                            <div id="quantity-grp" class="choose_quantity input-group">
                                                <input name="anzahl[{$smarty.foreach.positionen.index}]" id="quantity{$smarty.foreach.positionen.index}" class="form-control quantity form-control text-right" size="3" value="{$oPosition->nAnzahl}" />
                                                {if $oPosition->Artikel->cEinheit}
                                                    <span class="input-group-addon unit hidden-xs">{$oPosition->Artikel->cEinheit}</span>
                                                {/if}
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-default" title="{lang key='refresh' section='checkout'}"><i class="fa fa-refresh"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                            {/if}
                        {elseif $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_GRATISGESCHENK}
                            <input name="anzahl[{$smarty.foreach.positionen.index}]" type="hidden" value="1" />
                        {/if}
                    {else}
                        {$oPosition->nAnzahl|replace_delim} {if !empty($oPosition->Artikel->cEinheit)}{$oPosition->Artikel->cEinheit}{/if}
                    {/if}
                </td>
                {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
                    <td class="price-col text-right hidden-xs">
                        {if !$oPosition->istKonfigVater()}
                            {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                        {/if}
                    </td>
                {/if}
                <td class="price-col text-right">
                    <strong class="price_overall">
                        {if $oPosition->istKonfigVater()}
                            {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                        {else}
                            {$oPosition->cGesamtpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                        {/if}
                    </strong>
                </td>
                {if $tplscope === 'cart'}
                    <td class="delitem-col text-right">
                    {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                        <button type="submit" class="btn btn-xs btn-small btn-danger droppos" name="dropPos" value="{$smarty.foreach.positionen.index}" title="{lang key="delete" section="global"}"><span class="fa fa-trash-o"></span></button>
                    {/if}
                    </td>
                {/if}
            </tr>
        {/if}
    {/foreach}

    </tbody>
    <tfoot>
    {if $NettoPreise}
        <tr class="total-net">
            <td class="hidden-xs"></td>
            <td class="hidden-xs"></td>
            <td class="text-right" colspan="{if $tplscope === 'cart'}3{else}2{/if}"><span class="price_label"><strong>{lang key="totalSum" section="global"} ({lang key="net" section="global"}):</strong></span></td>
            <td class="text-right price-col"><strong class="price total-sum">{$WarensummeLocalized[$NettoPreise]}</strong></td>
        </tr>
    {/if}
    
    {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && $Steuerpositionen|@count > 0}
        {foreach name=steuerpositionen from=$Steuerpositionen item=Steuerposition}
            <tr class="tax">
                <td class="hidden-xs"></td>
                <td class="hidden-xs"></td>
                <td class="text-right" colspan="{if $tplscope === 'cart'}3{else}2{/if}"><span class="tax_label">{$Steuerposition->cName}:</span></td>
                <td class="text-right price-col"><span class="tax_label">{$Steuerposition->cPreisLocalized}</span></td>
            </tr>
        {/foreach}
    {/if}
    
    {if isset($smarty.session.Bestellung->GuthabenNutzen) && $smarty.session.Bestellung->GuthabenNutzen == 1}
         <tr class="customer-credit">
            <td class="hidden-xs"></td>
            <td class="hidden-xs"></td>
            <td class="text-right" colspan="{if $tplscope === 'cart'}3{else}2{/if}">{lang key="useCredit" section="account data"}</td>
            <td class="text-right">{$smarty.session.Bestellung->GutscheinLocalized}</td>
         </tr>
    {/if}

    <tr class="total info">
        <td class="hidden-xs"></td>
        <td class="hidden-xs"></td>
        <td class="text-right" colspan="{if $tplscope === 'cart'}3{else}2{/if}"><span class="price_label"><strong>{lang key="totalSum" section="global"}:</strong></span></td>
        <td class="text-right price-col"><strong class="price total-sum">{$WarensummeLocalized[0]}</strong></td>
    </tr>
    </tfoot>
</table>
