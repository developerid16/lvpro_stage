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
            <div class="app-search d-none d-lg-flex" style=" align-items: center;">
                <h3 class="mb-0 logo_txt">Rewards Management System</h3>
            </div>

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
                        <span class="d-xl-inline-block ms-1">
                            {{ Auth::check() ? ucfirst(Auth::user()->name) : '' }}
                        </span>
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
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">

                                @can('role-list')
                                    <a class="dropdown-item" key="t-alerts" href="{{url('/admin/roles')}}">Roles</a>
                                @endcan
                               
                                @can('department-list')
                                    <a class="dropdown-item" key="t-alerts" href="{{url('/admin/departments')}}">Departments</a>
                                @endcan
                                
                                @can('user-list')
                                    <a class="dropdown-item" key="t-buttons" href="{{url('/admin/user')}}">CMS Users</a>
                                @endcan
                                @can(['app-user-list'])
                                <a class="dropdown-item" key="t-dashboards" href="{{url('/admin/app-user')}}">App Users</a>
                                @endcan                               
                                @can('reward-update-request')
                                    <a class="dropdown-item" key="t-buttons" href="{{url('/admin/reward-update-request')}}">Reward Update Request</a>
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
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">Content / Ad Management</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                           
                            @can('dashboard-list')
                                <a class="dropdown-item" key="t-buttons" href="{{url('/admin/dashboardpopup')}}">Dashboard Popup</a>
                            @endcan
                            @can('annoucement-list')
                                <a class="dropdown-item" key="t-buttons" href="{{url('/admin/announcement')}}">Announcement</a>
                            @endcan
                            
                        </div>
                    </li>
                    @endcan

                    @canany(['reward-list', 'reward-redemption-pos', 'reward-redemption-cms'])

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">Rewards Management</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            @can('reward-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/category')}}">Reward Category</a>
                            @endcan
                            @can('reward-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/reward')}}">Treats & Deals</a>
                            @endcan
                            @can('evoucher-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/evoucher')}}">eVoucher</a>
                            @endcan
                            @can('bday-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/birthday-voucher')}}">Birthday Voucher</a>
                            @endcan
                            @can('push-voucher')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/push-voucher')}}">Push Voucher Log</a>
                            @endcan                          
                        </div>
                    </li>
                    @endcan

                     @canany(['reward-list', 'reward-redemption-pos', 'reward-redemption-cms'])

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">CSO</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            @can('cso-purchase-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/cso-purchase')}}">CSO Purchase</a>
                            @endcan                           
                            @can('cso-physical-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/cso-physical')}}">CSO Physical Collection</a>
                            @endcan                           
                            @can('cso-issuance-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/cso-issuance')}}">CSO Issuance</a>
                            @endcan                           
                        </div>
                    </li>
                    @endcan                
                    

                    @canany(['merchant-list', 'participating-merchant-list'])

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">Merchant Management</span>
                            <!-- <div class="arrow-down"></div> -->
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            @can('merchant-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/merchants')}}">Merchant</a>
                            @endcan
                            @can('participating-merchant-list')
                                <a class="dropdown-item" key="t-alerts" href="{{url('admin/participating-merchant')}}">Participating Merchant</a>
                            @endcan                           
                        </div>
                    </li>
                    @endcan
                     @can(['tier-list'])
                        <a class="nav-link  " href="{{url('/admin/tiers')}}">
                            Tier Management
                        </a>
                    @endcan
                   
                    @canany(['cms-setting', 'transaction-history'])
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                            <i class="bx bx-customize me-2"></i><span key="t-apps" class="">Others</span>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">                         
                           
                            @can(['cms-setting'])
                                <a class="dropdown-item  " href="{{url('/admin/cms-setting')}}">
                                    CMS Setting
                                </a>
                            @endcan
                            @can(['transaction-history'])
                                <a class="dropdown-item  " href="{{url('/admin/transaction-history')}}">
                                    Transaction History
                                </a>
                            @endcan
                            @can(['voucher-logs'])
                                <a class="dropdown-item  " href="{{url('/admin/voucherlogs')}}">
                                    Voucher Logs
                                </a>
                            @endcan
                            @can(['voucher-list'])
                                <a class="dropdown-item  " href="{{url('/admin/voucher-list')}}">
                                    Voucher List
                                </a>
                            @endcan
                           

                        </div>
                    </li>
                    @endcan

                    @can(['redeem-vouche'])
                        {{-- <a class="nav-link  " href="{{url('/admin/redeem-voucher')}}">
                            <i class="fa-solid fa-gauge-simple me-2"></i><span key="t-dashboards">Redeem Voucher</span>
                        </a> --}}
                    @endcan
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