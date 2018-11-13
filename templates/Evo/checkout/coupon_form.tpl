{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset(Shop::Container()->getAlertService()->getCustomAlert('couponError'))}
    {include file='snippets/alert.tpl' alert=Shop::Container()->getAlertService()->getCustomAlert('couponError')}
{/if}
{*{if isset($smarty.session.alerts) && isset($smarty.session.alerts->getCustomAlert('couponError'))}*}
    {*{include file='snippets/alert.tpl' alert=$smarty.session.alerts->getCustomAlert('couponError')}*}
{*{/if}*}
{if $KuponMoeglich == 1}
    <form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form form-inline evo-validate">
        {$jtl_token}
        <input type="hidden" name="pruefekupon" value="1" />
        <fieldset>
            <div class="input-group">
                <input type="text" name="Kuponcode"  maxlength="32" value="{if !empty($Kuponcode)}{$Kuponcode}{/if}" id="kupon" class="form-control" placeholder="{lang key='couponCode' section='account data'}" aria-label="{lang key='couponCode' section='account data'}" required/>
                <div class="input-group-btn">
                    <input type="submit" value="{lang key='useCoupon' section='checkout'}" class="submit btn btn-default" />
                </div>
            </div>
        </fieldset>
    </form>
{/if}
