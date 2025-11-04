<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box main_logo">
                <a href="{{url('/')}}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('/build/images/logo-light-small.png') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ URL::asset('/build/images/logo-dark.png') }}" alt="" height="50">
                    </span>
                </a>

                <a href="{{url('/')}}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ URL::asset('/build/images/logo-light-small.png') }}" alt="" height="50">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ URL::asset('/build/images/logo-light.png') }}" alt="" height="50">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item waves-effect waves-light"
                data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- App Search-->
            <div class="app-search d-none d-lg-flex" style="
    align-items: center;">
                <h3 class="mb-0 logo_txt">Rewards Management System</h3>
            </div>

            {{-- <div class="dropdown dropdown-mega d-none d-lg-block ml-2">
                <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown"
                    aria-haspopup="false" aria-expanded="false">
                    <span key="t-megamenu">@lang('translation.Mega_Menu')</span>
                    <i class="mdi mdi-chevron-down"></i>
                </button>
                <div class="dropdown-menu dropdown-megamenu">
                    <div class="row">
                        <div class="col-sm-8">

                            <div class="row">
                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-ui-components">
                                        @lang('translation.UI_Components')</h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-lightbox">@lang('translation.Lightbox')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-range-slider">@lang('translation.Range_Slider')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-sweet-alert">@lang('translation.Sweet_Alert')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-rating">@lang('translation.Rating')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-forms">@lang('translation.Forms')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tables">@lang('translation.Tables')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-charts">@lang('translation.Charts')</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-applications">@lang('translation.Applications')
                                    </h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-ecommerce">@lang('translation.Ecommerce')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-calendar">@lang('translation.Calendars')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-email">@lang('translation.Email')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-projects">@lang('translation.Projects')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tasks">@lang('translation.Tasks')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-contacts">@lang('translation.Contacts')</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-extra-pages">@lang('translation.Extra_Pages')
                                    </h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-light-sidebar">@lang('translation.Light_Sidebar')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-compact-sidebar">@lang('translation.Compact_Sidebar')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-horizontal">@lang('translation.Horizontal_layout')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-maintenance">@lang('translation.Maintenance')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-coming-soon">@lang('translation.Coming_Soon')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-timeline">@lang('translation.Timeline')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-faqs">@lang('translation.FAQs')</a>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5 class="font-size-14 mt-0" key="t-ui-components">
                                        @lang('translation.UI_Components')</h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-lightbox">@lang('translation.Lightbox')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-range-slider">@lang('translation.Range_Slider')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);"
                                                key="t-sweet-alert">@lang('translation.Sweet_Alert')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-rating">@lang('translation.Rating')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-forms">@lang('translation.Forms')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tables">@lang('translation.Tables')</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-charts">@lang('translation.Charts')</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-sm-5">
                                    <div>
                                        <img src="{{ URL::asset ('/build/images/megamenu-img.png') }}" alt=""
                                            class="img-fluid mx-auto d-block">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div> --}}
        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ml-2">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-magnify"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="@lang('translation.Search')"
                                    aria-label="Search input">

                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>s
                            </div>
                        </div>
                    </form>
                </div>
            </div>







            <div class="dropdown d-inline-block">
                {{-- <button type="button" class="btn header-item noti-icon waves-effect"
                    id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i class="bx bx-bell bx-tada"></i>
                    <span class="badge bg-danger rounded-pill">3</span>
                </button> --}}
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0" key="t-notifications"> @lang('translation.Notifications') </h6>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="small" key="t-view-all"> @lang('translation.View_All')</a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">
                        <a href="" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                                        <i class="bx bx-cart"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1" key="t-your-order">@lang('translation.Your_order_is_placed')
                                    </h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-grammer">
                                            @lang('translation.If_several_languages_coalesce_the_grammar')
                                        </p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span
                                                key="t-min-ago">@lang('translation.3_min_ago')</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="" class="text-reset notification-item">
                            <div class="d-flex">
                                <img src="{{ URL::asset('/build/images/users/avatar-3.jpg') }}"
                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1">@lang('translation.James_Lemire')</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-simplified">
                                            @lang('translation.It_will_seem_like_simplified_English')
                                        </p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span
                                                key="t-hours-ago">@lang('translation.1_hours_ago')</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-success rounded-circle font-size-16">
                                        <i class="bx bx-badge-check"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1" key="t-shipped">@lang('translation.Your_item_is_shipped')</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-grammer">
                                            @lang('translation.If_several_languages_coalesce_the_grammar')
                                        </p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span
                                                key="t-min-ago">@lang('translation.3_min_ago')</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="" class="text-reset notification-item">
                            <div class="d-flex">
                                <img src="{{ URL::asset('/build/images/users/avatar-4.jpg') }}"
                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                <div class="flex-grow-1">
                                    <h6 class="mt-0 mb-1">@lang('translation.Salena_Layfield')</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-occidental">
                                            @lang('translation.As_a_skeptical_Cambridge_friend_of_mine_occidental')
                                        </p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span
                                                key="t-hours-ago">@lang('translation.1_hours_ago')</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="p-2 border-top d-grid">
                        <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                            <i class="mdi mdi-arrow-right-circle me-1"></i> <span
                                key="t-view-more">@lang('translation.View_More')</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user"
                        src="{{ isset(Auth::user()->avatar) ? asset(Auth::user()->avatar) : asset('/build/images/users/avatar-1.jpg') }}"
                        alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1" key="t-henry">{{ucfirst(Auth::user()->name)}}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item d-block" href="#" data-bs-toggle="modal"
                        data-bs-target=".change-password"><i class="bx bx-wrench font-size-16 align-middle me-1"></i>
                        <span key="t-settings">Change Password</span></a>
                    <a class="dropdown-item text-danger" href="javascript:void();"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                            class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span
                            key="t-logout">@lang('translation.Logout')</span></a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>

            {{-- <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                    <i class="bx bx-cog bx-spin"></i>
                </button>
            </div> --}}

        </div>
    </div>
</header>

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">
                    <a class="nav-link  " href="{{url('/')}}">
                        <i class="fa-solid fa-gauge-simple me-2"></i><span key="t-dashboards">Dashboard</span>
                    </a>


                    @canany(['user-list', 'role-list', 'app-user-list', 'campaign-voucher-group-list'])

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">CMS User Management</span>
                            <!-- <div class="arrow-down"></div> -->
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            @can('role-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('/admin/roles')}}">Roles</a>
                            @endcan
                            @can('user-list')
                                <a class="dropdown-item" key="t-buttons" href="{{url('/admin/user')}}">CMS Users</a>
                            @endcan
                            @can(['app-user-list'])
                                <a class="dropdown-item" key="t-dashboards" href="{{url('/admin/app-user')}}">App Users</a>
                            @endcan
                            @can(['campaign-voucher-group-list'])
                                <a class="dropdown-item" key="t-dashboards"
                                    href="{{url('/admin/campaign-voucher-group')}}">Campaign Voucher Group</a>
                            @endcan

                        </div>
                    </li>

                    @endcan
                    @canany(['content-management', 'app-management', 'slider-list', 'about-app-banner-list', 'learn-more-page', 'faq-list', 'app-content-management'])

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">Content / Ad
                                Management</span>
                            <!-- <div class="arrow-down"></div> -->
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            @can('content-management')
                                <a class="dropdown-item" key="t-alerts"
                                    href="{{url('admin/content-management')}}">T&C/PDPA/Legal Privacy Policy</a>
                            @endcan
                            @can('app-management')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/website-management')}}">Website
                                    Management</a>
                            @endcan
                            @can('app-content-management')
                                <a class="dropdown-item" key="t-alerts"
                                    href="{{url('admin/app-content-management')}}">Content Management</a>
                            @endcan
                            @can('slider-list')
                                <a class="dropdown-item" key="t-buttons" href="{{url('/admin/slider')}}">Advertisement
                                    Slider</a>
                            @endcan
                            @can('slider-list')
                                <a class="dropdown-item" key="t-buttons" href="{{url('/admin/dashboardpopup')}}">Dashboard
                                    Popup</a>
                            @endcan
                            {{-- @can('about-app-banner-list')
                            <a class="dropdown-item" key="t-buttons" href="{{url('/admin/about-app-banner')}}">About App
                                Banner</a>
                            @endcan --}}
                            @can('learn-more-page')
                                <a class="dropdown-item" key="t-buttons" href="{{url('/admin/learn-more-page')}}">Learn More
                                    Page</a>
                            @endcan
                            @can('faq-list')
                                <a class="dropdown-item" key="t-buttons" href="{{url('/admin/faq-category')}}">FAQ</a>
                            @endcan
                        </div>
                    </li>
                    @endcan

                    @canany(['reward-list', 'reward-redemption-pos', 'reward-redemption-cms'])

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">Rewards Management</span>
                            <!-- <div class="arrow-down"></div> -->
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            @can('reward-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/reward')}}">Rewards</a>
                            @endcan
                            @can('reward-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/automated-reward')}}">Automated
                                    Reward</a>
                            @endcan
                            @can('reward-list')
                                <a class="dropdown-item" key="t-alerts"
                                    href="{{url('admin/reward?type=campaign-voucher')}}">Rewards Campaign
                                    Voucher</a>
                            @endcan
                            @can('reward-redemption-cms')
                                <a class="dropdown-item" key="t-buttons" href="{{url('/admin/redemption-reward')}}">Rewards
                                    Management CMS</a>

                            @endcan
                            @can('reward-redemption-pos')
                                <a class="dropdown-item" key="t-buttons"
                                    href="{{url('/admin/reward-redemption-pos')}}">Rewards
                                    Redemption POS</a>
                            @endcan
                        </div>
                    </li>
                    @endcan
                 
                    @canany(['aircrew-company-list'])
                    <a class="nav-link  " href="{{url('/admin/partner-company')}}">
                        <i class="fa-solid fa-gauge-simple me-2"></i><span key="t-dashboards">Partner Management</span>
                    </a>
                    @endcan
                    @canany(['report-customer', 'report-sales', 'report-reward'])
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">Report</span>
                            <!-- <div class="arrow-down"></div> -->
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            @can('report-customer')
                                <a class="dropdown-item" key="t-alerts"
                                    href="{{url('/admin/report/customer')}}">Customer</a>
                            @endcan
                            @can('report-sales')
                                <a class="dropdown-item" key="t-alerts" href="{{url('/admin/report/sales')}}">Sales</a>
                            @endcan
                            @can('report-reward')
                                <a class="dropdown-item" key="t-alerts" href="{{url('/admin/report/reward')}}">Redemption
                                </a>
                            @endcan

                        </div>
                    </li>
                    @endcan
                    @canany(['referral-rate'])
                    <a class="nav-link  " href="{{url('/admin/tiers')}}">
                        <i class="fa-solid fa-gauge-simple me-2"></i><span key="t-dashboards">Tier</span>
                    </a>
                    @endcan

                    @canany(['referral-rate'])
                    <a class="nav-link  " href="{{url('/admin/referral-rate')}}">
                        <i class="fa-solid fa-gauge-simple me-2"></i><span key="t-dashboards">Referral Rate</span>
                    </a>
                    @endcan

                    @canany(['contact-us-list', 'notification-setting'])
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">Others</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">

                            {{--<div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-email"
                                    role="button">
                                    <span key="t-calendar">Report</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="{{url('/admin/report/customer')}}" class="dropdown-item"
                                        key="t-tui-calendar">Customer</a>
                                    <a href="{{url('/admin/report/sales')}}" class="dropdown-item"
                                        key="t-full-calender">Sales</a>
                                    <a href="{{url('/admin/report/reward')}}" class="dropdown-item"
                                        key="t-full-calender">Reward</a>
                                </div>
                            </div>--}}

                            {{--@can(['tier-management'])
                            <a class="dropdown-item  " href="{{url('/admin/tiers')}}">
                                Tier
                            </a>
                            @endcan
                            @can(['referral-rate'])
                            <a class="dropdown-item  " href="{{url('/admin/referral-rate')}}">
                                Referral Rate
                            </a>
                            @endcan
                            @can(['sales-list'])
                            <a class="dropdown-item  " href="{{url('/admin/sales')}}">
                                Sales
                            </a>
                            @endcan--}}
                            @can(['contact-us-list'])
                                <a class="dropdown-item  " href="{{url('/admin/contact-us')}}">
                                    Contact Us Request
                                </a>
                            @endcan
                            @can(['contact-us-list'])
                                <a class="dropdown-item  " href="{{url('/admin/notification-setting')}}">
                                    Notification Settings
                                </a>
                            @endcan
                            @can(['contact-us-list'])
                                <a class="dropdown-item  " href="{{url('/admin/broadcast')}}">
                                    Broadcast
                                </a>
                            @endcan
                            @can(['apilogs-list'])
                                <a class="dropdown-item  " href="{{url('/admin/apilogs')}}">
                                    POS API Log
                                </a>
                            @endcan
                            @can(['apilogs-list'])
                                <a class="dropdown-item  " href="{{url('/admin/apilogs-sorting')}}">
                                    POS API Log Not Triggered
                                </a>
                            @endcan
                            @can(['apilogs-list'])
                                <a class="dropdown-item  " href="{{url('/admin/voucherlogs')}}">
                                    Voucher Logs
                                </a>
                            @endcan
                            @can(['system-backup'])
                                <a class="dropdown-item  " href="{{url('/admin/qr-setting')}}">
                                    Qrcode Setting
                                </a>
                            @endcan
                            @can(['cms-setting'])
                                <a class="dropdown-item  " href="{{url('/admin/cms-setting')}}">
                                    CMS Setting
                                </a>
                            @endcan

                        </div>
                    </li>
                    @endcan

                    <a class="nav-link  " href="{{url('/admin/redeem-voucher')}}">
                        <i class="fa-solid fa-gauge-simple me-2"></i><span key="t-dashboards">Redeem Voucher</span>
                    </a>


                    {{-- <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps">@lang('translation.Apps')</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-calendar"
                                    role="button">
                                    <span key="t-email">@lang('translation.Calendars')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-calendar">
                                    <a href="calendar" class="dropdown-item"
                                        key="t-tui-calendar">@lang('translation.TUI_Calendar')</a>
                                    <a href="calendar-full" class="dropdown-item"
                                        key="t-full-calendar">@lang('translation.Full_Calendar')</a>
                                </div>
                            </div>
                            <a href="chat" class="dropdown-item" key="t-chat">@lang('translation.Chat')</a>
                            <a href="apps-filemanager" class="dropdown-item"
                                key="t-file-manager">@lang('translation.File_Manager')</a>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-email"
                                    role="button">
                                    <span key="t-email">@lang('translation.Email')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="email-inbox" class="dropdown-item"
                                        key="t-inbox">@lang('translation.Inbox')</a>
                                    <a href="email-read" class="dropdown-item"
                                        key="t-read-email">@lang('translation.Read_Email')</a>

                                    <div class="dropdown">
                                        <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-blog"
                                            role="button">
                                            <span key="t-email-templates">@lang('translation.Templates')</span>
                                            <div class="arrow-down"></div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="topnav-blog">
                                            <a href="email-template-basic" class="dropdown-item"
                                                key="t-basic-action">@lang('translation.Basic_Action')</a>
                                            <a href="email-template-alert" class="dropdown-item"
                                                key="t-alert-email">@lang('translation.Alert_Email')</a>
                                            <a href="email-template-billing" class="dropdown-item"
                                                key="t-bill-email">@lang('translation.Billing_Email')</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-ecommerce"
                                    role="button">
                                    <span key="t-ecommerce">@lang('translation.Ecommerce')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-ecommerce">
                                    <a href="ecommerce-products" class="dropdown-item"
                                        key="t-products">@lang('translation.Products')</a>
                                    <a href="ecommerce-product-detail" class="dropdown-item"
                                        key="t-product-detail">@lang('translation.Product_Detail')</a>
                                    <a href="ecommerce-orders" class="dropdown-item"
                                        key="t-orders">@lang('translation.Orders')</a>
                                    <a href="ecommerce-customers" class="dropdown-item"
                                        key="t-customers">@lang('translation.Customers')</a>
                                    <a href="ecommerce-cart" class="dropdown-item"
                                        key="t-cart">@lang('translation.Cart')</a>
                                    <a href="ecommerce-checkout" class="dropdown-item"
                                        key="t-checkout">@lang('translation.Checkout')</a>
                                    <a href="ecommerce-shops" class="dropdown-item"
                                        key="t-shops">@lang('translation.Shops')</a>
                                    <a href="ecommerce-add-product" class="dropdown-item"
                                        key="t-add-product">@lang('translation.Add_Product')</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-crypto"
                                    role="button">
                                    <span key="t-crypto">@lang('translation.Crypto')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-crypto">
                                    <a href="crypto-wallet" class="dropdown-item"
                                        key="t-wallet">@lang('translation.Wallet')</a>
                                    <a href="crypto-buy-sell" class="dropdown-item"
                                        key="t-buy">@lang('translation.Buy_Sell')</a>
                                    <a href="crypto-exchange" class="dropdown-item"
                                        key="t-exchange">@lang('translation.Exchange')</a>
                                    <a href="crypto-lending" class="dropdown-item"
                                        key="t-lending">@lang('translation.Lending')</a>
                                    <a href="crypto-orders" class="dropdown-item"
                                        key="t-orders">@lang('translation.Orders')</a>
                                    <a href="crypto-kyc-application" class="dropdown-item"
                                        key="t-kyc">@lang('translation.KYC_Application')</a>
                                    <a href="crypto-ico-landing" class="dropdown-item"
                                        key="t-ico">@lang('translation.ICO_Landing')</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-project"
                                    role="button">
                                    <span key="t-projects">@lang('translation.Projects')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-project">
                                    <a href="projects-grid" class="dropdown-item"
                                        key="t-p-grid">@lang('translation.Projects_Grid')</a>
                                    <a href="projects-list" class="dropdown-item"
                                        key="t-p-list">@lang('translation.Projects_List')</a>
                                    <a href="projects-overview" class="dropdown-item"
                                        key="t-p-overview">@lang('translation.Project_Overview')</a>
                                    <a href="projects-create" class="dropdown-item"
                                        key="t-create-new">@lang('translation.Create_New')</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-task"
                                    role="button">
                                    <span key="t-tasks">@lang('translation.Tasks')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-task">
                                    <a href="tasks-list" class="dropdown-item"
                                        key="t-task-list">@lang('translation.Task_List')</a>
                                    <a href="tasks-kanban" class="dropdown-item"
                                        key="t-kanban-board">@lang('translation.Kanban_Board')</a>
                                    <a href="tasks-create" class="dropdown-item"
                                        key="t-create-task">@lang('translation.Create_Task')</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-contact"
                                    role="button">
                                    <span key="t-contacts">@lang('translation.Contacts')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                    <a href="contacts-grid" class="dropdown-item"
                                        key="t-user-grid">@lang('translation.User_Grid')</a>
                                    <a href="contacts-list" class="dropdown-item"
                                        key="t-user-list">@lang('translation.User_List')</a>
                                    <a href="contacts-profile" class="dropdown-item"
                                        key="t-profile">@lang('translation.Profile')</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-blog"
                                    role="button">
                                    <span key="t-blog">@lang('translation.Blog')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-blog">
                                    <a href="blog-list" class="dropdown-item"
                                        key="t-blog-list">@lang('translation.Blog_List')</a>
                                    <a href="blog-grid" class="dropdown-item"
                                        key="t-blog-grid">@lang('translation.Blog_Grid')</a>
                                    <a href="blog-details" class="dropdown-item"
                                        key="t-blog-details">@lang('translation.Blog_Details')</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-jobs"
                                    role="button">
                                    <span key="t-jobs">@lang('translation.Jobs')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-jobs">
                                    <a href="job-list" class="dropdown-item"
                                        key="t-job-list">@lang('translation.Job_List')</a>
                                    <a href="job-grid" class="dropdown-item"
                                        key="t-job-grid">@lang('translation.Job_Grid')</a>
                                    <a href="job-apply" class="dropdown-item"
                                        key="t-apply-job">@lang('translation.Apply_Job')</a>
                                    <a href="job-details" class="dropdown-item"
                                        key="t-job-details">@lang('translation.Job_Details')</a>
                                    <a href="job-categories" class="dropdown-item"
                                        key="t-Jobs-categories">@lang('translation.Jobs_Categories')</a>
                                    <div class="dropdown">
                                        <a class="dropdown-item dropdown-toggle arrow-none" href="#"
                                            id="topnav-candidate" role="button">
                                            <span key="t-candidate">@lang('translation.Candidate')</span>
                                            <div class="arrow-down"></div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="topnav-candidate">
                                            <a href="candidate-list" class="dropdown-item"
                                                key="t-list">@lang('translation.List')</a>
                                            <a href="candidate-overview" class="dropdown-item"
                                                key="t-overview">@lang('translation.Overview')</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-components" role="button">
                            <i class="bx bx-collection me-2"></i><span
                                key="t-components">@lang('translation.Components')</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-components">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-form"
                                    role="button">
                                    <span key="t-forms">@lang('translation.Forms')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-form">
                                    <a href="form-elements" class="dropdown-item"
                                        key="t-form-elements">@lang('translation.Form_Elements')</a>
                                    <a href="form-layouts" class="dropdown-item"
                                        key="t-form-layouts">@lang('translation.Form_Layouts')</a>
                                    <a href="form-validation" class="dropdown-item"
                                        key="t-form-validation">@lang('translation.Form_Validation')</a>
                                    <a href="form-advanced" class="dropdown-item"
                                        key="t-form-advanced">@lang('translation.Form_Advanced')</a>
                                    <a href="form-editors" class="dropdown-item"
                                        key="t-form-editors">@lang('translation.Form_Editors')</a>
                                    <a href="form-uploads" class="dropdown-item"
                                        key="t-form-upload">@lang('translation.Form_File_Upload')</a>
                                    <a href="form-xeditable" class="dropdown-item"
                                        key="t-form-xeditable">@lang('translation.Form_Xeditable')</a>
                                    <a href="form-repeater" class="dropdown-item"
                                        key="t-form-repeater">@lang('translation.Form_Repeater')</a>
                                    <a href="form-wizard" class="dropdown-item"
                                        key="t-form-wizard">@lang('translation.Form_Wizard')</a>
                                    <a href="form-mask" class="dropdown-item"
                                        key="t-form-mask">@lang('translation.Form_Mask')</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-table"
                                    role="button">
                                    <span key="t-tables">@lang('translation.Tables')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-table">
                                    <a href="tables-basic" class="dropdown-item"
                                        key="t-basic-tables">@lang('translation.Basic_Tables')</a>
                                    <a href="tables-datatable" class="dropdown-item"
                                        key="t-data-tables">@lang('translation.Data_Tables')</a>
                                    <a href="tables-responsive" class="dropdown-item"
                                        key="t-responsive-table">@lang('translation.Responsive_Table')</a>
                                    <a href="tables-editable" class="dropdown-item"
                                        key="t-editable-table">@lang('translation.Editable_Table')</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-charts"
                                    role="button">
                                    <span key="t-charts">@lang('translation.Charts')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-charts">
                                    <a href="charts-apex" class="dropdown-item"
                                        key="t-apex-charts">@lang('translation.Apex_Charts')</a>
                                    <a href="charts-echart" class="dropdown-item"
                                        key="t-e-charts">@lang('translation.E_Charts')</a>
                                    <a href="charts-chartjs" class="dropdown-item"
                                        key="t-chartjs-charts">@lang('translation.Chartjs_Charts')</a>
                                    <a href="charts-flot" class="dropdown-item"
                                        key="t-flot-charts">@lang('translation.Flot_Charts')</a>
                                    <a href="charts-tui" class="dropdown-item"
                                        key="t-ui-charts">@lang('translation.Toast_UI_Charts')</a>
                                    <a href="charts-knob" class="dropdown-item"
                                        key="t-knob-charts">@lang('translation.Jquery_Knob_Charts')</a>
                                    <a href="charts-sparkline" class="dropdown-item"
                                        key="t-sparkline-charts">@lang('translation.Sparkline_Charts')</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-icons"
                                    role="button">
                                    <span key="t-icons">@lang('translation.Icons')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-icons">
                                    <a href="icons-boxicons" class="dropdown-item"
                                        key="t-boxicons">@lang('translation.Boxicons')</a>
                                    <a href="icons-materialdesign" class="dropdown-item"
                                        key="t-material-design">@lang('translation.Material_Design')</a>
                                    <a href="icons-dripicons" class="dropdown-item"
                                        key="t-dripicons">@lang('translation.Dripicons')</a>
                                    <a href="icons-fontawesome" class="dropdown-item"
                                        key="t-font-awesome">@lang('translation.Font_awesome')</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-map"
                                    role="button">
                                    <span key="t-maps">@lang('translation.Maps')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-map">
                                    <a href="maps-google" class="dropdown-item"
                                        key="t-g-maps">@lang('translation.Google_Maps')</a>
                                    <a href="maps-vector" class="dropdown-item"
                                        key="t-v-maps">@lang('translation.Vector_Maps')</a>
                                    <a href="maps-leaflet" class="dropdown-item"
                                        key="t-l-maps">@lang('translation.Leaflet_Maps')</a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-more" role="button">
                            <i class="bx bx-file me-2"></i><span
                                key="t-extra-pages">@lang('translation.Extra_Pages')</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-more">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-invoice"
                                    role="button">
                                    <span key="t-invoices">@lang('translation.Invoices')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-invoice">
                                    <a href="invoices-list" class="dropdown-item"
                                        key="t-invoice-list">@lang('translation.Invoice_List')</a>
                                    <a href="invoices-detail" class="dropdown-item"
                                        key="t-invoice-detail">@lang('translation.Invoice_Detail')</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-auth"
                                    role="button">
                                    <span key="t-authentication">@lang('translation.Authentication')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-auth">
                                    <a href="auth-login" class="dropdown-item"
                                        key="t-login">@lang('translation.Login')</a>
                                    <a href="auth-login-2" class="dropdown-item"
                                        key="t-login-2">@lang('translation.Login') 2</a>
                                    <a href="auth-register" class="dropdown-item"
                                        key="t-register">@lang('translation.Register')</a>
                                    <a href="auth-register-2" class="dropdown-item"
                                        key="t-register-2">@lang('translation.Register') 2</a>
                                    <a href="auth-recoverpw" class="dropdown-item"
                                        key="t-recover-password">@lang('translation.Recover_Password')</a>
                                    <a href="auth-recoverpw-2" class="dropdown-item"
                                        key="t-recover-password-2">@lang('translation.Recover_Password') 2</a>
                                    <a href="auth-lock-screen" class="dropdown-item"
                                        key="t-lock-screen">@lang('translation.Lock_Screen')</a>
                                    <a href="auth-lock-screen-2" class="dropdown-item"
                                        key="t-lock-screen-2">@lang('translation.Lock_Screen') 2</a>
                                    <a href="auth-confirm-mail" class="dropdown-item"
                                        key="t-confirm-mail">@lang('translation.Confirm_Mail')</a>
                                    <a href="auth-confirm-mail-2" class="dropdown-item"
                                        key="t-confirm-mail-2">@lang('translation.Confirm_Mail') 2</a>
                                    <a href="auth-email-verification" class="dropdown-item"
                                        key="t-email-verification">@lang('translation.Email_verification')</a>
                                    <a href="auth-email-verification-2" class="dropdown-item"
                                        key="t-email-verification-2">@lang('translation.Email_verification') 2</a>
                                    <a href="auth-two-step-verification" class="dropdown-item"
                                        key="t-two-step-verification">@lang('translation.Two_step_verification')</a>
                                    <a href="auth-two-step-verification-2" class="dropdown-item"
                                        key="t-two-step-verification-2">@lang('translation.Two_step_verification') 2</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-utility"
                                    role="button">
                                    <span key="t-utility">@lang('translation.Utility')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-utility">
                                    <a href="pages-starter" class="dropdown-item"
                                        key="t-starter-page">@lang('translation.Starter_Page')</a>
                                    <a href="pages-maintenance" class="dropdown-item"
                                        key="t-maintenance">@lang('translation.Maintenance')</a>
                                    <a href="pages-comingsoon" class="dropdown-item"
                                        key="t-coming-soon">@lang('translation.Coming_Soon')</a>
                                    <a href="pages-timeline" class="dropdown-item"
                                        key="t-timeline">@lang('translation.Timeline')</a>
                                    <a href="pages-faqs" class="dropdown-item"
                                        key="t-faqs">@lang('translation.FAQs')</a>
                                    <a href="pages-pricing" class="dropdown-item"
                                        key="t-pricing">@lang('translation.Pricing')</a>
                                    <a href="pages-404" class="dropdown-item"
                                        key="t-error-404">@lang('translation.Error_404')</a>
                                    <a href="pages-500" class="dropdown-item"
                                        key="t-error-500">@lang('translation.Error_500')</a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-layout" role="button">
                            <i class="bx bx-layout me-2"></i><span key="t-layouts">@lang('translation.Layouts')</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-layout">
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-layout-verti"
                                    role="button">
                                    <span key="t-vertical">@lang('translation.Vertical')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-layout-verti">
                                    <a href="layouts-light-sidebar" class="dropdown-item"
                                        key="t-light-sidebar">@lang('translation.Light_Sidebar')</a>
                                    <a href="layouts-compact-sidebar" class="dropdown-item"
                                        key="t-compact-sidebar">@lang('translation.Compact_Sidebar')</a>
                                    <a href="layouts-icon-sidebar" class="dropdown-item"
                                        key="t-icon-sidebar">@lang('translation.Icon_Sidebar')</a>
                                    <a href="layouts-boxed" class="dropdown-item"
                                        key="t-boxed-width">@lang('translation.Boxed_Width')</a>
                                    <a href="layouts-preloader" class="dropdown-item"
                                        key="t-preloader">@lang('translation.Preloader')</a>
                                    <a href="layouts-colored-sidebar" class="dropdown-item"
                                        key="t-colored-sidebar">@lang('translation.Colored_Sidebar')</a>
                                    <a href="layouts-scrollable" class="dropdown-item"
                                        key="t-scrollable">@lang('translation.Scrollable')</a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-layout-hori"
                                    role="button">
                                    <span key="t-horizontal">@lang('translation.Horizontal')</span>
                                    <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-layout-hori">
                                    <a href="layouts-horizontal" class="dropdown-item"
                                        key="t-horizontal">@lang('translation.Horizontal')</a>
                                    <a href="layouts-hori-topbar-light" class="dropdown-item"
                                        key="t-topbar-light">@lang('translation.Topbar_Light')</a>
                                    <a href="layouts-hori-boxed-width" class="dropdown-item"
                                        key="t-boxed-width">@lang('translation.Boxed_Width')</a>
                                    <a href="layouts-hori-preloader" class="dropdown-item"
                                        key="t-preloader">@lang('translation.Preloader')</a>
                                    <a href="layouts-hori-colored-header" class="dropdown-item"
                                        key="t-colored-topbar">@lang('translation.Colored_Header')</a>
                                    <a href="layouts-hori-scrollable" class="dropdown-item"
                                        key="t-scrollable">@lang('translation.Scrollable')</a>
                                </div>
                            </div>
                        </div>
                    </li> --}}

                </ul>
            </div>
        </nav>
    </div>
</div>

<!--  Change-Password example -->
<div class="modal fade change-password" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="change-password">
                    @csrf
                    <input type="hidden" value="{{ Auth::user()->id }}" id="data_id">
                    <div class="mb-3">
                        <label for="current_password">Current Password</label>
                        <input id="current-password" type="password"
                            class="form-control @error('current_password') is-invalid @enderror" name="current_password"
                            autocomplete="current_password" placeholder="Enter Current Password"
                            value="{{ old('current_password') }}">
                        <div class="text-danger" id="current_passwordError" data-ajax-feedback="current_password"></div>
                    </div>

                    <div class="mb-3">
                        <label for="newpassword">New Password</label>
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            autocomplete="new_password" placeholder="Enter New Password">
                        <div class="text-danger" id="passwordError" data-ajax-feedback="password"></div>
                    </div>

                    <div class="mb-3">
                        <label for="userpassword">Confirm Password</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                            autocomplete="new_password" placeholder="Enter New Confirm password">
                        <div class="text-danger" id="password_confirmError" data-ajax-feedback="password-confirm"></div>
                    </div>

                    <div class="mt-3 d-grid">
                        <button class="btn btn-primary waves-effect waves-light UpdatePassword"
                            data-id="{{ Auth::user()->id }}" type="submit">Update Password</button>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->