<div class="widget-custom-data">
    {if count($searchQueries) > 0}
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{__('Search queries')}</th>
                        <th class="text-center">{__('Language')}</th>
                        <th class="text-center">{__('Count')}</th>
                    </tr>
                </thead>
                {foreach $searchQueries as $query}
                    <tr>
                        <td>{$query->cSuche}</td>
                        <td class="text-center">{$query->cIso}</td>
                        <td class="text-center">{$query->nAnzahlGesuche}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
    {else}
        <div class="alert alert-info" role="alert">
            {__('No search queries found.')}
        </div>
    {/if}
</div>
