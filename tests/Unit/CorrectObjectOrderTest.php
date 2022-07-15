<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Facades\MessageTemplateDataClient;
use OpenDialogAi\Core\Conversation\Facades\ScenarioDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Tests\TestCase;

class CorrectObjectOrderTest extends TestCase
{
    public function testCorrectObjectOrderingRelational()
    {
        $scenario = $this->getFullTestScenario();

        Config::set('opendialog.core.relational_conversation_models', true);

        ScenarioDataClient::addFullScenarioGraph($scenario);

        $turn = $scenario->getConversations()[0]->getScenes()[0]->getTurns()[0];

        $intent1 = $this->createIntent($turn, '0x0001', 'welcome_intent_1', Intent::USER);
        $intent1->setOrder(3);
        ConversationDataClient::addRequestIntent($intent1);
        sleep(1);

        $intent2 = $this->createIntent($turn, '0x0002', 'welcome_intent_2', Intent::USER);
        $intent2->setOrder(2);
        ConversationDataClient::addRequestIntent($intent2);
        sleep(1);

        $intent3 = $this->createIntent($turn, '0x0003', 'welcome_intent_3', Intent::USER);
        $intent3->setOrder(1);
        ConversationDataClient::addRequestIntent($intent3);

        $messageTemplate1 = $this->addMessageTemplateToIntent($intent1, 3);
        sleep(1);

        $messageTemplate2 = $this->addMessageTemplateToIntent($intent1, 2);
        sleep(1);

        $messageTemplate3 = $this->addMessageTemplateToIntent($intent1, 2);

        $this->artisan('scenarios:correct-order');

        $scenario = ScenarioDataClient::getFullScenarioGraph($scenario->getUid());

        $turn = $scenario->getConversations()[0]->getScenes()[0]->getTurns()[0];

        $intent1 = $turn->getRequestIntents()->first(function (Intent $intent) use($intent1) {
            return $intent1->getUid() == $intent->getUid();
        });
        $intent2 = $turn->getRequestIntents()->first(function (Intent $intent) use($intent2) {
            return $intent2->getUid() == $intent->getUid();
        });
        $intent3 = $turn->getRequestIntents()->first(function (Intent $intent) use($intent3) {
            return $intent3->getUid() == $intent->getUid();
        });

        $this->assertTrue($intent3->getOrder() > $intent2->getOrder());
        $this->assertTrue($intent2->getOrder() > $intent1->getOrder());

        $messageTemplate1 = $intent1->getMessageTemplates()->first(function (MessageTemplate $messageTemplate) use ($messageTemplate1) {
           return $messageTemplate1->getUid() == $messageTemplate->getUid();
        });

        $messageTemplate2 = $intent1->getMessageTemplates()->first(function (MessageTemplate $messageTemplate) use ($messageTemplate2) {
            return $messageTemplate2->getUid() == $messageTemplate->getUid();
        });
        $messageTemplate3 = $intent1->getMessageTemplates()->first(function (MessageTemplate $messageTemplate) use ($messageTemplate3) {
            return $messageTemplate3->getUid() == $messageTemplate->getUid();
        });

        $this->assertTrue($messageTemplate3->getOrder() > $messageTemplate2->getOrder());
        $this->assertTrue($messageTemplate2->getOrder() > $messageTemplate1->getOrder());
    }

    /**
     * Returns a test scenario with conversations,scenes,turns and intents
     *
     * @return Scenario
     */
    public function getFullTestScenario(): Scenario
    {
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario_full");
        $scenario->setName("Test scenario (Full)");
        $scenario->setDescription("Test scenario description.");
        $scenario->setInterpreter("interpreter.core.nlp");
        $scenario->setBehaviors(new BehaviorsCollection([
            new Behavior(Behavior::STARTING_BEHAVIOR),
            new Behavior(Behavior::OPEN_BEHAVIOR)
        ]));
        $scenario->setActive(true);
        $scenario->setStatus(Scenario::LIVE_STATUS);

        // Conversations
        $conversationA = new Conversation($scenario);
        $conversationA->setOdId("test_conversation_a");
        $conversationA->setName("Test conversation (A)");
        $conversationA->setDescription("(A) Test conversation description.");
        $conversationA->setInterpreter("interpreter.core.nlp");
        $conversationA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $scenario->setConversations(new ConversationCollection([
            $conversationA
        ]));

        // Scenes
        $sceneA = new Scene($conversationA);
        $sceneA->setOdId("test_scene_a");
        $sceneA->setName("Test scene (A)");
        $sceneA->setDescription("(A) Test scene description.");
        $sceneA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $conversationA->setScenes(new SceneCollection([
            $sceneA
        ]));

        // Turns
        $turnA = new Turn($sceneA);
        $turnA->setOdId("test_turn_a");
        $turnA->setName("Test turn (A)");
        $turnA->setDescription("(A) Test turn description.");
        $turnA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING_BEHAVIOR)]));

        $sceneA->setTurns(new TurnCollection([
            $turnA
        ]));

        return $scenario;
    }

    private function addMessageTemplateToIntent(Intent $intent, $order)
    {
        $messageTemplate = new MessageTemplate();
        $messageTemplate->setIntent($intent);
        $messageTemplate->setOrder($order);
        $messageTemplate->setOdId('od-' . $order);
        $messageTemplate->setMessageMarkup('test markup');
        $intent->addMessageTemplate($messageTemplate);

        return MessageTemplateDataClient::addMessageTemplateToIntent($messageTemplate);
    }
}
