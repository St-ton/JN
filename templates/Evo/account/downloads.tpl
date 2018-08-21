{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($Bestellung->oDownload_arr)}
    <h2>{lang key='yourDownloads'}</h2>
    <div class="panel-group" role="tablist">
        {foreach $Bestellung->oDownload_arr as $oDownload}
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="download-{$oDownload@iteration}">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#accordion"
                           href="#collapse-download-{$oDownload@iteration}"
                           aria-controls="collapse-download-{$oDownload@iteration}">
                            <i class="fa fa-chevron-{if $oDownload@iteration === 1}up{else}down{/if}"></i>
                            {$oDownload->oDownloadSprache->getName()}
                        </a>
                    </h4>
                </div>
                <div id="collapse-download-{$oDownload@iteration}"
                     class="panel-collapse collapse{if $oDownload@iteration === 1} in{/if}" role="tabpanel"
                     aria-labelledby="download-{$oDownload@iteration}">
                    <div class="panel-body">
                        <dl>
                            <dt>{lang key='downloadLimit'}</dt>
                            <dd class="bottom17">{if isset($oDownload->cLimit)}{$oDownload->cLimit}{else}{lang key='unlimited'}{/if}</dd>
                            <dt>{lang key='validUntil'}</dt>
                            <dd class="bottom17">{if isset($oDownload->dGueltigBis)}{$oDownload->dGueltigBis}{else}{lang key='unlimited'}{/if}</dd>
                            <dt>{lang key='download'}</dt>
                            <dd class="bottom17">
                                {if $Bestellung->cStatus == $BESTELLUNG_STATUS_BEZAHLT || $Bestellung->cStatus == $BESTELLUNG_STATUS_VERSANDT}
                                    <form method="post" action="{get_static_route id='jtl.php'}">
                                        {$jtl_token}
                                        <input name="a" type="hidden" value="getdl" />
                                        <input name="bestellung" type="hidden" value="{$Bestellung->kBestellung}" />
                                        <input name="dl" type="hidden" value="{$oDownload->getDownload()}" />
                                        <button class="btn btn-default btn-xs" type="submit">
                                            <i class="fa fa-download"></i> {lang key='download'}
                                        </button>
                                    </form>
                                {else}
                                    {lang key='downloadPending'}
                                {/if}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{elseif !empty($oDownload_arr)}
    <h2>{lang key='yourDownloads'}</h2>
    <div class="panel-group" role="tablist">
        {foreach $oDownload_arr as $oDownload}
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="download-{$oDownload@iteration}">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#accordion"
                           href="#collapse-download-{$oDownload@iteration}"
                           aria-controls="collapse-download-{$oDownload@iteration}">
                            <i class="fa fa-chevron-{if $oDownload@iteration === 1}up{else}down{/if}"></i>
                            {$oDownload->oDownloadSprache->getName()}
                        </a>
                    </h4>
                </div>
                <div id="collapse-download-{$oDownload@iteration}"
                     class="panel-collapse collapse{if $oDownload@iteration === 1} in{/if}" role="tabpanel"
                     aria-labelledby="download-{$oDownload@iteration}">
                    <div class="panel-body">
                        <dl>
                            <dt>{lang key='downloadLimit'}</dt>
                            <dd class="bottom17">{if isset($oDownload->cLimit)}{$oDownload->cLimit}{else}{lang key='unlimited'}{/if}</dd>
                            <dt>{lang key='validUntil'}</dt>
                            <dd class="bottom17">{if isset($oDownload->dGueltigBis)}{$oDownload->dGueltigBis}{else}{lang key='unlimited'}{/if}</dd>
                            <dt>{lang key='download'}</dt>
                            <dd class="bottom17">
                                <form method="post" action="{get_static_route id='jtl.php'}">
                                    {$jtl_token}
                                    <input name="kBestellung" type="hidden" value="{$oDownload->kBestellung}"/>
                                    <input name="kKunde" type="hidden" value="{$smarty.session.Kunde->kKunde}"/>
                                    {assign var=cStatus value=$BESTELLUNG_STATUS_OFFEN}
                                    {foreach $Bestellungen as $Bestellung}
                                        {if $Bestellung->kBestellung == $oDownload->kBestellung}
                                            {assign var=cStatus value=$Bestellung->cStatus}
                                        {/if}
                                    {/foreach}
                                    {if $cStatus == $BESTELLUNG_STATUS_BEZAHLT || $cStatus == $BESTELLUNG_STATUS_VERSANDT}
                                        <input name="dl" type="hidden" value="{$oDownload->getDownload()}"/>
                                        <button class="btn btn-default btn-xs" type="submit">
                                            <i class="fa fa-download"></i> {lang key='download'}
                                        </button>
                                    {else}
                                        {lang key='downloadPending'}
                                    {/if}
                                </form>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{/if}
