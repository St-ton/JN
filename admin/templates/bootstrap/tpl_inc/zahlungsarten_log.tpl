{include file='tpl_inc/seite_header.tpl' cTitel=#paymentmethods# cBeschreibung=#log# cDokuURL=#paymentmethodsURL#}
<div id="content">
    {if isset($cHinweis) && $cHinweis|count_characters > 0}
        <div class="alert alert-info">{$cHinweis}</div>
    {/if}
    {if !empty($oLog_arr)}
        <div>
            <a href="zahlungsarten.php?a=logreset&kZahlungsart={$kZahlungsart}&token={$smarty.session.jtl_token}" class="btn btn-danger reset"><i class="fa fa-trash"></i> {#logReset#}</a>
        </div>

        {foreach name=log from=$oLog_arr item=oLog}
            <hr>
            <p class="pull-right">
                <small class="text-muted">{$oLog->dDatum}</small>
                {if $oLog->nLevel == 1}
                    <span class="label label-danger logError">{#logError#}</span>
                {elseif $oLog->nLevel == 2}
                    <span class="label label-info logNotice">{#logNotice#}</span>
                {else}
                    <span class="label label-default logDebug">{#logDebug#}</span>
                {/if}
            </p>
            <div class="custom-content">{$oLog->cLog}</div>
        {/foreach}

        <div>
            <a href="zahlungsarten.php" class="btn btn-default">{#pageBack#}</a>
        </div>
    {else}
        <div class="alert alert-info">
            <p>Keine Logs vorhanden.</p>
        </div>
        <div>
            <a href="zahlungsarten.php" class="btn btn-default">{#pageBack#}</a>
        </div>
    {/if}
</div>
