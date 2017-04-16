<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use Acme\Transformers\EventTransformer;

class EventsController extends ApiController
{
    protected $request;
    
    /**
     * Acme/Transformers/postTransformer
     * @var postTransformer
     */
    protected $postTransformer;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::latest()->paginate(10);

        return $this->respondWithPagination($events, New EventTransformer);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('events.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $path = $this->uploadEventImageTos3();
        $this->request->merge(['image' => $path]);
        $event =  New Event;
        $event->fill($this->request->all());
        $event->save();
        return redirect()->action(
            'EventsController@show'
        );       
    }

    public function uploadEventImageTos3()
    {
        $storage = config('app.storage');
        $path = $this->request->file('image_url')->store(
            'img/events', 's3'
        );
        
        return $path;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        //
        $events = Event::all();
        return view('events.index',compact('events'));
    }

    public function view($id)
    {
        $event = Event::where('id', '=', $id)->first();
        return view('events.view',compact('event'));
    }

    public function delete($id)
    {
        Event::where(['id' => $id])->delete();
        return redirect()->action(
            'EventsController@show'
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        //
    }
}
