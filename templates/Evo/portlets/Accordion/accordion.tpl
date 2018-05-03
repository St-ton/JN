{assign var="areaId" value=$instance->getProperty('uid')}
{if ($instance->getAttributeString('layout') === 'button')}
    <div {$instance->addClass('row')->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
        <button
                class="btn btn-{$instance->getProperty('cllps-button-type')} btn-{$instance->getProperty('cllps-button-size')}"
                type="button" data-toggle="collapse"
                data-target="#div_{$areaId}"
                aria-expanded="{if $isPreview || $instance->getProperty('cllps-initial-state') === '1'}true{else}false{/if}"
                aria-controls="div_{$areaId}">
            {$instance->getProperty('cllps-button-text')}
        </button>
        <div class="collapse{if $isPreview || $instance->getProperty('cllps-initial-state') === '1'} in{/if}" id="div_{$areaId}">
            <div class="opc-area" {if $isPreview}data-area-id="{$areaId}"{/if}>
                {if $isPreview}
                    {$instance->getSubareaPreviewHtml($areaId)}
                {else}
                    {$instance->getSubareaFinalHtml($areaId)}
                {/if}
            </div>
        </div>
    </div>
{else}
    <div {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
        <div class="panel-group"
             id="accordion_{$areaId}"
             role="tablist" aria-multiselectable="true">
            <div class="panel panel-default">
                <div class="panel-heading"
                     role="tab" id="pnl_hd_{$areaId}">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse"
                           data-parent="#accordion_{$areaId}"
                           href="#div_{$areaId}"
                           aria-expanded="{if $isPreview || $instance->getProperty('cllps-initial-state') === '1'}true{else}false{/if}"
                           aria-controls="div_{$areaId}">
                            {if $isPreview}
                                <div class="opc-area" {if $isPreview}data-area-id="{$areaId}_0"{/if}>
                                    {$instance->getSubareaPreviewHtml($areaId|cat:'_0')}
                                </div>
                            {else}
                                {$instance->getSubareaFinalHtml($areaId|cat:'_0')}
                            {/if}
                        </a>
                    </h4>
                </div>
                <div id="div_{$areaId}"
                     class="panel-collapse collapse{if $isPreview || $instance->getProperty('cllps-initial-state') === '1'} in{/if}" id="div_{$areaId}"
                     role="tabpanel" aria-labelledby="pnl_hd_{$areaId}">
                    <div class="panel-body">
                        {if $isPreview}
                            <div class="opc-area" {if $isPreview}data-area-id="{$areaId}_1"{/if}>
                                {$instance->getSubareaPreviewHtml($areaId|cat:'_1')}
                            </div>
                        {else}
                            {$instance->getSubareaFinalHtml($areaId|cat:'_1')}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}
