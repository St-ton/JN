<div id="wrap-newerversions">
    <label class="col-form-label" for="newerversions">{__('choose release')}:</label>
    {select
        name="newerversions"
        id="newerversions"
        class="onchangeSubmit custom-select"
        aria=["label"=>"{lang key='newerVersions' section='upgrade'}"]
    }
        <option value="">{__('please select')}</option>
        {foreach $availableVersions as $version}
            <option value="{$version->id}"{if !$version->isNewer} disabled{/if}>
                {(string)$version->version}
            </option>
        {/foreach}
    {/select}
</div>
