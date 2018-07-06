<div class="row border-bottom">
    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">

        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
            {{-- <form role="search" class="navbar-form-custom" action="search_results.html">
                <div class="form-group">
                    <input type="text" placeholder="Search for someone..." class="form-control" name="top-search" id="top-search">
                </div>
            </form> --}}
        </div>

        <ul class="nav navbar-top-links navbar-right">
            @if (isset($page) && $page instanceof \App\Page)
                <?php $rating = $page->rating_avg(); ?>
                @if ($rating > 0)
                    <li>
                        <span class="m-r-sm text-muted welcome-message">
                            Reviews
                            {!! str_repeat('<i class="fa fa-star"></i>', $rating) !!}
                            {!! str_repeat('<i class="fa fa-star-o"></i>', (5 - $rating)) !!}
                        </span>
                    </li>
                @endif
            @endif

            @if (Auth::check())
                <li class="dropdown">
                    <?php $unread = Auth::user()->unreadNotifications->count(); ?>
                    <a id="{{ ($unread > 0) ? 'btn-alerts' : '' }}" class="dropdown-toggle" data-toggle="dropdown" data-time="{{ time() }}" href="#">
                        <i class="fa fa-bell"></i>  <span class="label label-primary">{{ $unread }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-alerts" style="width: 500px;">
                        <?php
                            $fakeid = App::make('fakeid');
                        ?>
                        @foreach(Auth::user()->notifications->sortBy('created_at') as $notification)
                            <li>
                                @if ($notification->type == 'App\Notifications\Subscription')
                                    <div class="p-xs">
                                        <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                            @if(isset($notification->data['refer_name']))
                                                {{ $notification->data['refer_name'] }}
                                                <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span><br>
                                            @endif
                                            @if(isset($notification->data['subscription_renew_text']))
                                                {{ $notification->data['subscription_renew_text'] }}
                                                @if(isset($notification->data['subscription_renew_date']))
                                                    <span class="pull-right text-muted small">{{ $notification->data['subscription_renew_date'] }}</span><br>
                                                @endif
                                            @endif
                                            @if(isset($notification->data['trial_for_text']))
                                                {{ $notification->data['trial_for_text'] }}
                                                @if(isset($notification->data['trial_for_date']))
                                                    <span class="pull-right text-muted small">{{ $notification->data['trial_for_date'] }}</span><br>
                                                @endif
                                            @endif
                                            @if(isset($notification->data['channel_name']))
                                                <p>Your Channel <span style="color: gray"> {{$notification->data['channel_name']['name']}}</span></p>
                                            @endif
                                        </a>
                                    </div>
                                @endif

                                @if ($notification->type == 'App\Notifications\WebhookNotifications')
                                <div class="p-xs">
                                    <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                        <i class="fa fa-user-plus fa-fw"></i> {{ $notification->data['refer_name'] }}
                                        <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                    </a>
                                </div>
                                @endif

                                @if ($notification->type == 'App\Notifications\ProfileUpdate')
                                <div class="p-xs">
                                    <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                        <i class="fa fa-user-plus fa-fw"></i> {{ $notification->data['refer_name'] }}
                                        <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                    </a>
                                </div>
                                @endif
                                @if ($notification->type == 'App\Notifications\InviteAccepted')
                                <div class="p-xs">
                                    <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                        <i class="fa fa-user-plus fa-fw"></i> {{ $notification->data['user_name'] }} joined <a style="display: inline; padding: 0;" href="{{ route('project.dashboard', [$fakeid->encode($notification->data['project_id'])]) }}">{{ $notification->data['project_name'] }}</a>
                                        <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                    </a>
                                </div>
                                @endif

                                @if ($notification->type == 'App\Notifications\UserInvited')
                                <div class="p-xs">
                                    <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                        <i class="fa fa-user-plus fa-fw"></i> {{ $notification->data['refer_name'] }} invited you to <a style="display: inline; padding: 0;" href="{{ route('project.dashboard', [$fakeid->encode($notification->data['project_id'])]) }}">{{ $notification->data['project_name'] }}</a>
                                        <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                    </a>
                                </div>
                                @endif

                                @if ($notification->type == 'App\Notifications\ChannelReady')
                                <div class="p-xs">
                                    <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                        <i class="fa fa-{{ App\Channel::$supported[$notification->data['channel_type']]['icon'] }} fa-fw"></i> Channel <a style="display: inline; padding: 0;" href="{{ route('channel.dashboard', [$fakeid->encode($notification->data['project_id']), $fakeid->encode($notification->data['channel_id'])]) }}">{{ $notification->data['channel_name'] }}</a> is ready
                                        <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                    </a>
                                </div>
                                @endif

                                @if ($notification->type == 'App\Notifications\CsvImported')
                                <div class="p-xs">
                                    <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                        <i class="fa fa-upload fa-fw"></i> CSV {{ $notification->data['csv']['name'] }} imported
                                        <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                    </a>
                                </div>
                                @endif

                                @if ($notification->type == 'App\Notifications\Tier')
                                    <div class="p-xs">
                                        <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                            <i class="fa fa-upload fa-fw"></i> {{ $notification->data['refer_name'] }}
                                            <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                        </a>
                                    </div>
                                @endif

                                @if ($notification->type == 'App\Notifications\Plan')
                                    <div class="p-xs">
                                        <a style="display: inline; padding: 0;" href="{{ route('notifications.mark_as_read') }}">
                                            <i class="fa fa-upload fa-fw"></i> {{ $notification->data['refer_name'] }}
                                            <span class="pull-right text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                        </a>
                                    </div>
                                @endif
                            </li>
                        @endforeach

                        {{-- <li>
                            <div class="text-center link-block">
                                <a href="notifications.html">
                                    <strong>See All Alerts</strong>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </div>
                        </li> --}}
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ucfirst(trans(Auth::user()->name))}}
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a href="{{ route('user.getProfile', [Auth::user()]) }}">Profile</a></li>
                        <li><a href="{{ route('logout') }}">Logout</a></li>
                    </ul>
                </li>
            @endif
        </ul>

    </nav>
</div>
<div class="row wrapper border-bottom white-bg page-heading" style="line-height: 30px; padding-bottom: 0px;">
    <div class="col-lg-12">
        <!--
        <h2>@yield('contentheader_title', 'Page Header here')</h2>
        <small>@yield('contentheader_description')</small>
        -->
        @yield('breadcrumb')
    </div>
</div>