{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="dbcheck"}
{include file='tpl_inc/seite_header.tpl' cTitel=#dbcheck# cBeschreibung=#dbcheckDesc# cDokuURL=#dbcheckURL#}
<div id="content" class="container-fluid">
    {if $maintenanceResult !== null}
        {if $maintenanceResult|is_array}
            <ul class="list-group">
                {foreach name=results from=$maintenanceResult item=result}
                    <li class="list-group-item">
                        <strong>{$result->Op} {$result->Table}:</strong> {$result->Msg_text}
                    </li>
                {/foreach}
            </ul>
        {else}
            <div class="alert alert-info">Konnte Aktion nicht ausf&uuml;hren.</div>
        {/if}
    {/if}
    <div id="pageCheck">
        {if $cDBFileStruct_arr|@count > 0}
            {if $engineUpdate !== null}
                {include file='tpl_inc/dbcheck_engineupdate.tpl'}
            {else}
                <div class="alert alert-info"><strong>Anzahl Tabellen:</strong> {$cDBFileStruct_arr|@count}<br /><strong>Anzahl modifizierter Tabellen:</strong> {$cDBError_arr|@count}</div>
                {if $cDBError_arr|@count > 0}
                    <p>
                        <button id="viewAll" name="viewAll" type="button" class="btn btn-primary hide" value="Alle anzeigen"><i class="fa fa-share"></i> Alle anzeigen</button>
                        <button id="viewModified" name="viewModified" type="button" class="btn btn-danger viewModified" value="Modifizierte anzeigen"><i class="fa fa-warning"></i> Modifizierte anzeigen</button>
                    </p>
                    <br />
                {/if}
            {/if}
            <form action="dbcheck.php" method="post">
                <div id="contentCheck" class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">DB-Struktur</h3>
                    </div>
                    <table class="table req">
                        <thead>
                        <tr>
                            <th>Tabelle</th>
                            <th>Engine</th>
                            <th>Kollation</th>
                            <th class="centered">Zeilen</th>
                            <th class="centered">Daten</th>
                            <th>Status</th>
                            <th class="centered">Aktion</th>
                        </tr>
                        </thead>
                        {foreach name=datei from=$cDBFileStruct_arr key=cTable item=oDatei}
                            {assign var=hasError value=$cTable|array_key_exists:$cDBError_arr}
                            <tr class="filestate mod{$smarty.foreach.datei.iteration%2} {if !$cTable|array_key_exists:$cDBError_arr}unmodified{else}modified{/if}">
                                <td>
                                    {if $hasError}
                                        {$cTable}
                                    {else}
                                        <label for="check-{$smarty.foreach.datei.iteration}">{$cTable}</label>
                                    {/if}
                                </td>
                                <td>
                                    {if $cTable|array_key_exists:$cDBStruct_arr}
                                        <span class="badge alert-{if $cDBStruct_arr.$cTable->ENGINE === 'InnoDB'}info{else}warning{/if}">{$cDBStruct_arr.$cTable->ENGINE}</span>
                                    {/if}
                                </td>
                                <td>
                                    {if $cTable|array_key_exists:$cDBStruct_arr}
                                        <span class="badge alert-{if $cDBStruct_arr.$cTable->TABLE_COLLATION|strpos:'utf8' === 0}info{else}warning{/if}">{$cDBStruct_arr.$cTable->TABLE_COLLATION}</span>
                                    {/if}
                                </td>
                                <td class="centered">
                                    {if $cTable|array_key_exists:$cDBStruct_arr}{$cDBStruct_arr.$cTable->TABLE_ROWS|number_format:0:",":"."}{/if}
                                </td>
                                <td class="centered">
                                    {if $cTable|array_key_exists:$cDBStruct_arr}{$cDBStruct_arr.$cTable->DATA_SIZE|formatSize:"%.0f"|upper|strip:"&nbsp;"}{/if}
                                </td>
                                <td>
                                    {if $hasError}
                                        <span class="badge red">{$cDBError_arr[$cTable]}</span>
                                    {else}
                                        <span class="badge green">Ok</span>
                                    {/if}
                                </td>
                                <td class="centered">
                                    {if $cDBStruct_arr.$cTable->Locked}
                                        <span title="Tabelle in Benutzung"><i class="fa fa-cog fa-spin fa-2x fa-fw"></i></span>
                                    {elseif ($cDBStruct_arr.$cTable->ENGINE !== 'InnoDB' || $cDBStruct_arr.$cTable->TABLE_COLLATION|strpos:'utf8' === false) && $DB_Version->collation_utf8 && $DB_Version->innodb->support}
                                        <a href="#" class="btn btn-default" data-action="migrate" data-table="{$cTable}" data-step="1"><i class="fa fa-cogs"></i></a>
                                    {elseif (isset($cDBError_arr.$cTable) && $cDBError_arr.$cTable|strpos:'Inkonsistente Kollation' === 0) && $DB_Version->collation_utf8 && $DB_Version->innodb->support}
                                        <a href="#" class="btn btn-default" data-action="migrate" data-table="{$cTable}" data-step="2"><i class="fa fa-cogs"></i></a>
                                    {elseif !$hasError}
                                        <input id="check-{$smarty.foreach.datei.iteration}" type="checkbox" name="check[]" value="{$cTable}" />
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </table>
                    <div class="panel-footer">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <input type="checkbox" name="ALL_MSG" id="ALLMSGS" onclick="AllMessages(this.form);"/> <label for="ALLMSGS">alle markieren</label>
                            </span>
                            <select name="action" class="form-control">
                                <option value="">Aktion</option>
                                <option value="optimize">optimieren</option>
                                <option value="repair">reparieren</option>
                                <option value="analyze">analysieren</option>
                                <option value="check">pr&uuml;fen</option>
                            </select>
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-primary">absenden</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        {else}
            {if isset($cFehler) && $cFehler|strlen > 0}
                <div class="alert alert-danger">{$cFehler}</div>
            {/if}
        {/if}
    </div>
</div>
<div id="modalWait" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><span>&nbsp;</span> <img src="{$shopURL}/{$PFAD_ADMIN}/{$currentTemplateDir}gfx/widgets/ajax-loader.gif"></h4>
            </div>
            <div class="modal-body">
                <div class="progress" data-notify="progressbar">
                    <div class="progress-bar progress-bar-{ldelim}0{rdelim}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group">
                    <button id="cancelWait" class="btn btn-danger"><i class="fa fa-close"></i>&nbsp;Migration abbrechen</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    {literal}
    $(document).ready(function () {
        $('#viewAll').click(function () {
            $('#viewAll').hide();
            $('#viewModified').show().removeClass('hide');
            $('.unmodified').show();
            $('.modified').show();
            colorLines();
        });

        $('#viewModified').click(function () {
            $('#viewAll').show().removeClass('hide');
            $('#viewModified').hide();
            $('.unmodified').hide();
            $('.modified').show();
            colorLines();
        });

        $('*[data-action="migrate"]').click(function (e) {
            var $this = $(this);

            e.preventDefault();
            showModalWait('', parseInt($this.data('step')) === 1 ? 2 : 1);
            doSingleMigration($this.data('table'), $this.data('step'), $this.closest('tr'));
        });

        $('#cancelWait').click(function (e) {
            cancelWait(true);
            e.preventDefault();
        });

        function colorLines() {
            var mod = 1;
            $('.req li:not(:hidden)').each(function () {
                if (mod === 1) {
                    $(this).removeClass('mod0');
                    $(this).removeClass('mod1');
                    $(this).addClass('mod1');
                    mod = 0;
                } else {
                    $(this).removeClass('mod1');
                    $(this).removeClass('mod0');
                    $(this).addClass('mod0');
                    mod = 1;
                }
            });
        }
    });
    function showModalWait(msg, maxMigrationTables) {
        var $modalWait = $("#modalWait");

        if (msg) {
            $('h4 > span', $modalWait).text(msg);
        }
        if (typeof maxMigrationTables === 'undefined') {
            maxMigrationTables = 1;
        }
        cancelWait(false);

        $modalWait.modal({
            backdrop: 'static'
        });
        $('.progress-bar', $modalWait).attr('aria-valuenow', 0);
        $('.progress-bar', $modalWait).attr('aria-valuemax', maxMigrationTables);
        $('.progress-bar', $modalWait).css('width', 0);

        return $modalWait;
    }
    function updateModalWait(msg, step) {
        var $modalWait = $("#modalWait");

        if (typeof msg !== 'undefined' && msg !== null && msg !== '') {
            $('h4 > span', $modalWait).text(msg);
        }
        if (typeof step !== 'undefined' && step !== null && step > 0) {
            var progressMax     = $('.progress-bar', $modalWait).attr('aria-valuemax');
            var progressNow     = parseInt($('.progress-bar', $modalWait).attr('aria-valuenow')) + step;
            var progressPercent = progressNow > progressMax ? 100 : progressNow / progressMax * 100;
            $('.progress-bar', $modalWait).attr('aria-valuenow', progressNow > progressMax ? progressMax : progressNow);
            $('.progress-bar', $modalWait).css('width', progressPercent + '%');
        }
    }
    function closeModalWait() {
        $("#modalWait").modal("hide");
    }
    function cancelWait(cancel) {
        var $cancelWait = $('#cancelWait');

        if (typeof cancel === 'undefined') {
            return $cancelWait.data('canceled');
        }

        $cancelWait.prop('disabled', cancel);
        $cancelWait.data('canceled', cancel);
    }
    function doSingleMigration(table, step, $row) {
        if (cancelWait()) {
            closeModalWait();
            return;
        }
        if (typeof step === 'undefined' || step === 0) {
            step = 1;
        }
        if (typeof table !== 'undefined' && table !== '') {
            updateModalWait('Migrate ' + table + ' Schritt ' + step);
        }
        ioCall('migrateToInnoDB_utf8', ['migrate', table, step],
            function (data, context) {
                if (data && typeof data.status !== 'undefined' && data.status !== 'failure') {
                    if (data.status === 'migrate' && data.nextStep === 2) {
                        updateModalWait(null, 1);
                        doSingleMigration(table, 2, $row);
                    } else {
                        updateModalWait(null, 1);
                        updateRow($row, table);
                        closeModalWait();
                    }
                } else {
                    window.alert('Bei der Migration der Tabelle ' + table + ' ist ein Fehler aufgetreten!');
                    window.location.reload(true);
                }
            },
            function (responseJSON) {
                window.alert('Bei der Migration der Tabelle ' + table + ' ist ein Fehler aufgetreten!');
                window.location.reload(true);
            },
            {}
        );
    }
    function updateRow($row, table) {
        var $cols = $('td', $row);
        if ($cols.length > 0) {
            $($cols[1]).html('<span class="badge alert-info">InnoDB</span>');
            $($cols[2]).html('<span class="badge alert-info">utf8_general_ci</span>');
            $($cols[5]).html('<span class="badge green">Ok</span>');
            $($cols[6]).html('');
        }
    }
    {/literal}
</script>
{include file='tpl_inc/footer.tpl'}