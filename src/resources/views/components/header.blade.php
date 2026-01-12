<header class="header">
    <div class="header__logo">
        @if (request()->is('admin*'))
            <a href="{{ route('admin.attendance.list') }}"><img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
        @else
            <a href="{{ route('attendance.index') }}"><img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
        @endif
    </div>

    @php
        $hideNav =
            request()->routeIs('login', 'register', 'login.store')
            || request()->routeIs('admin.login.form', 'admin.login.store')
            || request()->is('login', 'register', 'admin', 'admin/login');
    @endphp

    @unless($hideNav)
        @if (request()->is('admin*') && \Illuminate\Support\Facades\Auth::guard('admin')->check())
            <nav class="header__nav">
                <ul>
                    <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                    @if (\Illuminate\Support\Facades\Route::has('admin.staff.list'))
                        <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                    @endif
                    <li><a href="{{ route('admin.stamp_correction_request.list') }}">申請一覧</a></li>
                    <li>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button class="header__logout">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        @elseif (!request()->is('admin*') && \Illuminate\Support\Facades\Auth::guard('web')->check())
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
        @endif
    @endunless
</header>