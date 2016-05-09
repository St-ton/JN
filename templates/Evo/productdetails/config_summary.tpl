{strip}
    {if $oKonfig->oKonfig_arr|@count > 0}
        {foreach from=$oKonfig->oKonfig_arr item=oKonfiggruppe}
            {if $oKonfiggruppe->bAktiv}
                {assign var=oGruppeSprache value=$oKonfiggruppe->getSprache()}
                <h4>{$oGruppeSprache->getName()}</h4>
                <dl class="dl-horizontal">
                    {foreach from=$oKonfiggruppe->oItem_arr item=oKonfigitem}
                        {if $oKonfigitem->bAktiv}
                            <dt>{$oKonfigitem->fAnzahl}x</dt>
                            <dd>{$oKonfigitem->getName()}</dd>
                        {/if}
                    {/foreach}
                </dl>
            {/if}
        {/foreach}
    {/if}
{/strip}
