{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{counter assign=imgcounter print=0}
<div class="image-box">
    <div class="image-content">
        {image alt="{$alt}" fluid=true lazy=true src="{$imageBaseURL}gfx/trans.png" data=["src"=>"{$src}","id"=>"{$imgcounter}"]}
        {if !empty($src)}
            <meta itemprop="image" content="{$src}">
        {/if}
    </div>
</div>
