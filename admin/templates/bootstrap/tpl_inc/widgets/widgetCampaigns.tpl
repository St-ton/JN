<div class="widget-custom-data">
    <script type="text/javascript">
        $(function() {
            $('#select_kampagne').change(function() {
                let kKampagne = $('#select_kampagne option:selected').val();
                window.location = "{$adminURL}?kKampagne=" + kKampagne;
            });
        });
    </script>
    <select name="kKampagne" id="select_kampagne" class="custom-select mb-3" >
    {foreach $campaigns as $campaign}
        <option value="{$campaign->kKampagne}" {if $campaign->kKampagne == $kKampagne}selected="selected"{/if}>{$campaign->cName}</option>
    {/foreach}
    </select>
    <table class="table">
        <thead>
            <tr>
            <th>{__('Statistics')}</th>
            <th class="text-center">{$campaignStats[$types.0].cDatum}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $campaignDefinitions as $campaignDefinition}
                {assign var=kKampagneDef value=$campaignDefinition->kKampagneDef}
                <tr>
                    <td>{$campaignDefinition->cName}</td>
                    <td class="text-center">{$campaignStats[$types.0][$kKampagneDef]}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
