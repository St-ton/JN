{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-alert'}
    {alert
        variant={$alert->getCssType()}
        data=["fade-out"=>{$alert->getFadeOut()}, "key"=>{$alert->getKey()}]
        id="{if $alert->getId()}{$alert->getId()}{/if}"
    }
        {if $alert->getIcon()}
            <i class="fa fa-{if $alert->getIcon() === 'warning'}exclamation-triangle{else}{$alert->getIcon()}{/if}"></i>
        {/if}
        {if $alert->getDismissable()}<div class="close ml-3">&times;</div>{/if}
        {if !empty($alert->getLinkHref()) && empty($alert->getLinkText())}
            {link href=$alert->getLinkHref()}{$alert->getMessage()}{/link}
        {elseif !empty($alert->getLinkHref()) && !empty($alert->getLinkText())}
            {$alert->getMessage()}
            {link href=$alert->getLinkHref()}{$alert->getLinkText()}{/link}
        {else}
            {$alert->getMessage()}
        {/if}
    {/alert}
{/block}
