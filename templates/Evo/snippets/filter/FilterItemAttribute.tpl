
<br>OLD:<br>
{assign var='is_dropdown' value=false}
{if ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
    {assign var='is_dropdown' value=true}
{/if}

<ul {if $is_dropdown}class="dropdown-menu" role="menu" {elseif isset($class)}class="{$class}" {else}class="nav nav-list"{/if}>
    {foreach $Merkmal->oMerkmalWerte_arr as $MerkmalWert}
        {if $MerkmalWert->nAktiv}
            <li class="active">
                <a rel="nofollow" href="{if !empty($MerkmalWert->cURL)}{$MerkmalWert->cURL}{else}#{/if}"{if $Merkmal->cTyp === 'BILD'} title="{$MerkmalWert->cWert}"{/if}>
                    <span class="value">
                        <i class="fa fa-check-square-o text-muted"></i>
                        {if $MerkmalWert->cBildpfadKlein !== 'gfx/keinBild_kl.gif' && $Merkmal->cTyp !== 'TEXT'}
                            <img src="{$MerkmalWert->cBildpfadKlein}" alt="{$MerkmalWert->cWert|escape:'html'}" class="vmiddle" />
                        {/if}
                        {if $Merkmal->cTyp !== 'BILD'}
                            <span class="word-break">{$MerkmalWert->cWert|escape:'html'}</span>
                        {/if}
                        <span class="badge pull-right">{$MerkmalWert->nAnzahl}</span>
                    </span>
                </a>
            </li>
        {else}
            <li>
                <a rel="nofollow" href="{$MerkmalWert->cURL}"{if $Merkmal->cTyp === 'BILD'} title="{$MerkmalWert->cWert|escape:'html'}"{/if}>
                    <span class="value">
                        <i class="fa fa-square-o text-muted"></i>
                        {if $MerkmalWert->cBildpfadKlein !== 'gfx/keinBild_kl.gif' && $Merkmal->cTyp !== 'TEXT'}
                            <img src="{$MerkmalWert->cBildpfadKlein}" alt="{$MerkmalWert->cWert|escape:'html'}" class="vmiddle" />
                        {/if}
                        {if $Merkmal->cTyp !== 'BILD'}
                            <span class="word-break">{$MerkmalWert->cWert|escape:'html'}</span>
                        {/if}
                        <span class="badge pull-right">{$MerkmalWert->nAnzahl}</span>
                    </span>
                </a>
            </li>
        {/if}
    {/foreach}
</ul>
<br>new:<br>