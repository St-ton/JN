{assign var='isFluidBanner' value=isset($Einstellungen.template.theme.banner_full_width) && $Einstellungen.template.theme.banner_full_width === 'Y' &&  isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($oImageMap)}
{assign var='isQuickview' value=isset($smarty.get.quickView) && $smarty.get.quickView == 1}
{if !$isFluidBanner && !$isQuickview}
    {include file='snippets/banner.tpl' isFluid=$isFluidBanner}
{/if}

{assign var='isFluidSlider' value=isset($Einstellungen.template.theme.slider_full_width) && $Einstellungen.template.theme.slider_full_width === 'Y' &&  isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($oSlider) && count($oSlider->getSlides()) > 0}
{if !$isFluidSlider && !$isQuickview}
    {include file='snippets/slider.tpl'}
{/if}

