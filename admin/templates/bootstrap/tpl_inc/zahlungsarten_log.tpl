{include file='tpl_inc/seite_header.tpl' cTitel=#paymentmethods# cBeschreibung=#log# cDokuURL=#paymentmethodsURL#}
<div id="content">
    {if !empty($oLog_arr)}
        {include file='tpl_inc/pagination.tpl' oPagination=$paginationPaymentLog cParam_arr=['a'=>'log',
        'token'=>$smarty.session.jtl_token, 'kZahlungsart'=>$kZahlungsart]}
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <th>Hinweis</th>
                    <th>Datum</th>
                    <th>Level</th>
                </thead>
                {foreach $oLog_arr as $oLog}
                    <tr>
                        <td>{$oLog->cLog}</td>
                        <td>
                            <small class="text-muted">{$oLog->dDatum}</small>
                        </td>
                        <td>
                            {if $oLog->nLevel == 1}
                                <span class="label label-danger logError">{#logError#}</span>
                            {elseif $oLog->nLevel == 2}
                                <span class="label label-info logNotice">{#logNotice#}</span>
                            {else}
                                <span class="label label-default logDebug">{#logDebug#}</span>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
        <div class="btn-group">
            <a href="zahlungsarten.php" class="btn btn-default"><i class="fa fa-angle-double-left"></i> {#pageBack#}</a>
            <button class="btn btn-danger reset" data-toggle="modal" data-target="#reset-payment-modal" data-href="zahlungsarten.php?a=logreset&kZahlungsart={$kZahlungsart}&token={$smarty.session.jtl_token}">
            <i class="fa fa-trash"></i> {#logReset#}</button>
        </div>
    {else}
        <div class="alert alert-info">
            <p>Keine Logs vorhanden.</p>
        </div>
        <a href="zahlungsarten.php" class="btn btn-default"><i class="fa fa-angle-double-left"></i> {#pageBack#}</a>
    {/if}
</div>
{include file='tpl_inc/modal_confirm.tpl' modalTitle='Log zurücksetzen' modalID='reset-payment'}

