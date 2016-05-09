<div class="widget-custom-data">
    <ul class="infolist">
        <li class="first">
            <p>
                <strong>Maximale PHP Ausf&uuml;hrungszeit:</strong> <span class="value{if $bMaxExecutionTime === false} error{/if}">{$maxExecutionTime}</span>
            </p>
        </li>
        <li>
            <p>
                <strong>PHP-Speicherlimit:</strong> <span class="value{if $bMemoryLimit === false} error {/if}">{$memoryLimit}</span>
            </p>
        </li>
        <li>
            <p>
                <strong>Maximale PHP &Uuml;bertragungsgr&ouml;&szlig;e (FILE):</strong> <span class="value{if $bMaxFilesize === false} aaaaa error{/if}">{$maxFilesize}</span>
            </p>
        </li>
        <li>
            <p>
                <strong>Maximale PHP &Uuml;bertragungsgr&ouml;&szlig;e (POST):</strong> <span class="value{if $bPostMaxSize === false} error{/if}">{$postMaxSize}</span>
            </p>
        </li>
        <li class="last">
            <p>
                <strong>allow_url_fopen aktiviert:</strong> <span class="value{if $bAllowUrlFopen == false}">nein{else}">ja{/if}</span>
            </p>
        </li>
        <li class="last">
            N&auml;here Informationen zu Systemvorraussetzungen finden Sie im <a href="http://guide.jtl-software.de/jtl/JTL-Shop:Installation:Neuinstallation" target="_blank"><i class="fa fa-external-link"></i> Guide</a>.
        </li>
    </ul>
</div>