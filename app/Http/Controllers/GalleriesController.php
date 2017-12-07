<?php

namespace App\Http\Controllers;

use App\Pin;
use App\User;
use App\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\GalleryTransformer;
use Illuminate\Http\Response as IlluminateResponse;

class GalleriesController extends ApiController
{
    protected $request;
    protected $galleries;
    protected $galleryTransformer;

    public function __construct(Gallery $gallery, Request $request, GalleryTransformer $galleryTransformer)
    {
        $this->request = $request;
        $this->galleries = $gallery;
        $this->galleryTransformer = $galleryTransformer;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($user_id)
    {
        $user = User::find($user_id);
        if ($user) {

            if(Auth::check() && $user->id == Auth::user()->id){
                $galleries = $user->galleries()->orderBy('sequence')->paginate(30);

                return $this->respondWithPagination($galleries, $this->galleryTransformer );
            }

            $galleries = $user->galleries()->public()->orderBy('sequence')->paginate(30);

            return $this->respondWithPagination($galleries, $this->galleryTransformer );
        }

        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError("User not found.");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $rules = [
            'name' => 'required|max:40',
            'description' => 'max:350',
            'is_private' => 'nullable|in:0,1'
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $is_name_taken = $this->galleries->where('name', $this->request->name)->where('user_id', Auth::user()->id)->first();
        if (!$is_name_taken) {
            $gallery = Auth::user()->galleries()->create($this->request->all());
            
            if ($gallery) {
                return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)
                    ->respond([
                        'success'=>[
                            'message' => 'New gallery successfully created.',
                            'gallery_id' => $gallery->id,
                            'status_code' => $this->getStatusCode()
                        ]
                    ]);
            }
        }

        return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError("This user already has a gallery of the same name.");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function show($user_id, $gallery_id)
    {
        $gallery = Gallery::where('id', $gallery_id)->where('user_id', $user_id)->first();
        
        if ($gallery) {
            $pins = Pin::where('gallery_id', $gallery_id)->orderBy('sequence')->get();

            return $this->respond([
                'data' => $pins
            ]);
        }
        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError("No gallery found with matching id under the given user.");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function edit(Gallery $gallery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function update($gallery_id)
    {
        $rules = [
            'name' => 'max:40',
            'description' => 'nullable|max:350',
            'email' => 'nullable|email',
            'website' => 'nullable',
            'is_private' => 'nullable|in:0,1'
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $gallery = Gallery::where('id', $gallery_id)->where('user_id', Auth::user()->id)->first();

        if ($gallery) {
            $is_name_taken = $this->galleries->where('name', $this->request->name)->where('user_id', Auth::user()->id)->where('id', '<>', $gallery->id)->first();
            if (!$is_name_taken) {
                $gallery->name = $this->request->name;
            }else{
                return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError("This user already has a gallery of the same name.");
            }

            $gallery->description = $this->request->description;
            $gallery->email = $this->request->email;
            $gallery->website = $this->request->website;
            if ($this->request->is_private == null) {
                $this->request->is_private = 0;
            }
            $gallery->is_private = $this->request->is_private;

            $gallery->save();

            return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respond([ "message" => "Gallery successfully updated."]);
        }

        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError("No gallery found with the given id for the specified user.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function destroy($gallery_id)
    {
        $gallery = Gallery::where('id', $gallery_id)->where('user_id', Auth::user()->id)->first();
        if (!$gallery) {
            return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError("No gallery found with the given id for the specified user.");
        }

        $gallery->delete();

        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respond([ "message" => "Gallery successfully deleted."]);
    }

    /**
     * arranges the galleries of a user according to gallery ids
     * @return [type] [description]
     */
    public function arrangeGallery()
    {
        $rules = [
            'sequence' => 'required'
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $count = 1;
        foreach ($this->request->sequence as $gallery_id) {
            $gallery = Gallery::where('id', $gallery_id)->where('user_id', Auth::user()->id)->first();

            if (!$gallery) {
                return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError("Gallery {$gallery_id} does not belong to the authenticated user.");
            }
            $gallery->sequence = $count;
            $gallery->save();
            $count++;
        }
        
        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respond(["message" =>"Gallery successfully rearranged."]);
    }

    /**
     * arranges the pins within a gallery according to a given sequence
     * @return [type] [description]
     */
    public function arrangePins($gallery_id)
    {
        $gallery = Gallery::where('id', $gallery_id)->where('user_id', Auth::user()->id)->first();
        if(!$gallery){
            return $this->responseNotFound('No Gallery found with matching id for the authenticated user.');
        }

        $rules = [
            'sequence' => 'required'
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $count = 1;
        foreach ($this->request->sequence as $pin_id) {
            $pin = Pin::where('id', $pin_id)->where('gallery_id', $gallery_id)->where('user_id', Auth::user()->id)->first();

            if (!$pin) {
                return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError("Pin {$pin_id} does not belong to the given gallery or the authenticated user.");
            }
            $pin->sequence = $count;
            $pin->save();
            $count++;
        }
        
        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respond(["message" =>"Pins successfully rearranged."]);
    }
}
