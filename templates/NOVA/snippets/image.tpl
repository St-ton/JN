{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-image'}
    {counter assign=imgcounter print=0}
    <div class="image-box">
        <div class="image-content">
            {block name='snippets-image-image'}
                {image alt=$alt fluid=true lazy=true src=$src data=["id" => $imgcounter]}
            {/block}
            {if !empty($src)}
                <meta itemprop="image" content="{$src}">
            {/if}
        </div>
    </div>
{/block}
