<!-- Header Navbar -->
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            @if (Auth::guest())
                <li><a href="{{ url('/register') }}">{{ trans('adminlte_lang::message.register') }}</a></li>
                <li><a href="{{ url('/login') }}">{{ trans('adminlte_lang::message.login') }}</a></li>
            @else
                <li class="nav-header">
                    <div class="dropdown profile-element">
                        <span>
                            @if (isset($channel) && isset($channel->status) && $channel->status == 'assigned' && isset($channel->channable))
                                <img alt="Avatar" class="img-circle" src="{{ $channel->channable->picture() }}" style="width: 48px; height:48px;" />
                            @else
                                <img alt="Avatar" class="img-circle" src="{{ Auth::user()->picture }}" style="width: 48px; height:48px;" />
                            @endif
                        </span>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear">
                                <span class="block m-t-xs">
                                    <strong class="font-bold">
                                        @if (isset($channel) && isset($channel->status) && $channel->status == 'assigned' && isset($channel->channable))
                                            {{ $channel->channable->name }}
                                        @else
                                            {{ Auth::user()->name }}
                                        @endif
                                    </strong>
                                </span>
                            </span>
                            {{-- <span class="text-muted text-xs block">Social Media Manager <b class="caret"></b></span> </span> --}}
                        </a>
                        {{-- <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a href="profile.html">Profile</a></li>
                            <li><a href="contacts.html">Contacts</a></li>
                            <li><a href="#">Mailbox</a></li>
                            <li class="divider"></li>
                            <li><a href="{{ url('/logout') }}">{{ trans('adminlte_lang::message.signout') }}</a></li>
                        </ul> --}}
                    </div>
                    <div class="logo-element">
                        <img alt="Avatar" class="img-circle" src="{{ Auth::user()->picture }}" style="width: 32px; height:32px;" />
                    </div>
                </li>
                @if (Auth::user()->isTeia())
                    <li class="{{ (Request::is('admin/*')) ? 'active' : '' }}">
                        <a href="#"><i class="fa fa-gears"></i> <span class="nav-label">Admin</span></a>
                        <ul class="nav nav-second-level collapse">
                            <li class="{{ (Request::is('admin/users')) ? 'active' : '' }}">
                                <a href="{{ route('admin.users') }}"><i class="fa fa-users"></i> <span class="nav-label">Users</span></a>
                            </li>
                        </ul>
                    </li>
                @endif
                <li {{ (Request::is('/')) ? 'class="active"' : '' }}>
                    <a href="/"><i class="fa fa-home"></i> <span class="nav-label">Projects</span></a>
                </li>
                
                @if (isset($projects))
                    @foreach($projects as $project)
                        <li class="{{ (Request::is('project/' . $project->getRouteKey() . '*')) ? 'active' : '' }}">
                            <a href="{{ route('project.dashboard', [$project]) }}"><i class="fa fa-server"></i> <span class="nav-label">{{ $project->name }}</span><span class="pull-right label label-primary">{{ _n($project->channels->count()) }}</span></a>
                            <ul class="nav nav-second-level collapse">
                                @if (count($project->channels) > 1 || (isset($project->channels[0]) && ! ($project->channels[0]->channable instanceof \App\Channels\Facebook\Competitor)))
                                    <li class="{{ (Request::is('project/' . $project->getRouteKey() . '/rewards')) ? 'active' : '' }}">
                                        <a href="{{ route('project.rewards', [$project]) }}"><i class="fa fa-gift"></i> <span class="nav-label">Rewards</span><span class="pull-right label label-primary">{{ _n($project->rewards()->count()) }}</span></a>
                                    </li>

                                    <li class="{{ (Request::is('project/' . $project->getRouteKey() . '/crm/*')) ? 'active' : '' }}">
                                        <a href="{{ route('crm.users', [$project]) }}"><i class="fa fa-address-book"></i> <span class="nav-label">CRM</span></a>
                                    </li>
                                @endif

                                @foreach($project->channels as $c)
                                    <li>
                                        <a href="{{ route('channel.dashboard', [$project, $c]) }}"><i class="fa fa-{{ $c->icon() }}"></i> {{ ucwords($c->type) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach

                    <li {{ (Request::is('/settings')) ? 'class="active"' : '' }}>
                        <a href="{{ route('settings') }}"><i class="fa fa-code"></i> <span class="nav-label">Api</span></a>
                    </li>

                    @if (Auth::user()->hasRole('superadministrator|administrator|project_role:admin'))
                        <li>
                            <a href= "{{route('plan.index')}}" ><i class="fa fa-product-hunt"></i> <span class="nav-label">Product Plans</span></a>
                        </li>

                        <li>
                            <a href="#" data-toggle="modal" data-target="#modal_add_channel" ><i class="fa fa-subscript"></i> <span class="nav-label">Subscriptions</span></a>
                        </li>
						<li>
                            <a href= "" ><i class="fa fa-users"></i> <span class="nav-label">Manage Users</span></a>
                            <ul class="nav-label sub-menu" style="list-style: none">
                                <li style="color: white">
                                    <a href= "{{route('user.index')}}" ><i class="fa fa-users"></i> <span class="nav-label">Users List</span></a>
                                </li>
                                <br>
                                <li>
                                    <a href= "{{route('roles.index')}}" ><i class="fa fa-universal-access"></i> <span class="nav-label">Roles</span></a>
                                </li>
                                <br>
                                <li>
                                    <a href= "{{route('permission.index')}}" ><i class="fa fa-low-vision"></i> <span class="nav-label">Permission</span></a>
                                </li>

                            </ul>
                        </li>
						<li {{ (Request::is('/subscribed')) ? 'class="active"' : '' }}>
                            <a href="/subscribed"><i class="fa fa-home"></i> <span class="nav-label">Enterprise Plans</span></a>
                        </li>

                    @endif
                @endif

                @if (isset($channel) && isset($channel->status) && $channel->status == 'assigned' && isset($channel->channable))

                    @if ($channel->channable instanceof \App\Channels\Facebook)
                        @include('channels.facebook._nav')
                    @endif

                    @if ($channel->channable instanceof \App\Channels\Woocommerce)
                        @include('channels.woocommerce._nav')
                    @endif

                    @if ($channel->channable instanceof \App\Channels\Captive)
                        @include('channels.captive._nav')
                    @endif

                    @if ($channel->channable instanceof \App\Channels\Zepto)
                        @include('channels.zepto._nav')
                    @endif

                    @if (! ($channel->channable instanceof \App\Channels\Facebook\Competitor))
                        <li class="{{ (Request::is('project/' . $project->getRouteKey() . '/crm/*')) ? 'active' : '' }}">
                            <a href="{{ route('crm.users', [$project]) }}"><i class="fa fa-address-book"></i> <span class="nav-label">CRM</span></a>
                        </li>
                    @endif
                
<!--                     @if (Auth::user()->hasRole('superadministrator|administrator|project_role:admin')) -->
<!--                         <li> -->
<!--                             <a href= "{{route('plan.index')}}" ><i class="fa fa-product-hunt"></i> <span class="nav-label">Product Plans</span></a> -->
<!--                         </li> -->

<!--                         <li> -->
<!--                             <a href="#" data-toggle="modal" data-target="#modal_add_channel" ><i class="fa fa-subscript"></i> <span class="nav-label">Subscriptions</span></a> -->
<!--                         </li> -->

<!--                         <li> -->
<!--                             <a href= "" ><i class="fa fa-users"></i> <span class="nav-label">Manage Users</span></a> -->
                            <ul class="nav-label sub-menu" style="list-style: none">
                                <li style="color: white">
<!--                                     <a href= "{{route('user.index')}}" ><i class="fa fa-users"></i> <span class="nav-label">Users List</span></a> -->
<!--                                 </li> -->
<!--                                 <br> -->
<!--                                 <li> -->
<!--                                     <a href= "{{route('roles.index')}}" ><i class="fa fa-universal-access"></i> <span class="nav-label">Roles</span></a> -->
<!--                                 </li> -->
<!--                                 <br> -->
<!--                                 <li> -->
<!--                                     <a href= "{{route('permission.index')}}" ><i class="fa fa-low-vision"></i> <span class="nav-label">Permission</span></a> -->
<!--                                 </li> -->

<!--                             </ul> -->
<!--                         </li> -->
<!-- 						<li {{ (Request::is('/subscribed')) ? 'class="active"' : '' }}> -->
<!--                             <a href="/subscribed"><i class="fa fa-home"></i> <span class="nav-label">Plan Subscribed</span></a> -->
<!--                         </li> -->

<!--                     @endif  -->
                     
                @endif 
                {{-- @if (isset($project) && Request::is('project/' . $project->getRouteKey() . '/crm*'))
                    @include('channels.crm._nav')
                @endif --}}
            @endif
        </ul>
    </div>
</nav>
<div id="modals">
    <div class="modal inmodal modal_add_channel" id="modal_add_channel" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Channel name</label>
                        </div>
                    </div>
                    @foreach(array_chunk(App\Channel::$supported, 4) as $chunk)
                        <div class="row">
                            @foreach($chunk as $channel)
                                <div class="col-md-3">
                                    <div class="widget navy-bg p-lg text-center">
                                        <i class="fa fa-{{ $channel['icon'] }} fa-3x"></i>
                                        <br /><br />
                                        <h3 class="font-bold no-margins">{{ ucwords($channel['name'])}}</h3>
                                        <br />
                                        <a style="color: #1ab394;" class="btn btn-default" data-method="get" href="{{ route('tier.show', [$channel['name'], $channel['name']]) }}">Tiers</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>

        $(document).ready(function() {
            $('.footable').footable();

            $('.modal_add_channel').on('submit', function(e) {
                var name = $(this).find('.channel_name').val();

                $(e.target).append('<input type="hidden" name="name" value="' + name + '" />');

                return true;
            });
        });
    </script>
@endsection