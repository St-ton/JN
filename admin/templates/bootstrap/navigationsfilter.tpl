{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="navigationsfilter"}

<script type="text/javascript">
    var bManuell = false;

    function selectCheck(selectBox) {ldelim}
        if (selectBox.selectedIndex === 1) {ldelim}
            // Laden falls vorhanden
            {if isset($oPreisspannenfilter_arr) && $oPreisspannenfilter_arr|@count > 0}
            {assign var=i value=1}
            {foreach name=werte from=$oPreisspannenfilter_arr item=oPreisspannenfilter}
            document.getElementById('nVon_{$smarty.foreach.werte.iteration-$i}').value = {$oPreisspannenfilter->nVon};
            document.getElementById('nBis_{$smarty.foreach.werte.iteration-$i}').value = {$oPreisspannenfilter->nBis};
            {/foreach}
            {/if}

            document.getElementById('Werte').style.display = 'block';
            bManuell = true;
            {rdelim} else if (selectBox.selectedIndex === 0) {ldelim}
            document.getElementById('Werte').style.display = 'none';
            bManuell = false;
            {rdelim}
        {rdelim}

    // Plausibilitaetspruefung
    function speicherDaten() {ldelim}
        if (bManuell === true) {ldelim}
            var bCheck = true,
                cFehler = '',
                j;
            // Resetten
            for (j = 0; j < 10; j++) {ldelim}
                document.getElementById('nVon_' + j).style.background = '#FFFFFF';
                document.getElementById('nBis_' + j).style.background = '#FFFFFF';
                {rdelim}

            for (var i = 0; i < 10; i++) {ldelim}
                if (i > 0) {ldelim}// Zeilen >= 2
                    if (document.getElementById("nVon_" + i).value.length > 0) {ldelim}
                        // Wenn das Feld "nVon" gesetzt wurde, muss auch "nBis" gesetzt werden
                        if (document.getElementById("nBis_" + i).value.length > 0) {ldelim}
                            // Wenn beide Felder gesetzt wurde, muss der Wert von "nVon" < sein als "nBis"
                            if (parseFloat(document.getElementById("nVon_" + i).value) < parseFloat(document.getElementById("nBis_" + i).value)) {ldelim}

                                // Wenn beide Felder gesetzt wurden, "nVon" < ist als "nBis", dann muss "nVon" aus der Iteration > sein als "nBis" von der Iteration -1
                                if (parseFloat(document.getElementById("nVon_" + i).value) < parseFloat(document.getElementById("nBis_" + (i - 1)).value)) {ldelim}
                                    bCheck = false;
                                    cFehler += "Fehler: Das Feld \"Von\" muss gr&ouml;&szlig;er oder gleich sein, als das Feld \"Bis\" von der voherigen Zeile.";
                                    document.getElementById("nVon_" + i).style.background = "#FFE4E1";
                                    {rdelim}
                                {rdelim}
                            else {ldelim}
                                bCheck = false;
                                cFehler += "Fehler: Das Feld \"Von\" muss kleiner sein als \"Bis\".";
                                document.getElementById("nVon_" + i).style.background = "#FFE4E1";
                                document.getElementById("nBis_" + i).style.background = "#FFE4E1";
                                {rdelim}
                            {rdelim}
                        else {ldelim}
                            bCheck = false;
                            cFehler += "Fehler: Wenn \"Von\" gesetzt wurd, muss auch \"Bis\" gesetzt werden.";
                            document.getElementById("nBis_" + i).style.background = "#FFE4E1";
                            {rdelim}
                        {rdelim}
                    {rdelim} else {ldelim} // Erster Durchlauf, Zeile 1
                    if (document.getElementById("nVon_" + i).value.length > 0 && document.getElementById("nBis_" + i).value.length > 0) {ldelim}
                        if (parseFloat(document.getElementById("nVon_" + i).value) >= parseFloat(document.getElementById("nBis_" + i).value)) {ldelim}
                            bCheck = false;
                            cFehler = "Fehler: Das Feld \"Von\" muss kleiner sein als \"Bis\".";
                            document.getElementById("nVon_" + i).style.background = "#FFE4E1";
                            document.getElementById("nBis_" + i).style.background = "#FFE4E1";
                            {rdelim}
                        {rdelim} else {ldelim}
                        bCheck = false;
                        cFehler = "Fehler: Keiner der beiden Felder darf leer sein.";
                        document.getElementById("nVon_" + i).style.background = "#FFE4E1";
                        document.getElementById("nBis_" + i).style.background = "#FFE4E1";
                        {rdelim}
                    {rdelim}
                {rdelim}

            if (!bCheck) {ldelim}
                //document.getElementById("Werte").innerHTML += cFehler;
                alert(cFehler);
                {rdelim} else {ldelim} // Alles O.K. -> Form abschicken
                document.einstellen.submit();
                {rdelim}
            {rdelim} else {ldelim}
            document.einstellen.submit();
            {rdelim}
        {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#navigationsfilter# cBeschreibung=#navigationsfilterDesc# cDokuURL=#navigationsfilterUrl#}
<div id="content" class="container-fluid">
    <form name="einstellen" method="post" action="navigationsfilter.php" id="einstellen">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1"/>
        <div id="settings">
            {assign var=open value=false}
            {foreach name=conf from=$oConfig_arr item=oConfig}
            {if $oConfig->cConf === 'Y'}
            <div class="item input-group">
                <span class="input-group-addon">
                    <label for="{$oConfig->cWertName}">{$oConfig->cName}</label>
                </span>
                {if $oConfig->cInputTyp === 'selectbox'}
                    <span class="input-group-wrap">
                       <select id="{$oConfig->cWertName}" name="{$oConfig->cWertName}"
                                class="form-control combo" {if $oConfig->cWertName === 'preisspannenfilter_anzeige_berechnung'} onChange="selectCheck(this);"{/if}>
                            {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                <option value="{$wert->cWert}"
                                        {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                            {/foreach}
                        </select>
                  </span>
                {elseif $oConfig->cInputTyp === 'number'}
                    <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"
                           value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1"/>
                {else}
                    <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"
                           value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1"/>
                {/if}
                <span class="input-group-addon">
                   {if $oConfig->cBeschreibung}
                        {getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}
                    {/if}
               </span>
                {if $oConfig->cWertName === 'preisspannenfilter_anzeige_berechnung'}
            </div>
            <div id="Werte" style="display: {if $oConfig->gesetzterWert === 'M'}block{else}none{/if};"
                 class="form-inline">
                {section name="werte" start=0 loop=10 step=1}
                    <div class="price-row" id="zeile_{$smarty.section.werte.index}">
                        <label for="nVon_{$smarty.section.werte.index}">{#navigationsfilterFrom#}:</label>
                        <input class="form-control" name="nVon[]" type="text" id="nVon_{$smarty.section.werte.index}"
                               value="{if isset($oPreisspannenfilter_arr[$smarty.section.werte.index]->nVon)}{$oPreisspannenfilter_arr[$smarty.section.werte.index]->nVon}{/if}">
                        <label for="nBis_{$smarty.section.werte.index}">{#navigationsfilterTo#}:</label>
                        <input class="form-control" name="nBis[]" type="text" id="nBis_{$smarty.section.werte.index}"
                               value="{if isset($oPreisspannenfilter_arr[$smarty.section.werte.index]->nBis)}{$oPreisspannenfilter_arr[$smarty.section.werte.index]->nBis}{/if}">
                    </div>
                {/section}
            </div>
            <div class="item input-group">
                {/if}
            </div>
            {else}
            {if $oConfig->cName}
            {if $open}</div>
</div>{/if}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            {$oConfig->cName} <span class="pull-right">{getHelpDesc cID=$oConfig->kEinstellungenConf}</span>
        </h3>
    </div>
    <div class="panel-body">
        {assign var=open value=true}
        {/if}
        {/if}
        {/foreach}
        {if $open}
    </div>
</div>
{/if}
</div>

<p class="submit">
    <button name="speichern" class="btn btn-primary" type="button" value="{#navigationsfilterSave#}" onclick="speicherDaten();">
        <i class="fa fa-save"></i> {#navigationsfilterSave#}
    </button>
</p>
</form>
</div>

<script type="text/javascript">
    selectCheck(document.getElementById('preisspannenfilter_anzeige_berechnung'));
</script>

{include file='tpl_inc/footer.tpl'}