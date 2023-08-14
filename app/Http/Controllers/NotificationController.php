<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    public function getNotiByUser(Request $request, $user_id){
        if($request->user()->id == $user_id){
            $notis = Notification::query();
            $notis = $notis->where('user_id', $user_id);
            if($request->unread) $notis->whereNull('read_at');
            $notis = $notis->orderByDesc('id')->get();
            return $this->jsonResponse(true, "Get notifications by user", $notis);
        }
    }

    public function readNotification(Request $request, $id){
        
        $noti = Notification::findOrFail($id);
        if($request->user()->id == $noti->user_id){
            $noti->read_at = Carbon::now();
            $noti->save();
        } else
            return $this->jsonResponse(false, "Fobidden", [], 403);
    }

    public function countUnreadNotiByUser(Request $request){
        $count = Notification::where('user_id', $request->user()->id)
                                ->whereNull('read_at')
                                ->get()
                                ->count();
        return $this->jsonResponse(true, "Count unread notification", $count);

    }
}
