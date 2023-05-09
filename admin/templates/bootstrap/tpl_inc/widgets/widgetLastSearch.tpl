<div class="widget-custom-data">
    {if $lastQueries|count > 0}
        <div class="table-responsive">
            <table class="table table-striped" cellpadding="0" cellspacing="0" border="0">
                <thead>
                    <tr>
                        <th>{__('Search')}</th>
                        <th class="text-center">{__('Language')}</th>
                        <th class="text-center">{__('Hits')}</th>
                        <th class="text-center">{__('Search count')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $lastQueries as $query}
                        <tr>
                            <td>{$query->cSuche}</td>
                            <td class="text-center">{$query->cIso}</td>
                            <td class="text-center">{$query->nAnzahlTreffer}</td>
                            <td class="text-center">{$query->nAnzahlGesuche}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <div class="alert alert-info" role="alert">
            {__('No search queries found.')}
        </div>
    {/if}
</div>
