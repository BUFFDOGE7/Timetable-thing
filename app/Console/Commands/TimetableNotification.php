<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Mail\Timetable;
use Illuminate\Support\Facades\Mail;

#[Signature('app:timetable-notification')]
#[Description('Send timetable notifications')]
class TimetableNotification extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $url = config('services.timetable.url');

        if (! is_string($url) || $url === '') {
            $this->error('Set TIMETABLE_API_URL in your .env file to the timetable API endpoint.');

            return self::FAILURE;
        }
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $response = Http::get($url, [
            'from' => $startDate->toISOString(),
            'lang' => 'ET',
            'page' => 0,
            'schoolId' => 38,
            'size' => 50,
            'studentGroups' => 'ea0550fb-8387-4aa2-880a-9abbd37a69ce',
            'thru' => $endDate->toISOString(),
        ]);

        $timetableEvents = collect($response->json()['content'])
    ->sortBy(['date', 'timeStart'])
    ->groupBy(fn ($event) => Carbon::parse($event['date'])->locale('et')->dayName);

    Mail::to('test@example.com')->send(
    new Timetable($timetableEvents, $startDate, $endDate)
);

$this->info('Timetable email sent!');

return self::SUCCESS;
    }
}