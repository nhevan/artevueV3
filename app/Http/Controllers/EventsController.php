<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\EventTransformer;

class EventsController extends ApiController
{
    protected $request;

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

        $this->trackAction(Auth::user(), "View Events Feed");

        return $this->respondWithPagination($events, New EventTransformer);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAddForm()
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
        $this->validate($this->request, [
            'headline' => 'required|max:255',
            'description' => 'required',
            'location' => 'required|max:255',
            'url' => 'required|url',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'publish_date' => 'required|date',
            'image_url' => 'required|file',
        ]);
        $path = $this->uploadEventImageTos3();
        $this->request->merge(['image' => $path]);
        $event =  New Event;
        $event->fill($this->request->all());
        $event->save();
        return redirect()->action(
            'EventsController@all'
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
        return view('events.view',compact('event'));
    }

    /**
     * displays list of all events
     * @return [type] [description]
     */
    public function all()
    {
        $events = Event::latest()->paginate(10);
        return view('events.index', compact('events'));
    }

    /**
     * Show the form for editing a resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showEditForm(Event $event)
    {
        return view('events.edit',compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        $this->validate($this->request, [
            'headline' => 'required|max:255',
            'description' => 'required',
            'location' => 'required|max:255',
            'url' => 'required|url',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'publish_date' => 'required|date'
        ]);

        if($this->request->hasFile('image_url')) {
            $path = $this->uploadEventImageTos3();
            $this->request->merge(['image' => $path]);
        }
        $event->fill($this->request->all());
        $event->save();
        return redirect()->action(
            'EventsController@all'
        );
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
        $event->delete();
        return redirect()->action(
            'EventsController@all'
        );
    }
}
