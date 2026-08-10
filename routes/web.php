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
use Illuminate\Support\Facades\Log;


// Rotte principali
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/profile', [UserController::class, 'profile'])->name('profile');
Route::get('/preferences', [UserController::class, 'showPreferences'])->name('preferences');
Route::get('/contents/{content}', [ContentController::class, 'show'])->name('films.show');

//Rotte per gestione di preferiti con middleware per ajax
Route::post('/favorites/toggle/{id}', [FavoriteController::class, 'toggle'])
    ->middleware('auth')
    ->name('favorites.toggle');


// Login & Logout
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Registrazione
Route::get('/register', [UserController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [UserController::class, 'register']);

// Feed
Route::get('/search', [FeedController::class, 'search'])->name('search');
Route::post('/like/{id}', [LikeController::class, 'toggle']);
Route::get('/api/tags', [FeedController::class, 'getTags']);

 //gestione like

// Forum - rotte pubbliche

Route::prefix('forum')->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/{tag}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/{tag}/post', [ForumController::class, 'storePost'])->middleware('auth')->name('forum.post');
});


// Forum - rotte protette da autenticazione
Route::middleware('auth')->group(function () {
    Route::post('/forum/{category:slug}/posts', [ForumController::class, 'storePost'])->name('forum.posts.store');
});

//Eliminare post da forum
Route::delete('/forum/posts/{post}', [ForumController::class, 'destroyPost'])->middleware('auth')->name('forum.posts.destroy');

// Like
Route::post('/like/{post}', [PostLikeController::class, 'toggle'])->name('posts.like')->middleware('auth');

    

// Commenti
Route::post('/posts/{post}/comments', [PostCommentController::class, 'store'])->name('posts.comments.store');
Route::delete('/comments/{comment}', [PostCommentController::class, 'destroy'])->name('comments.destroy');
    
//Commenti sui film
// Per ottenere i commenti di un contenuto
Route::get('/comments/{id}', [CommentController::class, 'index']);

// Per inviare un nuovo commento
Route::post('/comments/{id}', [CommentController::class, 'store']);

// Per eliminare un commento
Route::middleware(['auth', 'admin'])->delete('/admin/comments/{id}', [CommentController::class, 'destroy'])->name('admin.comments.destroy');

//Route per eliminazione di altro utente 
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'adminUsers'])->name('admin.users.index');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroyUser'])->name('admin.users.destroy');
});


;