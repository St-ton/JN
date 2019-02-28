<ul id="{$eContainer}" class="dashboard-col col-md-4 col-sm-6 col-xs-12">
    {foreach $oActiveWidget_arr as $oWidget}
        {if $oWidget->eContainer === $eContainer}
            <li id="widget-{$oWidget->cClass}" class="widget panel panel-default" ref="{$oWidget->kWidget}">
                <div class="widget-head panel-heading">
                    <h4>{$oWidget->cTitle}</h4>
                    <span class="options"></span>
                </div>
                <div class="widget-content
                            {if $oWidget->hasBody === true}panel-body{/if}
                            {if !$oWidget->bExpanded}widget-hidden{/if}
                ">
                    {$oWidget->cContent}
                </div>
            </li>
        {/if}
    {/foreach}
</ul>
