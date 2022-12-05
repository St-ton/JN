{foreach $oHelp_arr as $oHelp}
    <li>
        <p>
            {if $oHelp->cIconURL|strlen > 0}
                <img src="{urldecode($oHelp->cIconURL)}" alt="" title="{$oHelp->cTitle}" />
            {/if}
            <a href="{$oHelp->cURL}" title="{$oHelp->cTitle}" target="_blank" rel="noopener">
                {$oHelp->cTitle|truncate:50:'...'}
            </a>
        </p>
    </li>
{/foreach}
