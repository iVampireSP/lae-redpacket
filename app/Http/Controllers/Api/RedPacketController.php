<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GrabRecord;
use App\Models\RedPacket;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RedPacketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $red_packets = RedPacket::thisUser()->get();

        return $this->success($red_packets);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request)
    {
        $request->validate([
            'total' => 'required|numeric|min:1',
            'total_amount' => 'required|numeric|min:0.01|max:1000',
            'greeting' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        if ($request->input('total') * 0.01 > $request->input('total_amount')) {
            $this->error('单个红包不能低于 0.01 元。');
        }

        $user = $request->user('api');

        $resp = $this->http->patch('users/' . $user->id, [
            'balance' => "-" . $request->input('total_amount'),
            'description' => "发了一个 " . $request->input('total') . " 个红包，总金额为 " . $request->input('total_amount') . " 元",
        ]);

        if (!$resp->successful()) {
            return $this->forbidden('余额不足');
        }

        try {
            $red_packet = RedPacket::create([
                'total_amount' => $request->input('total_amount'),
                'greeting' => $request->input('greeting'),
                'password' => $request->input('password'),
                'user_id' => $user->id,
                'total' => $request->input('total'),
            ]);
        } catch (Exception) {
            $this->http->patch('users/' . $user->id, [
                'balance' => $request->input('total_amount'),
                'description' => "红包发送失败，退回 " . $request->input('total_amount') . " 元",
            ]);

            return $this->error('发送失败。');
        }


        return $this->success($red_packet);
    }

    /**
     * Display the specified resource.
     *
     * @param Request   $request
     * @param RedPacket $redPacket
     *
     * @return JsonResponse
     */
    public function show(Request $request, RedPacket $redPacket): JsonResponse
    {
        if ($request->user('api')->id === $redPacket->user_id) {
            $redPacket->makeVisible('password');
        }

        $redPacket->load('grabRecords');

        return $this->success($redPacket);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param RedPacket $redPacket
     *
     * @return JsonResponse
     */
    public function destroy(RedPacket $redPacket)
    {
        if ($redPacket->remaining_amount > 0) {
            $this->http->patch('users/' . $redPacket->user_id, [
                'balance' => $redPacket->remaining_amount,
                'description' => '退回红包金额',
            ]);
        }

        $redPacket->delete();

        return $this->deleted();
    }


    public function grab(Request $request, RedPacket $redPacket)
    {
        if ($redPacket->remain === 0) {
            return $this->error('红包已经被抢完了。');
        }

        if ($redPacket->password && $redPacket->password !== $request->input('password')) {
            return $this->error('红包密码错误。');
        }

        if (GrabRecord::where('red_packet_id', $redPacket->id)->where('user_id', auth('api')->id())->exists()) {
            return $this->error('你已经抢过这个红包了。');
        }

        $redPacket->load('user');

        try {
            $amount = $redPacket->grab();

            $this->http->patch('users/' . auth('api')->id(), [
                'balance' => $amount,
                'description' => "抢到了 {$redPacket->user->name} 的红包，金额为 " . $amount . " 元",
            ]);

            return $this->success([
                'amount' => $amount,
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
