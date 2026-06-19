@extends('layouts.app')

{{-- Empty mount point flagged for the SPA; Vue Router renders the active page. --}}
@section('app-attrs', 'data-spa')

@section('content')
@endsection
