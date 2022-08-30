{block name='basket-cart-dropdown'}
    <div class="cart-dropdown dropdown-menu dropdown-menu-right lg-min-w-lg">
        {$cartPositions = JTL\Session\Frontend::getCart()->PositionenArr}
        {block name='basket-cart-dropdown-max-cart-positions'}
            {$maxCartPositions = 15}
        {/block}
        {if $cartPositions|count > 0}
            {block name='basket-cart-dropdown-cart-items-content'}
                {block name='basket-cart-dropdown-cart-items'}
                <div class="table-responsive max-h-sm lg-max-h">
                    {block name='basket-cart-dropdown-cart-items-table'}
                    <table class="table dropdown-cart-items">
                        <tbody>
                            {block name='basket-cart-dropdown-cart-item'}
                                {foreach $cartPositions as $oPosition}
                                    {if $oPosition@iteration > $maxCartPositions}
                                        {break}
                                    {/if}
                                    {if !$oPosition->istKonfigKind()}
                                        {if $oPosition->nPosTyp == $smarty.const.C_WARENKORBPOS_TYP_ARTIKEL
                                        || $oPosition->nPosTyp == $smarty.const.C_WARENKORBPOS_TYP_GRATISGESCHENK}
                                            <tr>
                                                <td>
                                                    {formrow}
                                                        {block name='basket-cart-dropdown-cart-item-item-image'}
                                                            {col class="col-auto"}
                                                                {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}
                                                                    {include file='snippets/image.tpl'
                                                                        fluid=false
                                                                        item=$oPosition->Artikel
                                                                        square=false
                                                                        srcSize='xs'
                                                                        sizes='50px'
                                                                        class='img-sm'}
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
                                                    <td class="text-right-util text-nowrap-util">
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
                                                    <td class="text-right-util text-nowrap-util">
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
                    {/block}
                    {block name='basket-cart-dropdown-items-item-overflow-notice'}
                        {if $cartPositions|count > $maxCartPositions}
                            <hr>
                            <div class="item-overflow-notice">
                                {lang key='itemOverflowNotice' section='basket' printf=((int)($cartPositions|count)-$maxCartPositions)|cat:':::'|cat:{get_static_route id='warenkorb.php'}}
                            </div>
                            <hr>
                        {/if}
                    {/block}
                </div>
                {/block}
                {block name='basket-cart-dropdown-cart-items-body'}
                <div class="dropdown-body">
                    {block name='basket-cart-dropdown-total'}
                        <ul class="list-unstyled">
                            {if $NettoPreise}
                                {block name='basket-cart-dropdown-cart-item-net'}
                                    <li class="cart-dropdown-total-item">
                                        {if empty($smarty.session.Versandart)}
                                            {lang key='subtotal' section='account data'}
                                        {else}
                                            {lang key='totalSum'}
                                        {/if} ({lang key='net'}) <span class="cart-dropdown-total-item-price">{$WarensummeLocalized[$NettoPreise]}</span>
                                    </li>
                                {/block}
                            {/if}
                            {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && isset($Steuerpositionen) && $Steuerpositionen|count > 0}
                                {block name='basket-cart-dropdown-cart-item-tax'}
                                    {foreach $Steuerpositionen as $Steuerposition}
                                        <li class="cart-dropdown-total-item">
                                            {$Steuerposition->cName}
                                            <span class="cart-dropdown-total-item-price">{$Steuerposition->cPreisLocalized}</span>
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
                                    {/if}: <span class="cart-dropdown-total-item-price">{$WarensummeLocalized[0]}</span>
                                </li>
                            {/block}
                            {block name='basket-cart-dropdown-cart-item-favourable-shipping'}
                                {if $favourableShippingString !== ''}
                                    <li class="cart-dropdown-total-item">{$favourableShippingString}</li>
                                {/if}
                            {/block}
                        </ul>
                    {/block}
                    {block name='basket-cart-dropdown-buttons'}
                        {row class="cart-dropdown-buttons"}
                            {col cols=12 lg=6}
                                {button variant="outline-primary" type="link" block=true  size="sm" href="{get_static_route id='bestellvorgang.php'}?wk=1" class="cart-dropdown-next"}
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
                            <ul class="cart-dropdown-shipping-notice list-icons font-size-sm">
                                <li>
                                    <a class="popup"
                                       href="{if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{else}#{/if}"
                                       data-toggle="tooltip"
                                       data-placement="bottom"
                                       title="{lang key='shippingInfo' section='login'}">
                                        <i class="fa fa-info-circle"></i>
                                    </a>
                                    {$WarenkorbVersandkostenfreiHinweis|truncate:160:"..."}
                                </li>
                            </ul>
                        {/block}
                    {/if}
                </div>
                {/block}
            {/block}
        {else}
            {block name='basket-cart-dropdown-hint-empty'}
                {dropdownitem class="cart-dropdown-empty" href="{{get_static_route id='warenkorb.php'}}" rel="nofollow" title="{lang section='checkout' key='emptybasket'}"}
                    {lang section='checkout' key='emptybasket'}
                {/dropdownitem}
            {/block}
        {/if}
    </div>
{/block}
