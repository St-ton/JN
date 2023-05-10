<div id="wrap-newerversions">
    <label class="col-form-label" for="newerversions">{__('chooseRelease')}:</label>
{*    <pre>*}
{*        {foreach $availableVersions as $version}*}
{*        {var_dump($version->version)}*}
{*        {/foreach}*}
{*    </pre>*}
    {select
    name="newerversions"
    id="newerversions"
    class="onchangeSubmit custom-select"
    aria=["label"=>"{lang key='newerVersions' section='upgrade'}"]
    }
    {foreach $availableVersions as $version}
        <option value="{$version->id}"{if !$version->isNewer} disabled{/if}>
            {(string)$version->version}
        </option>
    {/foreach}
    {/select}
</div>