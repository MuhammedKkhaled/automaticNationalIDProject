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
        'start_time' => now()->addDay()->toIso8601String(), // tomorrow
        'duration' => 30, // minutes
        'agenda' => 'Discuss project updates',
    ];

    $zoomController = new ZoomIntegrateController();

    $meeting = $zoomController->createZoomMeeting($data);

    dd($meeting);
    // $meetings = FacadesZoom::createMeeting
    // return response()->json([
    //     'message' => 'Meeting created successfully',
    //     'data' => $meetings
    // ], 200);
});
