<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Item;
use App\Models\AdminToken;
use App\Models\User;
use App\Models\AdminNotification;
use App\Models\VendorNotification;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Events\TestEvent;
use App\Services\WhatsappService;
use App\Services\ResendService;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function test(Request $request)
    {
        // $res=WhatsappService::send('+966582566094', 'TestMessage');
        // dd($res);
        $data=[
            "title"=> "Test Email",
            "message"=> "test email message from cleanbasket using resend services",
            "user" => [
                "name" => "Test Bilal"
            ],
            "mail" =>[
                "template" => "common_mail"
            ]
        ];

        $res=ResendService::send('bilal@devicebee.com', $data);

        return view('mails.common_mail', compact('data'));
    }

    public function testAuth(Request $request)
    {
        $admin = Cache::get('admin');
        $sessionAdmin = session('admin');

        return response()->json([
            'cache_admin' => $admin ? 'exists' : 'not exists',
            'session_admin' => $sessionAdmin ? 'exists' : 'not exists',
            'cache_partner' => Cache::get('partner') ? 'exists' : 'not exists',
            'session_partner' => session('partner') ? 'exists' : 'not exists',
        ]);
    }


    public function logout(Request $request)
    {
        auth('admin')->logout();
        // unset($_SESSION['admin']);
        // dd(session('admin')->web_token);
        if (session('admin')) {
            AdminToken::where(['token'=>session('admin')->web_token])->delete();
        }
        $request->session()->forget('admin');
        \Illuminate\Support\Facades\Cache::forget('admin');
        return redirect('admin/login')->with('success',"You have successfully logged out!");
    }
    public function partnerLogout(Request $request)
    {
        // unset($_SESSION['admin']);
        $request->session()->forget('partner');
        \Illuminate\Support\Facades\Cache::forget('partner');
        return redirect('partner/login')->with('success',"You have successfully logged out!");
    }

    public function clearNotification(Request $request)
    {
        // unset($_SESSION['admin']);
        AdminNotification::whereIn('is_read',[0,1])->delete();
        return back()->with('success',"Notifications cleared!");
    }

    public function clearVendorNotification(Request $request)
    {
        // unset($_SESSION['admin']);
        VendorNotification::where('vendor_id',session('partner')->id)->delete();
        return back()->with('success',"Notifications cleared!");
    }



}
