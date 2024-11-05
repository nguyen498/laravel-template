<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use Carbon\Carbon;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function getModel()
    {
        return Notification::class;
    }

    public function findNotificationByWhere($status, $from, $to)
    {
        return $this->model->where('status', $status)
            ->where('send_type', Notification::TYPE_ONCE)
            ->whereRaw('send_date between \''.$from . '\' and \''. $to . '\'')
            ->get();
    }

    public function findNotificationDay (){
        $timezone = new \DateTimeZone('Asia/Ho_Chi_Minh');
        $now = Carbon::now()->setTimezone($timezone);
//        $from = Carbon::now()->setTimezone($timezone)->subMinutes(4);
        $from = Carbon::now()->setTimezone($timezone);
        $query = $this->model->where('status', Notification::STATUS_READY)
            ->where('send_type', Notification::TYPE_EVERYDAY)
            ->whereRaw('(last_run is null or date(last_run) != '. "'{$now->toDateString()}')")
            ->whereRaw('hour(send_date) = '. $now->hour)
            ->whereRaw('minute(send_date) between '. $from->minute . ' and '. $now->minute)->get();

        return $query;
    }

    public function findNotificationWeek (){
        $timezone = new \DateTimeZone('Asia/Ho_Chi_Minh');
        $now = Carbon::now()->setTimezone($timezone);
//        $from = Carbon::now()->setTimezone($timezone)->subMinutes(4);
        $from = Carbon::now()->setTimezone($timezone);
        return $this->model->where('status', Notification::STATUS_READY)
            ->where('send_type', Notification::TYPE_EVERY_WEEK)
//                ->where('send_date', '2022-10-26 10:23:33')->get();
            ->whereRaw('(last_run is null or date(last_run) != '. "'{$now->toDateString()}')")
            ->whereRaw('dayofweek(send_date) = dayofweek(\''. $now . '\')')
            ->whereRaw('hour(send_date) = '. $now->hour)
            ->whereRaw('minute(send_date) between '. $from->minute . ' and '. $now->minute)->get();
    }

    public function findNotificationMonth(){
        $timezone = new \DateTimeZone('Asia/Ho_Chi_Minh');
        $now = Carbon::now()->setTimezone($timezone);
//        $from = Carbon::now()->setTimezone($timezone)->subMinutes(4);
        $from = Carbon::now()->setTimezone($timezone);
        $day = Carbon::now()->setTimezone($timezone)->day;
//        $str_sql = 'IF (last_day(send_date) = date(send_date) OR date(\'' . $now->toDateString() . '\') = last_day(\'' . $now->toDateString() .'\'),
//                    IF (day(last_day(send_date)) > day (\'' .$now->toDateString().'\'), day(\'' . $now->toDateString() .'\'), day(send_date)), day(send_date)) = day(\'' .$now->toDateString() . '\')';

        return $this->model->where('status', Notification::STATUS_READY)
            ->where('send_type', Notification::TYPE_EVERY_MONTH)
            ->whereRaw('(last_run is null or date(last_run) != '. "'{$now->toDateString()}')")
            ->whereRaw('DAY(send_date) = '. $day)
            ->whereRaw('hour(send_date) = '. $now->hour)
            ->whereRaw('minute(send_date) between '. $from->minute . ' and '. $now->minute)->get();
    }
}
