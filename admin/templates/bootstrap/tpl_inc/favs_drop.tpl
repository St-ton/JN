<a href="#" class="btn btn-primary favorites dropdown-toggle parent" data-toggle="dropdown" title="{__('favorites')}">
    <i class="fa fa-star mr-1" aria-hidden="true"></i> {__('favorites')}
</a>
<div class="dropdown-menu dropdown-menu-right" role="main">
    {if isset($favorites) && is_array($favorites) && count($favorites) > 0}
        <span class="dropdown-header">Favoriten</span>
        <div class="dropdown-divider"></div>
        {foreach $favorites as $favorite}
            <a class="dropdown-item" href="{$favorite->cAbsUrl}" rel="{$favorite->kAdminfav}"{if $favorite->bExtern} target="_blank"{/if}>{$favorite->cTitel}{if $favorite->bExtern} <i class="fa fa-external-link"></i>{/if}</a>
        {/foreach}

        <div class="dropdown-divider"></div>
    {/if}
    <a class="dropdown-item" href="favs.php"><i class="fa fa-pencil mr-1"></i> {__('manageFavorites')}</a>
</div>