{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div
    class="alert alert-{$alert->getCssType()}"
    data-fade-out="{$alert->getFadeOut()}"
    data-key="{$alert->getKey()}"
    {if $alert->getId()}id="{$alert->getId()}"{/if}
>
    {if $alert->getDismissable()}<div class="close">&times;</div>{/if}

    {if !empty($alert->getLinkHref()) && empty($alert->getLinkText())}
        <a href="{$alert->getLinkHref()}">{$alert->getMessage()}</a>
    {elseif !empty($alert->getLinkHref()) && !empty($alert->getLinkText())}
        {$alert->getMessage()}
        <a href="{$alert->getLinkHref()}">{$alert->getLinkText()}</a>
    {else}
        {$alert->getMessage()}
    {/if}
</div>
