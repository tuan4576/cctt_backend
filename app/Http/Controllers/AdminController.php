<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    // public function index()
    // {
    //     try {
    //         // Authenticate the user
    //         if (!$user = JWTAuth::parseToken()->authenticate()) {
    //             return response()->json(['error' => 'User not found'], 404);
    //         }

    //         // Check if the authenticated user has role_id 1
    //         if ($user->role_id !== 1) {
    //             return response()->json(['error' => 'Unauthorized. Only users with role_id 1 can view this.'], 403);
    //         }

    //         // Fetch all users
    //         $users = User::all();
            
    //         return response()->json(['users' => $users], 200);
    //     } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    //         return response()->json(['error' => 'Token expired'], 401);
    //     } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
    //         return response()->json(['error' => 'Token invalid'], 401);
    //     } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
    //         return response()->json(['error' => 'Token absent'], 401);
    //     }
    // }
    public function register(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->json()->get('name'),
            'email' => $request->json()->get('email'),
            'password' => Hash::make($request->json()->get('password')), // Hashing the password
            'photo' => 'defaultuser.jpg', // Set default photo
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'token'), 201);
    }
    public function login(Request $request) {
        $credentials = $request->json()->all();
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could not create token'], 500);
        }
        $user = auth()->user();
        $photo = $user->photo; // Assuming the User model has a 'photo' attribute
        $background = $user->background; // Assuming the User model has a 'background' attribute
        return response()->json(compact('user', 'token', 'photo', 'background'));
    }
    public function getAuthenticatedUser() {
    try {
        if (!$user = JWTAuth:: parseToken()->authenticate()) {
            return response()->json(['user_not_found'], 404);
            }
    }
    catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['token_expired'], $e->getStatusCode());
    }

    catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['token_invalid'], $e->getStatusCode());
    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['token_absent'], $e->getStatusCode());
    }
        return response()->json (compact('user'));
    }
    public function updatePhoto(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $originalFileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = pathinfo($originalFileName, PATHINFO_FILENAME);

            $counter = 1;
            while (file_exists(public_path('../public/img/image/' . $originalFileName))) {
                $originalFileName = $fileName . '_' . $counter . '.' . $fileExtension;
                $counter++;
            }

            $path = $file->move(public_path('../public/img/image'), $originalFileName);

            // Delete old photo if exists and it's not the default photo
            if ($user->photo && $user->photo !== 'defaultuser.jpg') {
                $oldPhotoPath = public_path('../public/img/image/' . $user->photo);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            $user->photo = $originalFileName;
            $user->save();

            return response()->json(['photo' => $originalFileName]);
        }

        return response()->json(['error' => 'No photo provided'], 400);
    }
    public function updateBackground(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->hasFile('background')) {
            $file = $request->file('background');
            $originalFileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = pathinfo($originalFileName, PATHINFO_FILENAME);

            $counter = 1;
            while (file_exists(public_path('../public/img/image/' . $originalFileName))) {
                $originalFileName = $fileName . '_' . $counter . '.' . $fileExtension;
                $counter++;
            }

            $path = $file->move(public_path('../public/img/image'), $originalFileName);

            // Delete old background if exists and it's not the default background
            if ($user->background && $user->background !== 'defaultbackground.jpg') {
                $oldBackgroundPath = public_path('../public/img/image/' . $user->background);
                if (file_exists($oldBackgroundPath)) {
                    unlink($oldBackgroundPath);
                }
            }

            $user->background = $originalFileName;
            $user->save();

            return response()->json(['background' => $originalFileName]);
        }

        return response()->json(['error' => 'No background image provided'], 400);
    }
}
