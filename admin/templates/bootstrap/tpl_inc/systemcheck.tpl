{function test_result}
    {if $test->getResult() === Systemcheck_Tests_Test::RESULT_OK}
        <div class="hidden-xs">
            <h4 class="label-wrap"><span class="label label-success">
                {$state = $test->getCurrentState()}
                {if $state !== null && $state|strlen > 0}
                    {$state}
                {else}
                    <i class="fal fa-check text-success" aria-hidden="true"></i>
                {/if}
            </span></h4>
        </div>
        <div class="visible-xs">
            <h4 class="label-wrap">
                <span class="label label-success"> <i class="fal fa-check text-success" aria-hidden="true"></i></span>
            </h4>
        </div>
    {elseif $test->getResult() === Systemcheck_Tests_Test::RESULT_FAILED}
        {if $test->getIsOptional()}
            <div class="hidden-xs">
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
                                <i class="fal fa-times" aria-hidden="true"></i>
                            {/if}
                        </span>
                    </h4>
                {/if}
            </div>
            <div class="visible-xs">
                {if $test->getIsRecommended()}
                    <h4 class="label-wrap">
                        <span class="label label-warning">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        </span>
                    </h4>
                {else}
                    <h4 class="label-wrap"><span class="label label-primary">
                        <i class="fal fa-times" aria-hidden="true"></i>
                    </span></h4>
                {/if}
            </div>
        {else}
            <div class="hidden-xs">
                {$state = $test->getCurrentState()}
                <h4 class="label-wrap">
                    <span class="label label-danger">
                        {if $state !== null && $state|strlen > 0}
                            {$state}
                        {else}
                            <i class="fal fa-times" aria-hidden="true"></i>
                        {/if}
                    </span>
                </h4>
            </div>
            <div class="visible-xs">
                <h4 class="label-wrap">
                    <span class="label label-danger">
                        <i class="fal fa-times" aria-hidden="true"></i>
                    </span>
                </h4>
            </div>
        {/if}
    {/if}
{/function}
