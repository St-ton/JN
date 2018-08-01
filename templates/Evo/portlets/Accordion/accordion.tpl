{assign var="areaId" value=$instance->getProperty('uid')}
{if ($instance->getProperty('layout') === 'button')}
    <div {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
        <button
                class="btn btn-{$instance->getProperty('cllps-button-type')} btn-{$instance->getProperty('cllps-button-size')}"
                type="button" data-toggle="collapse"
                data-target="#div_{$areaId}"
                aria-expanded="{if $isPreview || $instance->getProperty('cllps-initial-state') === '1'}true{else}false{/if}"
                aria-controls="div_{$areaId}">
            {$instance->getProperty('cllps-button-text')}
        </button>
        <div class="collapse{if $isPreview || $instance->getProperty('cllps-initial-state') === '1'} in{/if}" id="div_{$areaId}">
            {if $isPreview}
                <div class="opc-area" data-area-id="cllpse_0">
                    {$instance->getSubareaPreviewHtml('cllpse_0')}
                </div>
            {else}
                {$instance->getSubareaFinalHtml('cllpse_0')}
            {/if}
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
                                <div class="opc-area" {if $isPreview}data-area-id="cllpse_0"{/if}>
                                    {$instance->getSubareaPreviewHtml('cllpse_0')}
                                </div>
                            {else}
                                {$instance->getSubareaFinalHtml('cllpse_0')}
                            {/if}
                        </a>
                    </h4>
                </div>
                <div id="div_{$areaId}"
                     class="panel-collapse collapse{if $isPreview || $instance->getProperty('cllps-initial-state') === '1'} in{/if}" id="div_{$areaId}"
                     role="tabpanel" aria-labelledby="pnl_hd_{$areaId}">
                    <div class="panel-body">
                        {if $isPreview}
                            <div class="opc-area" {if $isPreview}data-area-id="cllpse_1"{/if}>
                                {$instance->getSubareaPreviewHtml('cllpse_1')}
                            </div>
                        {else}
                            {$instance->getSubareaFinalHtml('cllpse_1')}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}
