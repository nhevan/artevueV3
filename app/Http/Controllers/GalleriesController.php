<?php

namespace App\Http\Controllers;

use App\Pin;
use App\User;
use App\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response as IlluminateResponse;

class GalleriesController extends ApiController
{
    protected $request;
    protected $galleries;

    public function __construct(Gallery $gallery, Request $request)
    {
        $this->request = $request;
        $this->galleries = $gallery;
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
            return $this->respond([
                'data' => $user->galleries
            ]);
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
            'description' => 'max:350'
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
            $pins = Pin::where('gallery_id', $gallery_id)->orderBy('id', 'DESC')->get();

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
            'description' => 'max:350',
            'email' => 'email',
            'website' => 'url',
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $gallery = Gallery::where('id', $gallery_id)->where('user_id', Auth::user()->id)->first();

        if ($gallery) {
            if ($this->request->name) {
                $is_name_taken = $this->galleries->where('name', $this->request->name)->where('user_id', Auth::user()->id)->first();
                if (!$is_name_taken) {
                    $gallery->name = $this->request->name;
                }else{
                    return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError("This user already has a gallery of the same name.");
                }
            }
            if ($this->request->description) {
                $gallery->description = $this->request->description;
            }
            if ($this->request->email) {
                $gallery->email = $this->request->email;
            }
            if ($this->request->website) {
                $gallery->website = $this->request->website;
            }

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
    public function destroy(Gallery $gallery)
    {
        //
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
}
