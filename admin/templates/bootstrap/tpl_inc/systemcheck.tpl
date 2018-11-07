{function test_result}
    {if $test->getResult() === Systemcheck_Tests_Test::RESULT_OK}
        <span class="hidden-xs">
            <h4 class="label-wrap"><span class="label label-success">
                {$state = $test->getCurrentState()}
                {if $state !== null && $state|strlen > 0}
                    {$state}
                {else}
                    <i class="fa fa-check" aria-hidden="true"></i>
                {/if}
            </span></h4>
        </span>
        <span class="visible-xs">
            <h4 class="label-wrap"><span class="label label-success">
                <i class="fa fa-check" aria-hidden="true"></i>
            </span></h4>
        </span>
    {elseif $test->getResult() === Systemcheck_Tests_Test::RESULT_FAILED}
        {if $test->getIsOptional()}
            <span class="hidden-xs">
                {if $test->getIsRecommended()}
                    {$state = $test->getCurrentState()}
                    <h4 class="label-wrap">
                        <span class="label label-warning">
                            {if $state !== null && $state|strlen > 0}
                                {$state}
                            {else}
                                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            {/if}
                        </span>
                    </h4>
                {else}
                    {$state = $test->getCurrentState()}
                    <h4 class="label-wrap">
                        <span class="label label-primary">
                            {if $state !== null && $state|strlen > 0}
                                {$state}
                            {else}
                                <i class="fa fa-times" aria-hidden="true"></i>
                            {/if}
                        </span>
                    </h4>
                {/if}
            </span>
            <span class="visible-xs">
                {if $test->getIsRecommended()}
                    <h4 class="label-wrap">
                        <span class="label label-warning">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        </span>
                    </h4>
                {else}
                    <h4 class="label-wrap"><span class="label label-primary">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </span></h4>
                {/if}
            </span>
        {else}
            <span class="hidden-xs">
                {$state = $test->getCurrentState()}
                <h4 class="label-wrap">
                    <span class="label label-danger">
                        {if $state !== null && $state|strlen > 0}
                            {$state}
                        {else}
                            <i class="fa fa-times" aria-hidden="true"></i>
                        {/if}
                    </span>
                </h4>
            </span>
            <span class="visible-xs">
                <h4 class="label-wrap">
                    <span class="label label-danger">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </span>
                </h4>
            </span>
        {/if}
    {/if}
{/function}