<?php

namespace App\Http\Controllers;

use App\ArtType;
use Illuminate\Http\Request;

class ArtTypesController extends ApiController
{
    protected $request;
    protected $art_types;

    public function __construct(ArtType $art_types, Request $request)
    {
        $this->request = $request;
        $this->art_types = $art_types;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $art_types = $this->art_types->select(['id', 'title'])->get();

        return $this->respond(['data' => $art_types]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ArtType  $artType
     * @return \Illuminate\Http\Response
     */
    public function show(ArtType $artType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ArtType  $artType
     * @return \Illuminate\Http\Response
     */
    public function edit(ArtType $artType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ArtType  $artType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ArtType $artType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ArtType  $artType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ArtType $artType)
    {
        //
    }
}
