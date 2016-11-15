<div class="list-group selection-wizard">
    {foreach $AWA->getQuestionAttributes() as $nQuestion => $oFrageMerkmal}
        {include file='selectionwizard/question.tpl' AWA=$AWA nQuestion=$nQuestion oFrageMerkmal=$oFrageMerkmal}
    {/foreach}
</div>