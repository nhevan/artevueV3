@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="container-fluid">
                <div class="row">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">Quick View</div>
                        <div class="panel-body">
                            <div class="col-md-12">
                                @include('dashboard.activities-line-graph', [
                                    'dataset' => $analytics['timed']
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">Global System information</div>
                        <div class="panel-body">
                            
                            <div class="col-md-6">
                                @include('dashboard.system-summary')
                            </div>

                            <div class="col-md-6">
                                @include('dashboard.user-activities')
                            </div>

                            <div class="col-md-5">
                                @include('dashboard.posts')
                            </div>

                            <div class="col-md-7">
                                @include('dashboard.user-types-chart', [ 'dataset' => $analytics['user_types'] ] )
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
