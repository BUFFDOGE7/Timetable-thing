<?php

use App\Mail\Timetable;
use App\Models\Author;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tere', function () {

    $authors = Author::all();

    $authors->load('books.reviews', 'reviews');

    // $books = [];

    // foreach ($authors as $author) {
    //     $books = array_merge($books, $author->books->toArray());
    // }

    return view('tere', [
        'authors' => $authors,
    ]);

});

Route::get('/mailable', function () {
    $url = config('services.timetable.url');

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

    return new Timetable($timetableEvents, $startDate, $endDate);
});