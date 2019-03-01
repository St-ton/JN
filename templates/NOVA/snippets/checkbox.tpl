{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if empty($cPost_arr)}
    {assign var=cPost_arr value=null}
{/if}
{if empty($cPost_arr)}
    {assign var=cPost_arr value=$smarty.post}
{/if}

{getCheckBoxForLocation nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$cPlausi_arr cPost_arr=$cPost_arr assign='checkboxes'}
{if !empty($checkboxes)}
    {foreach $checkboxes as $cb}
        {formgroup}
            {checkbox id="{if isset($cIDPrefix)}{$cIDPrefix}_{/if}{$cb->cID}" required=$cb->nPflicht === 1}
               {$cb->cName}
                {if !empty($cb->cLinkURL)}
                    <span class='moreinfo'>({link href=$cb->cLinkURL class='popup checkbox-popup'}{lang key='read' section='account data'}{/link})</span>
                {/if}
                {if empty($cb->nPflicht)}<span class='optional'> - {lang key='optional'}</span>{/if}
            {/checkbox}
            {if !empty($cb->cBeschreibung)}
                <p class="description text-muted small">
                    {$cb->cBeschreibung}
                </p>
            {/if}
        {/formgroup}
    {/foreach}
{/if}
