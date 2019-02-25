{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($Bestellung->oDownload_arr)}
    <div class="h2">{lang key='yourDownloads'}</div>
    {foreach $Bestellung->oDownload_arr as $oDownload}
        {card no-body=true}
            {cardheader id="download-{$oDownload@iteration}"}
                <div class="h4">
                    <a role="button" data-toggle="collapse"
                        href="#collapse-download-{$oDownload@iteration}"
                        aria-controls="collapse-download-{$oDownload@iteration}"
                    >
                        <i class="fa fa-chevron-{if $oDownload@iteration === 1}up{else}down{/if}"></i>
                        {$oDownload->oDownloadSprache->getName()}
                    </a>
                </div>
            {/cardheader}
            {collapse id="collapse-download-{$oDownload@iteration}" visible=$oDownload@iteration === 1}
                {cardbody}
                    <dl>
                        <dt>{lang key='downloadLimit'}</dt>
                        <dd class="bottom17">{$oDownload->cLimit|default:{lang key='unlimited'}}</dd>
                        <dt>{lang key='validUntil'}</dt>
                        <dd class="bottom17">{$oDownload->dGueltigBis|default:{lang key='unlimited'}}</dd>
                        <dt>{lang key='download'}</dt>
                        <dd class="bottom17">
                            {if $Bestellung->cStatus == $BESTELLUNG_STATUS_BEZAHLT || $Bestellung->cStatus == $BESTELLUNG_STATUS_VERSANDT}
                                {form method="post" action="{get_static_route id='jtl.php'}"}
                                    {input name="a" type="hidden" value="getdl"}
                                    {input name="bestellung" type="hidden" value=$Bestellung->kBestellung}
                                    {input name="dl" type="hidden" value=$oDownload->getDownload()}
                                    {button size="sm" type="submit"}
                                        <i class="fa fa-download"></i> {lang key='download'}
                                    {/button}
                                {/form}
                            {else}
                                {lang key='downloadPending'}
                            {/if}
                        </dd>
                    </dl>
                {/cardbody}
            {/collapse}
        {/card}
    {/foreach}
{elseif !empty($oDownload_arr)}
    <div class="h2">{lang key='yourDownloads'}</div>
    {foreach $oDownload_arr as $oDownload}
        {card no-body=true}
            {cardheader id="download-{$oDownload@iteration}"}
                <div class="h4">
                    <a role="button" data-toggle="collapse"
                        href="#collapse-download-{$oDownload@iteration}"
                        aria-controls="collapse-download-{$oDownload@iteration}"
                    >
                        <i class="fa fa-chevron-{if $oDownload@iteration === 1}up{else}down{/if}"></i>
                        {$oDownload->oDownloadSprache->getName()}
                    </a>
                </div>
            {/cardheader}
            {collapse id="collapse-download-{$oDownload@iteration}" visible=$oDownload@iteration === 1}
                {cardbody}
                    <dl>
                        <dt>{lang key='downloadLimit'}</dt>
                        <dd class="bottom17">{$oDownload->cLimit|default:{lang key='unlimited'}}</dd>
                        <dt>{lang key='validUntil'}</dt>
                        <dd class="bottom17">{$oDownload->dGueltigBis|default:{lang key='unlimited'}}</dd>
                        <dt>{lang key='download'}</dt>
                        <dd class="bottom17">
                            {form method="post" action="{get_static_route id='jtl.php'}"}
                                {input name="kBestellung" type="hidden" value=$oDownload->kBestellung}
                                {input name="kKunde" type="hidden" value=$smarty.session.Kunde->kKunde}
                                {assign var=cStatus value=$BESTELLUNG_STATUS_OFFEN}
                                {foreach $Bestellungen as $Bestellung}
                                    {if $Bestellung->kBestellung == $oDownload->kBestellung}
                                        {assign var=cStatus value=$Bestellung->cStatus}
                                    {/if}
                                {/foreach}
                                {if $cStatus == $BESTELLUNG_STATUS_BEZAHLT || $cStatus == $BESTELLUNG_STATUS_VERSANDT}
                                    {input name="dl" type="hidden" value=$oDownload->getDownload()}
                                    {button size="sm" type="submit"}
                                        <i class="fa fa-download"></i> {lang key='download'}
                                    {/button}
                                {else}
                                    {lang key='downloadPending'}
                                {/if}
                            {/form}
                        </dd>
                    </dl>
                {/cardbody}
            {/collapse}
        {/card}
    {/foreach}
{/if}
