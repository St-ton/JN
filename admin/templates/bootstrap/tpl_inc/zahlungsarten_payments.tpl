{include file='tpl_inc/seite_header.tpl' cTitel="Zahlungseing&auml;nge f&uuml;r: "|cat:$oZahlungsart->cName cBeschreibung='Hello World' cDokuURL=Nix}
<div id="content" class="container-fluid">
    <div class="panel panel-default">
        <table class="table">
            <thead>
                <tr>
                    <th>Datum/Zeit</th>
                    <th>Bestell-Nr.</th>
                    <th>Kunde</th>
                    <th>Gezahlter Betrag</th>
                    <th>Zahlungsgeb&uuml;hr</th>
                    <th>W&auml;hrung</th>
                    <th>Abgeholt durch Wawi</th>
                </tr>
            </thead>
            <tbody>
                {foreach $oZahlunseingang_arr as $oZahlunseingang}
                    <tr>
                        <td>{$oZahlunseingang->dZeit}</td>
                        <td>Bestell-Nr.</td>
                        <td>Kunde</td>
                        <td>Gezahlter Betrag</td>
                        <td>Zahlungsgeb&uuml;hr</td>
                        <td>W&auml;hrung</td>
                        <td>Abgeholt durch Wawi</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>