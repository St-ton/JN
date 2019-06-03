{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-checkbox'}
    {if empty($cPost_arr)}
        {assign var=cPost_arr value=null}
    {/if}
    {if empty($cPost_arr)}
        {assign var=cPost_arr value=$smarty.post}
    {/if}

    {getCheckBoxForLocation nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$cPlausi_arr cPost_arr=$cPost_arr assign='checkboxes'}
    {if !empty($checkboxes)}
        {block name='snippets-checkbox-checkboxes'}
            {foreach $checkboxes as $cb}
                {formgroup}
                    {block name='snippets-checkbox-checkbox'}
                        {checkbox
                            id="{if isset($cIDPrefix)}{$cIDPrefix}_{/if}{$cb->cID}"
                            name={$cb->cID}
                            required=$cb->nPflicht === 1
                            checked=$cb->isActive
                        }
                           {$cb->cName}
                            {if !empty($cb->cLinkURL)}
                                <span class='moreinfo'>({link href=$cb->cLinkURL class='popup checkbox-popup'}{lang key='read' section='account data'}{/link})</span>
                            {/if}
                            {if empty($cb->nPflicht)}<span class='optional'> - {lang key='optional'}</span>{/if}
                        {/checkbox}
                    {/block}
                    {if !empty($cb->cBeschreibung)}
                        {block name='snippets-checkbox-description'}
                            <p class="description text-muted small">
                                {$cb->cBeschreibung}
                            </p>
                        {/block}
                    {/if}
                {/formgroup}
            {/foreach}
        {/block}
    {/if}
{/block}
