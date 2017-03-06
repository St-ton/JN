<td>Versionspr&uuml;fung</td>
<td>
{if !is_object($oVersion) || $oVersion->nType == -2}
    <span class="text-warning">Fehler beim Abruf der Information</span>
{elseif $oVersion->nType == -1}
    <span class="text-success">Aktuellste Version bereits vorhanden</span>
{elseif $oVersion->nType == -3}
    <span class="text-info">Entwicklung (Version {$oVersion->nVersion})</span>
{elseif $oVersion->nType >= 0}
        <a class="btn {if $oVersion->nType == 2}btn-warning{else}btn-info{/if}" href="{$oVersion->cURL|urldecode}" target="_blank">
          {if $oVersion->nType == 0}
              Empfohlenes Update
          {elseif $oVersion->nType == 1}
              Neue Features
          {elseif $oVersion->nType == 2}
              Wichtiges Update
          {/if}
          verf&uuml;gbar (Version: {$oVersion->nVersion})
        </a>
{/if}
</td>