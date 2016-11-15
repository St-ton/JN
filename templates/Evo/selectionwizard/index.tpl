{if isset($AWA)}
    <script>
        var nSelection_arr = [{$AWA->getSelections()|implode:','}];

        function setSelectionWizardAnswerJS(kMerkmalWert)
        {
            nSelection_arr.push(kMerkmalWert);
            $.evo.io().call('setSelectionWizardAnswers', ['{$AWA->getLocationKeyName()}', {$AWA->getLocationKeyId()},
                {$smarty.session.kSprache}, nSelection_arr], {}, function (error, data) { });
        }

        function resetSelectionWizardAnswerJS(nFrage)
        {
            nSelection_arr.splice(nFrage);
            $.evo.io().call('setSelectionWizardAnswers', ['{$AWA->getLocationKeyName()}', {$AWA->getLocationKeyId()},
                {$smarty.session.kSprache}, nSelection_arr], { }, function (error, data) { });
        }
    </script>
    <div id="selectionwizard">
        <p class="selection-wizard-desc">
            {$AWA->getDescription()}
        </p>
        {include file="selectionwizard/form.tpl" AWA=$AWA}
    </div>
{/if}