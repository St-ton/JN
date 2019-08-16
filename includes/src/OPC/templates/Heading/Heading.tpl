{if $isPreview}
    <div data-portlet="{$instance->getDataAttribute()}" class="opc-Heading">
        <div class="opc-Heading-label">
            <i class="fas fa-heading opc-Heading-icon"></i><span>{$instance->getProperty('level')}</span>
        </div>
        <div class="opc-Heading-propinfo">
            {if !empty($instance->getProperty('text'))}
                <h{$instance->getProperty('level')} style="{$instance->getStyleString()}">
                    {$instance->getProperty('text')}
                </h{$instance->getProperty('level')}>
            {/if}
        </div>
    </div>
{else}
    <h{$instance->getProperty('level')} style="{$instance->getStyleString()}">
        {$instance->getProperty('text')}
    </h{$instance->getProperty('level')}>
{/if}