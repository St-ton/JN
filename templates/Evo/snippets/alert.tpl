{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($alert->getMessage())}
    <div
        class="alert alert-{$alert->getCssType()}"
        data-fade-out="{$alert->getFadeOut()}"
        data-key="{$alert->getKey()}"
        {if $alert->getId()}id="{$alert->getId()}"{/if}
    >
        {if !empty($alert->getIcon())}<span class="fa fa-{$alert->getIcon()}"></span>{/if}

        {if !empty({$alert->getLinkHref()}) && empty({$alert->getLinkText()})}
            <a href="{$alert->getLinkHref()}">
        {/if}

        {$alert->getMessage()}

        {if !empty({$alert->getLinkHref()}) && empty({$alert->getLinkText()})}
            </a>
        {/if}

        {if !empty({$alert->getLinkHref()}) && !empty({$alert->getLinkText()})}
            <a href="{$alert->getLinkHref()}">{$alert->getLinkText()}</a>
        {/if}
        {if $alert->getDismissable()}<div class="close">&times;</div>{/if}
    </div>
{/if}
