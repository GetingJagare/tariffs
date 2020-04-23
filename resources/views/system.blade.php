@extends('layouts.app')

@section('title')
    Управление
@endsection

@section('header')
    @include('chunks.header', ['user' => $user])
@endsection

@section('content')

    <router-view></router-view>

@endsection