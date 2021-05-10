<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageTemplateCollection;
use App\Http\Resources\MessageTemplateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use OpenDialogAi\ResponseEngine\Rules\MessageConditions;
use OpenDialogAi\ResponseEngine\Rules\MessageXML;

class MessageTemplatesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @param $outgoingIntentId
     * @return MessageTemplateCollection
     */
    public function index($outgoingIntentId): MessageTemplateCollection
    {
        /** @var MessageTemplate $messageTemplates */
        $messageTemplates = MessageTemplate::where('outgoing_intent_id', $outgoingIntentId)->paginate(50);

        foreach ($messageTemplates as $messageTemplate) {
            $messageTemplate->makeVisible('id');
        }

        return new MessageTemplateCollection($messageTemplates);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param         $outgoingIntentId
     * @param Request $request
     * @return MessageTemplateResource
     */
    public function store($outgoingIntentId, Request $request)
    {
        if (!OutgoingIntent::find($outgoingIntentId)) {
            return response("The requested Outgoing Intent ID does not exist.", 404);
        }

        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = MessageTemplate::make($request->all());
        $messageTemplate->outgoing_intent_id = $outgoingIntentId;

        if ($error = $this->validateValue($messageTemplate)) {
            return response($error, 400);
        }

        $messageTemplate->save();

        $messageTemplate->makeVisible('id');

        return new MessageTemplateResource($messageTemplate);
    }


    /**
     * Display the specified resource.
     *
     * @param     $outgoingIntentId
     * @param int $id
     * @return MessageTemplateResource
     */
    public function show($outgoingIntentId, $id): MessageTemplateResource
    {
        $messageTemplate = MessageTemplate::where('outgoing_intent_id', $outgoingIntentId)->find($id);

        if ($messageTemplate) {
            $messageTemplate->makeVisible('id');
            return new MessageTemplateResource($messageTemplate);
        }

        abort(404);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param         $outgoingIntentId
     * @param int     $id
     * @return Response
     */
    public function update(Request $request, $outgoingIntentId, $id): Response
    {
        if ($messageTemplate = MessageTemplate::where('outgoing_intent_id', $outgoingIntentId)->find($id)) {
            $messageTemplate->fill($request->all());

            if ($error = $this->validateValue($messageTemplate)) {
                return response($error, 400);
            }

            $messageTemplate->save();
        }

        return response()->noContent(200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param     $outgoingIntentId
     * @param int $id
     * @return Response
     */
    public function destroy($outgoingIntentId, $id): Response
    {
        if ($messageTemplate = MessageTemplate::where('outgoing_intent_id', $outgoingIntentId)->find($id)) {
            $messageTemplate->delete();
        }

        return response()->noContent(200);
    }

    /**
     * @param MessageTemplate $messageTemplate
     * @return array
     */
    private function validateValue(MessageTemplate $messageTemplate): ?array
    {
        $ruleXML = new MessageXML();
        $ruleConditions = new MessageConditions();

        if (strlen($messageTemplate->name) > 255) {
            return [
                'field' => 'name',
                'message' => 'The maximum length for message template name is 255.',
            ];
        }

        if (!$messageTemplate->name) {
            return [
                'field' => 'name',
                'message' => 'Message template name field is required.',
            ];
        }

        if (MessageTemplate::where('name', $messageTemplate->name)->where('id', '<>', $messageTemplate->id)->count()) {
            return [
                'field' => 'name',
                'message' => 'Message template name is already in use.',
            ];
        }

        if (!$ruleConditions->passes(null, $messageTemplate->conditions)) {
            return [
                'field' => 'conditions',
                'message' => $ruleConditions->message(),
            ];
        }

        if (!$ruleXML->passes(null, $messageTemplate->message_markup)) {
            return [
                'field' => 'message_markup',
                'message' => $ruleXML->message() . '.',
            ];
        }

        return null;
    }
}
