{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var='isFluidBanner' value=$Einstellungen.template.theme.banner_full_width === 'Y' && isset($oImageMap)}
{if !$isFluidBanner}
    {include file='snippets/banner.tpl' isFluid=$isFluidBanner}
{/if}

{assign var='isFluidSlider' value=$Einstellungen.template.theme.slider_full_width === 'Y' && isset($oSlider) && count($oSlider->getSlides()) > 0}
{if !$isFluidSlider}
    {include file='snippets/slider.tpl'}
{/if}

