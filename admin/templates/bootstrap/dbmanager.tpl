{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="dbcheck"}
{include file='tpl_inc/seite_header.tpl' cTitel="Datenbank-Manager" cBeschreibung=#dbcheckDesc# cDokuURL=#dbcheckURL#}
<style type="text/css">
.CodeMirror {
    background: #f7f7f7;
    font-size: 12px;
    height: auto;
}

.code-inline {
    background-color: #eee;
    padding: 0 5px;
    margin: 0;
}

.code-inline pre {
    display: table-cell;
    white-space: nowrap;
    background-color: transparent !important;
    border: 0;
    padding: 0;
    margin: 0;
}

.nowrap td, .nowrap th, td.nowrap {
    white-space: pre;
}
</style>
<div id="content" class="container-fluid">
    <div id="pageCheck">
        {if isset($tables) && $tables|@count > 0}
            <div class="alert alert-info"><strong>Anzahl Tabellen:</strong> {$tables|@count}</div>
            
            {if isset($command)}
                <div class="code-inline">{$command->formattedHtml}</div>

                <p>&nbsp;</p>
                
                {if $result instanceof PDOException}
                    <div class="alert alert-danger" role="alert">
                        {$result->errorInfo[2]}
                    </div>
                {else}
                    <div>
                        {$headers = array_keys($result[0])}
                        <table class="table table-striped table-condensed nowrap">
                            <thead>
                                <tr>
                                    {foreach $headers as $h}
                                        <th>{$h}</th>
                                    {/foreach}
                                </tr>
                            </thead>
                            {foreach $result as $r}
                                <tr>
                                    {foreach $headers as $l}
                                        <td>{$r[$l]}</td>
                                    {/foreach}
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                {/if}
                
                <p>&nbsp;</p>
            {/if}

            <form action="dbmanager.php" method="POST">
                <textarea name="command" id="command" class="codemirror sql" data-hint='{$tableColumns|json_encode}'>{if isset($command)}{$command->formattedPlain}{/if}</textarea>
                <br />
                <input type="submit" value="Ausführen" class="btn btn-primary" />
            </form>
            
            <p>&nbsp;</p>

            <table class="table table-striped table-condensed">
                <thead>
                <tr>
                    <th>Tabelle</th>
                    <th>Aktion</th>
                    <th>Typ</th>
                    <th>Kollation</th>
                    <th class="text-right">Datensätze</th>
                    <th class="text-right">Auto-Inkrement</th>
                </tr>
                </thead>
                {foreach $tables as $table}
                    <tr class="text-vcenter{if count($definedTables) > 0 && !($table@key|in_array:$definedTables || $table@key|substr:0:8 == 'xplugin_')} warning{/if}">
                        <td>{$table@key}</td>
                        <td>
                            <div class="btn-group btn-group-xs" role="group">
                                <a href="#" class="btn btn-default">Anzeigen</a>
                                <a href="#" class="btn btn-default">Struktur</a>
                            </div>
                        </td>
                        <td>{$table->Engine}</td>
                        <td>{$table->Collation}</td>
                        <td class="text-right">{$table->Rows|number_format}</td>
                        <td class="text-right">{$table->Auto_increment}</td>
                    </tr>
                {/foreach}
            </table>
        {/if}
    </div>
</div>

{include file='tpl_inc/footer.tpl'}