<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a list of users.
     */
    public function index(): JsonResponse
    {
        $users = User::orderBy('id')->cursorPaginate(10);

        return response()->json([
            'users' => $users,
        ], Response::HTTP_OK);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request input
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', Password::min(8)->letters()->numbers()->mixedCase()],
            ]);
            // Hash the password before saving
            $validated['password'] = Hash::make($validated['password']);

            // Create the user record
            $user = User::create($validated);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
            ], Response::HTTP_CREATED);

        } catch (ValidationException $exception) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $exception->errors(), // This will return all the validation errors
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a specific user.
     *
     * @param  int  $id
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'user' => $user,
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update a user.
     *
     * @param  int  $id
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
        ]);

        try {
            $user = User::findOrFail($id);
            $user->update($validated);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user,
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a user by their ID.
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'User deletion failed',
                'error' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
