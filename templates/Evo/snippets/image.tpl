{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{counter assign=imgcounter print=0}
<div class="image-box loading">
    <div class="image-content">
        <img alt="{$alt}" src="{$imageBaseURL}gfx/trans.png" data-src="{$src}" data-id="{$imgcounter}"/>
        {if !empty($src)}
            <meta itemprop="image" content="{$src}">
        {/if}
    </div>
</div>
