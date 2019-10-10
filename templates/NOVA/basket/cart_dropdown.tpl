{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='basket-cart-dropdown'}
    <div class="dropdown-menu dropdown-menu-right lg-min-w-lg">
        {if $smarty.session.Warenkorb->PositionenArr|@count > 0}
            {block name='basket-cart-dropdown-cart-items-content'}
                <div class="table-responsive max-h-sm lg-max-h">
                    <table class="table table-striped dropdown-cart-items">
                        <tbody>
                            {block name='basket-cart-dropdown-cart-item'}
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
                                                </td>
                                                <td class="item-price">
                                                    {if $oPosition->istKonfigVater()}
                                                        {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                                    {else}
                                                        {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                                    {/if}
                                                </td>
                                            </tr>
                                        {else}
                                            <tr>
                                                <td></td>
                                                <td class="item-name" colspan="2">
                                                    {$oPosition->nAnzahl|replace_delim}&nbsp;&times;&nbsp;{$oPosition->cName|trans|escape:'htmlall'}
                                                </td>
                                                <td class="item-price">
                                                    {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                                </td>
                                            </tr>
                                        {/if}
                                    {/if}
                                {/foreach}
                            {/block}
                        </tbody>
                    </table>
                </div>
                <div class="dropdown-body">
                    {block name='basket-cart-dropdown-total'}
                        <ul class="list-unstyled">
                            {if $NettoPreise}
                                <li class="text-muted mb-2 font-size-sm">
                                    {lang key='totalSum'} ({lang key='net'}) <span class="float-right text-nowrap">{$WarensummeLocalized[$NettoPreise]}</span>
                                </li>
                            {/if}
                            {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && isset($Steuerpositionen) && $Steuerpositionen|@count > 0}
                                {foreach $Steuerpositionen as $Steuerposition}
                                    <li class="text-muted mb-2 font-size-sm">
                                        {$Steuerposition->cName}
                                        <span class="float-right text-nowrap">{$Steuerposition->cPreisLocalized}</span>
                                    </li>
                                {/foreach}
                            {/if}
                            <li class="font-weight-bold">
                                {lang key='totalSum'}: <span class="float-right text-nowrap">{$WarensummeLocalized[0]}</span>
                            </li>
                            {if isset($FavourableShipping)}
                                {if $NettoPreise}
                                    {$shippingCosts = "`$FavourableShipping->cPriceLocalized[$NettoPreise]` {lang key='plus' section='basket'} {lang key='vat' section='productDetails'}"}
                                {else}
                                    {$shippingCosts = $FavourableShipping->cPriceLocalized[$NettoPreise]}
                                {/if}
                                <li class="text-muted mb-2 font-size-sm">
                                    {lang|sprintf:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL():$shippingCosts:$FavourableShipping->cCountryCode key='shippingInformationSpecific' section='basket'}
                                </li>
                            {elseif empty($FavourableShipping)}
                                <li class="text-muted mb-2 font-size-sm">
                                    {lang|sprintf:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() key='shippingInformation' section='basket'}
                                </li>
                            {/if}
                        </ul>
                    {/block}
                    {block name='basket-cart-dropdown-buttons'}
                        {row class='mt-3'}
                            {col cols=12 lg=6}
                                {link href="{get_static_route id='bestellvorgang.php'}?wk=1" class="btn btn-outline-primary btn-block btn-sm mb-3"}
                                    {lang key='nextStepCheckout' section='checkout'}
                                {/link}
                            {/col}
                            {col cols=12 lg=6}
                                {link class="btn btn-primary btn-block btn-sm" title="{lang key='gotoBasket'}" href="{get_static_route id='warenkorb.php'}"}
                                    {lang key='gotoBasket'}
                                {/link}
                            {/col}
                        {/row}
                    {/block}
                    {if !empty($WarenkorbVersandkostenfreiHinweis)}
                        {block name='basket-cart-dropdown-shipping-free-hint'}
                            <hr>
                            <p class="small text-muted mb-0">
                                <a class="popup" href="{if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{else}#{/if}" data-toggle="tooltip"  data-placement="bottom" title="{lang key='shippingInfo' section='login'}">
                                    <i class="fa fa-info-circle"></i>
                                </a>
                                {$WarenkorbVersandkostenfreiHinweis|truncate:120:"..."}
                            </p>
                        {/block}
                    {/if}
                </div>
            {/block}
        {else}
            {block name='basket-cart-dropdown-hint-empty'}
                {dropdownitem class='p-2' href="{{get_static_route id='warenkorb.php'}}" rel="nofollow" title="{lang section='checkout' key='emptybasket'}"}
                    {lang section='checkout' key='emptybasket'}
                {/dropdownitem}
            {/block}
        {/if}
    </div>
{/block}
