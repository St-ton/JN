{if $header|count > 1}Invalid header length!{/if}
{$targetLength = ($header[0]|default:[])|count}

{if $targetLength > 0}
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                {foreach $header[0] as $item}
                    <th class="text-left">{$item}</th>
                {/foreach}
                </tr>
            </thead>
            <tbody>
                {foreach $content as $item}
                    <tr>
                        {$last = 0}
                        {foreach $item as $i => $ele}
                            <td>{$ele}{if $i >= $targetLength}<strong>No matching headline!{/if}</td>
                            {$last = $last+1}
                        {/foreach}
                        {while $last < $targetLength}
                            <td>Missing ele!</td>
                            {$last = $last+1}
                        {/while}
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{else}
    <div class="alert alert-danger">No valid header found</div>
{/if}
