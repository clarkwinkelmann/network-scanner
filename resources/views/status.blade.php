@extends('master')

@section('content')

@if (count($unknown_interfaces))
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Unknown devices</h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped">
                <tbody>
                    @foreach ($unknown_interfaces as $interface)
                        <tr>
                            <td>
                                <i class="fa fa-2x fa-question text-muted"></i>
                            </td>
                            <td>
                                <div>{{ $interface->mac }}</div>
                                <div class="text-muted">{{ $interface->time->diffForHumans() }}</div>
                            </td>
                            <td>
                                {{ $interface->ip }}
                            </td>
                            <td>
                                {{ $interface->info }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<?php
    $type_icon_mapping = [
        // Devices
        'laptop' => 'laptop',
        'desktop' => 'desktop',
        'tv' => 'television',
        'phone' => 'mobile',
        'tablet' => 'tablet',
        'accesspoint' => 'wifi',
        'router' => 'wifi',
        'server' => 'server',
        'printer' => 'print',

        // Interfaces
        'wlan' => 'wifi',
        'lan' => 'plug',
    ]
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <a class="btn btn-xs btn-default pull-right" href="{{ url('/scan') }}">Refresh</a>
        <h3 class="panel-title">Devices</h3>
    </div>
    <div class="panel-body">
        <p>
            <?php
                $recent_minutes = 5;
                $recent = Carbon\Carbon::now()->subMinutes($recent_minutes);
            ?>
            Online devices ({{ $recent_minutes }}min):
            {{ $known_devices->filter(function($device) use($recent) { return $device->last_seen && $device->last_seen->gt($recent); })->count() }}
        </p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th></th>
                    <th>Device</th>
                    <th>Interfaces</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($known_devices as $device)
                    <tr {!! $device->last_seen && $device->last_seen->gt($recent) ? 'class="success"' : '' !!}>
                        <td class="text-center">
                            <i class="fa fa-2x fa-{{ $type_icon_mapping[$device->type] }} text-muted"></i>
                        </td>
                        <td>
                            <div>
                                <strong>
                                    @if ($device->name)
                                        {{ $device->name }}
                                    @else
                                        {{ $device->model }}
                                    @endif
                                    @if ($device->owner)
                                        <span class="text-muted">@</span> {{ $device->owner }}
                                    @endif
                                </strong>
                            </div>
                            @if ($device->name && $device->model)
                                <div class="text-muted">{{ $device->model }}</div>
                            @endif
                            <div class="text-muted">Last heard of: {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'never' }}</div>
                        </td>
                        <td>
                            @foreach ($device->interfaces as $interface)
                                <div class="row">
                                    <div class="col-xs-2">
                                        <i class="fa fa-{{ $type_icon_mapping[$interface->type] }} text-muted"></i>
                                    </div>
                                    <div class="col-xs-10">
                                        <div>{{ $interface->mac }}</div>
                                        <div>
                                            @if ($interface->ip->type === 'dhcp')
                                                <span class="text-muted">DHCP:</span>
                                                @if ($interface->ip->actual_value)
                                                    {{ $interface->ip->actual_value }}
                                                @else
                                                    unknown
                                                @endif
                                            @elseif ($interface->ip->type === 'static')
                                                <span class="text-muted">Fixed:</span>
                                                @if ($interface->ip->actual_value === $interface->ip->fixed_value || !$interface->ip->actual_value)
                                                    {{ $interface->ip->fixed_value }}
                                                @else
                                                    <span class="text-danger">{{ $interface->ip->fixed_value }}</span>
                                                    {{ $interface->ip->actual_value }}
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                        <div class="text-muted">
                                            Last heard of: {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'never' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
