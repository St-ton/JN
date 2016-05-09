<div class="clearall well">
    <h1 {if isset($cBeschreibung) && $cBeschreibung|@count_characters == 0}class="nospacing"{/if}>{if $cTitel|@count_characters > 0}{$cTitel}{else}Unbekannt{/if}</h1>
    {if isset($cDokuURL) && $cDokuURL|@count_characters > 0}
        <div class="documentation">
            <a href="{$cDokuURL}" class="btn btn-default" title="Dokumentation" target="_blank"><i class="fa fa-external-link"></i> Dokumentation zu {$cTitel}</a>
        </div>
    {/if}
    {if isset($cBeschreibung) && $cBeschreibung|@count_characters > 0}
        <p class="description {if isset($cClass)}{$cClass}{/if}">
            <span><!-- right border --></span>
            {if isset($onClick)}<a href="#" onclick="{$onClick}">{/if}{$cBeschreibung}{if isset($onClick)}</a>{/if}
        </p>
    {/if}
    {if isset($oPlugin)}
        <p><strong>{#pluginAuthor#}:</strong> {$oPlugin->cAutor}</p>
        <p><strong>{#pluginHomepage#}:</strong> <a href="{$oPlugin->cURL}" target="_blank"><i class="fa fa-external-link"></i> {$oPlugin->cURL}</a></p>
        <p><strong>{#pluginVersion#}:</strong> {$oPlugin->nVersion}</p>
        <p><strong>{#pluginDesc#}:</strong> {$oPlugin->cBeschreibung}</p>
    {/if}
</div>
{if isset($cHinweis) && $cHinweis|count_characters > 0}
    <div class="alert alert-success"><i class="fa fa-info-circle"></i> {$cHinweis}</div>
{elseif isset($hinweis) && $hinweis|count_characters > 0}
    <div class="alert alert-success"><i class="fa fa-info-circle"></i> {$hinweis}</div>
{/if}

{if isset($cFehler) && $cFehler|count_characters > 0}
    <div class="alert alert-danger"><i class="fa fa-warning"></i> {$cFehler}</div>
{elseif isset($fehler) && $fehler|count_characters > 0}
    <div class="alert alert-danger"><i class="fa fa-warning"></i> {$fehler}</div>
{/if}