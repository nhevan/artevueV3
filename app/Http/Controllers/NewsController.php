<?php

namespace App\Http\Controllers;

use App\News;
use Illuminate\Http\Request;
use Acme\Transformers\NewsTransformer;

class NewsController extends ApiController
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
        $news = News::latest()->paginate(10);

        return $this->respondWithPagination($news, New NewsTransformer);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('news.add');
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
            'url' => 'required|url',
            'publish_date' => 'required|date',
            'image_url' => 'required|file',
        ]);
        $path = $this->uploadNewsImageTos3();
        $this->request->merge(['image' => $path]);
        $news =  New News;
        $news->fill($this->request->all());
        $news->save();
        return redirect()->action(
            'NewsController@show'
        );     
    }

    public function uploadNewsImageTos3()
    {
        $storage = config('app.storage');
        $path = $this->request->file('image_url')->store(
            'img/news', 's3'
        );   
        return $path;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(News $news)
    {
        $newses = News::all();
        return view('news.index',compact('newses'));
    }

    public function view($id)
    {
        $news = News::where('id', '=', $id)->first();
        return view('news.view',compact('news'));
    }

    public function delete($id)
    {
        News::where(['id' => $id])->delete();
        return redirect()->action(
            'NewsController@show'
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $news)
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
    public function update(Request $request, Event $news)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $news)
    {
        //
    }
}
