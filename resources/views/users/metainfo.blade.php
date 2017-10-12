<div class="col-md-8 user-metainfo-holder">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-sm-12">
				<table class="general-info">
					<tr>
						<td class="text-center" colspan="3">
							<h4 style="border-bottom: 1px solid #e6e6e6;padding-bottom: 5px;">
							General info
							<h4>
						</td>
					</tr>
					<tr>
						<td><strong>DOB</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td>{{ $user->dob }}</td>
					</tr>
					<tr>
						<td><strong>Phone</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td>{{ $user->phone }}</td>
					</tr>
					<tr>
						<td><strong>Website</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td><a href="{{$user->website}}">{{ $user->website }}</a></td>
					</tr>

					<tr>
						<td><strong>Gallery Name</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td>{{ $user->metadata->gallery_name or "not available" }}</td>
					</tr>
					<tr>
						<td><strong>Museum Name</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td>{{ $user->metadata->museum_name  or "not available"}}</td>
					</tr>
					<tr>
						<td><strong>Foundation Name</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td>{{ $user->metadata->foundation_name  or "not available"}}</td>
					</tr>
					<tr>
						<td><strong>Foundation Name</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td>{{ $user->metadata->foundation_name  or "not available"}}</td>
					</tr>
				</table>
			</div>
			<div class="col-md-4 col-sm-12">
				<table class="activity-info">
					<tr>
						<td class="text-center" colspan="3">
							<h4 style="border-bottom: 1px solid #e6e6e6;padding-bottom: 5px;">
							Activity info
							<h4>
						</td>
					</tr>
					
					@if (!$user->metadata)
						<tr>
							<td colspan="2">
								No activity info found
							</td>
						</tr>
					@else
						<tr>
							<td><strong>{{ $user->metadata->post_count  or "not available"}}</strong></td>
							<td>
								@if (isset($user->metadata))
									{{ str_plural('post', $user->metadata->post_count) }}
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $user->metadata->like_count  or "not available"}}</strong></td>
							<td>
								@if (isset($user->metadata))
									{{ str_plural('like', $user->metadata->like_count) }} made
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $likes_received  or "not available"}}</strong></td>
							<td>
								@if (isset($likes_received))
									{{ str_plural('like', $likes_received) }} received
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $user->metadata->comment_count  or "not available"}}</strong></td>
							<td>
								@if (isset($user->metadata))
									{{ str_plural('comment', $user->metadata->comment_count) }} made
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $comments_received  or "not available"}}</strong></td>
							<td>
								@if (isset($comments_received))
									{{ str_plural('comment', $comments_received) }} received
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $user->metadata->pin_count  or "not available"}}</strong></td>
							<td>
								@if (isset($user->metadata))
									{{ str_plural('pin', $user->metadata->pin_count) }}
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $user->metadata->message_count  or "not available"}}</strong></td>
							<td>
								@if (isset($user->metadata))
									{{ str_plural('message', $user->metadata->message_count) }} sent
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $user->metadata->follower_count  or "not available"}}</strong></td>
							<td>
								@if (isset($user->metadata))
									{{ str_plural('follower', $user->metadata->follower_count) }}
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $user->metadata->following_count  or "not available"}}</strong></td>
							<td>
								@if (isset($user->metadata))
									{{ str_plural('following', $user->metadata->following_count) }}
								@endif
							</td>
						</tr>
						<tr>
							<td><strong>{{ $user->metadata->tagged_count  or "not available"}}</strong></td>
							<td>
								@if (isset($user->metadata))
									tagged {{ str_plural('post', $user->metadata->tagged_count) }}
								@endif
							</td>
						</tr>
					@endif
				</table>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<table>
					<tr>
						<td style="vertical-align: top;"><strong>Biography</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td style="text-align: justify;">{{ $user->biography }}</td>
					</tr>
					<tr>
						<td style="vertical-align: top;"><strong>Gallery Description</strong></td>
						<td style="padding: 0 5px;"> :  </td>
						<td style="text-align: justify;">{{ $user->metadata->gallery_description  or "not available"}}</td>
					</tr>					
				</table>
			</div>
		</div>
	</div>
</div>