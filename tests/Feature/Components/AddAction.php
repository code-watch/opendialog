<?php

namespace Tests\Feature\Components;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;

class AddAction extends BaseAction
{
    protected static string $componentId = 'action.test.add';

    protected static array $requiredAttributes = ['num_1', 'num_2'];
    protected static array $outputAttributes = ['sum'];

    /**
     * @inheritDoc
     */
    public function perform(ActionInput $actionInput): ActionResult
    {
        $num1 = $actionInput->getAttributeBag()->getAttributeValue('num_1');
        $num2 = $actionInput->getAttributeBag()->getAttributeValue('num_2');

        return ActionResult::createSuccessfulActionResultWithAttributes([
            AttributeResolver::getAttributeFor('sum', $num1 + $num2),
        ]);
    }
}
