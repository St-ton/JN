{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($hinweis)}
    <div class="alert alert-info">
        {$hinweis}
    </div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">
        {$fehler}
    </div>
{/if}
{if isset($Einstellungen.global.global_versandermittlung_anzeigen) && $Einstellungen.global.global_versandermittlung_anzeigen === 'Y'}
    {include file='snippets/opc_mount_point.tpl' id='opc_shipping_prepend'}
    {if isset($smarty.session.Warenkorb->PositionenArr) && $smarty.session.Warenkorb->PositionenArr|@count > 0}
        <form method="post"
              action="{if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{else}index.php{/if}{if $bExclusive}?exclusive_content=1{/if}"
              class="form form-inline evo-validate" id="shipping-calculator-form">
            {$jtl_token}
            <input type="hidden" name="s" value="{$Link->getID()}" />
            {include file='snippets/shipping_calculator.tpl' checkout=false}
        </form>
    {else}
        {lang key='estimateShippingCostsNote' section='global'}
    {/if}
    {include file='snippets/opc_mount_point.tpl' id='opc_shipping_append'}
{/if}
