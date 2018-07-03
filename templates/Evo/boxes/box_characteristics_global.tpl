{foreach $oBox->getItems() as $oMerkmal}
    <section class="panel panel-default box box-global-characteristics" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">
            {if !empty($oMerkmal->cBildpfadKlein) && $oMerkmal->cBildpfadKlein !== $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN}
                <img src="{$oMerkmal->cBildURLKlein}" alt="" class="vmiddle" />
            {/if}
            {$oMerkmal->cName}
            </div>
        </div>
        <div class="box-body">
            {if ($oMerkmal->cTyp === 'SELECTBOX') && $oMerkmal->oMerkmalWert_arr|@count > 1}
                <div class="dropdown">
                    <button class="btn btn-default btn-block dropdown-toggle" type="button" id="dropdown-characteristics-{$oMerkmal->kMerkmal}" data-toggle="dropdown" aria-expanded="true">
                        {$oMerkmal->cName}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdown-characteristics-{$oMerkmal->kMerkmal}">
                        {foreach $oMerkmal->oMerkmalWert_arr as $oMerkmalWert}
                            <li role="presentation">
                                <a role="menuitem" tabindex="-1" href="{$oMerkmalWert->cSeo}">
                                    {if ($oMerkmal->cTyp === 'BILD' || $oMerkmal->cTyp === 'BILD-TEXT') && $oMerkmalWert->nBildKleinVorhanden === 1}
                                       <img src="{$oMerkmalWert->cBildURLKlein}" alt="{$oMerkmalWert->cWert|escape:'quotes'}" />
                                    {/if}
                                    {if $oMerkmal->cTyp !== 'BILD'}
                                        {$oMerkmalWert->cWert}
                                    {/if}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {else}
                <ul class="nav nav-list">
                    {foreach $oMerkmal->oMerkmalWert_arr as $oMerkmalWert}
                        <li>
                            <a href="{$oMerkmalWert->cURL}"{if $NaviFilter->hasAttributeValue() && isset($oMerkmalWert->kMerkmalWert) && $NaviFilter->getAttributeValue()->getValue() == $oMerkmalWert->kMerkmalWert} class="active"{/if}>
                                {if ($oMerkmal->cTyp === 'BILD' || $oMerkmal->cTyp === 'BILD-TEXT') && $oMerkmalWert->nBildKleinVorhanden === 1}
                                   <img src="{$oMerkmalWert->cBildURLKlein}" alt="{$oMerkmalWert->cWert|escape:'quotes'}" />
                                {/if}
                                {if $oMerkmal->cTyp !== 'BILD'}
                                    {$oMerkmalWert->cWert}
                                {/if}
                            </a>
                        </li>
                    {/foreach}
                </ul>
            {/if}
        </div>
    </section>
{/foreach}