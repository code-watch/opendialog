<?php

namespace Tests\Feature\Components;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Actions\BaseAction;

class BrokenAction extends BaseAction
{
    protected static string $componentId = 'action.test.broken';

    /**
     * @inheritDoc
     */
    public function perform(ActionInput $actionInput): ActionResult
    {
        $someString = "this will not work";
        array_push($someString);

        return ActionResult::createSuccessfulActionResult();
    }
}
