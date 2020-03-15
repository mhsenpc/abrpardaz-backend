<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Image\AddImageRequest;
use App\Http\Requests\Image\EditImageRequest;
use App\Http\Requests\Image\RemoveImageRequest;
use App\Http\Requests\Image\ShowImageRequest;
use App\Models\Image;
use App\Services\Responder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImageController extends BaseController
{
    /**
     * @OA\Get(
     *      tags={"Image"},
     *      path="/images/list",
     *      summary="List all images",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="List of images"
     *     ),
     *
     *     )
     *
     */
    function index()
    {
        $images = Image::paginate(10);
        return Responder::result(['pagination' => $images]);
    }

    /**
     * @OA\Get(
     *      tags={"Image"},
     *      path="/images/{id}/show",
     *      summary="Return a specific image",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="Return a specific image"
     *     ),
     *
     *     )
     *
     */
    function show(ShowImageRequest $request)
    {
        $item = Image::find(request('id'));
        return Responder::result(['item' => $item]);
    }

    /**
     * @OA\Post(
     *      tags={"Image"},
     *      path="/images/add",
     *      summary="Add an image",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="remote_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_disk",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_ram",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     )
     *
     */
    function add(AddImageRequest $request)
    {
        Image::create([
            'remote_id' => $request->input('remote_id'),
            'name' => $request->input('name'),
            'version' => $request->input('version'),
            'min_disk' => $request->input('min_disk'),
            'min_ram' => $request->input('min_ram'),
        ]);
        Log::info('new image created. user #' . Auth::id());
        return Responder::success("تصویر با موفقیت اضافه شد");
    }

    /**
     * @OA\Post(
     *      tags={"Image"},
     *      path="/images/{id}/edit",
     *      summary="Edit an image using its id",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="remote_id",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_disk",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="min_ram",
     *         in="query",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *
     *     )
     *
     */
    function edit(EditImageRequest $request)
    {
        Image::find(\request('id'))->update([
            'remote_id' => $request->input('remote_id'),
            'name' => $request->input('name'),
            'version' => $request->input('version'),
            'min_disk' => $request->input('min_disk'),
            'min_ram' => $request->input('min_ram'),
        ]);
        Log::info('image edited. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("تصویر با موفقیت ویرایش شد");
    }

    /**
     * @OA\Delete(
     *      tags={"Image"},
     *      path="/images/{id}/remove",
     *      summary="Remove an image using its id",
     *      description="",
     *
     * @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     )
     *
     */
    function remove(RemoveImageRequest $request)
    {
        Image::destroy(\request('id'));
        Log::info('image removed. key #' . request('id') . ',user #' . Auth::id());
        return Responder::success("تصویر با موفقیت حذف شد");
    }
}
