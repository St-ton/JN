<div class="list-group-item selection-wizard-question {if $nQuestion > $AWA->getCurQuestion()}disabled{/if}">
    <h4 class="list-group-item-heading selection-wizard-question-heading">
        {$oFrageMerkmal->cFrage}
        {if $nQuestion < $AWA->getCurQuestion()}
            <a href="#" class="fa fa-edit" onclick="resetSelectionWizardAnswerJS({$nQuestion});return false;"></a>
        {/if}
    </h4>
    {if $nQuestion < $AWA->getCurQuestion()}
        <span class="selection-wizard-answer">
            {assign var="oWert" value=$AWA->getSelectedValue($nQuestion)}
            {if $oWert->cBildpfadKlein !== ''}
                <img src="{$oWert->cBildpfadKlein}">
            {/if}
            {$oWert->cWert}
        </span>
    {elseif $nQuestion === $AWA->getCurQuestion()}
        {foreach $oFrageMerkmal->oWert_arr as $i => $oWert}
            {if isset($oWert->nAnzahl)}
                <a class="selection-wizard-answer" href="#"
                   onclick="setSelectionWizardAnswerJS({$oWert->kMerkmalWert}, {$oWert->nAnzahl});return false;">
                    {if $oWert->cBildpfadKlein !== ''}
                        <img src="{$oWert->cBildpfadKlein}">
                    {/if}
                    {$oWert->cWert}
                    <span class="badge">
                        {$oWert->nAnzahl}
                    </span>
                </a>
            {/if}
        {/foreach}
    {elseif $nQuestion > $AWA->getCurQuestion()}
        {foreach $oFrageMerkmal->oWert_assoc as $oWert}
            {if isset($oWert->nAnzahl)}
                <span class="selection-wizard-answer">
                    {if $oWert->cBildpfadKlein !== ''}
                        <img src="{$oWert->cBildpfadKlein}">
                    {/if}
                    {$oWert->cWert}
                </span>
            {/if}
        {/foreach}
    {/if}
</div>