<div class="sidebar-wrapper">
    <nav class="mt-2">
        <!--begin::Sidebar Menu-->
        <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="navigation"
            aria-label="Main navigation"
            data-accordion="false"
            id="navigation"
        >
{{--            <li class="nav-header">EXAMPLES</li>--}}
{{--            <li class="nav-item menu-open">--}}
{{--                <a href="#" class="nav-link active">--}}
{{--                    <i class="nav-icon bi bi-speedometer"></i>--}}
{{--                    <p>--}}
{{--                        Dashboard--}}
{{--                        <i class="nav-arrow bi bi-chevron-right"></i>--}}
{{--                    </p>--}}
{{--                </a>--}}
{{--                <ul class="nav nav-treeview">--}}
{{--                    <li class="nav-item">--}}
{{--                        <a href="./index.html" class="nav-link active">--}}
{{--                            <i class="nav-icon bi bi-circle"></i>--}}
{{--                            <p>Dashboard v1</p>--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a href="./index2.html" class="nav-link">--}}
{{--                            <i class="nav-icon bi bi-circle"></i>--}}
{{--                            <p>Dashboard v2</p>--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a href="./generate/theme.html" class="nav-link">--}}
{{--                    <i class="nav-icon bi bi-palette"></i>--}}
{{--                    <p>Theme Generate</p>--}}
{{--                </a>--}}
{{--            </li>--}}

            <li class="nav-item">
                <a href="{{@route('admin.articles.index')}}" class="nav-link">
                    <i class="nav-icon bi bi-palette"></i>
                    <p>Articles</p>
                </a>
            </li>


        </ul>
        <!--end::Sidebar Menu-->
    </nav>
</div>
