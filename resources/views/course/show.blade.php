@extends('layouts.app')
@section('content')
<h1>{{ $course->title }}</h1>
<p>{{ $course->description }}</p>
<h2>Modules:</h2>
@foreach($course->modules as $module)
    <div>
        <h3>{{ $module->title }}</h3>
        <ul>
            @foreach($module->contents as $content)
                <li>{{ $content->type }}: {{ $content->data }}</li>
            @endforeach
        </ul>
    </div>
@endforeach
@endsection