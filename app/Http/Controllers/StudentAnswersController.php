<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use Illuminate\Http\Request;

class StudentAnswersController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'studentAnswers' => 'required|array',
            'studentAnswers.*.question_id' => 'required|integer',
            'studentAnswers.*.answer_id' => 'required|integer',
        ]);

        $result = TestResult::create([
            'user_id' => auth()->id(),
            'data' => json_encode($request->studentAnswers),
        ]);

        return response()->json($result, 201);
    }
}
