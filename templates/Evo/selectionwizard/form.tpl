<div class="list-group selection-wizard">
    <p class="selection-wizard-desc">
        {$AWA->getDescription()}
    </p>
    {foreach $AWA->getQuestionAttributes() as $nQuestion => $oFrageMerkmal}
        {include file='selectionwizard/question.tpl' AWA=$AWA nQuestion=$nQuestion oFrageMerkmal=$oFrageMerkmal}
    {/foreach}
</div>