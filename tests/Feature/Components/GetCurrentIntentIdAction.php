<?php

namespace Tests\Feature\Components;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\ActionEngine\Configuration\ActionConfiguration;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Conversation\Intent;

class GetCurrentIntentIdAction extends BaseAction
{
    protected static string $componentId = 'action.test.get_current_intent_id';

    protected static bool $usesBeforeCallback = true;

    private string $intentId;

    public function __construct(ActionConfiguration $configuration)
    {
        parent::__construct($configuration);

        $this->setBeforePerformCallback(fn (Intent $i) => $this->intentId = $i->getUid());
    }

    /**
     * @inheritDoc
     */
    public function perform(ActionInput $actionInput): ActionResult
    {
        return ActionResult::createSuccessfulActionResultWithAttributes([
            AttributeResolver::getAttributeFor('intent_id', $this->intentId),
        ]);
    }
}
