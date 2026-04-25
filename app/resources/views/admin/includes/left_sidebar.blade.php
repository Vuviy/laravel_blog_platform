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

            <li class="nav-item">
                <a href="{{route('admin.articles.index')}}" class="nav-link">
                    <i class="nav-icon bi bi-palette"></i>
                    <p>{{__('common.articles')}}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{route('admin.news.index')}}" class="nav-link">
                    <i class="nav-icon bi bi-palette"></i>
                    <p>{{__('common.news')}}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{route('admin.tags.index')}}" class="nav-link">
                    <i class="nav-icon bi bi-palette"></i>
                    <p>{{__('common.tags')}}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{route('admin.comments.index')}}" class="nav-link">
                    <i class="nav-icon bi bi-palette"></i>
                    <p>{{__('common.comments')}}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{route('admin.users.index')}}" class="nav-link">
                    <i class="nav-icon bi bi-palette"></i>
                    <p>{{__('common.users')}}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{route('admin.roles.index')}}" class="nav-link">
                    <i class="nav-icon bi bi-palette"></i>
                    <p>{{__('common.roles')}}</p>
                </a>
            </li>


            <li class="nav-item menu-open">
                <a href="#" class="nav-link active">
                    <i class="nav-icon bi bi-speedometer"></i>
                    <p>
                        {{__('common.seo')}}
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{route('admin.seo.index')}}" class="nav-link">
                            <i class="nav-icon bi bi-palette"></i>
                            <p>{{__('common.seo_pages')}}</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{route('admin.robotForm')}}" class="nav-link">
                            <i class="nav-icon bi bi-palette"></i>
                            <p>{{__('common.robotForm')}}</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{route('admin.sitemapForm')}}" class="nav-link">
                            <i class="nav-icon bi bi-palette"></i>
                            <p>{{__('common.sitemapForm')}}</p>
                        </a>
                    </li>

                </ul>
            </li>


        </ul>
        <!--end::Sidebar Menu-->
    </nav>
</div>
