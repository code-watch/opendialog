<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\MessageTemplateRequest;
use App\Http\Resources\MessageTemplateResource;
use OpenDialogAi\Core\Conversation\Facades\MessageTemplateDataClient;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;

class MessageTemplateController extends Controller
{
    use ConversationObjectTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Intent $intent, MessageTemplateRequest $request)
    {
        /** @var MessageTemplate $newMessageTemplate */
        $newMessageTemplate = Serializer::deserialize($request->getContent(), MessageTemplate::class, 'json');
        $newMessageTemplate->setIntent($intent);
        $newMessageTemplate->setOrder($intent->getMessageTemplates() ? count($intent->getMessageTemplates()) : 0);

        $messageTemplate = MessageTemplateDataClient::addMessageTemplateToIntent($newMessageTemplate);

        $resource = new MessageTemplateResource($messageTemplate);

        /** @var Intent $originalIntent */
        $originalIntent = ConversationDataClient::getScenarioWithFocusedIntent($intent->getUid());

        return $this->prepareODHeaders($originalIntent, $messageTemplate, $resource);
    }

    public function show(?Intent $intent, MessageTemplate $messageTemplate)
    {
        return new MessageTemplateResource($messageTemplate);
    }

    public function destroy(?Intent $intent, MessageTemplate $messageTemplate)
    {
        $messageTemplate = MessageTemplateDataClient::deleteMessageTemplate($messageTemplate->getUid());

        $resource = new MessageTemplateResource($messageTemplate);

        /** @var Intent $originalIntent */
        $originalIntent = ConversationDataClient::getScenarioWithFocusedIntent($intent->getUid());

        return $this->prepareODHeaders($originalIntent, $messageTemplate, $resource);
    }

    public function update(Intent $intent, MessageTemplateRequest $request)
    {
        $update = Serializer::deserialize($request->getContent(), MessageTemplate::class, 'json');
        $update->setIntent($intent);

        $messageTemplate = MessageTemplateDataClient::updateMessageTemplate($update);

        $resource = new MessageTemplateResource($messageTemplate);

        /** @var Intent $originalIntent */
        $originalIntent = ConversationDataClient::getScenarioWithFocusedIntent($intent->getUid());

        return $this->prepareODHeaders($originalIntent, $messageTemplate, $resource);
    }
}
