{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset(Shop::Container()->getAlertService()->getCustomAlert('couponError'))}
    <div class="alert alert-{Shop::Container()->getAlertService()->getCustomAlert('couponError')->getVariant()}">
        {Shop::Container()->getAlertService()->getCustomAlert('couponError')->getMessage()}
        {Shop::Container()->getAlertService()->unsetAlert('Custom', 'couponError')}
    </div>
{/if}
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
