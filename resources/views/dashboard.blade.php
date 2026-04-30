@extends('statamic::layout')

@section('title', 'SimpleStats')

@section('content')
    <simplestats-dashboard
        :endpoint="{{ Js::from(cp_route('simplestats.all')) }}"
        :endpoint-grouped-template="{{ Js::from(cp_route('simplestats.grouped', ['type' => '__TYPE__'])) }}"
        :initial-filters="{{ Js::from(request()->query()) }}"
    ></simplestats-dashboard>
@endsection
