<strong>Version:</strong>{if !is_object($oVersion) || $oVersion->nType == -2}
    <span class="version critical">Version konnte nicht ermittelt werden</span>
{elseif $oVersion->nType == -1}
    <span class="version">Aktuellste Version bereits vorhanden</span>
{elseif $oVersion->nType == -3}
    <span class="version">Entwicklung (Version {$oVersion->nVersion})</span>
{elseif $oVersion->nType >= 0}
    <span class="version {if $oVersion->nType == 2}critical{else}new_version{/if}">
        <a href="{$oVersion->cURL|urldecode}" target="_blank">
          {if $oVersion->nType == 0}
              Empfohlenes Update
          {elseif $oVersion->nType == 1}
              Neue Features
          {elseif $oVersion->nType == 2}
              Wichtiges Update
          {/if}
          verf&uuml;gbar (Version: {$oVersion->nVersion})
        </a>
   </span>
{/if}