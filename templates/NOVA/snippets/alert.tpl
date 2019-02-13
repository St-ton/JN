{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{alert
    variant={$alert->getCssType()}
    data=["fade-out"=>{$alert->getFadeOut()}, "key"=>{$alert->getKey()}]
    id="{if $alert->getId()}{$alert->getId()}{/if}"
}
    {if !empty($alert->getLinkHref()) && empty($alert->getLinkText())}
        {link href="{$alert->getLinkHref()}"}{$alert->getMessage()}{/link}
    {elseif !empty($alert->getLinkHref()) && !empty($alert->getLinkText())}
        {$alert->getMessage()}
        {link href="{$alert->getLinkHref()}"}{$alert->getLinkText()}{/link}
    {else}
        {$alert->getMessage()}
    {/if}

    {if $alert->getDismissable()}<div class="close">&times;</div>{/if}
{/alert}
