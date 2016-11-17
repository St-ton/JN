<div class="list-group-item selection-wizard-question {if $nQuestion !== $AWA->getCurQuestion()}disabled{/if}">
    <h4 class="list-group-item-heading selection-wizard-question-heading">
        {$oFrage->cFrage}
        {if $nQuestion < $AWA->getCurQuestion()}
            <a href="#" class="fa fa-edit" onclick="return resetSelectionWizardAnswerJS({$nQuestion});"></a>
        {/if}
    </h4>
    {if $nQuestion < $AWA->getCurQuestion()}
        <span class="selection-wizard-answer">
            {assign var="oWert" value=$AWA->getSelectedValue($nQuestion)}
            {if $AWA->getOption('auswahlassistent_anzeigeformat')|in_array:['B', 'BT'] && $oWert->cBildpfadKlein !== ''}
                <img src="{$oWert->cBildpfadKlein}">
            {/if}
            {if $AWA->getOption('auswahlassistent_anzeigeformat')|in_array:['T', 'BT', 'S']}
                {$oWert->cWert}
            {/if}
        </span>
    {elseif $nQuestion === $AWA->getCurQuestion()}
        {if $AWA->getOption('auswahlassistent_anzeigeformat') === 'S'}
            <label for="kMerkmalWert-{$nQuestion}" class="sr-only">{lang key="pleaseChoose" section="global"}</label>
            <select id="kMerkmalWert-{$nQuestion}"
                    onchange="return setSelectionWizardAnswerJS($(this).val());">
                <option value="-1">{lang key="pleaseChoose" section="global"}</option>
                {foreach $oFrage->oWert_arr as $oWert}
                    {if isset($oWert->nAnzahl)}
                        <option value="{$oWert->kMerkmalWert}">
                            {$oWert->cWert}
                            {if $AWA->getOption('auswahlassistent_anzahl_anzeigen') === 'Y'}
                                ({$oWert->nAnzahl})
                            {/if}
                        </option>
                    {/if}
                {/foreach}
            </select>
        {else}
            {foreach $oFrage->oWert_arr as $oWert}
                {if isset($oWert->nAnzahl)}
                    <a class="selection-wizard-answer" href="#"
                       onclick="return setSelectionWizardAnswerJS({$oWert->kMerkmalWert});">
                        {if $AWA->getOption('auswahlassistent_anzeigeformat')|in_array:['B', 'BT'] && $oWert->cBildpfadKlein !== ''}
                            <img src="{$oWert->cBildpfadKlein}">
                        {/if}
                        {if $AWA->getOption('auswahlassistent_anzeigeformat')|in_array:['T', 'BT']}
                            {$oWert->cWert}
                            {if $AWA->getOption('auswahlassistent_anzahl_anzeigen') === 'Y'}
                                <span class="badge">
                                    {$oWert->nAnzahl}
                                </span>
                            {/if}
                        {/if}
                    </a>
                {/if}
            {/foreach}
        {/if}
    {elseif $nQuestion > $AWA->getCurQuestion()}
        {if $AWA->getOption('auswahlassistent_anzeigeformat') === 'S'}
            <label for="kMerkmalWert-{$nQuestion}" class="sr-only">{lang key="pleaseChoose" section="global"}</label>
            <select id="kMerkmalWert-{$nQuestion}" disabled="disabled">
                <option value="-1">{lang key="pleaseChoose" section="global"}</option>
            </select>
        {else}
            {foreach $oFrage->oWert_arr as $oWert}
                {if isset($oWert->nAnzahl)}
                    <span class="selection-wizard-answer">
                        {if $AWA->getOption('auswahlassistent_anzeigeformat')|in_array:['B', 'BT'] && $oWert->cBildpfadKlein !== ''}
                            <img src="{$oWert->cBildpfadKlein}">
                        {/if}
                        {if $AWA->getOption('auswahlassistent_anzeigeformat')|in_array:['T', 'BT']}
                            {$oWert->cWert}
                            {if $AWA->getOption('auswahlassistent_anzahl_anzeigen') === 'Y'}
                                <span class="badge">
                                    {$oWert->nAnzahl}
                                </span>
                            {/if}
                        {/if}
                    </span>
                {/if}
            {/foreach}
        {/if}
    {/if}
</div>