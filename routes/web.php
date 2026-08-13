<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\CommentController;

// ============ PRINCIPALI ============
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/profile', [UserController::class, 'profile'])->name('profile');
Route::get('/preferences', [UserController::class, 'showPreferences'])->name('preferences');
Route::get('/contents/{content}', [ContentController::class, 'show'])->name('films.show');

// ============ AUTH ============
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/register', [UserController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [UserController::class, 'register']);

// ============ FEED & SEARCH ============
Route::get('/search', [FeedController::class, 'search'])->name('search');
Route::get('/api/tags', [FeedController::class, 'getTags']);

// ============ FORUM ============
Route::prefix('forum')->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/{tag}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/{tag}/post', [ForumController::class, 'storePost'])
        ->middleware('auth')
        ->name('forum.post');
});

Route::delete('/forum/posts/{post}', [ForumController::class, 'destroyPost'])
    ->middleware('auth')
    ->name('forum.posts.destroy');

// ============ LIKE SUI POST DEL FORUM ============
Route::post('/posts/{post}/like', [PostLikeController::class, 'toggle'])
    ->middleware('auth')
    ->name('posts.like');

// ============ COMMENTI SUI POST DEL FORUM ============
Route::post('/posts/{post}/comments', [PostCommentController::class, 'store'])
    ->middleware('auth')
    ->name('posts.comments.store');

Route::delete('/posts/comments/{comment}', [PostCommentController::class, 'destroy'])
    ->middleware('auth')
    ->name('posts.comments.destroy');

// ============ LIKE SUI FILM ============
Route::post('/contents/{id}/like', [LikeController::class, 'toggle'])
    ->middleware('auth')
    ->name('like.toggle');

// ============ COMMENTI SUI FILM ============
Route::get('/comments/{id}', [CommentController::class, 'index'])
    ->name('comments.index');

Route::post('/comments/{id}', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('comments.store');

Route::delete('/contents/comments/{comment}', [CommentController::class, 'destroy'])
    ->middleware('auth')
    ->name('comments.destroy');

// ============ PREFERITI ============
Route::post('/favorites/toggle/{id}', [FavoriteController::class, 'toggle'])
    ->middleware('auth')
    ->name('favorites.toggle');
Route::get('/favorites', [FavoriteController::class, 'index'])
    ->middleware('auth')
    ->name('favorites.index');

// ============ ADMIN ============
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'adminUsers'])->name('admin.users.index');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::delete('/admin/comments/{comment}', [CommentController::class, 'destroy'])->name('admin.comments.destroy');
});