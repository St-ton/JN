{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($AWA)}
    <div class="h3 selection-wizard-desc">
        {$AWA->getDescription()}
    </div>
    {listgroup class="selection-wizard list-group-flush"}
        {foreach $AWA->getQuestions() as $nQuestion => $oFrage}
            {if $AWA->getConf('auswahlassistent_allefragen') === 'Y' || $nQuestion <= $AWA->getCurQuestion()}
                {include file='selectionwizard/question.tpl' AWA=$AWA nQuestion=$nQuestion oFrage=$oFrage}
            {/if}
        {/foreach}
    {/listgroup}
{/if}
