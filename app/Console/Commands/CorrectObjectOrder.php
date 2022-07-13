<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\MessageTemplateDataClient;
use OpenDialogAi\Core\Conversation\Facades\ScenarioDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\MessageTemplateCollection;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * Checks for the conversation objects order (intents and messages) and fixes them if there are problems.
 */
class CorrectObjectOrder extends Command
{
    protected $signature = 'scenarios:correct-order {--scenarioId=*}';

    protected $description = 'Corrects existing scenarios intents/messages order.';

    public function handle()
    {
        $scenarioIds = $this->option('scenarioId');

        $this->checkAndCorrectObjectsOrder($scenarioIds);
        $this->info('Correcting intents/messages orders has finished.');

        return 0;
    }

    private function checkAndCorrectObjectsOrder(array $scenarioIds = null)
    {
        if ($scenarioIds) {
            $scenarios = new ScenarioCollection();
            foreach ($scenarioIds as $id) {
                $scenario = ConversationDataClient::getScenarioByUid($id);
                $scenarios->addObject($scenario);
            }
        } else {
            $scenarios = ConversationDataClient::getAllScenarios();
        }

        foreach ($scenarios as $scenarioId) {
            $scenario = ScenarioDataClient::getFullScenarioGraph($scenarioId->getUid());
            foreach ($scenario->getConversations() as $conversation) {
                foreach ($conversation->getScenes() as $scene) {
                    foreach ($scene->getTurns() as $turn) {
                        if ($turn->hasRequestIntents()) {
                            $this->orderIntents($turn->getRequestIntents());
                        }
                        if ($turn->hasResponseIntents()) {
                            $this->orderIntents($turn->getResponseIntents());
                        }
                    }
                }
            }
        }
    }

    private function orderIntents(IntentCollection $intents): void
    {
        $sortedIntents = $intents->sortBy(function (Intent $intent) {
            return $intent->getCreatedAt();
        });

        $lastOrder = 0;
        foreach ($sortedIntents as $intent) {
            if ($intent->getOrder() < $lastOrder) {
                $intent->setOrder($lastOrder);
                $this->info('Updating order for intent: ' . $intent->getUid());
                ConversationDataClient::updateIntent($intent);
            }
            $lastOrder++;
            if ($lastOrder <= $intent->getOrder()) {
                $lastOrder = $intent->getOrder() + 1;
            }

            $this->orderMessages($intent->getMessageTemplates());
        }
    }

    private function orderMessages(?MessageTemplateCollection $messageTemplates): void
    {
        if (is_null($messageTemplates)) {
            return;
        }

        $sortedMessageTemplates = $messageTemplates->sortBy(function (MessageTemplate $messageTemplate) {
            return $messageTemplate->getCreatedAt();
        });
        $lastOrder = 0;
        foreach ($sortedMessageTemplates as $messageTemplate) {
            if ($messageTemplate->getOrder() < $lastOrder) {
                $messageTemplate->setOrder($lastOrder);
                $this->info('Updating order for message: ' . $messageTemplate->getUid());
                MessageTemplateDataClient::updateMessageTemplate($messageTemplate);
            }
            $lastOrder++;
            if ($lastOrder <= $messageTemplate->getOrder()) {
                $lastOrder = $messageTemplate->getOrder() + 1;
            }
        }
    }
}
