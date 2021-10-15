{if $useExstoreWidgetBanner === true}
    <a href="{__('extensionStoreURL')}" target="_blank">
        <img src="gfx/exstore-banner-dashboard-{$language}.jpg"
             alt="Extensions entdecken!" class="exstore-banner">
    </a>
{else}
    <a href="{__('extensionStoreURL')}" target="_blank">
        <img srcset="gfx/exstore-banner-mobile-{$language}.jpg 412w, gfx/exstore-banner-{$language}.jpg 2000w"
             sizes="(max-width: 768px) 412px, 100vw" src="gfx/exstore-banner-mobile-{$language}.jpg"
             alt="Extensions entdecken!" class="exstore-banner">
    </a>
{/if}