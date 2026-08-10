
@extends('layouts.app')
@section('content')
<div class = "row">
@foreach($movies as $movie)
    <div class = "col-md-4">
        <div class="card">
            <img src="{{$movie->image}}" class="card-img-top" alt="{{$movie->title}}">
            <div class="card-body">
                <h5 class="card-title">{{$movie->title}}</h5>
                <p class="card-text">{{$movie->description}}</p>
                <a href="{{route('movies.show', $movie->id)}}" class="btn btn-primary">Visualizza</a>
            </div>
        </div>
</div>
@endforeach
</div>
</div>
@endsection
