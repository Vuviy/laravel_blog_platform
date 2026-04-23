<header class="bg-dark text-white">
    <nav class="navbar navbar-expand-lg navbar-dark container">
        <a class="navbar-brand" href="{{route('home', ['locale' => app()->currentLocale()])}}">MyApp</a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{route('home', ['locale' => app()->currentLocale()])}}">Головна</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('articles', ['locale' => app()->currentLocale()])}}">{{__('common.articles')}}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{route('news', ['locale' => app()->currentLocale()])}}">{{__('common.news')}}</a>
                </li>
            </ul>

            @foreach(config('app.available_locales') as $locale)
                <a href="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters(), ['locale' => $locale])) }}"
                   class="{{ app()->getLocale() === $locale ? 'fw-bold' : '' }}">
                    {{ strtoupper($locale) }}
                </a>
            @endforeach

            <div>
                @if($user)
                    <form action="{{route('logout')}}" method="POST">
                        @csrf
                        <button type="submit">logout</button>
                    </form>
                    <p>{{$user->username->getValue()}}</p>
                @else
                    <a href="{{route('loginForm', ['locale' => app()->currentLocale()])}}">login</a>
                <sp>/</sp>
                    <a href="{{route('registerForm', ['locale' => app()->currentLocale()])}}">register</a>
                @endif
            </div>
        </div>
    </nav>
</header>
