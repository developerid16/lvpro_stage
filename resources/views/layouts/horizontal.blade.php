<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
           <div class="navbar-brand-box main_logo d-flex align-items-center justify-content-between gap-4">
                <div>
                    <a href="javascript:void(0)" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ URL::asset('/build/images/logo-light-small.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ URL::asset('/build/images/logo-dark.png') }}" alt="" height="50">
                        </span>
                    </a>
                    <a href="javascript:void(0)" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ URL::asset('/build/images/logo-light-small.png') }}" alt="" height="50">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ URL::asset('/build/images/logo-light.png') }}" alt="" height="50">
                        </span>
                    </a>
                </div>
                <div class="d-flex align-items-center powered-by-box">
                    <h6 class="text-primary mb-0 me-2">Powered by</h6>
                    <img src="{{ URL::asset('/build/images/trex-logo.png') }}?q={{ time() }}" 
                        alt="" 
                        style="max-width:100px" 
                        class="auth-trex-logo-dark">
                </div>
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

        <div class="d-flex align-items-center">

            {{-- ✅ DEPARTMENT SWITCHER DROPDOWN (Super Admin ke multi-dept user dikhay) --}}
            @if(isset($isSuperAdmin) && ($isSuperAdmin || $allDepartments->count() > 0))
                <div class="dropdown d-inline-block me-2">
                    <button type="button"
                        class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1"
                        id="dept-switcher-btn"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        style="min-width: 160px;">
                        <i class="bx bx-building-house font-size-14"></i>
                        <span id="selected-dept-label">
                            @if($selectedDeptId)
                                {{ $allDepartments->firstWhere('id', $selectedDeptId)?->name ?? 'Department' }}
                            @else
                                All Departments
                            @endif
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dept-switcher-btn" style="min-width:200px">
                        {{-- All option --}}
                        <form method="POST" action="{{ url('/admin/switch-department') }}">
                            @csrf
                            <input type="hidden" name="department_id" value="all">
                            <button type="submit"
                                class="dropdown-item d-flex align-items-center gap-2 {{ !$selectedDeptId ? 'active' : '' }}">
                                <i class="bx bx-grid-alt"></i> All Departments
                            </button>
                        </form>
                        <div class="dropdown-divider"></div>
                        {{-- Each department --}}
                        @foreach($allDepartments as $dept)
                            <form method="POST" action="{{ url('/admin/switch-department') }}">
                                @csrf
                                <input type="hidden" name="department_id" value="{{ $dept->id }}">
                                <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2 {{ $selectedDeptId == $dept->id ? 'active' : '' }}">
                                    <i class="bx bx-buildings"></i> {{ $dept->name }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

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

                <!-- 🔔 BUTTON -->
                <button type="button"
                    class="btn header-item noti-icon waves-effect position-relative"
                    id="page-header-notifications-dropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                    <i class="mdi mdi-bell-outline" style="font-size:22px;"></i>

                    @php
                        $unread = $notifications->where('is_read', 0)->count();
                    @endphp

                    @if($unread > 0)
                        <span class="badge bg-danger rounded-pill position-absolute top-[14px] right-0 translate-middle">
                            {{ $unread }}
                        </span>
                    @endif
                </button>

                <!-- 🔽 DROPDOWN -->
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-notifications-dropdown">

                    <div class="p-3 border-bottom">
                        <h6 class="m-0">Notifications</h6>
                    </div>

                    <div style="max-height: 300px; overflow-y:auto;">

                        @forelse($notifications as $item)
                            <a href="javascript:void(0)"
                            class="dropdown-item notification-item"
                            data-id="{{ $item->id }}">

                                <div class="d-flex">

                                    <div class="avatar-xs me-3 position-relative">
                                        <span class="avatar-title bg-warning rounded-circle">
                                            <i class="mdi mdi-bell-outline"></i>
                                        </span>

                                        @if($item->is_read == 0)
                                            <span style="position:absolute;top:0;right:0;width:8px;height:8px;background:red;border-radius:50%;"></span>
                                        @endif
                                    </div>

                                    <div class="flex-grow-1" style="white-space:normal;">
                                        <h6 class="mb-1">
                                            {{ $item->notification->title ?? '' }}
                                        </h6>
                                        <p class="mb-1 text-muted">
                                            {{ $item->notification->short_desc ?? '' }}
                                        </p>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                                        </small>
                                    </div>

                                </div>
                            </a>

                        @empty
                            <p class="text-center p-3">No notifications</p>
                        @endforelse

                    </div>

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
                                    <h6 class="mt-0 mb-1" key="t-your-order">@lang('translation.Your_order_is_placed')</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-grammer">@lang('translation.If_several_languages_coalesce_the_grammar')</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago">@lang('translation.3_min_ago')</span></p>
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
                                        <p class="mb-1" key="t-simplified">@lang('translation.It_will_seem_like_simplified_English')</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-hours-ago">@lang('translation.1_hours_ago')</span></p>
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
                                        <p class="mb-1" key="t-grammer">@lang('translation.If_several_languages_coalesce_the_grammar')</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago">@lang('translation.3_min_ago')</span></p>
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
                                        <p class="mb-1" key="t-occidental">@lang('translation.As_a_skeptical_Cambridge_friend_of_mine_occidental')</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-hours-ago">@lang('translation.1_hours_ago')</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="p-2 border-top d-grid">
                        <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                            <i class="mdi mdi-arrow-right-circle me-1"></i> <span key="t-view-more">@lang('translation.View_More')</span>
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
                    <a class="dropdown-item d-block" href="#" data-bs-toggle="modal"
                        data-bs-target=".change-password">
                        <i class="bx bx-wrench font-size-16 align-middle me-1"></i>
                        <span key="t-settings">Change Password</span>
                    </a>
                    <a class="dropdown-item text-danger" href="javascript:void();"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i>
                        <span key="t-logout">@lang('translation.Logout')</span>
                    </a>
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

                    {{-- Dashboard --}}
                    @if($deptPermissions->contains('dashboard'))
                        <a class="nav-link" href="{{ url('/') }}">
                            <i class="fa-solid fa-gauge-simple me-2"></i>
                            <span key="t-dashboards">Dashboard</span>
                        </a>
                    @endif

                    {{-- CMS User Management --}}
                    @if(
                        $deptPermissions->contains('department-list') ||
                        $deptPermissions->contains('role-list') ||
                        $deptPermissions->contains('cms-user-list') ||
                        $deptPermissions->contains('app-user-list') ||
                        $deptPermissions->contains('reward-update-request-list') ||
                        $deptPermissions->contains('evoucher-approval-list') ||
                        $deptPermissions->contains('treats-and-deals-approval-list') ||
                        $deptPermissions->contains('birthday-voucher-approval-list')
                    )
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                                <i class="bx bx-customize me-2"></i>
                                <span key="t-apps" class="">CMS User Management</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                @if($deptPermissions->contains('department-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/departments') }}">Departments</a>
                                @endif
                                @if($deptPermissions->contains('role-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/roles') }}">Roles</a>
                                @endif
                                @if($deptPermissions->contains('cms-user-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/user') }}">CMS Users</a>
                                @endif
                                @if($deptPermissions->contains('app-user-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/app-user') }}">App Users</a>
                                @endif
                                @if($deptPermissions->contains('evoucher-approval-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/evoucher-approval') }}">eVoucher Approval</a>
                                @endif
                                @if($deptPermissions->contains('treats-and-deals-approval-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/treats-and-deals-approval') }}">Treats & Deals Approval</a>
                                @endif
                                @if($deptPermissions->contains('birthday-voucher-approval-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/birthday-voucher-approval') }}">Birthday Voucher Approval</a>
                                @endif
                            </div>
                        </li>
                    @endif

                    {{-- Content Management --}}
                    @if(
                        $deptPermissions->contains('dashboard-popup') ||
                        $deptPermissions->contains('banner-list') ||
                        $deptPermissions->contains('notification-list') ||
                        $deptPermissions->contains('content-management')
                    )
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                                <i class="bx bx-customize me-2"></i>
                                <span key="t-apps" class="">Content Management</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                @if($deptPermissions->contains('dashboard-popup'))
                                    <a class="dropdown-item" href="{{ url('/admin/dashboardpopup') }}">Dashboard Popup</a>
                                @endif
                                @if($deptPermissions->contains('banner-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/banner') }}">Banner</a>
                                @endif
                                @if($deptPermissions->contains('notification-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/notification') }}">Notification</a>
                                @endif
                                @if($deptPermissions->contains('content-management'))
                                    <a class="dropdown-item" href="{{ url('/admin/app-content') }}">T&C / FAQ</a>
                                @endif
                            </div>
                        </li>
                    @endif

                    {{-- Rewards Management --}}
                    @if(
                        $deptPermissions->contains('t&d-reward-list') ||
                        $deptPermissions->contains('reward-category') ||
                        $deptPermissions->contains('evoucher-list') ||
                        $deptPermissions->contains('birthday-voucher-list') ||
                        $deptPermissions->contains('push-voucher-log')
                    )
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                                <i class="bx bx-customize me-2"></i>
                                <span key="t-apps" class="">Rewards Management</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                @if($deptPermissions->contains('reward-category'))
                                    <a class="dropdown-item" href="{{ url('admin/category') }}">Reward Category</a>
                                @endif
                                @if($deptPermissions->contains('t&d-reward-list'))
                                    <a class="dropdown-item" href="{{ url('admin/reward') }}">Treats & Deals</a>
                                @endif
                                @if($deptPermissions->contains('evoucher-list'))
                                    <a class="dropdown-item" href="{{ url('admin/evoucher') }}">eVoucher</a>
                                @endif
                                @if($deptPermissions->contains('birthday-voucher-list'))
                                    <a class="dropdown-item" href="{{ url('admin/birthday-voucher') }}">Birthday Voucher</a>
                                @endif
                                @if($deptPermissions->contains('push-voucher-log'))
                                    <a class="dropdown-item" href="{{ url('admin/push-voucher') }}">Push Voucher Log</a>
                                @endif
                            </div>
                        </li>
                    @endif

                    {{-- CSO --}}
                    @if(
                        $deptPermissions->contains('cso-issuance-paid-list') ||
                        $deptPermissions->contains('cso-issuance-free-list') ||
                        $deptPermissions->contains('cso-physical-list')
                    )
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                                <i class="bx bx-customize me-2"></i>
                                <span key="t-apps" class="">CSO</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                @if($deptPermissions->contains('cso-physical-list'))
                                    <a class="dropdown-item" href="{{ url('admin/cso-physical') }}">CSO Physical Collection</a>
                                @endif
                                @if($deptPermissions->contains('cso-issuance-paid-list'))
                                    <a class="dropdown-item" href="{{ url('admin/cso-issuance-paid') }}">CSO issuance (Paid)</a>
                                @endif
                                @if($deptPermissions->contains('cso-issuance-free-list'))
                                    <a class="dropdown-item" href="{{ url('admin/cso-issuance-free') }}">CSO issuance (Free)</a>
                                @endif
                            </div>
                        </li>
                    @endif

                    {{-- Merchant Management --}}
                    @if(
                        $deptPermissions->contains('merchant-list') ||
                        $deptPermissions->contains('participating-merchant-list') ||
                        $deptPermissions->contains('fabs-list') ||
                        $deptPermissions->contains('club-location-list')
                    )
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-down" href="#" id="topnav-pages" role="button">
                                <i class="bx bx-customize me-2"></i>
                                <span key="t-apps" class="">Merchant Management</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                @if($deptPermissions->contains('merchant-list'))
                                    <a class="dropdown-item" href="{{ url('admin/merchants') }}">Merchant</a>
                                @endif
                                @if($deptPermissions->contains('fabs-list'))
                                    <a class="dropdown-item" href="{{ url('admin/fabs') }}">Fabs</a>
                                @endif
                                @if($deptPermissions->contains('club-location-list'))
                                    <a class="dropdown-item" href="{{ url('admin/club-location') }}">Club Location</a>
                                @endif
                                @if($deptPermissions->contains('participating-merchant-list'))
                                    <a class="dropdown-item" href="{{ url('admin/participating-merchant') }}">Participating Merchant</a>
                                @endif
                            </div>
                        </li>
                    @endif

                    {{-- Tier Management --}}
                    @if($deptPermissions->contains('tier-list'))
                        <a class="nav-link" href="{{ url('/admin/tiers') }}">Tier Management</a>
                    @endif

                    {{-- Others --}}
                    @if(
                        $deptPermissions->contains('cms-setting') ||
                        $deptPermissions->contains('transaction-history') ||
                        $deptPermissions->contains('voucher-logs') ||
                        $deptPermissions->contains('voucher-list')
                    )
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                                <i class="bx bx-customize me-2"></i>
                                <span key="t-apps" class="">Others</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                @if($deptPermissions->contains('transaction-history'))
                                    <a class="dropdown-item" href="{{ url('/admin/transaction-history') }}">Transaction History</a>
                                @endif
                                @if($deptPermissions->contains('voucher-logs'))
                                    <a class="dropdown-item" href="{{ url('/admin/voucherlogs') }}">Voucher Logs</a>
                                @endif
                                @if($deptPermissions->contains('voucher-list'))
                                    <a class="dropdown-item" href="{{ url('/admin/voucher-list') }}">Voucher List</a>
                                @endif
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">                         
                            
                                @can(['cms-setting'])
                                    {{-- <a class="dropdown-item  " href="{{url('/admin/cms-setting')}}">
                                        CMS Setting
                                    </a> --}}
                                @endcan
                               @if($deptPermissions->contains('transaction-history'))
                                    <a class="dropdown-item  " href="{{url('/admin/transaction-history')}}">
                                        Transaction History
                                    </a>
                                @endcan
                                @if($deptPermissions->contains('voucher-logs'))
                                    <a class="dropdown-item  " href="{{url('/admin/voucherlogs')}}">
                                        Voucher Logs
                                    </a>
                                @endcan
                                <!-- @can(['treats-and-deals-list'])-->
                                @if($deptPermissions->contains('treats-and-deals-list'))
                                    <a  class="dropdown-item " href="{{ url('/admin/treats-and-deals-list') }}">Treats & Deals List</a>
                                @endcan
                                <!-- @can(['evoucher-list']) -->
                                @if($deptPermissions->contains('evoucher-list'))
                                    <a  class="dropdown-item " href="{{ url('/admin/evoucher-list') }}">E-Voucher List</a>
                                @endcan
                                <!-- @can(['birthday-voucher-list']) -->
                                @if($deptPermissions->contains('birthday-voucher-list'))
                                    <a  class="dropdown-item " href="{{ url('/admin/birthday-voucher-list') }}">Birthday Voucher List</a>
                                @endcan
                               

                            </div>
                        </li>
                    @endif

                    {{-- Stock Management --}}
                    @if(
                        $deptPermissions->contains('evoucher-stock') ||
                        $deptPermissions->contains('t&d-reward-stock')
                    )
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                                <i class="bx bx-customize me-2"></i>
                                <span key="t-apps" class="">Stock Management</span>
                                <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                @if($deptPermissions->contains('t&d-reward-stock'))
                                    <a class="dropdown-item" href="{{ url('/admin/treats-deals-stock') }}">Treats & Deals Stock</a>
                                @endif
                                @if($deptPermissions->contains('evoucher-stock'))
                                    <a class="dropdown-item" href="{{ url('/admin/evoucher-stock') }}">E-Voucher Stock</a>
                                @endif
                            </div>
                        </li>
                    @endif

                </ul>
            </div>
        </nav>
    </div>
</div>

<!--  Change-Password Modal -->
<div class="modal fade change-password" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                            class="form-control @error('current_password') is-invalid @enderror"
                            name="current_password" autocomplete="current_password"
                            placeholder="Enter Current Password" value="{{ old('current_password') }}">
                        <div class="text-danger" id="current_passwordError" data-ajax-feedback="current_password"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newpassword">New Password</label>
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password" autocomplete="new_password" placeholder="Enter New Password">
                        <div class="text-danger" id="passwordError" data-ajax-feedback="password"></div>
                    </div>
                    <div class="mb-3">
                        <label for="userpassword">Confirm Password</label>
                        <input id="password-confirm" type="password" class="form-control"
                            name="password_confirmation" autocomplete="new_password"
                            placeholder="Enter New Confirm password">
                        <div class="text-danger" id="password_confirmError" data-ajax-feedback="password-confirm"></div>
                    </div>
                    <div class="mt-3 d-grid">
                        <button class="btn btn-primary waves-effect waves-light UpdatePassword"
                            data-id="{{ Auth::user()->id }}" type="submit">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>