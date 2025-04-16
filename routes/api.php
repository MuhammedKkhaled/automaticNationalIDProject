<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Codesmiths\LaravelOcrSpace\OcrSpaceOptions;
use Codesmiths\LaravelOcrSpace\Facades\OcrSpace;
use App\Http\Controllers\API\IdVerificationController;
use Codesmiths\LaravelOcrSpace\Enums\Language;
use Codesmiths\LaravelOcrSpace\Enums\InputType;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('id-verification/upload', [IdVerificationController::class, 'upload']);

Route::post('id-verification/verify', function (Request $request) {
    $getFilePath = public_path('storage/ocr-images/2hwEs8cMy68b0yr6ozPfEM0ci8aLwzvPBBIKmjxN.jpg');

    $results = OcrSpace::parseImageFile(
        $getFilePath,
        OcrSpaceOptions::make()->language(Language::Arabic)->overlayRequired(true   )
    );

    $response =   $results->getParsedResults(); // Returns an Collection `ParsedResult`

    dd($response);
    // $results = $results->

    // Extract the parsed text from results
    $parsedText = $results->getParsedResults();
    // $processingTime = $results->processingTimeInMilliseconds;

    return response()->json([
        'message' => 'Image processed successfully',
        'data' => [
            'text' => $parsedText,
            // 'processing_time_ms' => $processingTime,
            // 'success' => !$results->isErroredOnProcessing
        ]
    ], 200);
});
