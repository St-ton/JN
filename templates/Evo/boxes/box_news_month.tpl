{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    <section class="panel panel-default box box-monthlynews" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='newsBoxMonthOverview'}</div>
        </div>
        <div class="box-body dropdown">
            <ul class="nav nav-list">
                {foreach $oBox->getItems() as $oNewsMonatsUebersicht}
                    <li>
                        <a href="{$oNewsMonatsUebersicht->cURL}"  title="{$oNewsMonatsUebersicht->cName}">
                            <span class="value">
                                {$oNewsMonatsUebersicht->cName}
                                <span class="badge pull-right">{$oNewsMonatsUebersicht->nAnzahl}</span>
                            </span>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </section>
{/if}
