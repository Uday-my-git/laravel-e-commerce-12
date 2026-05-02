<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class TempImagecontroller extends Controller
{
    public function create(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            
            $tempImage = new TempImage();
            $tempImage->name = 'TEST';
            $tempImage->save();

            $newName = $tempImage->id . '-' . time() . '.' . $ext;
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path() . '/temp', $newName);

            // Generate Image Thumbnail
            $srcPath = public_path() . '/temp/' . $newName;
            $destPath = public_path() . '/temp/thumb/' . $newName;

            $manager = new ImageManager(new Driver());
            $image = $manager->read($srcPath);
            $image->cover(300, 275)->save($destPath);

            return response()->json([
                'status'     => true,
                'msg'        => 'Image Uploaded Successfully...',
                'image_id'   => $tempImage->id,
                'image_path' => asset('/temp/thumb/' . $newName),
            ]);
        }
    }

    /*
    public function create123(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (!$request->hasFile('image')) {
            return response()->json([
                'status' => false,
                'msg' => 'No image file found',
            ], 422);
        }

        $uploadedImage = $request->file('image');
        $ext = $uploadedImage->getClientOriginalExtension();

        // ✅ Create temp image record ONCE
        $tempImage = TempImage::create([
            'name' => 'temp', // placeholder
        ]);

        $newName = $tempImage->id . '-' . time() . '.' . $ext;

        // ✅ Update filename
        $tempImage->update(['name' => $newName]);

        // ✅ Ensure directories exist
        $tempPath = public_path('temp');
        $thumbPath = public_path('temp/thumb');

        if (!File::exists($tempPath)) {
            File::makeDirectory($tempPath, 0755, true);
        }

        if (!File::exists($thumbPath)) {
            File::makeDirectory($thumbPath, 0755, true);
        }

        // ✅ Move original image
        $uploadedImage->move($tempPath, $newName);

        // ✅ Create thumbnail
        $manager = new ImageManager(new Driver());
        $imageInstance = $manager->read($tempPath . '/' . $newName);

        $imageInstance
            ->cover(300, 275)
            ->save($thumbPath . '/' . $newName);

        return response()->json([
            'status' => true,
            'msg' => 'Image uploaded successfully',
            'image_id' => $tempImage->id,
            'image_path' => asset('temp/thumb/' . $newName),
        ]);
    }
        */

}
