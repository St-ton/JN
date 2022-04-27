{if $account->oGroup->kAdminlogingruppe === 1}
    {if $cnf->getInputType() === 'selectbox'}
        {$defaultValue = __("{$cnf->getValueName()}_value({$cnf->getDefaultValue()})")}
    {else}
        {$defaultValue = $cnf->getDefaultValue()}
    {/if}
    <button type="button"
            name="resetSetting"
            value="{$cnf->getValueName()}"
            class="btn btn-link p-0 {if $cnf->getSetValue() === $cnf->getDefaultValue()}hidden{/if} delete-confirm btn-submit"
            title="{__('settingReset')|sprintf:$defaultValue}"
            data-toggle="tooltip"
            data-placement="top"
            data-modal-body="{__('confirmResetLog')|sprintf:__("{$cnf->getValueName()}_name"):$defaultValue}"
            data-modal-title="{__('confirmResetLogTitle')}"
            data-modal-submit="{__('reset')}"
    >
        <span class="icon-hover">
            <span class="fal fa-refresh"></span>
            <span class="fas fa-refresh"></span>
        </span>
    </button>
{/if}
