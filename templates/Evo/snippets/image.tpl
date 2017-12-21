{counter assign=imgcounter print=0}
<div class="image-box">
    <div class="image-content">
        <img alt="{$alt}" src="gfx/trans.png" data-src="{$src}" data-id="{$imgcounter}" />
        {if !empty($src)}
            <meta itemprop="image" content="{$ShopURL}/{$src}">
        {/if}
    </div>
</div>