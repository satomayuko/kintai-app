<header class="header">
    <div class="header__logo">
        <a href="/"><img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
    </div>

    @auth
    <nav class="header__nav">
        <ul>
            @if (!empty($finished) && $finished)
                <li><a href="{{ route('stamp_correction_request.list', ['today' => 1]) }}">今日の申請一覧</a></li>
                <li><a href="{{ route('stamp_correction_request.list') }}">申請一覧</a></li>
            @else
                <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                <li><a href="{{ route('stamp_correction_request.list') }}">申請</a></li>
            @endif
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="header__logout">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
    @endauth
</header>
