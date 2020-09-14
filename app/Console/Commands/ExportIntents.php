<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ExportIntents extends Command
{
    protected $signature = 'intents:export {outgoingIntent?} {--y|yes}';

    protected $description = 'Export all outgoing intents';

    public function handle()
    {
        $outgoingIntentName = $this->argument('outgoingIntent');

        if ($this->option("yes")) {
            $continue = true;
        } elseif ($outgoingIntentName) {
            $continue = $this->confirm(
                sprintf(
                    'Do you want to export %s outgoing intent and it own messages?',
                    $outgoingIntentName
                )
            );
        } else {
            $continue = $this->confirm('Do you want to export all outgoing intents and messages?');
        }

        if ($continue) {
            $outgoingIntents = OutgoingIntent::all();

            foreach ($outgoingIntents as $outgoingIntent) {
                $this->exportoutgoingIntent($outgoingIntent);
            }

            $this->info('Exports finished');
        } else {
            $this->info('Bye');
        }
    }

    protected function exportoutgoingIntent(OutgoingIntent $outgoingIntent): void
    {
        $this->info(sprintf('Exporting outgoing intent %s', $outgoingIntent->name));

        $output = "<intent>" . $outgoingIntent->name . "</intent>";

        $filename = base_path("resources/intents/$outgoingIntent->name.intent");
        file_put_contents($filename, $output);
    }
}
