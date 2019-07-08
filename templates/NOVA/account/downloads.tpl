{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-downloads'}
    {if !empty($Bestellung->oDownload_arr)}
        {block name='account-downloads-subheading'}
            <div class="h2">{lang key='yourDownloads'}</div>
        {/block}
        {block name='account-downloads-order-downloads'}
            {foreach $Bestellung->oDownload_arr as $oDownload}
                {card no-body=true}
                    {cardheader id="download-{$oDownload@iteration}" class="p-2 bg-white"}
                        {button
                            variant="link"
                            role="button"
                            block=true
                            class="text-left"
                            aria=["expanded"=>false,"controls"=>"#collapse-download-{$oDownload@iteration}"]
                            data=["toggle"=> "collapse", "target"=>"#collapse-download-{$oDownload@iteration}"]
                        }
                            {$oDownload->oDownloadSprache->getName()}
                            <i class="fa fa-chevron-down float-right"></i>
                        {/button}
                    {/cardheader}
                    {collapse id="collapse-download-{$oDownload@iteration}" visible=false}
                        {cardbody}
                            {row}
                                {col md=4}{lang key='downloadLimit'}:{/col}
                                {col md=8}{$oDownload->cLimit|default:{lang key='unlimited'}}{/col}
                            {/row}
                            {row}
                                {col md=4}{lang key='validUntil'}:{/col}
                                {col md=8}{$oDownload->dGueltigBis|default:{lang key='unlimited'}}{/col}
                            {/row}
                            {row}
                                {col md=4}{lang key='download'}:{/col}
                                {col md=8}
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
                                {/col}
                            {/row}
                        {/cardbody}
                    {/collapse}
                {/card}
            {/foreach}
        {/block}
    {elseif !empty($oDownload_arr)}
        {block name='account-downloads-customer-downloads'}
            {row class='mb-5'}
                {col cols=12 md=6}
                    {card no-body=true}
                        {cardheader class="bg-info"}
                            {lang key='myDownloads'}
                        {/cardheader}
                        {cardbody class="p-0"}
                            <div id="account-download-accordion">
                                {block name='account-downloads-customer-downloads'}
                                    {foreach $oDownload_arr as $oDownload}
                                        {card no-body=true}
                                            {cardheader id="download-{$oDownload@iteration}" class="p-2 bg-white"}
                                                {button
                                                    variant="link"
                                                    role="button"
                                                    block=true
                                                    class="text-left"
                                                    aria=["expanded"=>false,"controls"=>"#collapse-download-{$oDownload@iteration}"]
                                                    data=["toggle"=> "collapse", "target"=>"#collapse-download-{$oDownload@iteration}"]
                                                }
                                                    {$oDownload->oDownloadSprache->getName()}
                                                    <i class="fa fa-chevron-down float-right"></i>
                                                {/button}
                                            {/cardheader}
                                            {collapse id="collapse-download-{$oDownload@iteration}" visible=false
                                                aria=["labelledby"=>"download-{$oDownload@iteration}"]
                                                data=["parent"=>"#account-download-accordion"]
                                            }
                                                {cardbody}
                                                    {row}
                                                        {col md=4}{lang key='downloadLimit'}:{/col}
                                                        {col md=8}{$oDownload->cLimit|default:{lang key='unlimited'}}{/col}
                                                    {/row}
                                                    {row}
                                                        {col md=4}{lang key='validUntil'}:{/col}
                                                        {col md=8}{$oDownload->dGueltigBis|default:{lang key='unlimited'}}{/col}
                                                    {/row}
                                                    {row}
                                                        {col md=4}{lang key='download'}:{/col}
                                                        {col md=8}
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
                                                        {/col}
                                                    {/row}
                                                {/cardbody}
                                            {/collapse}
                                        {/card}
                                    {/foreach}
                                {/block}
                            </div>
                        {/cardbody}
                    {/card}
                {/col}
            {/row}
        {/block}
    {/if}
{/block}
