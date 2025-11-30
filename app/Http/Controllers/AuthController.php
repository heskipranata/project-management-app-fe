<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RegisterResponse")
     *     ),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Authenticate user and return JWT token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(ref="#/components/schemas/AuthLoginResponse")
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function  login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid login'], 401);
        }

        return response()->json([
            'message' => 'Login successfully',
            'token' => $token,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Auth"},
     *     summary="Get authenticated user's profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Profile retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function profile()
    {
        $user = Auth::user();
        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : null,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Invalidate current user's token (logout)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Logged out"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function logout()
    {
        JWTAuth::parseToken()->invalidate();
        return response()->json(['message' => 'Logged out']);
    }


    /**
     * @OA\Put(
     *     path="/api/profile",
     *     tags={"Auth"},
     *     summary="Update authenticated user's profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProfileUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ], 200);
    }
}
