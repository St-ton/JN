{if empty($tab)}
    {if $engineUpdate->tableCount > 10}
        {assign var="tab" value="update_automatic"}
    {else}
        {assign var="tab" value="update_individual"}
    {/if}
{/if}
<div class="alert alert-warning">
    <h3 class="panel-title">Struktur-Migration erforderlich!</h3>
    F&uuml;r {$engineUpdate->tableCount} Tabellen ist eine Verschiebung in den InnoDB-Tablespace und ggfs. die Konvertierung in einen UTF-8 Zeichensatz erforderlich.
    Von dieser Migration sind ca.&nbsp;{$engineUpdate->dataSize|formatSize:"%.0f"|upper|strip:"&nbsp;"} an Daten betroffen.
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Struktur-Migration f&uuml;r {$engineUpdate->tableCount} Tabellen</h3>
    </div>
    <div class="panel-body">
        <ul class="nav nav-tabs">
            <li{if $tab === 'update_individual'} class="active"{/if}><a data-toggle="tab" href="#update_individual">Einzeln &uuml;ber die Struktur-Tabelle</a></li>
            <li{if $tab === 'update_automatic'} class="active"{/if}><a data-toggle="tab" href="#update_automatic">Automatisch</a></li>
            {if isset($scriptGenerationAvailable) && $scriptGenerationAvailable}
            <li{if $tab === 'update_script'} class="active"{/if}><a data-toggle="tab" href="#update_script">Per Script auf der DB-Konsole</a></li>
            {/if}
        </ul>
        <div class="tab-content">
            <div id="update_individual" class="tab-pane fade{if $tab === 'update_individual'} in active{/if}">
                <h3>Einzeln &uuml;ber die Struktur-Tabelle</h3>
                <p>Die Einzel-Migration wird empfohlen, wenn nur einige wenige Tabellen ge&auml;ndert werden m&uuml;ssen oder einzelne Tabellen mit der automatischen Migration oder der Migration per Script nicht ge&auml;ndert werden konnten.</p>
                <p>Sie k&ouml;nnen mit einem Klick auf das <i class="fa fa-cogs">&nbsp;</i>-Symbol die Migration f&uuml;r jede Tabelle einzeln in der u.a. Liste durchf&uuml;hren.</p>
                <div class="alert alert-warning">Erstellen Sie unbedingt ein Backup der gesamten Datenbank, mindestens jedoch der Tabellen die Sie &auml;ndern wollen, <strong>BEVOR</strong> Sie die Migration durchf&uuml;hren!</div>
            </div>
            <div id="update_automatic" class="tab-pane fade{if $tab === 'update_automatic'} in active{/if}">
                <h3>Automatisch</h3>
                <p>Die automatische Migration wird empfohlen, wenn Ihre Shop-Datenbank komplett umgestellt werden mu&szlig; und sich die Datenmenge innerhalb der
                    <a title="Softwarebeschr&auml;nkungen und Grenzen der JTL-Produkte" href="https://guide.jtl-software.de/Softwarebeschr%C3%A4nkungen_und_Grenzen_der_JTL-Produkte">Spezifikationen</a> f&uuml;r
                    JTL-Shop befindet.
                </p>
                <p>Bitte haben Sie Geduld! Bei {$engineUpdate->tableCount} Tabellen und einer Datenmenge von ca.&nbsp;{$engineUpdate->dataSize|formatSize:"%.0f"|upper|strip:"&nbsp;"} kann die Migration
                    {if $engineUpdate->estimated[0] < 60}
                        weniger als eine Minute
                    {elseif $engineUpdate->estimated[0] < 3600}
                        ca. {($engineUpdate->estimated[0] / 60)|round:0} Minuten
                    {else}
                        ca. {($engineUpdate->estimated[0] / 3600)|round:1} Stunden
                    {/if} ggfs. aber auch bis zu
                    {if $engineUpdate->estimated[1] < 60}
                        einer Minute
                    {elseif $engineUpdate->estimated[1] < 3600}
                        ca. {($engineUpdate->estimated[1] / 60)|ceil} Minuten
                    {else}
                        ca. {($engineUpdate->estimated[1] / 3600)|ceil} Stunden
                    {/if} dauern. W&auml;hrend der Migration werden zudem wichtige Tabellen im Shop gesperrt, so dass es zu erheblichen Einschr&auml;nckungen im Frontend kommen kann.
                    Es wird deshalb empfohlen den <a title="Globale Einstellungen - Wartungsmodus" href="/admin/einstellungen.php?kSektion=1#wartungsmodus_aktiviert">Wartungsmodus</a> zu aktivieren,
                    w&auml;hrend Sie die Migration durchf&uuml;hren!
                </p>
                <div class="alert alert-warning">Erstellen Sie unbedingt ein Backup der gesamten Datenbank <strong>BEVOR</strong> Sie die Migration ausf&uuml;hren!</div>
                <form method="post" action="dbcheck.php">
                    <div id="settings" class="panel panel-default">
                        <div class="panel-body">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="update_auto_backup">Ich habe ein Backup der kompletten Shop-Datenbank erstellt!</label>
                                </span>
                                <span class="input-group-wrap">
                                    <input id="update_auto_backup" class="form-control" type="checkbox" name="update_auto_backup" value="1" required>
                                </span>
                            </div>
                            {if isset($Einstellungen.global.wartungsmodus_aktiviert) && $Einstellungen.global.wartungsmodus_aktiviert === 'Y'}
                            <div class="input-group">
                                <span class="input-group-addon"><span class="badge alert-success">Wartungsmodus ist aktiv!</span></span>
                                <input id="update_auto_wartungsmodus" type="hidden" name="update_auto_wartungsmodus" value="1" >
                            </div>
                            {else}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="update_auto_wartungsmodus_reject">Ich verzichte auf den Wartungsmodus!</label>
                                </span>
                                <span class="input-group-wrap">
                                    <input id="update_auto_wartungsmodus_reject" class="form-control" type="checkbox" name="update_auto_wartungsmodus_reject" value="1" required>
                                </span>
                            </div>
                            {/if}
                        </div>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-primary" name="update" value="automatic"><i class="fa fa-cogs"></i>&nbsp;Migration starten</button>
                    </div>
                </form>
            </div>
            {if isset($scriptGenerationAvailable) && $scriptGenerationAvailable}
            <div id="update_script" class="tab-pane fade{if $tab === 'update_script'} in active{/if}">
                <h3>Per Script auf der DB-Konsole</h3>
                <p>Die Migration per Script &uuml;ber die MySQL-Konsole wird empfohlen, wenn Sie administrativen Zugang zu Ihrem Datenbankserver haben und eine gro&szlig;e Menge an Daten migriert werden mu&szlig;.</p>
                <p>Mit einem Klick auf den Button &quot;Script erstellen&quot; k&ouml;nnen Sie sich ein Script zur Durchf&uuml;hrung der notwendigen Migration generieren lassen. Dieses Script k&ouml;nnen Sie dann komplett oder
                    in Teilen auf der Konsole Ihres Datenbankservers ausf&uuml;hren. Sie ben&ouml;tigen daf&uuml;r einen administrativen Zugang (z.B. per SSH) zu Ihrem Datenbank-Server. Eine Weboberfl&auml;che wie phpMyAdmin
                    ist f&uuml;r das Ausf&uuml;hren dieses Scriptes <strong>nicht</strong> geeignet.
                </p>
                <p>Das Script wird anhand der aktuellen Situation erstellt und beinhaltet nur die &Auml;nderungen, die f&uuml;r diesen JTL-Shop notwendig sind. Sie k&ouml;nnen das Script nicht verwenden, um die Migration
                    auf einem anderen JTL-Shop auszuf&uuml;hren!
                </p>
                <p>Bedenken Sie beim Ausf&uuml;hren des Scriptes das dieses ggfs. eine l&auml;ngere Zeit f&uuml;r den kompletten Durchlauf ben&ouml;tigt und w&auml;hrenddessen wichtige Tabellen im Shop f&uuml;r den Zugriff gesperrt werden.
                    Es wird deshalb empfohlen den <a title="Globale Einstellungen - Wartungsmodus" href="/admin/einstellungen.php?kSektion=1#wartungsmodus_aktiviert">Wartungsmodus</a> zu aktivieren,
                    w&auml;hrend Sie die Migration durchf&uuml;hren!
                </p>
                <div class="alert alert-warning">Erstellen Sie unbedingt ein Backup der gesamten Datenbank <strong>BEVOR</strong> Sie das Script ausf&uuml;hren!</div>
                <div class="alert alert-warning">Verwenden Sie eine Serverkonsole und <strong>NICHT</strong> phpMyAdmin zum Ausf&uuml;hren des Scriptes!</div>
                <div class="alert alert-warning">Verwenden Sie das Script nur f&uuml;r die Migration <strong>DIESES</strong> JTL-Shops!</div>
                <form action="dbcheck.php" method="post">
                    {$jtl_token}
                    <div class="btn-group">
                        <button class="btn btn-primary" name="update" value="script"><i class="fa fa-cogs"></i>&nbsp;Script erstellen</button>
                    </div>
                </form>
            </div>
            {/if}
        </div>
    </div>
</div>
<script>
    {if !empty($tab) && $tab !== 'update_individual'}
    {literal}
    $(document).ready(function () {
        $('#contentCheck').hide();
    });
    {/literal}
    {/if}
    {literal}
    function doAutoMigration(status, table,  step, exclude) {
        if (cancelWait() && window.confirm('Wollen Sie die Struktur-Migration wirklich abbrechen?')) {
            updateModalWait('Migration wird beendet...', 1);
            window.location.reload(true);
            return;
        } else {
            cancelWait(false);
        }

        if (typeof status === 'undefined' || status === null) {
            status = 'start';
        }
        if (typeof step === 'undefined' || step === 0) {
            step = 1;
        }
        if (typeof table !== 'undefined' && table !== '') {
            updateModalWait('Migrate ' + table + ' Schritt ' + step);
        }
        if (typeof exclude === 'undefined') {
            exclude = [];
        }
        if (status === ' finished') {
            updateModalWait('Migration wird beendet...');
            window.location.reload(true);
        } else {
            ioCall('migrateToInnoDB_utf8', [status, table, step, exclude],
                function (data, context) {
                    if (data && typeof data.status !== 'undefined') {
                        if (data.status === 'migrate') {
                            // migrate next table...
                            if (data.nextTable === table && data.nextStep === 1) {
                                exclude.push(table);
                                updateModalWait(null, 1);
                            } else if (data.nextStep === 1) {
                                updateModalWait(null, 1);
                            }
                            doAutoMigration(data.status, data.nextTable, data.nextStep, exclude);
                        } else if (data.status === 'failure' || data.status === 'in_use') {
                            var msg = data.status === 'failure'
                                ? 'Bei der Migration der Tabelle ' + table + ' ist ein Fehler aufgetreten! Fortfahren?'
                                : 'Die Tabelle ' + table + ' ist in Benutzung und kann nicht migriert werden! Fortfahren?';
                            if (window.confirm(msg)) {
                                exclude.push(table);
                                updateModalWait(null, 1);
                                doAutoMigration('start', '', 1, exclude);
                            } else {
                                updateModalWait('Migration wird beendet...', 1);
                                window.location.reload(true);
                            }
                        } else {
                            // Migration finished
                            updateModalWait('Migration wird beendet...', 1);
                            window.location.reload(true);
                        }
                    } else {
                        if (window.confirm('Bei der Migration der Tabelle ' + table + ' ist ein Fehler aufgetreten! Fortfahren?')) {
                            exclude.push(table);
                            updateModalWait(null, 1);
                            doAutoMigration('start', '', 1, exclude);
                        } else {
                            updateModalWait('Migration wird beendet...', 1);
                            window.location.reload(true);
                        }
                    }
                },
                function (responseJSON) {
                    if (window.confirm('Bei der Migration der Tabelle ' + table + ' ist ein Fehler aufgetreten! Fortfahren?')) {
                        exclude.push(table);
                        updateModalWait(null, 1);
                        doAutoMigration('start', '', 1, exclude);
                    } else {
                        updateModalWait('Migration wird beendet...', 1);
                        window.location.reload(true);
                    }
                },
                {}
            )
        }
    }
    $('.nav-tabs a[href="#update_individual"]')
        .on('hidden.bs.tab', function(event){
            $('#contentCheck').hide();
        })
        .on('shown.bs.tab', function(event){
            $('#contentCheck').show();
        });
    $('form', '#update_automatic').on('submit', function (e) {
        if ($('#update_auto_backup').is(':checked')
            && ($('#update_auto_wartungsmodus_reject').is(':checked') || parseInt($('#update_auto_wartungsmodus').val()) === 1)) {
            showModalWait('Starten der automatischen Migration...', {/literal}{$engineUpdate->tableCount}{literal});
            doAutoMigration('start');
        } else {
            alert('Bitte bestätigen Sie den Wartungsmodus und das Backup!');
        }

        e.preventDefault();
    });
    {/literal}
</script>