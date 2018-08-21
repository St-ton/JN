{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    <section class="panel panel-default box box-newscategories" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='newsBoxCatOverview'}</div>
        </div>
        <div class="box-body dropdown">
            <ul class="nav nav-list">
                {foreach $oBox->getItems() as $oNewsKategorie}
                    <li>
                        <a href="{$oNewsKategorie->cURLFull}" title="{$oNewsKategorie->cName}">
                            <span class="value">
                                {$oNewsKategorie->cName} <span class="badge pull-right">{$oNewsKategorie->nAnzahlNews}</span>
                            </span>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </section>
{/if}
