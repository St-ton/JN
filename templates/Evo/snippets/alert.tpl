{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}

<div class="alert alert-{$alert->getVariant()}" data-fade-out="{$alert->getFadeOut()}">
    {$alert->getMessage()}
    {if $alert->getDismissable()}<div class="close">x</div>{/if}
</div>
{Shop::Container()->getAlertService()->unsetAlert($alert)}