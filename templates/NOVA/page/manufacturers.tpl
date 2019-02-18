{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{include file='snippets/opc_mount_point.tpl' id='opc_manufacturers_preview_prepend'}
{row class="row-eq-height content-cats-small clearfix"}
    {foreach $oHersteller_arr as $Hersteller}
        {col cols=6 md=4 lg=3}
            {card class="text-center"}
                {link href=$Hersteller->cURL title=$Hersteller->cMetaTitle}
                    {image src=$Hersteller->cBildURLNormal alt=$Hersteller->getName()}
                    {$Hersteller->getName()}
                {/link}
            {/card}
        {/col}
    {/foreach}
{/row}
{include file='snippets/opc_mount_point.tpl' id='opc_manufacturers_preview_append'}
