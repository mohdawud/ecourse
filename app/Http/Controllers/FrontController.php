<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreSubscribeTransactionRequest;
use App\Models\SubscribeTransaction;

class FrontController extends Controller
{
    public function index()
    {
        $categories = Category::all();


        $categoriesTop = $categories->filter(function ($category, $key) {
            return ($key % 7 < 4);
        });

        $categoriesBottom = $categories->filter(function ($category, $key) {
            return ($key % 7 >= 4 && $key % 7 < 7);
        });
        $courses = Course::with('category', 'teacher', 'students')->orderByDesc('id')->get();
        return view('front.index', compact('courses', 'categoriesTop', 'categoriesBottom'));
    }

    public function details(Course $course)
    {
        return view('front.details', compact('course'));
    }

    public function category(Category $category)
    {
        $courses = $category->courses()->get();
        return view('front.category', compact('courses', 'category'));
    }


    public function learning(Course $course, $courseVideoId)
    {
        $user = Auth::user();
        if (!$user->hasActiveSubscription()) {
            return redirect()->route('front.pricing');
        }

        $video = $course->course_videos->firstWhere('id', $courseVideoId);

        $user->courses()->syncWithoutDetaching($course->id);

        return view('front.learning', compact('course', 'video'));
    }
    public function pricing()
    {
        if (Auth::user()->hasActiveSubscription()) {
            return redirect()->route('front.index');
        }
        return view('front.pricing');
    }

    public function checkout()
    {
        if (Auth::user()->hasActiveSubscription()) {
            return redirect()->route('front.index');
        }
        return view('front.checkout');
    }

    public function checkout_store(StoreSubscribeTransactionRequest $request)
    {

        $user = Auth::user();

        if (Auth::user()->hasActiveSubscription()) {
            return redirect()->route('front.index');
        }

        DB::transaction(function () use ($request, $user) {
            $validated = $request->validated();

            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $validated['user_id'] = $user->id;
            $validated['total_amount'] = 429000;
            $validated['is_paid'] = false;

            $transaction = SubscribeTransaction::create($validated);
        });

        return redirect()->route('dashboard');
    }
}
