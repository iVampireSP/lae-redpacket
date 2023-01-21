<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RedPacket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'password',
        'greeting',
        'total',
        'total_amount',
        'user_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    protected $hidden = [
        'password',
    ];

    // route key

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if ($model->total * 0.01 > $model->total_amount) {
                throw new Exception("单个红包不能低于 0.01 元");
            }

            $model->uuid = Str::uuid();

            $model->remain = $model->total;
            $model->remaining_amount = $model->total_amount;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grabRecords(): HasMany
    {
        return $this->hasMany(GrabRecord::class);
    }

    public function scopeThisUser()
    {
        return $this->where('user_id', auth('api')->id());
    }

    /**
     * @throws Exception
     */
    public function grab(): string
    {
        if ($this->remain <= 0) {
            throw new Exception("红包已经被抢完了");
        }

        // 锁
        return Cache::lock('red_packet_' . $this->id, 10)->get(function () {
            $this->refresh();

            if ($this->remain <= 0) {
                throw new Exception("红包已经被抢完了");
            }


            $amount = $this->pickAmount();

            $this->decrement('remain');
            $this->decrement('remaining_amount', $amount);

            $this->save();

            GrabRecord::create([
                'red_packet_id' => $this->id,
                'user_id' => auth('api')->id(),
                'amount' => $amount,
            ]);

            return $amount;
        });
    }

    public function pickAmount(): string
    {
        $cache_key = "red_packet:{$this->id}";

        if (!Cache::has($cache_key)) {
            Cache::put($cache_key, $this->generateRedPackets(), 60);
        }

        $red_packets = Cache::get($cache_key);

        // 取第一个
        $amount = array_shift($red_packets);

        // 重新写入缓存
        Cache::put($cache_key, $red_packets, 60);

        return $amount;
    }

    public function generateRedPackets(): array
    {
        // 存放随机红包
        $redPacket = [];

        // 未分配的金额
        $surplus = $this->remaining_amount;
        for ($i = 1; $i <= $this->remain; $i++) {
            // 安全金额
            $safeMoney = $surplus - ($this->remain - $i) * 0.01;
            // 平均金额
            $avg = $i == $this->remain ? $safeMoney : bcdiv($safeMoney, ($this->remain - $i), 2);
            // 随机金额

            $mul = mt_rand(1, bcmul($avg, "100", 2));
            $rand = $avg > 0.01 ? bcdiv($mul, "100", 2) : 0.01;

            // 剩余红包
            $surplus = bcsub($surplus, $rand, 2);
            $redPacket[] = $rand;
        }

        // 平分剩余红包
        $avg = bcdiv($surplus, $this->remain, 2);
        for ($n = 0; $n < count($redPacket); $n++) {
            $redPacket[$n] = bcadd($redPacket[$n], $avg, 2);
            $surplus = bcsub($surplus, $avg, 2);
        }

        // 如果还有红包没有分配完时继续分配
        if ($surplus > 0) {
            // 随机抽取分配好的红包，将剩余金额分配进去
            $keys = array_rand($redPacket, bcmul($surplus, "100", 2));
            // array_rand 第二个参数为 1 时返回的是下标而不是数组
            $keys = is_array($keys) ? $keys : [$keys];
            foreach ($keys as $key) {
                $redPacket[$key] = bcadd($redPacket[$key], 0.01, 2);
                $surplus = bcsub($surplus, 0.01, 2);
            }
        }

        // 红包分配结果
        return $redPacket;
    }
}
