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
                                        {if $oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL || $oPosition->nPosTyp == C_WARENKORBPOS_TYP_GRATISGESCHENK}
                                            <tr>
                                                <td>
                                                    {formrow}
                                                        {block name='basket-cart-dropdown-cart-item-item-image'}
                                                            {col class="col-auto"}
                                                                {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}
                                                                    {image lazy=true webp=true
                                                                        src=$oPosition->Artikel->Bilder[0]->cURLMini
                                                                        srcset="{$oPosition->Artikel->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                                            {$oPosition->Artikel->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                                            {$oPosition->Artikel->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                                                        sizes="45px"
                                                                        alt=$oPosition->Artikel->cName
                                                                        class="img-sm"}
                                                                {/link}
                                                            {/col}
                                                        {/block}
                                                        {block name='basket-cart-dropdown-cart-item-item-link'}
                                                            {col class="col-auto"}
                                                                {$oPosition->nAnzahl|replace_delim}x
                                                            {/col}
                                                            {col}
                                                                {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}
                                                                    {$oPosition->cName|trans}
                                                                {/link}
                                                            {/col}
                                                        {/block}
                                                    {/formrow}
                                                </td>
                                                {block name='basket-cart-dropdown-cart-item-item-price'}
                                                    <td class="text-right text-nowrap">
                                                        {if $oPosition->istKonfigVater()}
                                                            {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                                        {else}
                                                            {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                                        {/if}
                                                    </td>
                                                {/block}
                                            </tr>
                                        {else}
                                            <tr>
                                                {block name='basket-cart-dropdown-cart-item-no-item-count'}
                                                    <td>
                                                        {formrow}
                                                            {col class="col-auto"}{/col}
                                                            {col class="col-auto"}
                                                                {$oPosition->nAnzahl|replace_delim}x
                                                            {/col}
                                                            {col}
                                                                {$oPosition->cName|trans|escape:'htmlall'}
                                                            {/col}
                                                        {/formrow}
                                                    </td>
                                                {/block}
                                                {block name='basket-cart-dropdown-cart-item-noitem-price'}
                                                    <td class="text-right text-nowrap">
                                                        {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                                    </td>
                                                {/block}
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
                                {block name='basket-cart-dropdown-cart-item-net'}
                                    <li class="text-muted mb-2 font-size-sm">
                                        {if empty($smarty.session.Versandart)}
                                            {lang key='subtotal' section='account data'}
                                        {else}
                                            {lang key='totalSum'}
                                        {/if} ({lang key='net'}) <span class="float-right text-nowrap">{$WarensummeLocalized[$NettoPreise]}</span>
                                    </li>
                                {/block}
                            {/if}
                            {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && isset($Steuerpositionen) && $Steuerpositionen|@count > 0}
                                {block name='basket-cart-dropdown-cart-item-tax'}
                                    {foreach $Steuerpositionen as $Steuerposition}
                                        <li class="text-muted mb-2 font-size-sm">
                                            {$Steuerposition->cName}
                                            <span class="float-right text-nowrap">{$Steuerposition->cPreisLocalized}</span>
                                        </li>
                                    {/foreach}
                                {/block}
                            {/if}
                            {block name='basket-cart-dropdown-cart-item-total'}
                                <li class="font-weight-bold-util">
                                    {if empty($smarty.session.Versandart)}
                                        {lang key='subtotal' section='account data'}
                                    {else}
                                        {lang key='totalSum'}
                                    {/if}: <span class="float-right text-nowrap">{$WarensummeLocalized[0]}</span>
                                </li>
                            {/block}
                            {block name='basket-cart-dropdown-cart-item-favourable-shipping'}
                                {if $favourableShippingString !== '' && empty($smarty.session.Versandart)}
                                    <li class="text-muted mt-2 font-size-sm">{$favourableShippingString}</li>
                                {/if}
                            {/block}
                        </ul>
                    {/block}
                    {block name='basket-cart-dropdown-buttons'}
                        {row class='mt-3'}
                            {col cols=12 lg=6}
                                {button variant="outline-primary" type="link" block=true  size="sm" href="{get_static_route id='bestellvorgang.php'}?wk=1" class="mb-3 mb-lg-0"}
                                    {lang key='nextStepCheckout' section='checkout'}
                                {/button}
                            {/col}
                            {col cols=12 lg=6}
                                {button variant="primary" type="link" block=true  size="sm" title="{lang key='gotoBasket'}" href="{get_static_route id='warenkorb.php'}"}
                                    {lang key='gotoBasket'}
                                {/button}
                            {/col}
                        {/row}
                    {/block}
                    {if !empty($WarenkorbVersandkostenfreiHinweis)}
                        {block name='basket-cart-dropdown-shipping-free-hint'}
                            <hr>
                            <ul class="list-icons text-muted font-size-sm">
                                <li>
                                    <a class="popup" href="{if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{else}#{/if}" data-toggle="tooltip"  data-placement="bottom" title="{lang key='shippingInfo' section='login'}">
                                        <i class="fa fa-info-circle"></i>
                                    </a>
                                    {$WarenkorbVersandkostenfreiHinweis|truncate:160:"..."}
                                </li>
                            </ul>
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
