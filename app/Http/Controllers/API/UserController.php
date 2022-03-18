<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Notifications\RegisterNotification;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Support\Facades\Notification;

class UserController extends Controller
{
    public function UploadImage(Request $request){
        $this->validate($request,[
            'image' => 'required',

        ]);
        $user = auth()->user();
        if(!$user->avatar == 'avatar.png'){
            $currentphoto = $user->avatar;
        }else{
            $currentphoto = '';
        }
        $name = str_replace(' ', '',$user->fullname).time(). '.jpg';
        Image::make($request->image)->save(public_path('/assets/profile/').$name);
        $request->merge(['photo' => $name]);

        $storePhoto = public_path('/assets/profile/').$currentphoto;
        if(file_exists($storePhoto)){
            @unlink($storePhoto);
        }

        $user->avatar = $request->photo;

        $user->save();


        return response()->json([
            'message' => 'uploaded',
            'image' => $user->avatar
        ],200);

    }

    public function allUsers(){
        $users = User::latest()->limit(100)->get();

        return response()->json([
            'status' => true,
            'users' => $users
        ], 200);
    }
    public function validateEmail(Request $request){
        $check = User::where('email', $request->email)->exists();
        if($check){
            return response()->json([
                'status' => true
            ],404);
        }else{
            return response()->json([
                'status' => false
            ], 200);
        }
    }

    public function logout(){
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'Logged out'
        ],200);
    }


    public function user(){
        $user = auth()->user();
        $user->notifications = $user->unReadNotifications;
        $user->notifications->each(function($notification){
            if($notification->type == "App\Notifications\RegisterNotification"){
                $notification->type_noti = "Register";
            }
            return $notification;
        });
        $user->groups;
        return response()->json([
            'status'=> true,
            'user' => $user,
        ]);
    }

    public function groups(){
        $user = auth()->user();
        $groups = $user->groups;
        return response()->json([
            'status'=> true,
            'groups' => $groups,
        ]);
    }

    public function belongToGroups(){
        $user = auth()->user();
        $groups = $user->getUserGroups();

        return response()->json([
            'status'=> true,
            'groups' => $groups,
        ]);
    }


    public function forgot(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first(['email', 'fullname']);
        if(!$user){
            return response()->json([
                'status'=> false,
                'error' => 'user not found',
            ],404);
        }else{
            $code = rand(10000, 99999);
            $codes = DB::table('password_resets')->where('email', $user->email)->delete();
            $data = ['email' => $user->email, 'token' => $code, 'created_at' => Carbon::now(), 'expired_at' => Carbon::now()->addMinutes(15)];
            $insert = DB::table('password_resets')->insert($data);
            $expire = Carbon::now()->addMinutes(15)->format('d M Y H:i:A');
            try {
                $sendMail = \Mail::send('emails.otp',['user' => $user, 'code' => $code, 'expire' => $expire],function ($message) use($user){
                    $subject = "Your One Time Password";
                    $message->to($user->email, $user->fullname);
                    $message->subject($subject);
                    $message->from('no-reply@flurrypay.com', 'Flurry Pay');
                });
            } catch (\Throwable $th) {
                return $th;
            }
            return response()->json([
                'status'=> false,
                'user' => $user
            ],200);
        }
    }

    public function store(CreateUserRequest $request)
    {
        $user = new User();
        $user->fullname = $request->fullname;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        $token =  $user->createToken($user->email .'Personal Access Token')->plainTextToken;
        // Notification::send($user, new RegisterNotification($user));
        return response()->json([
            'status'=> true,
            'user' => $user,
            'token' => $token
        ],200);

    }

    public function login(LoginRequest $request){
        if(auth()->attempt(['email' => $request->email, 'password' => $request->password])){
            $user = auth()->user();
            $token =  $user->createToken($user->email .' Personal Access Token')->plainTextToken;
            return response()->json([
                'status'=> true,
                'token' => $token,
                'user' => $user,
            ], 200);
        }
        else{
            return response()->json([
                'status'=> false,
                'error'=>'Unauthorised'
            ], 401);
        }
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $user->update($request->all());
        return response()->json([
            'status' => true,
            'user' => $user
        ],200);
    }

    public function password(ResetPasswordRequest $request){
        $code = DB::table('password_resets')->where('token', $request->code)->first();
        $expired_at_date =$code->expired_at;
        $current_date = Carbon::now();
        $diff = $current_date->diffInMinutes($expired_at_date);
        if($diff > 15){
            return response() ->json([
                'status'=> false,
                'message' => 'Token expired'
            ], 404);
        }else{
            $user = User::where('email', $code->email)->first();
            $user->password = bcrypt($request->password);
            if($user->save()){
                return response()->json([
                    'status'=> true,
                    'message' => 'Password Changed',
                    'user' => $user
                ], 200);
            }
        }
    }

}
