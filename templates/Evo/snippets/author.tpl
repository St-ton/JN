<div itemprop="author" itemscope itemtype="https://schema.org/Person">
    <a itemprop="name" class="dropdown-toggle" href="#" title="{$oAuthor->cName}" data-toggle="modal" data-target="#author-{$oAuthor->kContentAuthor}">{$oAuthor->cName}</a>&nbsp;&ndash;&nbsp;
    <div class="modal fade" tabindex="-1" role="dialog" id="author-{$oAuthor->kContentAuthor}">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header v-wrap">
                    {if !empty($oAuthor->cAvatarImgSrc)}
                        <img itemprop="image" alt="{$oAuthor->cName}" src="{$oAuthor->cAvatarImgSrc}" height="80" class="img-circle" />
                    {/if}
                    <div itemprop="name" class="top10">{$oAuthor->cName}</div>
                </div>
                {if !empty($oAuthor->cVitaShort)}
                    <div itemprop="description" class="modal-body">
                        {$oAuthor->cVitaShort}
                    </div>
                {/if}
                {if !empty($oAuthor->cGplusProfile)}
                    <div class="modal-footer">
                        <a itemprop="url" href="{$oAuthor->cGplusProfile}?rel=author" title="{$oAuthor->cName}">Google+</a>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>