{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card class="box box-tagcloud mb-7" id="sidebox{$oBox->getID()}" title="{lang key='tagcloud'}"}
    <hr class="mt-0 mb-4">
    <div class="tagbox">
        {foreach $oBox->getItems() as $item}
            {link href="{$item->cURLFull}" class="tag{$item->Klasse}"}{$item->cName}{/link}
        {/foreach}
    </div>
{/card}
