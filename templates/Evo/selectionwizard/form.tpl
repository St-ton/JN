<p class="selection-wizard-desc">
    {$AWA->getDescription()}
</p>
<div class="list-group selection-wizard">
    {foreach $AWA->getQuestions() as $nQuestion => $oFrage}
        {include file='selectionwizard/question.tpl' AWA=$AWA nQuestion=$nQuestion oFrage=$oFrage}
    {/foreach}
    {*{foreach $AWA->getQuestionAttributes() as $nQuestion => $oFrageMerkmal}*}
        {*{include file='selectionwizard/question.tpl' AWA=$AWA nQuestion=$nQuestion oFrageMerkmal=$oFrageMerkmal}*}
    {*{/foreach}*}
</div>