<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Validated;
use Illuminate\Contracts\Cache\Store;
use App\Http\Requests\StoreTeacherRequest;
use Illuminate\Validation\ValidationException;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = Teacher::orderBy('id', 'desc')->get();
        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $validated = $request->validated();


        $user = User::where('email', $validated['email'])->first();



        if (!$user) {
            return back()->withErrors(['email' => 'User not found']);
        }

        if ($user->hasRole('teacher')) {
            return back()->withError(['email' => 'User already has a teacher role']);
        }

        DB::transaction(function () use ($user, $validated) {
            $validated['user_id'] = $user->id;
            $validated['is_active'] = true;

            Teacher::create($validated);

            if ($user->hasRole('student')) {
                $user->removeRole('student');
            }

            $user->assignRole('teacher');
        });

        return redirect()->route('admin.teachers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        try {
            $user = User::find($teacher->user_id);
            $teacher->delete();
            $user->removeRole('teacher');
            $user->assignRole('student');

            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessages([
                'system_error' => ['System Error!' . $e->getMessage()],
            ]);
            throw $error;
        }
    }
}
