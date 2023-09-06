<?php

namespace App\Http\Controllers;

use App\Models\Statistic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;

class StatisticController extends Controller
{
    public function getStat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'date' => 'required',
            'period' => 'required|in:d,w,m,y'
        ]);

        if ($validator->fails()) {
            return response("Access denied", 403);
        }

        $data = $validator->validated();

        $uid = $data['uid'];
        $date = $data['date'];
        $period = $data['period'];

        $start = Carbon::parse($date);
        $end = Carbon::parse($date);

        switch ($period) {
            case 'd':
                return Statistic::where('uid', $uid)->where('day', $start->format('Y-m-d'))->value('count');
            case 'w':
                $start->startOfWeek();
                $end->endOfWeek();
                break;
            case 'm':
                $start->startOfMonth();
                $end->endOfMonth();
                break;
            case 'y':
                $start->startOfYear();
                $end->endOfYear();
                break;
            default:
                return 0;
        }

        return Statistic::where('uid', $uid)
            ->whereBetween('day', [$start, $end])
            ->sum('count');
    }

    public static function addStat(string $uid)
    {
        $today = Carbon::now()->format('Y-m-d');

        $statistic = Statistic::firstOrNew([
            'uid' => $uid,
            'day' => $today,
        ]);

        if (!$statistic->exists) {
            $statistic->count = 0;
        }

        $statistic->count++;

        $statistic->save();
    }
}
