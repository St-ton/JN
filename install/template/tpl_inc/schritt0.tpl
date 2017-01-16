{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if isset($versionAbort) && $versionAbort === false }
<script type="text/javascript">
{literal}
    function hosterChanged(id) {
        if (id == 1 || id == 2) {
            $('#hoster1').slideDown();
        } else {
            $('#hoster1').slideUp();
        }
        if (id == 1) {
            $('#strato-help').hide();
            $('#einsundeins-help').show();
        }
        if (id == 2) {
            $('#einsundeins-help').hide();
            $('#strato-help').show();
        }
    }


    function toggleArrow(canvasName) {
        var canvas = $("[id$=" + canvasName + "_arrow]");
        if(canvas.hasClass('glyphicon-chevron-right')) {
            canvas.removeClass('glyphicon-chevron-right');
            canvas.addClass('glyphicon-chevron-down');
        } else {
            canvas.removeClass('glyphicon-chevron-down');
            canvas.addClass('glyphicon-chevron-right');
        }
        return(true);
    }

    function toggleCanvas(canvasName) {
        $("[id$=" + canvasName + "]").slideToggle("slow", function() {
            toggleArrow(canvasName); // toggle the arrow for that canvas
        });
        return(true);
    }

    function summary(summary_name, state) {
        switch(state) {
            case 'failed':
                    $("[id$="+summary_name+"_span]").addClass("label-danger");
                    $("[id$="+summary_name+"]").addClass("fa-exclamation-triangle");
                    return(true);
                break;
            case 'warning':
                    $("[id$="+summary_name+"_span]").addClass("label-warning");
                    $("[id$="+summary_name+"]").addClass("fa-exclamation-triangle");
                    return(true);
                break;
            default:
                    $("[id$="+summary_name+"_span]").addClass("label-success");
                    $("[id$="+summary_name+"]").addClass("fa-check");
                    return(true);
                break;
        }
    }

    function closeAllCanvas() {
        // close server-canvas if no warnings nor errors
        $('#canvas_server').find('span').find('.label-warning').length || $('#canvas_server').find('span').find('.label-danger').length
                ? summary('summary_server', 'warning') // we did NOT close the canvas
                : $("[id$=canvas_server]").slideUp(1)
                    && toggleArrow('canvas_server')
                    && summary('summary_server', 'ok');
        // if errors, change the summary-icon to 'danger'
        $('#canvas_server').find('span').find('.label-danger').length
                ? summary('summary_server', 'failed')
                : null;

        // colse folders-canvas if no warnings
        $('ul#canvas_folders').find('li.alert-danger').length
                ? summary('summary_folders', 'failed') // we did NOT close the canvas and summarize it to 'failed'
                : $("[id$=canvas_folders]").slideUp(1) // we fold the canvas and summarize 'ok'
                    && toggleArrow('canvas_folders')
                    && summary('summary_folders', 'ok');
    }


    $(document).ready(function() {
        closeAllCanvas();
    });

{/literal}
</script>
{/if}

<h2 class="welcome">Herzlich Willkommen bei der Installation Ihres neuen JTL-Shops</h2>
<div class="well">
    <p>Wir freuen uns, dass Sie sich für JTL-Shop entschieden haben. Bei dieser Installation führen wir Sie Schritt für Schritt durch die Installation Ihres neuen Shops.</p>
    <p>Tipps und Hilfestellungen zur Installation finden Sie in unserem <a href="http://jtl-url.de/shop3inst" target="_blank"><i class="fa fa-external-link"></i> Installationsguide</a>. Bei offenen Fragen können Sie eine Anfrage im <a href="http://kundencenter.jtl-software.de/" target="_blank"><i class="fa fa-external-link"></i> Kundencenter</a> stellen. Einer unserer Mitarbeiter hilft Ihnen gerne weiter.</p>
    {if isset($versionAbort) && $versionAbort === false }<p><strong>Wir wünschen Ihnen viel Erfolg und viel Freude mit Ihrem neuen JTL-Shop!</strong></p> {/if}
</div>

{if isset($cHinweis) && $cHinweis|@count_characters > 0}
    <div class="alert alert-danger">
        <i class="fa fa-warning"></i> {$cHinweis}
    </div>
{/if}


{if isset($versionAbort) && $versionAbort === false }
<div class="panel panel-default">
    <div class="panel-heading" style="cursor:pointer;background-color:#eeeeee" onclick="toggleCanvas('canvas_server');">
        <div style="width:100%;text-align:right;">
            <h3 class="panel-title" style="float:left;">
                <span id="summary_server_span" class="label"><i class="fa" id="summary_server"></i></span>
                <span style="margin-left:10px;">Erfüllt der Server alle Anforderungen?</span>
            </h3>
            <span class="glyphicon glyphicon-chevron-down" id="canvas_server_arrow"></span>
        </div>
    </div>

    <div id="canvas_server">

        {if $oTests.programs|count > 0}
            <div class="page-header">
                <h4>Installierte Software</h4>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="col-xs-7">Software</th>
                        <th class="col-xs-3 text-center">Voraussetzung</th>
                        <th class="col-xs-2 text-center">Vorhanden</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $oTests.programs as $test}
                        {if !$test->getIsOptional() || $test->getIsRecommended()}
                        <tr>
                            <td>
                                <div class="test-name">
                                    <strong>{$test->getName()|utf8_decode}</strong><br>
                                    {if $test->getDescription()|@count_characters > 0}
                                        <p class="hidden-xs expandable">{$test->getDescription()|utf8_decode}</p>
                                    {/if}
                                </div>
                            </td>
                            <td class="text-center">{$test->getRequiredState()}</td>
                            <td class="text-center">{call test_result test=$test}</td>
                        </tr>
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        {/if}

        {if $oTests.php_modules|count > 0}
            <div class="page-header">
                <h4>Ben&ouml;tigte PHP-Erweiterungen und -Funktionen</h4>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="col-xs-10">Bezeichnung</th>
                        <th class="col-xs-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $oTests.php_modules as $test}
                        {if !$test->getIsOptional() || $test->getIsRecommended()}
                            <tr>
                                <td>
                                    <div class="test-name">
                                        <strong>{$test->getName()|utf8_decode}</strong><br>
                                        {if $test->getDescription()|@count_characters > 0}
                                            <p class="hidden-xs expandable">{$test->getDescription()|utf8_decode}</p>
                                        {/if}
                                    </div>
                                </td>
                                <td class="text-center">{call test_result test=$test}</td>
                            </tr>
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        {/if}

        {if $oTests.php_config|count > 0}
            <div class="page-header">
                <h4>Ben&ouml;tigte PHP-Einstellungen</h4>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="col-xs-7">Einstellung</th>
                        <th class="col-xs-3 text-center">Ben&ouml;tigter Wert</th>
                        <th class="col-xs-2 text-center">Ihr System</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $oTests.php_config as $test}
                        {if !$test->getIsOptional() || $test->getIsRecommended()}
                            <tr>
                                <td>
                                    <div class="test-name">
                                        <strong>{$test->getName()|utf8_decode}</strong><br>
                                        {if $test->getDescription()|@count_characters > 0}
                                            <p class="hidden-xs expandable">{$test->getDescription()|utf8_decode}</p>
                                        {/if}
                                    </div>
                                </td>
                                <td class="text-center">{$test->getRequiredState()}</td>
                                <td class="text-center">{call test_result test=$test}</td>
                            </tr>
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        {/if}

    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading" style="cursor:pointer;background-color:#eeeeee;" onclick="toggleCanvas('canvas_folders');">
        <div style="width:100%;text-align:right;">
            <h3 class="panel-title" style="float:left;">
                <span id="summary_folders_span" class="label"><i id="summary_folders" class="fa"></i></span>
                <span style="margin-left:10px;">Überprüfe Schreibrechte</span>
            </h3>
            <span class="glyphicon glyphicon-chevron-down" id="canvas_folders_arrow"></span>
        </div>
    </div>
    <ul class="list-group req" id="canvas_folders">
        {foreach name=beschreibbareverzeichnisse from=$cVerzeichnis_arr key=cVerzeichnis item=bBeschreibbar}
            <li class="list-group-item {if $smarty.foreach.beschreibbareverzeichnisse.index % 2 == 0}first{else}second{/if}{if !$bBeschreibbar} alert-danger{/if}">
                {*<span style="text-align:middle;">*}
                <span style="text-align:middle;">
                    {if $bBeschreibbar}
                    <span class="label label-success"><i class="fa fa-check"></i></span>
                    {else}
                    <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i></span>
                    {/if}
                    <span style="margin-left:10px;">{$cVerzeichnis}</span>
                </span>
            </li>
        {/foreach}
    </ul>
</div>

{if $bOk}
    <form name="install" method="post" action="index.php" class="form-horizontal">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color:#eeeeee">
                <h3 class="panel-title">Datenbank einrichten</h3>
            </div>
            <div class="panel-body">
                <div class="well">
                    <p>Für die Installation des JTL-Shops benötigen wir eine MySQL-Datenbank.</p>

                    <p>Meistens müssen der Benutzer und die Datenbank erst manuell erstellt werden. Bei Problemen wenden Sie sich
                        bitte an Ihren Administrator bzw. Webhoster, da dieser Vorgang von Hoster zu Hoster unterschiedlich ist und von der eingesetzten Software abhängt.</p>

                    <p>Der Benutzer benötigt Lese-, Schreib- und Löschrechte (<i>Create, Insert, Update, Delete</i>) für diese Datenbank.</p>

                    <p>Als <strong>Host</strong> ist "localhost" zumeist die richtige Einstellung. Diese Information bekommen Sie ebenfalls von Ihrem Webhoster.</p>
                    <p>Das Feld <strong>Socket</strong> füllen Sie bitte nur aus, wenn Sie ganz sicher sind, dass Ihre Datenbank über einen Socket erreichbar ist. In diesem Fall tragen Sie bitte den absoluten Pfad zum Socket ein.</p>
                </div>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Host</strong></span>
                            <input class="form-control" id="dbhost" type="text" name="DBhost" required size="35" value="localhost" placeholder="Host" />
                            <span class="input-group-addon">
                                <i class="fa fa-home"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Socket (optional)</strong></span>
                            <input class="form-control" id="dbsocket" type="text" name="DBsocket" size="35" value="" placeholder="Socket (z.B. /tmp/mysql5.sock)" />
                            <span class="input-group-addon">
                                <i class="fa fa-exchange"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Benutzername</strong></span>
                            <input class="form-control" id="dbuser" type="text" name="DBuser" required size="35" placeholder="Datenbank-Benutzername" />
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Passwort</strong></span>
                            <input class="form-control" id="dbpass" type="text" name="DBpass" required size="35" placeholder="Datenbank-Passwort" />
                            <span class="input-group-addon">
                                <i class="fa fa-lock"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Datenbank-Name</strong></span>
                            <input class="form-control" id="dbname" type="text" name="DBname" required size="35" placeholder="Datenbank-Name" />
                            <span class="input-group-addon">
                                <i class="fa fa-database"></i>
                            </span>
                        </div>
                    </div>
                    <input type="hidden" name="installiere" value="1" />
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-share"></i> Installation starten</button>
    </form>
{/if} {* bOk *}

{/if} {* versionAbort *}

