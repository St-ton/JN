<form method="post" action="zusatzverpackung.php">
    {$jtl_token}
    {if isset($oVerpackung_arr) && $oVerpackung_arr|@count > 0}
    <div class="card">
        <div class="table-responsive card-body">
            {if $oVerpackung_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$pagination}
            {/if}

            <table class="list table table-striped">
                <thead>
                <tr>
                    <th class="th-1"></th>
                    <th class="th-2">{__('name')}</th>
                    <th class="th-3">{__('price')}</th>
                    <th class="th-4">{__('minOrderValue')}</th>
                    <th class="th-5">{__('zusatzverpackungExemptFromCharge')}</th>
                    <th class="th-6">{__('customerGroup')}</th>
                    <th class="th-7">{__('active')}</th>
                    <th class="th-8">&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                {foreach $oVerpackung_arr as $oVerpackung}
                    <tr>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" id="kVerpackung-{$oVerpackung->kVerpackung}" type="checkbox" name="kVerpackung[]" value="{$oVerpackung->kVerpackung}">
                                <label class="custom-control-label" for="kVerpackung-{$oVerpackung->kVerpackung}"></label>
                            </div>
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
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="nAktiv[]" type="checkbox" id="active-id-{$oVerpackung->kVerpackung}" value="{$oVerpackung->kVerpackung}"{if $oVerpackung->nAktiv == 1} checked{/if}>
                                <label class="custom-control-label" for="active-id-{$oVerpackung->kVerpackung}"></label>
                            </div>
                        </td>
                        <td>
                            <a href="zusatzverpackung.php?kVerpackung={$oVerpackung->kVerpackung}&token={$smarty.session.jtl_token}"
                               class="btn btn-default btn-circle" title="{__('modify')}"><i class="fal fa-edit"></i></a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
        {else}
        <div class="alert alert-info">{__('zusatzverpackungAddedNone')}</div>
        {/if}
        <div class="card-footer save-wrapper">
            <div class="row">
                {if isset($oVerpackung_arr) && $oVerpackung_arr|@count > 0}
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-block mb-3">
                            <i class="fas fa-trash-alt"></i> {__('delete')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button name="action" type="submit" value="refresh" class="btn btn-outline-primary btn-block mb-3">
                            <i class="fa fa-refresh"></i> {__('update')}
                        </button>
                    </div>
                {/if}
                <div class="{if !(isset($oVerpackung_arr) && $oVerpackung_arr|@count > 0)}ml-auto{/if} col-sm-6 col-xl-auto">
                    <a href="zusatzverpackung.php?kVerpackung=0&token={$smarty.session.jtl_token}"
                       class="btn btn-primary btn-block" title="{__('modify')}">
                        <i class="fa fa-share"></i> {__('zusatzverpackungCreate')}
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
