<div class="col-md-8 user-metainfo-holder">
	<table class="general-info">
		<tr>
			<td class="text-center" colspan="3">
				<h4 style="border-bottom: 1px solid #e6e6e6;padding-bottom: 5px;">
				General info
				<h4>
			</td>
		</tr>
		<tr>
			<td><strong>Description</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->description }}</td>
		</tr>
{{-- 		<tr>
			<td><strong>Hashtags Used</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->hashtags }}</td>
		</tr> --}}
		<tr>
			<td><strong>Address Title</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->address_title }}</td>
		</tr>
		<tr>
			<td><strong>Address Detail</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->address }}</td>
		</tr>
		<tr>
			<td><strong>Price</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->price }}</td>
		</tr>
		<tr>
			<td><strong>Pin Count</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->pin_count }}</td>
		</tr>
		<tr>
			<td><strong>Like Count</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->like_count }}</td>
		</tr>
		<tr>
			<td><strong>Comment Count</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->comment_count }}</td>
		</tr>
		<tr>
			<td><strong>Comment Count</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->comment_count }}</td>
		</tr>
		<tr>
			<td><strong>Is Public?</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>
				@if($post->is_public == 0)  No
				@else Yes
				@endif
			</td>
		</tr>
		<tr>
			<td><strong>Is Gallery Item?</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>
				@if($post->is_gallery_item == 0)  No
				@else Yes
				@endif
			</td>
		</tr>
		<tr>
			<td><strong>Is Locked?</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>
				@if($post->is_locked == 0)  No
				@else Yes
				@endif
			</td>
		</tr>
		<tr>
			<td><strong>Created</strong></td>
			<td style="padding: 0 5px;"> :  </td>
			<td>{{ $post->created_at->diffForHumans() }}</td>
		</tr>
	</table>
</div>