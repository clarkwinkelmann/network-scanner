<?php

namespace App\Http\Controllers;

use App\ArpScan;
use App\Jobs\ArpScanJob;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Storage;

class NetworkController extends Controller
{
    public function status()
    {
        $network = json_decode(Storage::get('network.json'), true);

        $scans = ArpScan::join(DB::raw('(SELECT mac, MAX(time) FROM arp_scans GROUP BY mac) latest_match'), 'latest_match.mac', '=', 'arp_scans.mac')->get()->keyBy('mac');

        $now = Carbon::now();

        $known_devices = collect();
        $known_interfaces = [];

        foreach ($network['devices'] as $stored_device) {
            $device = new \stdClass();
            $device->type = Arr::get($stored_device, 'type');
            $device->name = Arr::get($stored_device, 'name');
            $device->model = Arr::get($stored_device, 'model');
            $device->owner = Arr::get($stored_device, 'owner');
            $device->interfaces = [];
            $device->online = false;
            $device->last_seen = null;

            foreach (Arr::get($stored_device, 'interfaces', []) as $stored_interface) {
                $interface = new \stdClass();
                $interface->type = Arr::get($stored_interface, 'type');
                $interface->mac = Arr::get($stored_interface, 'mac');
                $interface->ip = new \stdClass();
                $interface->ip->type = Arr::get($stored_interface, 'ip.type');
                $interface->ip->fixed_value = Arr::get($stored_interface, 'ip.value');
                $interface->ip->actual_value = null;

                $interface->online = false;
                $interface->latest_scan = null;
                $interface->last_seen = null;

                if ($scans->has($stored_interface['mac'])) {
                    $scan = $scans->get($stored_interface['mac']);

                    $known_interfaces[] = $scan->mac;

                    $interface->ip->actual_value = $scan->ip;

                    $interface->latest_scan = Arr::only($scan->toArray(), ['time', 'mac', 'ip', 'info']);

                    if ($now->diffInMinutes($scan->time) < 10) {
                        $interface->online = true;
                        $device->online = true;
                    }

                    $interface->last_seen = $scan->time;

                    // Save the scan time if it's the lastest time seen for this device
                    if (is_null($device->last_seen) || $device->last_seen->lt($scan->time)) {
                        $device->last_seen = $scan->time;
                    }
                }

                $device->interfaces[] = $interface;
            }

            $known_devices->push($device);
        }

        $known_devices = $known_devices->sortByDesc(function($device) {
            if (is_null($device->last_seen)) {
                return 0;
            }

            return $device->last_seen->timestamp;
        });

        $unknown_interfaces = [];

        foreach ($scans as $scan) {
            if (in_array($scan->mac, $known_interfaces)) {
                continue;
            }

            $interface = new \stdClass();
            $interface->time = $scan->time;
            $interface->mac = $scan->mac;
            $interface->ip = $scan->ip;
            $interface->info = $scan->info;

            $unknown_interfaces[] = $interface;
        }

        return view('status')->with('known_devices', $known_devices)->with('unknown_interfaces', $unknown_interfaces);
    }

    public function scan()
    {
        dispatch(new ArpScanJob());

        return redirect('/');
    }
}