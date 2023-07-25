{if count($oPatch_arr) > 0}
    <ul class="linklist">
    {foreach $oPatch_arr as $oPatch}
        <li>
            {if $oPatch->cIconURL|strlen > 0}
                <img src="{urldecode($oPatch->cIconURL)}" alt="" title="{$oPatch->cTitle}" />
            {/if}
            <p><a href="{$oPatch->cURL}" title="{$oPatch->cTitle}" target="_blank" rel="noopener">
                {$oPatch->cTitle|truncate:50:'...'}
                {$oPatch->cDescription}
            </a></p>
        </li>
    {/foreach}
    </ul>
{else}
    <div class="alert alert-info">{__('noPatchesATM')}</div>
{/if}
