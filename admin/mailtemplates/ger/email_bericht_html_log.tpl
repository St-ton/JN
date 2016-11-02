{includeMailTemplate template=header type=html}

{if isset($oMailObjekt->oLogEntry_arr)}
    <h2>Log-Eintr�ge ({$oMailObjekt->oLogEntry_arr|@count}):</h2>
    {foreach $oMailObjekt->oLogEntry_arr as $oLogEntry}
        <h3>
            [{$oLogEntry->dErstellt|date_format:"%d.%m.%Y %H:%M:%S"}]
            {if $oLogEntry->nLevel == 1}
                <span style="color:#f00;">[Fehler]</span>
            {elseif $oLogEntry->nLevel == 2}
                <span style="color:#00f;">[Hinweis]</span>
            {elseif $oLogEntry->nLevel == 4}
                <span style="color:#fa0;">[Debug]</span>
            {/if}
        </h3>
        <pre>{$oLogEntry->cLog}</pre>
    {/foreach}
{/if}

{includeMailTemplate template=footer type=html}