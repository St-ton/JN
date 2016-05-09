<li>
    {if $smarty.session.Warenkorb->PositionenArr|@count > 0}
        <table class="table table-striped dropdown-cart-items">
            <tbody>
            {foreach from=$smarty.session.Warenkorb->PositionenArr item=oPosition}
                {if $oPosition->nPosTyp == 1 && !$oPosition->istKonfigKind()}
                    <tr>
                        <td class="item-image">
                            {if $oPosition->Artikel->Bilder[0]->cPfadNormal !== $BILD_KEIN_ARTIKELBILD_VORHANDEN}
                                <img src="{$oPosition->Artikel->Bilder[0]->cPfadMini}" alt="" class="img-sm" />
                            {/if}
                        </td>
                        <td class="item-quantity">
                            {$oPosition->nAnzahl|replace_delim} {if $oPosition->cEinheit|strlen > 0} {$oPosition->cEinheit}{else} &times; {/if}
                        </td>
                        <td class="item-name">
                            <a href="{$oPosition->Artikel->cURL}" title="{$oPosition->Artikel->cName|escape:'quotes'}">
                                {$oPosition->Artikel->cName}
                            </a>
                        </td>
                        <td class="item-price">
                            {if $oPosition->istKonfigVater()}
                                {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                            {else}
                                {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                            {/if}
                        </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
            <tfoot>
            {if $NettoPreise}
                <tr class="total total-net">
                    <td colspan="3">{lang key="totalSum"} ({lang key="net" section="global"}):</td>
                    <td class="text-nowrap text-right"><strong>{$WarensummeLocalized[$NettoPreise]}</strong></td>
                </tr>
            {/if}
            {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && $Steuerpositionen|@count > 0}
                {foreach name=steuerpositionen from=$Steuerpositionen item=Steuerposition}
                    <tr class="text-muted tax">
                        <td colspan="3">{$Steuerposition->cName}</td>
                        <td class="text-nowrap text-right">{$Steuerposition->cPreisLocalized}</td>
                    </tr>
                {/foreach}
            {/if}
            <tr class="total">
                <td colspan="3">{lang key="totalSum"}:</td>
                <td class="text-nowrap text-right total"><strong>{$WarensummeLocalized[0]}</strong></td>
            </tr>
            </tfoot>
        </table>
        {if !empty($WarenkorbVersandkostenfreiHinweis)}
            <p class="small text-muted">{$WarenkorbVersandkostenfreiHinweis} <a href="{if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[6])}{$oSpezialseiten_arr[6]->cURL}{else}#{/if}" data-toggle="tooltip"  data-placement="bottom" title="{$WarenkorbVersandkostenfreiLaenderHinweis}"><i class="fa fa-info-circle"></i></a></p>
        {/if}
        <div class="btn-group btn-group-justified btn-group-full">
            <a href="warenkorb.php" class="btn btn-primary"><i class="fa fa-shopping-cart"></i> {lang key="gotoBasket"}</a>
            {*
            <a href="bestellvorgang.php" class="btn btn-primary">{lang key="checkout" section="basketpreview"}</a>
            *}
        </div>
    {else}
        <a href="warenkorb.php">{lang section="checkout" key="emptybasket"}</a>
    {/if}
</li>