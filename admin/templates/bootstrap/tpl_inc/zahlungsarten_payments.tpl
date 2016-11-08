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
                        <td>{$oZahlunseingang->cBestellNr}</td>
                        <td>
                            {$oZahlunseingang->cVorname} {$oZahlunseingang->cNachname}<br>
                            &lt;{$oZahlunseingang->cMail}&gt;
                        </td>
                        <td>
                            {$oZahlunseingang->fBetrag|number_format:2:',':'.'}
                        </td>
                        <td>
                            {$oZahlunseingang->fZahlungsgebuehr|number_format:2:',':'.'}
                        </td>
                        <td>{$oZahlunseingang->cISO}</td>
                        <td>
                            {if $oZahlunseingang->cAbgeholt === 'Y'}
                                <span class="label label-success" title="Aktiv"><i class="fa fa-check"></i></span>
                            {elseif $oZahlunseingang->cAbgeholt === 'N'}
                                <span class="label label-danger" title="Inaktiv"><i class="fa fa-times"></i></span>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>