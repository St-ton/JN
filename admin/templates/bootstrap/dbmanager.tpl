{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="dbcheck"}
{include file='tpl_inc/seite_header.tpl' cTitel="Datenbank-Manager" cBeschreibung="<kbd>Tabellen und Views ({$tables|@count})</kbd>" cDokuURL=#dbcheckURL#}
<script>
{literal}
$(function() {
    $search = $('#db-search');

    $('table.table-sticky-header').stickyTableHeaders({
        fixedOffset: $('.navbar-header')
    });
    
    $search.keyup(function () {
        var val = $(this).val();
        var count = filter_tables(val);
        
        if (count > 0) {
            $search.parent().removeClass('has-error');
        }
        else {
            $search.parent().addClass('has-error');
        }
    });
});

function filter_tables(value) {
    var rex = new RegExp(value, 'i');
    var $nav = $('.db-sidenav');
    var $items = $nav.find('li');
    
    $items.hide();
    $nav.unhighlight();

    var $found = $items.filter(function () {
        return rex.test($(this).text());
    });
    
    $found.show();
    if ($found.length > 0) {
        $nav.highlight(value);
    }
    
    return $found.length;
}
{/literal}
</script>

{function table_scope_header table=null}
    <h2>Tabelle: {$table}
        <div class="btn-group btn-group-xs" role="group">
            <a href="dbmanager.php?table={$table}" class="btn btn-default"><span class="glyphicon glyphicon-equalizer"></span> Struktur</a>
            <a href="dbmanager.php?select={$table}" class="btn btn-default"><span class="glyphicon glyphicon-eye-open"></span> Anzeigen</a>
        </div>
    </h2>
{/function}


<div id="content" class="container-fluid">
    <div class="row">

        <div class="col-md-2">
            <div class="form-group">
                <input id="db-search" class="form-control" type="search" placeholder="Tabelle suchen">
            </div>
            <nav class="db-sidebar hidden-print hidden-xs hidden-sm">
                <ul class="nav db-sidenav">
                    {foreach $tables as $table}
                        <li><a href="dbmanager.php?select={$table@key}">{$table@key}</a></li>
                    {/foreach}
                </ul>
            </nav>
        </div>

        <div class="col-md-10">
        
            <ol class="simple-menu">
                <li><a href="dbmanager.php">Übersicht</a></li>
                <li><a href="dbmanager.php?command"><span class="glyphicon glyphicon-flash"></span> SQL Kommando</a></li>
                <li><a href="dbcheck.php">Konsistenz</a></li>
            </ol>
        
            {if $sub === 'command'}
                <h2>SQL Kommando</h2>

                <p class="text-muted">
                    <i class="fa fa-keyboard-o" aria-hidden="true"></i>
                    Code-Vervollständigung via <span class="label label-default">STRG+Leertaste</span> ausführen
                </p>
                
                {if isset($error)}
                    <div class="alert alert-danger" role="alert">
                        {get_class($error)}: <strong>{$error->getMessage()}</strong>
                    </div>
                {/if}

                <form action="dbmanager.php?command" method="POST">
                    <div class="form-group">
                        <textarea name="query" id="query" class="codemirror sql" data-hint='{$jsTypo|json_encode}'>{if isset($query)}{$query}{/if}</textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Ausführen" class="btn btn-primary" />
                    </div>
                </form>
                
                <!-- ###################################################### -->
                {if isset($result)}
                
                    {$headers = array_keys($result[0])}
                    <table class="table table-striped table-condensed table-bordered table-hover table-sql table-sticky-header nowrap">
                        <thead>
                            <tr>
                                {foreach $headers as $h}
                                    <th>{$h}</th>
                                {/foreach}
                            </tr>
                        </thead>
                        {foreach $result as $d}
                            <tr class="text-vcenter">
                                {foreach $headers as $h}
                                    {$value = $d[$h]|escape:'html'|truncate:100:'...'}
                                    <td class="data data-mixed{if $value == null} data-null{/if}"><span>{if $value == null}NULL{else}{$value}{/if}</span></td>
                                {/foreach}
                            </tr>
                        {/foreach}
                    </table>

                {/if}
                <!-- ###################################################### -->

            {else if $sub === 'default'}
                {if isset($tables) && $tables|@count > 0}
                    <table class="table table-striped table-condensed table-bordered table-hover table-sticky-header">
                        <thead>
                        <tr>
                            <th>Tabelle</th>
                            <th class="text-center">Aktion</th>
                            <th class="text-center">Typ</th>
                            <th class="text-center">Kollation</th>
                            <th class="text-right">Datensätze</th>
                            <th class="text-right">Auto-Inkrement</th>
                        </tr>
                        </thead>
                        {foreach $tables as $table}
                            <tr class="text-vcenter{if count($definedTables) > 0 && !($table@key|in_array:$definedTables || $table@key|substr:0:8 == 'xplugin_')} warning{/if}" id="table-{$table@key}">
                                <td><a href="dbmanager.php?select={$table@key}">{$table@key}</a></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-xs" role="group">
                                        <a href="dbmanager.php?table={$table@key}" class="btn btn-default"><span class="glyphicon glyphicon-equalizer"></span> Struktur</a>
                                        <a href="dbmanager.php?select={$table@key}" class="btn btn-default"><span class="glyphicon glyphicon-eye-open"></span> Anzeigen</a>
                                    </div>
                                </td>
                                <td class="text-center">{$table->Engine}</td>
                                <td class="text-center">{$table->Collation}</td>
                                <td class="text-right">{$table->Rows|number_format}</td>
                                <td class="text-right">{$table->Auto_increment}</td>
                            </tr>
                        {/foreach}
                    </table>
                {/if}
            {else if $sub === 'table'}
                {table_scope_header table=$selectedTable}
                <div class="row">
                    <div class="col-md-6">
                        <h3>Struktur</h3>
                        <table class="table table-striped table-condensed table-bordered table-hover table-sticky-header">
                            <thead>
                            <tr>
                                <th>Spalte</th>
                                <th>Typ</th>
                                <th>Kollation</th>
                            </tr>
                            </thead>
                            {foreach $columns as $column}
                                <tr class="text-vcenter">
                                    <th><span class="text-vcenter">{$column->Field}</span> {if $column->Extra == 'auto_increment'}<span class="label label-default text-vcenter"><abbr title="Auto-Inkrement">AI</abbr></span>{/if}</th>
                                    <td>{$column->Type} {if $column->Null === 'YES'}<i class="text-danger">NULL</i>{/if} {if $column->Default !== null}<strong class="text-muted">[{$column->Default}]</strong>{/if}</td>
                                    <td>{$column->Collation}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h3>Indizes</h3>
                        <table class="table table-striped table-condensed table-bordered table-hover table-sticky-header">
                            <thead>
                            <tr>
                                <th>Typ</th>
                                <th>Spalten</th>
                                <th>Name</th>
                            </tr>
                            </thead>
                            {foreach $indexes as $index}
                                <tr class="text-vcenter">
                                    <th>{$index->Index_type}</th>
                                    <td>{array_keys($index->Columns)|implode:'<strong>,</strong> '}</td>
                                    <td>{$index@key}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            {else if $sub === 'select'}
                {table_scope_header table=$selectedTable}
                
                <div class="alert alert-warning" role="alert">todo - assign filter</div>
                
                {$headers = array_keys($data[0])}
                <table class="table table-striped table-condensed table-bordered table-hover table-sql table-sticky-header nowrap">
                    <thead>
                        <tr>
                            {foreach $headers as $h}
                                <th>{$h}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    {foreach $data as $d}
                        <tr class="text-vcenter">
                            {foreach $headers as $h}
                                {$value = $d[$h]}
                                {$class = 'none'}
                                {$info = $columns[$h]->Type_info}

                                {if $info->Name|in_array:['text', 'varchar']}
                                    {$class = 'str'}
                                    {$value = $value|escape:'html'|truncate:100:'...'}
                                {else if $info->Name|in_array:['float', 'decimal']}
                                    {$class = 'float'}
                                    {$decimals = (int)$info->Size[1]}
                                    {$value = $value|number_format:$decimals}
                                {else if $info->Name|in_array:['double']}
                                    {$class = 'float'}
                                    {$value = $value|number_format:2}
                                {else if $info->Name|in_array:['int', 'tinyint', 'bigint', 'smallint']}
                                    {$class = 'int'}
                                {else if $info->Name|in_array:['date', 'datetime']}
                                    {$class = 'date'}
                                    {*$default = ($value == '0000-00-00' || $value == '0000-00-00 00-00-00')*}
                                {else if $info->Name|in_array:['char']}
                                    {$class = 'char'}
                                {/if}

                                <td class="data data-{$class}{if $value == null} data-null{/if}"><span>{if $value == null}NULL{else}{$value}{/if}</span></td>
                            {/foreach}
                        </tr>
                    {/foreach}
                </table>
            {/if}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}