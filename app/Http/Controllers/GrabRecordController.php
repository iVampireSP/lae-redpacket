<?php

namespace App\Http\Controllers;

use App\Models\GrabRecord;
use App\Models\RedPacket;

class GrabRecordController extends Controller
{
    // public function redPackets() {
    //     $red_packets = RedPacket::with('user')->paginate(100);
    //
    //     return $this->success($red_packets);
    // }
    public function index()
    {
        $grabRecords = GrabRecord::with(['redPacket' => function ($query) {
            $query->with('user');
        }, 'user'])->paginate(100);

        return view('grab-records', compact('grabRecords'));
    }
}
