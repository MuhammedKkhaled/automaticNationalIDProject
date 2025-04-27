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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('id-verification/upload', [IdVerificationController::class, 'upload']);

Route::post('id-verification/verify', function (Request $request) {
    $getFilePath = public_path('storage/image/1744884394.jpeg');

    $results = OcrSpace::parseImageFile(
        $getFilePath,
        OcrSpaceOptions::make()
            ->language(Language::Auto)
            ->OCREngine(OcrSpaceEngine::Engine2)

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
