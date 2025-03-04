<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request){
        $userId = Auth::id();
        $data = $request->input('data');
        $type = $request->input('type');
        event(new NewNotification($userId, $type, $data));
        return response()->json(['success' => true]);
    }
}
