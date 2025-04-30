<?php

use App\Helpers\IdCardParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Codesmiths\LaravelOcrSpace\Enums\Language;
use Codesmiths\LaravelOcrSpace\Enums\InputType;
use Codesmiths\LaravelOcrSpace\OcrSpaceOptions;
use Codesmiths\LaravelOcrSpace\Facades\OcrSpace;
use Codesmiths\LaravelOcrSpace\Enums\OcrSpaceEngine;
use App\Http\Controllers\API\IdVerificationController;
use App\Http\Controllers\API\ZoomIntegrateController;
use App\Http\Controllers\ZoomController;
use Jubaer\Zoom\Facades\Zoom as FacadesZoom;
use Jubaer\Zoom\Zoom;
use Jubaer\Zoom\ZoomServiceProvider;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('id-verification/upload', [IdVerificationController::class, 'upload']);

Route::post('id-verification/verify', function (Request $request) {
    $getFilePath = public_path('storage/ocr-images/WhatsApp Image 2025-04-16 at 2.37.00 PM.jpeg');

    $results = OcrSpace::parseImageFile(
        $getFilePath,
        OcrSpaceOptions::make()
            ->language(Language::English)
        // ->OCREngine(OcrSpaceEngine::Engine2)

    );

    $response =   $results->getParsedResults(); // Returns an Collection `ParsedResult`

    // Extract the parsed text from results
    $parsedText = $response->first()->getSerializedParsedText(); // Returns the parsed text from the first parsed result


    return response()->json([
        'message' => 'Image processed successfully',
        'data' => [
            'text' => $parsedText,
            'national_id' => IdCardParser::arabicToEnglish($parsedText),
        ]
    ], 200);
});


Route::get('start', [ZoomController::class, 'index']);
Route::any('redirect-url', [ZoomController::class, 'index']);
Route::post('create-meeting', function(){

    $data = [
        'topic' => 'Test Meeting',
        'start_time' => now()->addHour()->toIso8601String(), // tomorrow
        'duration' => 30, // minutes
        'agenda' => 'Discuss project updates',
    ];

    $zoomController = new ZoomIntegrateController();

    $meeting = $zoomController->createZoomMeeting($data);

    // dd($meeting);
    // $meetings = FacadesZoom::createMeeting
    return response()->json([
        'message' => 'Meeting created successfully',
        'data' => $meeting
    ], 200);

    //  $zoomController = new ZoomIntegrateController();
    // $data =  $zoomController->participants(86767866906);
    // dd($data);



});


Route::get('participants', function () {

    $meetingID = "88636371650";
    // "YsesTv3RSUSp+UcVH4jLbw=="
    $zoomController = new ZoomIntegrateController();

    $participants = $zoomController->participants($meetingID);

    return response()->json([
        'message' => 'Participants fetched successfully',
        'data' => $participants
    ], 200);

});

Route::post('create-event' , function(){
    $data = [
        "name" => "Event for testing",
        'description' => "Event for testing Description",
        'event_type'=> "SIMPLE_EVENT",
        'access_level' => "PRIVATE_UNRESTRICTED",
    ];

    $zoomController = new ZoomIntegrateController();
    $event = $zoomController->createEvent($data);

    return response()->json([
        'message' => 'Event created successfully',
        'data' => $event
    ], 201);
});


Route::get('get-hubs' , function (){
    $zoomController = new ZoomIntegrateController();
    $hubs = $zoomController->getHubs();
    return response()->json([
        'message' => 'Hubs fetched successfully',
        'data' => $hubs
    ], 200);
});

Route::post('create-webinar' , function (){
        $data =[
            'start_time' => now()->addMinutes(30)->toIso8601String(),
            'topic'=>"New Webinar Test",
            'duration' => 30,
            'agenda' => "This is a test webinar",
            'schedule_for' => "moez@career-180.com",
        ];

        $zoomController = new ZoomIntegrateController();
        $webinar = $zoomController->createWebinar($data);
        return response()->json([
            'message' => 'Webinar created successfully',
            'data' => $webinar
        ], 201);
});
