<?php

namespace Tests\Unit;

use App\Post;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PostTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * visibility of a post can be swapped
     */
    public function visibility_of_a_post_can_be_swapped()
    {
    	$post = factory('App\Post')->create();

    	$this->assertDatabaseHas('posts', ['id' => $post->id, 'is_undiscoverable' => false]);

    	$post->swapDiscoverability();

    	$this->assertDatabaseHas('posts', ['id' => $post->id, 'is_undiscoverable' => true]);

    	$post->swapDiscoverability();

    	$this->assertDatabaseHas('posts', ['id' => $post->id, 'is_undiscoverable' => false]);	
    }

    /**
     * @test
     * sale status of a post can be swaped
     */
    public function sale_status_of_a_post_can_be_swaped()
    {
        $post = factory('App\Post')->create();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_selected_for_sale' => false]);

        $post->swapSaleStatus();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_selected_for_sale' => true]);

        $post->swapSaleStatus();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_selected_for_sale' => false]);
    }

    /**
     * @test
     * curator selection status of a post can be swaped
     */
    public function curator_selection_status_of_a_post_can_be_swaped()
    {
        $post = factory('App\Post')->create();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_selected_by_artevue' => false]);

        $post->swapCuratorSelectionStatus();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_selected_by_artevue' => true]);

        $post->swapCuratorSelectionStatus();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_selected_by_artevue' => false]);
    }

    /**
     * @test
     * a post has a art type relationship
     */
    public function a_post_has_a_art_type_relationship()
    {
        //arrange
        $this->seed('PostArtTypesTableSeeder');
        $post = factory('App\Post')->create([ 'post_art_type_id' => 1]);
        
        //assert
        $this->assertInstanceOf(BelongsTo::class, $post->type());
        $this->assertInstanceOf('App\PostArtType', $post->type);
        $this->assertEquals($post->type->title, 'Painting');
    }

    /**
     * @test
     * by default a post is set to others art type
     */
    public function by_default_a_post_is_set_to_others_art_type()
    {
        //act
        $post = Post::find(factory('App\Post')->create()->id);

        //assert
        $this->assertEquals($post->type->title, 'Others');
    }
}
