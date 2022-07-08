<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\MessageTemplateRequest;
use App\Http\Resources\MessageTemplateResource;
use OpenDialogAi\Core\Conversation\DataClients\MessageTemplateDataClient;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Turn;

class MessageTemplateController extends Controller
{
    use ConversationObjectTrait;

    /**
     * @var MessageTemplateDataClient
     */
    private $messageTemplateDataClient;

    public function __construct()
    {
        $this->middleware('auth');
        $this->messageTemplateDataClient = resolve(MessageTemplateDataClient::class);
    }

    public function store(Intent $intent, MessageTemplateRequest $request)
    {
        /** @var MessageTemplate $newMessageTemplate */
        $newMessageTemplate = Serializer::deserialize($request->getContent(), MessageTemplate::class, 'json');
        $newMessageTemplate->setIntent($intent);
        $newMessageTemplate->setOrder($intent->getMessageTemplates() ?
            $intent->getMessageTemplates()->getNextOrderNumber() : 0);

        $messageTemplate = $this->messageTemplateDataClient->addMessageTemplateToIntent($newMessageTemplate);

        $resource = new MessageTemplateResource($messageTemplate);

        /** @var Turn $originalIntent */
        $originalIntent = ConversationDataClient::getScenarioWithFocusedIntent($intent->getUid());

        return $this->prepareODHeaders($originalIntent, $messageTemplate, $resource);
    }

    public function show(?Intent $intent, MessageTemplate $messageTemplate)
    {
        return new MessageTemplateResource($messageTemplate);
    }

    public function destroy(?Intent $intent, MessageTemplate $messageTemplate)
    {
        $messageTemplate = $this->messageTemplateDataClient->deleteMessageTemplate($messageTemplate->getUid());

        $resource = new MessageTemplateResource($messageTemplate);

        /** @var Turn $originalTurn */
        $originalIntent = ConversationDataClient::getScenarioWithFocusedIntent($intent->getUid());

        return $this->prepareODHeaders($originalIntent, $messageTemplate, $resource);
    }

    public function update(Intent $intent, MessageTemplateRequest $request)
    {
        $update = Serializer::deserialize($request->getContent(), MessageTemplate::class, 'json');
        $update->setIntent($intent);

        $messageTemplate = $this->messageTemplateDataClient->updateMessageTemplate($update);

        $resource = new MessageTemplateResource($messageTemplate);

        /** @var Turn $originalTurn */
        $originalIntent = ConversationDataClient::getScenarioWithFocusedIntent($intent->getUid());

        return $this->prepareODHeaders($originalIntent, $messageTemplate, $resource);
    }
}
