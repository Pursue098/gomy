<li class="{{ (Request::is('project/' . $project->getRouteKey() . '/channel/' . $channel->getRouteKey())) ? 'active' : '' }}">
    <a href="{{ route('channel.dashboard', [$project, $channel]) }}"><i class="fa fa-connectdevelop"></i> <span class="nav-label">Dashboard</span>
    @if (isset($zepto))
        <span class="pull-right label label-primary">{{ _n($zepto->complexity, 0.0) }}</span>
    @endif
    </a>
</li>