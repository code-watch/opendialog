<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\VirtualIntent;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Runs migrations on the sqlite database
     */
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    /**
     * Adds Uid values to the provided Scenario and all its conversation objects.
     *
     * @param  Scenario  $scenario
     *
     * @return Scenario
     */
    public function addFakeUids(Scenario $scenario)
    {
        static $currentUid = 0;

        $getUid = fn (int $count) => "0x".(1000 + $count);
        $scenario->setUid($getUid($currentUid++));
        $conversations = $scenario->getConversations();
        foreach ($conversations as $conversation) {
            $conversation->setUid($getUid($currentUid++));
            foreach ($conversation->getScenes() as $scene) {
                $scene->setUid($getUid($currentUid++));
                foreach ($scene->getTurns() as $turn) {
                    $turn->setUid($getUid($currentUid++));
                    foreach ($turn->getRequestIntents() as $intent) {
                        $intent->setUid($getUid($currentUid++));
                    }
                    foreach ($turn->getResponseIntents() as $intent) {
                        $intent->setUid($getUid($currentUid++));
                    }
                }
            }
        }
        return $scenario;
    }

    /**
     * @param Turn $turn
     * @param $uid
     * @param $odId
     * @param $speaker
     * @return Intent
     */
    protected function createIntent(Turn $turn, $uid, $odId, $speaker): Intent
    {
        $intent = new Intent($turn);
        $intent->setUid($uid);
        $intent->setOdId($odId);
        $intent->setName('Welcome intent 1');
        $intent->setDescription('A welcome intent 1');
        $intent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $intent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $intent->setInterpreter('interpreter.core.nlp');
        $intent->setConditions(new ConditionCollection());
        $intent->setBehaviors(new BehaviorsCollection());
        $intent->setSpeaker($speaker);
        $intent->setConfidence(1.0);
        $intent->setListensFor(['intent_a', 'intent_b']);
        $intent->setTransition(new Transition(null, null, null));
        $intent->setVirtualIntent(VirtualIntent::createEmpty());
        $intent->setSampleUtterance('Hello!');

        return $intent;
    }
}
