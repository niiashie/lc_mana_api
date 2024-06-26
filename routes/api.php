<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register',[Controller::class,'register']);
Route::post('/login',[Controller::class,'login']);
Route::post('/security',[Controller::class,'confirmQuestions']);
Route::post('/resetPassword',[Controller::class,'resetPassword']);

Route::middleware('auth:sanctum')->post('/updateAccountImage',[Controller::class,'updateAccountImage']);
Route::middleware('auth:sanctum')->post('/updateAccount',[Controller::class,'updateAccount']);
Route::middleware('auth:sanctum')->post('/addBook',[Controller::class,'addBook']);
Route::middleware('auth:sanctum')->get('/getBooks',[Controller::class,'getBooks']);
Route::middleware('auth:sanctum')->post('/changeBookPrice',[Controller::class,'changeBookPrice']);
Route::middleware('auth:sanctum')->post('/buyBook',[Controller::class,'buyBook']);
Route::middleware('auth:sanctum')->get('/getOrders',[Controller::class,'getOrders']);
Route::middleware('auth:sanctum')->get('/getMyBooks/{id}',[Controller::class,'getMyBooks']);
Route::middleware('auth:sanctum')->get('/searchBook/{keyword}',[Controller::class,'searchBook']);
Route::middleware('auth:sanctum')->get('/getUsers',[Controller::class,'getUsers']);
Route::middleware('auth:sanctum')->get('/groupUsers',[Controller::class,'groupUsersByCountry']);
Route::middleware('auth:sanctum')->get('/mostBoughtBooks',[Controller::class,'mostBoughtBooks']);
Route::middleware('auth:sanctum')->post('/postComment',[Controller::class,'postComment']);
Route::middleware('auth:sanctum')->get('/getUserComment/{user_id}/{book_id}',[Controller::class,'getUserComment']);
Route::middleware('auth:sanctum')->get('/getBookComments/{book_id}',[Controller::class,'getBookComments']);
Route::middleware('auth:sanctum')->post('/initiateMoMoPayment',[Controller::class,'initiateMoMoPayment']);
Route::middleware('auth:sanctum')->post('/validateOTP',[Controller::class,'validateOTP']);
Route::middleware('auth:sanctum')->get('/verifyTransaction/{id}',[Controller::class,'verifyTransaction']);
Route::middleware('auth:sanctum')->post('/generateCheckoutUrl',[Controller::class,'genereateCheckoutUrl']);
Route::middleware('auth:sanctum')->post('/addPost',[Controller::class,'addPost']);
Route::middleware('auth:sanctum')->get('/getPosts',[Controller::class,'getPosts']);
Route::middleware('auth:sanctum')->get('/deletePost/{id}',[Controller::class,'deletePost']);
Route::middleware('auth:sanctum')->post('/commentOnPost',[Controller::class,'conmmentOnPost']);
Route::middleware('auth:sanctum')->get('/getCommentsOnPost/{post_id}',[Controller::class,'getCommentsOnPost']);
Route::middleware('auth:sanctum')->get('/deleteCommentPost/{user_id}',[Controller::class,'deleteCommentPost']);
Route::middleware('auth:sanctum')->post('/editCommentOnPost',[Controller::class,'editCommentOnPost']);
Route::middleware('auth:sanctum')->get('/getBookFile/{id}',[Controller::class,'getBookFile']);
Route::middleware('auth:sanctum')->get('/getUserBookPurchaseStatus/{id}',[Controller::class,'getUserBookPurchaseStatus']);
