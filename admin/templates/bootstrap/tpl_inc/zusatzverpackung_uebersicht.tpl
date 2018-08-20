{if $oVerpackung_arr|@count > 0}
    {include file='tpl_inc/pagination.tpl' oPagination=$oPagination}
{/if}

<form method="post" action="zusatzverpackung.php">
    {$jtl_token}
    {if isset($oVerpackung_arr) && $oVerpackung_arr|@count > 0}
    <div class="panel panel-default">
        <div class="table-responsive">
            <table class="list table table-striped">
                <thead>
                <tr>
                    <th class="th-1"></th>
                    <th class="th-2">{#zusatzverpackungName#}</th>
                    <th class="th-3">{#zusatzverpackungPrice#}</th>
                    <th class="th-4">{#zusatzverpackungMinValue#}</th>
                    <th class="th-5">{#zusatzverpackungExemptFromCharge#}</th>
                    <th class="th-6">{#zusatzverpackungCustomerGrp#}</th>
                    <th class="th-7">{#zusatzverpackungActive#}</th>
                    <th class="th-8">&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                {foreach $oVerpackung_arr as $oVerpackung}
                    <tr>
                        <td>
                            <input id="kVerpackung-{$oVerpackung->kVerpackung}" type="checkbox" name="kVerpackung[]" value="{$oVerpackung->kVerpackung}">
                        </td>
                        <td><label for="kVerpackung-{$oVerpackung->kVerpackung}">{$oVerpackung->cName}</label></td>
                        <td>{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fBrutto}</td>
                        <td>{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fMindestbestellwert}</td>
                        <td>{getCurrencyConversionSmarty fPreisBrutto=$oVerpackung->fKostenfrei}</td>
                        <td>
                            {foreach $oVerpackung->cKundengruppe_arr as $cKundengruppe}
                                {$cKundengruppe}{if !$cKundengruppe@last},{/if}
                            {/foreach}
                        </td>
                        <td>
                            <input name="nAktivTMP[]" type="hidden" value="{$oVerpackung->kVerpackung}" checked>
                            <input name="nAktiv[]" type="checkbox" value="{$oVerpackung->kVerpackung}"{if $oVerpackung->nAktiv == 1} checked{/if}>
                        </td>
                        <td>
                            <a href="zusatzverpackung.php?kVerpackung={$oVerpackung->kVerpackung}&token={$smarty.session.jtl_token}"
                               class="btn btn-default" title="{#modify#}"><i class="fa fa-edit"></i></a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
        {else}
        <div class="alert alert-info">{#zusatzverpackungAddedNone#}</div>
        {/if}
        <div class="panel-footer">
            <div class="btn-group">
                <a href="zusatzverpackung.php?kVerpackung=0&token={$smarty.session.jtl_token}"
                   class="btn btn-primary" title="{#modify#}">
                    <i class="fa fa-share"></i> {#zusatzverpackungAdd#}
                </a>
                {if isset($oVerpackung_arr) && $oVerpackung_arr|@count > 0}
                    <button type="submit" name="action" value="delete" class="btn btn-danger"><i class="fa fa-trash"></i> {#zusatzverpackungDelete#}</button>
                    <button name="action" type="submit" value="refresh" class="btn btn-default"><i class="fa fa-refresh"></i> {#zusatzverpackungUpdate#}</button>
                {/if}
            </div>
        </div>
    </div>
</form>
