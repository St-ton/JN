{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-manufacturers'}
    {container}
        {include file='snippets/opc_mount_point.tpl' id='opc_before_manufacturers'}
        {block name='page-manufacturers-content'}
            <div class="card-columns manufacturers-columns">
                {foreach $oHersteller_arr as $Hersteller}
                    {link href=$Hersteller->cURL title=$Hersteller->cMetaTitle}
                        {card class="text-center p-2 mb-5 border-0" img-src=$Hersteller->cBildURLNormal img-alt=$Hersteller->getName()}
                            {$Hersteller->getName()}
                        {/card}
                    {/link}
                {/foreach}
            </div>
        {/block}
    {/container}
{/block}
