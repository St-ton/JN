{config_load file="$lang.conf" section="systemcheck"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#systemcheck# cBeschreibung=#systemcheckDesc# cDokuURL=#systemcheckURL#}

{function test_result}
    {if $test->getResult() == 0}
        <span class="hidden-xs">
            <h4 class="label-wrap"><span class="label label-success">
                {if $test->getCurrentState()|@count_characters > 0}
                    {$test->getCurrentState()}
                {else}
                    <i class="fa fa-check" aria-hidden="true"></i>
                {/if}
            </span></h4>
        </span>
        <span class="visible-xs">
            <h4 class="label-wrap"><span class="label label-success">
                <i class="fa fa-check" aria-hidden="true"></i>
            </span></h4>
        </span>
    {elseif $test->getResult() == 1}
        {if $test->getIsOptional()}
        <span class="hidden-xs">
            {if $test->getIsRecommended()}
                <h4 class="label-wrap"><span class="label label-warning">
                    {if $test->getCurrentState()|@count_characters > 0}
                        {$test->getCurrentState()}
                    {else}
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    {/if}
                </span></h4>
            {else}
                <h4 class="label-wrap"><span class="label label-primary">
                    {if $test->getCurrentState()|@count_characters > 0}
                        {$test->getCurrentState()}
                    {else}
                        <i class="fa fa-times" aria-hidden="true"></i>
                    {/if}
                </span></h4>
            {/if}
        </span>
        <span class="visible-xs">
            {if $test->getIsRecommended()}
                <h4 class="label-wrap"><span class="label label-warning">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                </span></h4>
            {else}
                <h4 class="label-wrap"><span class="label label-primary">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </span></h4>
            {/if}
        </span>
        {else}
        <span class="hidden-xs">
            <h4 class="label-wrap"><span class="label label-danger">
                {if $test->getCurrentState()|@count_characters > 0}
                    {$test->getCurrentState()}
                {else}
                    <i class="fa fa-times" aria-hidden="true"></i>
                {/if}
            </span></h4>
        </span>
        <span class="visible-xs">
            <h4 class="label-wrap"><span class="label label-danger">
                <i class="fa fa-times" aria-hidden="true"></i>
            </span></h4>
        </span>
        {/if}
    {elseif $test->getResult() == 2}
    {/if}
{/function}

<div id="content" class="container-fluid">
    <div class="systemcheck">
        {*
        <div class="form-horizontal">
            <div class="page-header">
                <h1>Webhosting-Plattform</h1>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Provider:</label>
                <div class="col-sm-10">
                    <p class="form-control-static">
                        {if $platform->getProvider() == 'jtl'}
                            JTL-Software GmbH
                        {elseif $platform->getProvider() == 'hosteurope'}
                            HostEurope
                        {elseif $platform->getProvider() == 'strato'}
                            Strato
                        {elseif $platform->getProvider() == '1und1'}
                            1&amp;1
                        {elseif $platform->getProvider() == 'alfahosting'}
                            Alfahosting
                        {else}
                            <em>Unbekannt</em> ({$platform->getHostname()})
                        {/if}
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">PHP-Version:</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{$platform->getPhpVersion()}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Document Root:</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{$platform->getDocumentRoot()}</p>
                </div>
            </div>
            {if $platform->getProvider() == 'hosteurope' || $platform->getProvider() == 'strato' || $platform->getProvider() == '1und1'}
            <div class="form-group">
                <label class="col-sm-2 control-label">Hinweise:</label>
                <div class="col-sm-10">
                    <p class="form-control-static">
                        {$version = $platform->getPhpVersion()}
                        {if $platform->getProvider() == 'hosteurope'}
                            Sie können die PHP-Einstellungen im <a href="https://kis.hosteurope.de/">HostEurope-KIS</a> (<a href="https://kis.hosteurope.de/">https://kis.hosteurope.de/</a>) anpassen.
                        {elseif $platform->getProvider() == 'strato'}
                            Bitte laden Sie <a href="http://www.ioncube.com/loaders.php">hier</a> den ionCube-Loader herunter und entpacken Sie das Archiv nach {$platform->getDocumentRoot()} auf dem Server.<br>
                            Erstellen Sie auf dem Server eine Datei <code>php.ini</code> mit dem folgenden Inhalt:<br><br>
                        <pre>[Zend]
    zend_extension = {$platform->getDocumentRoot()}/ioncube/ioncube_loader_lin_{$version|substr:0:3}.so</pre>
                        {elseif $platform->getProvider() == '1und1'}
                            Bitte laden Sie <a href="http://www.ioncube.com/loaders.php">hier</a> den ionCube-Loader herunter und entpacken Sie das Archiv nach {$platform->getDocumentRoot()} auf dem Server.<br>
                            Erstellen Sie auf dem Server eine Datei <code>php.ini</code> mit dem folgenden Inhalt:<br><br>
                        <pre>[Zend]
    zend_extension = {$platform->getDocumentRoot()}/ioncube/ioncube_loader_lin_{$version|substr:0:3}.so</pre>
                        {/if}
                    </p>
                </div>
            </div>
            {/if}
        </div>
        *}

        {if !$passed}
            <div class="alert alert-warning">
                Um einen einwandfreien Betrieb gewährleisten zu können ist es zwingend erforderlich alle <code>markierten</code> Eigeschaften zu überprüfen.
            </div>
        {/if}
        
        {if $tests.recommendations|count > 0}
            <div class="page-header">
                <h1>Empfohlene Anpassungen</h1>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="col-xs-7">&nbsp;</th>
                        <th class="col-xs-3 text-center">Empfohlener Wert</th>
                        <th class="col-xs-2 text-center">Ihr System</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $tests.recommendations as $test}
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
                        <td class="text-center">{test_result test=$test}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        {/if}

        {if $tests.programs|count > 0}
            <div class="page-header">
                <h1>Installierte Software</h1>
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
                    {foreach $tests.programs as $test}
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
                            <td class="text-center">{test_result test=$test}</td>
                        </tr>
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        {/if}

        {if $tests.php_modules|count > 0}
            <div class="page-header">
                <h1>Ben&ouml;tigte PHP-Erweiterungen und -Funktionen</h1>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="col-xs-10">Bezeichnung</th>
                        <th class="col-xs-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $tests.php_modules as $test}
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
                                <td class="text-center">{test_result test=$test}</td>
                            </tr>
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        {/if}

        {if $tests.php_config|count > 0}
            <div class="page-header">
                <h1>Ben&ouml;tigte PHP-Einstellungen</h1>
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
                    {foreach $tests.php_config as $test}
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
                                <td class="text-center">{test_result test=$test}</td>
                            </tr>
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        {/if}
    </div>
</div>

{include file='tpl_inc/footer.tpl'}