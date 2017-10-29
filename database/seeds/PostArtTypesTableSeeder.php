<?php

use App\PostArtType;
use Illuminate\Database\Seeder;

class PostArtTypesTableSeeder extends Seeder
{
	protected $settings;

	public function __construct(PostArtType $post_art_types)
	{
		$this->post_art_types = $post_art_types;
	}

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->newPostArtType(1, 'Painting');
        $this->newPostArtType(2, 'Drawing');
        $this->newPostArtType(3, 'Illustration');
        $this->newPostArtType(4, 'Sculpture');
        $this->newPostArtType(5, 'Photography');
        $this->newPostArtType(6, 'Digital Art');
        $this->newPostArtType(7, 'Mixed media');
        $this->newPostArtType(8, 'Print');
        $this->newPostArtType(9, 'Installation');
        $this->newPostArtType(10, 'Others');
    }

    public function newPostArtType($id, $title)
    {
    	$post_art_type = $this->post_art_types->where('title', $title)->first();
    	if (!$post_art_type) {
	        return factory('App\PostArtType')->create([
                    'id' => $id,
	        		'title' => $title
	        	]);
    	}
    }
}
