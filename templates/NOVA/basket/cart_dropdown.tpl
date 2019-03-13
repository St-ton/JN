{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{collapse id="nav-cart-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-md-2 py-0"}
    {if $smarty.session.Warenkorb->PositionenArr|@count > 0}
        <div class="p-3">
            <table class="table table-striped dropdown-cart-items">
                <tbody>
                {foreach $smarty.session.Warenkorb->PositionenArr as $oPosition}
                    {if !$oPosition->istKonfigKind()}
                        {if $oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL}
                            <tr>
                                <td class="item-image">
                                    {if $oPosition->Artikel->Bilder[0]->cPfadMini !== $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN}
                                        {image src=$oPosition->Artikel->Bilder[0]->cURLMini alt=$oPosition->Artikel->cName class="img-sm"}
                                    {/if}
                                </td>
                                <td class="item-name" colspan="2">
                                    {$oPosition->nAnzahl|replace_delim}&nbsp;&times;&nbsp;
                                    {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}
                                        {$oPosition->cName|trans}
                                    {/link}
                                {/col}
                                {col md=3 class="text-right"}
                                    {if $oPosition->istKonfigVater()}
                                        {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                    {else}
                                        {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                    {/if}
                                {/col}
                            {else}
                                {col md=9}
                                    {$oPosition->nAnzahl|replace_delim}&nbsp;&times;&nbsp;{$oPosition->cName|trans|escape:'htmlall'}
                                {/col}
                                {col md=3 class="text-right"}
                                    {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {/col}
                            {/if}
                        {/if}
                        </div>
                    {/foreach}
                {/row}
                {row class="py-3 px-2"}
                    {if $NettoPreise}
                        {col md="7" class="text-right"}
                            {lang key='totalSum'} ({lang key='net'}):
                        {/col}
                        {col md="5" class="text-right"}
                            {$WarensummeLocalized[$NettoPreise]}
                        {/col}
                    {/if}
                    {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && isset($Steuerpositionen) && $Steuerpositionen|@count > 0}
                        {foreach $Steuerpositionen as $Steuerposition}
                            {col md="7" class="text-right"}
                                {$Steuerposition->cName}
                            {/col}
                            {col md="5" class="text-right"}
                                {$Steuerposition->cPreisLocalized}
                            {/col}
                        {/foreach}
                    {/if}
                    <div class="d-flex w-100 pt-2">
                        {col md="7" class="text-right"}
                            <span class="font-weight-bold">{lang key='totalSum'}:</span>
                        {/col}
                        {col md="5" class="text-right"}
                            {$WarensummeLocalized[0]}
                        {/col}
                    </div>
                {/row}
                {row class="px-2"}
                    {if isset($FavourableShipping)}
                        {if $NettoPreise}
                            {$shippingCosts = "`$FavourableShipping->cPriceLocalized[$NettoPreise]` {lang key='plus' section='basket'} {lang key='vat' section='productDetails'}"}
                        {else}
                            {$shippingCosts = $FavourableShipping->cPriceLocalized[$NettoPreise]}
                        {/if}
                        {col md="12" class="text-right"}
                            <small>{lang|sprintf:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL():$shippingCosts:$FavourableShipping->cCountryCode key='shippingInformationSpecific' section='basket'}</small>
                        {/col}
                    {elseif empty($FavourableShipping)}
                        {col md="12" class="text-right"}
                            <small>{lang|sprintf:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() key='shippingInformation' section='basket'}</small>
                        {/col}
                    {/if}
                {/row}
                {if !empty($WarenkorbVersandkostenfreiHinweis)}
                    <p class="small text-muted">{$WarenkorbVersandkostenfreiHinweis|truncate:120:"..."}
                        <a class="popup" href="{if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{else}#{/if}" data-toggle="tooltip"  data-placement="bottom" title="{lang key='shippingInfo' section='login'}">
                            <span class="fa fa-info-circle"></span>
                        </a>
                    </p>
                {/if}
                </tfoot>
            </table>
            {if !empty($WarenkorbVersandkostenfreiHinweis)}
                <p class="small text-muted">{$WarenkorbVersandkostenfreiHinweis|truncate:120:"..."}
                    <a class="popup" href="{if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{else}#{/if}" data-toggle="tooltip"  data-placement="bottom" title="{lang key='shippingInfo' section='login'}">
                        <i class="fa fa-info-circle"></i>
                    </a>
                </p>
            {/if}
            {row class="py-4 px-2"}
                {col}
                    {link href="{get_static_route id='bestellvorgang.php'}?wk=1" class="btn btn-secondary btn-block text-nowrap"}
                        {lang key='nextStepCheckout' section='checkout'}
                    {/link}
                {/col}
                {col}
                    {link class="btn btn-primary btn-block text-nowrap" title="{lang key='gotoBasket'}" href="{get_static_route id='warenkorb.php'}"}
                        <i class="fas fa-shopping-cart"></i> {lang key='gotoBasket'}
                    {/link}
                {/col}
            {/row}
        {/container}
    {else}
        {dropdownitem href="{{get_static_route id='warenkorb.php'}}" rel="nofollow" title="{lang section='checkout' key='emptybasket'}"}
            <div class="py-2">
                {lang section='checkout' key='emptybasket'}
            </div>
        {/dropdownitem}
    {/if}
{/collapse}