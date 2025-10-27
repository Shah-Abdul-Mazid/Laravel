<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/create-course', [CourseController::class, 'create'])->name('courses.create');
Route::post('/create-course', [CourseController::class, 'store'])->name('courses.store');

// Optional: Show created course
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');