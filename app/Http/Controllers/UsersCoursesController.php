<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Group;

class UsersCoursesController extends Controller
{
    public function index()
    {
        $groups = Group::all();
        $users = User::all();
        $courses = Course::all();

        return response()->json([
            'users' => $users,
            'groups' => $groups,
            'courses' => $courses,
        ]);
    }
}
