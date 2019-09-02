{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{opcMountPoint id='opc_before_manufacturers'}

<div class="row row-eq-height content-cats-small clearfix">
    {foreach $oHersteller_arr as $Hersteller}
        <div class="col-xs-6 col-md-4 col-lg-3">
            <div class="thumbnail">
                <div class="caption">
                    <a href="{$Hersteller->cURL}" class="text-center" title="{$Hersteller->cMetaTitle}">
                        <img src="{$Hersteller->cBildURLNormal}" alt="{$Hersteller->getName()}" />
                        {$Hersteller->getName()}
                    </a>
                </div>
            </div>
        </div>
    {/foreach}
</div>