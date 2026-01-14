@extends('layouts.default')

@section('body_class', 'verify-page')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/verify.css') }}">
@endsection

@section('content')
    @include('components.header')

    <div class="verify__wrap">
        <p class="verify__lead">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="{{ route('verification.notice') }}" class="verify__primary">
            認証はこちらから
        </a>

        @if (session('status') === 'verification-link-sent')
            <p class="verify__flash">
                認証メールを再送しました。メールをご確認ください。
            </p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="verify__resend">
            @csrf
            <button type="submit" class="verify__resend-link">
                認証メールを再送する
            </button>
        </form>
    </div>
@endsection