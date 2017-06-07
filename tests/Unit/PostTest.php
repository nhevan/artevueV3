<?php

namespace Tests\Unit;

use Tests\TestCase;
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
}
