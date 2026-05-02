<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use File;

use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductImageController extends Controller
{
    public function update(Request $request)
    {
        if ($request->hasFile('image')) {
            $image   = $request->image;
            $ext     = $image->getClientOriginalExtension();
            $srcPath = $image->getPathName();

            $productImage = new ProductImage();
            $productImage->product_id = $request->product_id;
            $productImage->image = 'NULL';
            $productImage->save();

            $newImgName = $request->product_id . '-' . $productImage->id . '-' . time() . '.' . $ext;
            $productImage->image = $newImgName;
            $productImage->save();
            
            // Generate Large Image Thumbnail
            $destPath = public_path('uploads/product/large/' . $newImgName);

            $manager = new ImageManager(new Driver());
            $image   = $manager->read($srcPath);
            $image->scaleDown(1400)->save($destPath);     // 200 x 150 scale down to fixed width

            // Generate Small Image Thumbnail
            $destPath = public_path('uploads/product/small/' . $newImgName);

            $manager = new ImageManager(new Driver());
            $image   = $manager->read($srcPath);
            $image->cover(300, 300)->save($destPath);

            return response()->json([
                'status'     => true,
                'msg'        => 'product image updated successfully',
                'image_id'   => $productImage->id,
                'image_path' => asset('/uploads/product/small/' . $productImage->image),
            ]);
        }
    }

    // delete prdocut imge
    public function deleteProdcutImg(Request $request)
    {
        $productImage = ProductImage::find($request->id);

        if (empty($productImage)) return response()->json(['status' => false, 'msg' => 'This image not found in database !!!']);

        File::delete(public_path() . '/uploads/product/large/' . $productImage->image);
        File::delete(public_path() . '/uploads/product/small/' . $productImage->image);

        $productImage->delete();

        return response()->json(['status' => true, 'msg' => 'prodcut image deleted successfully']);
    }


}
