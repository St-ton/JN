{if isset($Einstellungen.global.global_versandermittlung_anzeigen)
    && $Einstellungen.global.global_versandermittlung_anzeigen === 'Y'
    && (!isset($smarty.get.shipping_calculator)
        || (isset($smarty.get.shipping_calculator) && $smarty.get.shipping_calculator !== "0")
    )}
    {opcMountPoint id='opc_before_shipping'}

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
{/if}
