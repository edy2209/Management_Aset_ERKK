<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;

class RatingController extends Controller
{
    // Cek apakah user sudah isi rating
    public function check(Request $request)
    {
        $userId = $request->input('user_id');
        $hasRated = Rating::where('user_id', $userId)->exists();
        return response()->json(['hasRated' => $hasRated]);
    }

    // Simpan rating user
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:peminjams,id',
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        if (Rating::where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'You have already rated.'], 409);
        }

        $rating = Rating::create([
            'user_id' => $request->user_id,
            'rating' => $request->rating,
            'feedback' => $request->feedback,
        ]);

        return response()->json(['success' => true, 'rating' => $rating]);
    }

    //get data rating
    public function index()
    {
        $ratings = Rating::with('user')->latest()->get(); // gunakan eager loading jika relasi dengan user
        return response()->json(['ratings' => $ratings]);
    }

}