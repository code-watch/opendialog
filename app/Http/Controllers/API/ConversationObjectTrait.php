<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenDialogAi\Core\Conversation\ConversationObject;

trait ConversationObjectTrait
{
    /**
     * @param ConversationObject $originalParent
     * @param ConversationObject $newObject
     * @param JsonResource $resource
     * @return JsonResource|JsonResponse
     */
    public function prepareODHeaders(
        ConversationObject $originalParent,
        ConversationObject $newObject,
        JsonResource $resource
    ) {
        if (!is_null($originalParent->getScenario()->getUid()) && !is_null($newObject->getScenario()->getUid())
            && $originalParent->getScenario()->getUid() !== $newObject->getScenario()->getUid()) {
            return $resource
                ->response()
                ->header('OD-New-Scenario', $newObject->getScenario()->getUid());
        }

        return $resource;
    }
}
