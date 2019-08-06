{if $isPreview}
    <div data-portlet="{$instance->getDataAttribute()}" class="opc-Heading">
        <div class="opc-Heading-label">
            <i class="fas fa-heading opc-Heading-icon"></i>
            <span class="opc-Heading-label-text">Heading</span>
        </div>
        <div class="opc-Heading-propinfo">
            {if !empty($instance->getProperty('text'))}
                <div><i class="fas fa-font fa-fw"></i> {$instance->getProperty('text')}</div>
            {/if}
        </div>
    </div>
{else}
    <h{$instance->getProperty('level')}>
        {$instance->getProperty('text')}
    </h{$instance->getProperty('level')}>
{/if}