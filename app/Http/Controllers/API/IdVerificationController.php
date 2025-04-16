<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\IdVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;
use Intervention\Image\Drivers\Gd\Driver;


class IdVerificationController extends Controller
{

    private $manager;


    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }


    public function upload(Request $request)
    {


        $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();

            // Store the image
            $path = $image->storeAs('image', $filename, 'public');

            // Auto-verify the ID (basic implementation)
            $verificationResult = $this->verifyEgyptianId($image);

            // Create verification record
            $verification = new IdVerification();
            $verification->user_id = "1";
            $verification->image_path = $path;
            $verification->status = $verificationResult['is_valid'] ? 'approved' : 'rejected';
            $verification->notes = $verificationResult['message'];
            $verification->save();

            if ($verificationResult['is_valid']) {
                return response()->json(
                    ['message' => "It's valid image"],
                    200
                );
            } else {
                return response()->json(
                    ['message' => "there is an error"],
                    400
                );
            }
        }
    }


    private function verifyEgyptianId($image)
    {
        // Load the image using Intervention Image package
        $img = $this->manager->read($image->getRealPath());

        // Get dimensions
        $width = $img->width();
        $height = $img->height();

        // ID-1 format dimensions (used for credit cards, ID cards, etc.)
        // Standard size: 85.60 × 53.98 millimeters (3.370 × 2.125 inches)
        $expectedAspectRatio = 85.60 / 53.98; // Approximately 1.586

        // Calculate actual aspect ratio
        $aspectRatio = $width / $height;

        // Remove the debug line
        // dd($img, $width, $height, $aspectRatio, $expectedAspectRatio);

        // Allow for a 5% tolerance in aspect ratio
        $isValidDimensions = abs($aspectRatio - $expectedAspectRatio) < ($expectedAspectRatio * 0.05);

        // Check if image is large enough to be readable
        // Assuming 300 DPI scan, dimensions should be approximately:
        // 85.60mm * (300/25.4) = 1012 pixels width
        // 53.98mm * (300/25.4) = 638 pixels height
        $isValidSize = ($width >= 1000 && $height >= 600);

        $result = [
            'is_valid' => $isValidDimensions && $isValidSize,
            'message' => $isValidDimensions && $isValidSize
                ? 'ID automatically verified - matches ID-1 format'
                : 'ID image dimensions do not match ID-1 format standards'
        ];

        return $result;
    }
}
