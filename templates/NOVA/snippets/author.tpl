{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='news-author'}
    <div itemprop="author" itemscope itemtype="https://schema.org/Person">
        {link
            itemprop="name"
            href="#"
            title="{$oAuthor->cName}"
            data=["toggle"=>"modal",
                "target"=>"#author-{$oAuthor->kContentAuthor}"]
        }
            {$oAuthor->cName}
        {/link}&nbsp;&ndash;&nbsp;
        {if isset($cDate)}
            <span class="creation-date">{$cDate}</span>
        {/if}
        {modal
            id="author-{$oAuthor->kContentAuthor}"
            title="<img alt='{$oAuthor->cName}' src='{$oAuthor->cAvatarImgSrcFull}' height='80' class='rounded-circle' /><span itemprop='name' class='ml-3'>{$oAuthor->cName}</span>"
            footer="{if !empty($oAuthor->cGplusProfile)}<a itemprop='url' href='{$oAuthor->cGplusProfile}?rel=author' title='{$oAuthor->cName}'>Google+</a>{/if}"}
            {if !empty($oAuthor->cVitaShort)}
                <meta itemprop="image" content="{$oAuthor->cAvatarImgSrcFull}">
                <div itemprop="description">
                    {$oAuthor->cVitaShort}
                </div>
            {/if}
        {/modal}
    </div>
{/block}
{block name='news-publisher'}
    <div itemprop="publisher" itemscope itemtype="http://schema.org/Organization" class="d-none">
        <span itemprop="name">{$meta_publisher}</span>
        <meta itemprop="url" content="{$ShopURL}">
        <meta itemprop="logo" content="{$imageBaseURL}{$ShopLogoURL}">
    </div>
{/block}
