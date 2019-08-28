{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='selectionwizard-index'}
    {if isset($AWA)}
        {container}
            {opcMountPoint id='opc_before_selection_wizard'}
            {block name='selectionwizard-index-script'}
                <script>
                    var deferredTasks = window.deferredTasks || [];
                    deferredTasks.push(["ready",function (){
                        var nSelection_arr = [{$AWA->getSelections()|implode:','}];

                        function setSelectionWizardAnswerJS(kMerkmalWert)
                        {
                            kMerkmalWert = parseInt(kMerkmalWert);
                            nSelection_arr.push(kMerkmalWert);
                            $.evo.io().call('setSelectionWizardAnswers', ['{$AWA->getLocationKeyName()}', {$AWA->getLocationKeyId()},
                                {$smarty.session.kSprache}, nSelection_arr], {}, function (error, data) {
                                    resetSelectionWizardListeners();
                            });

                            return false;
                        }

                        function resetSelectionWizardAnswerJS(nFrage)
                        {
                            nFrage = parseInt(nFrage);
                            nSelection_arr.splice(nFrage);
                            $.evo.io().call('setSelectionWizardAnswers', ['{$AWA->getLocationKeyName()}', {$AWA->getLocationKeyId()},
                                {$smarty.session.kSprache}, nSelection_arr], { }, function (error, data) {
                                    resetSelectionWizardListeners();
                            });

                            return false;
                        }

                        function resetSelectionWizardListeners()
                        {
                            $("[id^=kMerkmalWert]").on('change', function() {
                                return setSelectionWizardAnswerJS($(this).val());
                            } );
                            $(".question-edit").on('click', function() {
                                return resetSelectionWizardAnswerJS($(this).data('value'));
                            } );
                            $(".selection-wizard-answer").on('click', function() {
                                return setSelectionWizardAnswerJS($(this).data('value'));
                            } );
                        }

                        $(window).on("load", function() {
                            resetSelectionWizardListeners();
                        } );
                    }]);
                </script>
            {/block}
            {block name='selectionwizard-index-include-form'}
                <div id="selectionwizard" class="my-7">
                    {include file='selectionwizard/form.tpl' AWA=$AWA}
                </div>
            {/block}
        {/container}
    {/if}
{/block}
