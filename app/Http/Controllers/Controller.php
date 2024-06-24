<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use App\Models\Book;
use App\Models\Post;
use App\Models\Order;
use App\Models\PostComment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Comment;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function register(Request $request)
    {
         $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'country' => 'required',
            'phone' => 'required|unique:users',
            'role' => 'required'
        ]);

        
      
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->password = Hash::make($request->password);
        $user->country = $request->country;
        $user->save();
        $userId = $user->id;

       
        return response(
            [
                 'user' => $user,
                 'message' => "User account successfully created",
          ]);
    }

    public function login(Request $request){
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);
        if (!auth()->attempt($data)) {
            return response(['message' => 'Incorrect Details.Please try again'],400);  
        }
        else{
            $user = User::where('email', $request->email)->with('books')->first();
            $books = Book::all();
            $orders = Order::where('id',$user->id)->get();
            $token = $user->createToken('API Token')->plainTextToken;
            $posts = Post::all();
            return response([
                "message" => 'Login successful',
                "user" => $user,
                "token" => $token,
                "available_books" => count($books),
                "purchased_books" => count($orders),
                "posts" => count($posts)
            
            ]);
        }

    }

    public function updateAccount(Request $request){
        $name = $request->name;
        $phone = $request->phone;
        $id = $request->id;
        User::where('id',$id)->update([
          'name'  => $name,
          'phone' => $phone
        ]);
        $user = User::where('id',$id)->first();
  
        return response([ 
               'message' => "Successfully updated user account",
               'user' => $user
          ]);
      }
  
      public function updateAccountImage(Request $request){
         $id = $request->id;
         $profile = $request->file('profile');
  
         $profile_img_uploaded = $profile->store('profile/', 'public');
         $profile_img_uploaded_path = Storage::url($profile_img_uploaded); 
  
         User::where('id',$id)->update([
          'image'  => $profile_img_uploaded_path
         ]);
  
         return response([ 
          'message' => "Successfully updated user profile image",
          'path' => $profile_img_uploaded_path
         ]);
      }
  
      public function addBook(Request $request){
          $title = $request->title;
          $cover = $request->file('cover_img');
          $pdf = $request->file('pdf') ;
          $intro = $request->intro;
          $foreign = $request->foreign_price;
          $price = $request->price;
          $author = $request->author;
          $page_numbers = $request->page_number;
        
          $cover_img_uploaded = $cover->store('cover_img', 'public');
          $pdf_upload = $pdf->store('books', 'public');
          $cover_img_uploaded_path = Storage::url($cover_img_uploaded);
          $pdf_upload_path = Storage::url($pdf_upload);
  
          $book = new Book;
          $book->title = $title;
          $book->cover_img = $cover_img_uploaded_path;
          $book->intro = $intro;
          $book->foreign_price = $foreign;
          $book->author = $author;
          $book->price = $price;
          $book->rating = 0;
          $book->page_number = $page_numbers;
          $book->rating_number = 0;
          $book->file =$pdf_upload_path;
          $save = $book->save();
         
   
          if($save){
           return response([ 'message' => "Successfully added book"]);
          }else{
           return response([ 'message' => "Failed to add book"],400);
          }
      }
  
      public function getBooks(){
          return Book::all(); 
      }
  
      public function changeBookPrice(Request $request){
          $id = $request->id;
          $price = $request->price;
          $dollar = $request->dollar;
          Book::where('id',$id)->update([
              'price'  => $price,
              'foreign_price' => $dollar
          ]);
  
          return response([ 
            'message' => "Successfully changed book price",
            'book' => Book::where('id',$id)->first()
        ]);
      }
  
      public function buyBook(Request $request){
          
          $user_id = $request->user_id;
          $book_id = $request->book_id;
          $total = $request->total;
          $reference = $request->reference;
  
          $res = Order::where('user_id',$user_id)->where('book_id',$book_id)->get();
  
          if(count($res) > 0){
              return response([ 'message' => "Already purchase book"],400);
          }
          else{
              $order = new Order;
              $order->user_id = $user_id;
              $order->book_id = $book_id;
              $order->total = $total;
              $order->payment_status = 'paid';
              $order->reference = $reference;
              $save = $order->save();
      
              if($save){
                return response([ 'message' => "Successfully purchased book"]);
              }else{
                return response([ 'message' => "Failed to purchase book"],400);
              }
          }
  
         
      }
  
      public function paginate($items, $perPage = 10, $page = null, $options = [])
      {
          $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
          //$items = $items instanceof Collection ? $items : Collection::make($items);
          return new LengthAwarePaginator(collect($items)->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, $options);
      }
  
      public function getOrders(){
          return $this->paginate(Order::with(['user','book'])->get());
      }
  
      public function getMyBooks(String $id){
        $myBooks = Order::where('user_id',$id)->with('book')->get();
        return $myBooks;
      }
  
      public function searchBook(String $keyword){
          
        return Book::query()->where('title', 'LIKE', "%{$keyword}%")->get();
      }
  
      public function getUsers(){
          return $this->paginate(User::orderBy('created_at', 'DESC')->get());
      }
  
      public function groupUsersByCountry(){
         $group =  User::select('country', DB::raw('COUNT(*) as count'))
          ->groupBy('country')
          ->get();
          return $group;
      }
  
      public function mostBoughtBooks(){
        $res =   Order::select('book_id', DB::raw('COUNT(*) as count'))->with('book')
                  ->groupBy('book_id')->orderBy(DB::raw('COUNT(book_id)'), 'DESC')->paginate(6);
        //->orderBy(DB::raw('COUNT(book_id)'), 'DESC')
        return $res;          
      } 
  
      public function postComment(Request $request){
          $userId = $request->user_id;
          $bookId = $request->book_id;
          $review = $request->review;
          $rating = $request->rating;
          $type = $request->type;
  
          if($type == "post"){
              $comment = new Comment;
              $comment->user_id = $userId;
              $comment->book_id = $bookId;
              $comment->comment = $review;
              $comment->rating = $rating;
              $save = $comment->save();
  
              //Book id
              $commentList = Comment::where('book_id',$bookId)->get();
              $length = sizeof($commentList);
              $rate = 0;
              foreach($commentList as $c ){
                  $rate = $rate + $c['rating'];
              }
              
              $oldRating = $rate/$length;
              $newRating =  number_format((float)$oldRating, 2, '.', ''); 
  
              //Get book rating number
              $ratedBook = Book::where('id',$bookId)->first();
              $ratingNumber = $ratedBook->rating_number;
              $newRatingNumber = $ratingNumber + 1;
  
              //Update rating
              Book::where('id',$bookId)->update([
                  'rating'  => $newRating,
                  'rating_number' => $newRatingNumber
              ]);
          
  
              if($save){
                  return response([ 'message' => "Successfully posted comment"]);
              }else{
                  return response([ 'message' => "Failed to post comment"],400);
              }
          }
          else{
              Comment::where('book_id',$bookId)->where('user_id',$userId)->update([
                  'comment'  => $review,
                  'rating' => $rating
              ]);
              $commentList = Comment::where('book_id',$bookId)->get();
              $length = sizeof($commentList);
              $rate = 0;
              foreach($commentList as $c ){
                  $rate = $rate + $c['rating'];
              }
              
              $oldRating = $rate/$length;
              $newRating =  number_format((float)$oldRating, 2, '.', ''); 
  
              Book::where('id',$bookId)->update([
                  'rating'  => $newRating,
              ]);
              return response([ 'message' => "Successfully updated comment"]);
          }
  
          
  
      }
  
      public function getUserComment(Request $request){
          $userId = $request->user_id;
          $bookId = $request->book_id;
          $comment = Comment::where('user_id',$userId)->where('book_id',$bookId)->get();
       
         
  
          if(sizeof($comment) == 1){
              return response([
                  "message" => "present",
                  "comment" => Comment::where('user_id',$userId)->where('book_id',$bookId)->first()
              ]);
          }else{
              return response([
                  "message" => "absent",
                  "comment" => "N/A"
              ]);
          }
  
      }
  
  
      public function getBookComments(String $book_id){
       
          $fiveStar = Comment::where('book_id',$book_id)->where('rating',5)->get();
          $fourStar = Comment::where('book_id',$book_id)->where('rating',4)->get();
          $threeStar = Comment::where('book_id',$book_id)->where('rating',3)->get();
          $twoStar = Comment::where('book_id',$book_id)->where('rating',2)->get();
          $oneStar = Comment::where('book_id',$book_id)->where('rating',1)->get();
  
          $comments = Comment::where('book_id',$book_id)->with('user')->orderBy('created_at', 'desc')->get();
         
          if(sizeof($comments) > 0){
              $fiveStarFraction = sizeof($fiveStar)/sizeof($comments);
              $fourStarFraction = sizeof($fourStar)/sizeof($comments);
              $threeStarFraction = sizeof($threeStar)/sizeof($comments);
              $twoStarFraction = sizeof($twoStar)/sizeof($comments);
              $oneStarFraction = sizeof($oneStar)/sizeof($comments);
  
              return response([
                  "fiveStar" => $fiveStarFraction,
                  "fourStar" => $fourStarFraction,
                  "threeStar" => $threeStarFraction,
                  "twoStar" => $twoStarFraction,
                  "oneStar" => $oneStarFraction,
                  "comments" => $comments
              ]);
          }
          else{
              return response([
                  "fiveStar" => 0,
                  "fourStar" => 0,
                  "threeStar" => 0,
                  "twoStar" => 0,
                  "oneStar" => 0,
                  "comments" => $comments
              ]);
          }
         
  
       
         
      }
  
      public function validateOTP(Request $request){
        $otp = $request->otp;
        $reference = $request->reference;
  
        $body = [
          "otp" => $otp, 
          "reference"=> $reference
        ];
  
        $response = Http::withHeaders([
              'Authorization' =>  'Bearer '.getenv('PAYSTACK_CLIENT_SECRET'),
              'Content-Type' => 'application/json' 
            ]
          )->post('https://api.paystack.co/charge/submit_otp',$body);
  
          return $response;
      }
  
      public function initiateMoMoPayment(Request $request){
        $amount = $request->amount;
        $email = $request->email;
        $phone = $request->phone;
        $network = $request->network;
        $actual_amount = $amount * 100;
        $body = [
          "amount" => $actual_amount,
          "email"  => $email,
          "currency"=> "GHS",
          "mobile_money"  => [
             "phone" => $phone,
             "provider" => $network
          ]
          ];
        $response  = Http::withHeaders([
          'Authorization' =>  'Bearer '.getenv('PAYSTACK_CLIENT_SECRET'),
          'Content-Type' => 'application/json' 
        ])->post('https://api.paystack.co/charge',$body);
        
        return $response;  
       
      }
  
      public function verifyTransaction(String $id){
        $response  = Http::withHeaders([
          'Authorization' =>  'Bearer '.getenv('PAYSTACK_CLIENT_SECRET'),
        ])->get('https://api.paystack.co/transaction/verify/'.$id);
        return $response;
      }
  
      public function genereateCheckoutUrl(Request $request){
        
        $email = $request->email;
        $amount = $request->amount;
        $currency = $request->currency;
        if($currency == "USD"){
            $amount = ($amount * 15) * 100;
        }
        else{
            $amount = $amount * 100;
        }
  
        $body = [
          "email" => $email,
          "amount" => $amount,
         
        ];
  
        $response  = Http::withHeaders([
          'Authorization' =>  'Bearer '.getenv('PAYSTACK_CLIENT_SECRET'),
          'Content-Type' => 'application/json' 
        ])->post('https://api.paystack.co/transaction/initialize',$body);
  
        return $response;
        
  
      }
  
      public function addPost(Request $request){
          $description = $request->description;
          $cover = $request->file('cover_img');
          $cover_img_uploaded_path = "";
          if ($request->hasFile('cover_img')) {
              $cover_img_uploaded = $cover->store('posts', 'public');
              $cover_img_uploaded_path = Storage::url($cover_img_uploaded);
          }
         
  
          $post = new Post;
          $post->image = $cover_img_uploaded_path;
          $post->content = $description;
          $post->likes = 0;
          $save = $post->save();
         
   
          if($save){
           return response([ 'message' => "Successfully added post"]);
          }else{
           return response([ 'message' => "Failed to add post"],400);
          }
  
         
      }
  
      public function getPosts(){
          return $this->paginate(Post::orderBy('created_at', 'desc')->get());
      }
  
      public function deletePost(String $id){
        $post = Post::where('id',$id)->first();
        $imagePath = $post->image;
        $path = "public".substr($imagePath,8);
  
         if(Storage::exists($path)){
              Storage::delete($path);
              Post::where('id',$id)->delete();
              return response([ 'message' => "Successfully deleted post"],200);
              /*
                  Delete Multiple File like this way
                  Storage::delete(['upload/test.png', 'upload/test2.png']);
              */
          }else{
            return response([ 'message' => "The image path does not exist ".$path],400);
          }
  
      }

      public function getBookFile(String $id){
        $bookSelected = Book::where('id',$id)->first();
        $bookPath = $bookSelected->file;
       $path = "public".substr( $bookPath ,8);
       $filePath = public_path($bookPath);
       if(Storage::exists($path)){
         
           return response()->file( $filePath);
           //return response([ 'message' => "Book Found "],200); 
       }else{
           return response([ 'message' => "There is no file to return "],400); 
       }
       
      }
  
      public function conmmentOnPost(Request $request){
         $post_id = $request->post_id;
         $user_id = $request->user_id;
         $comment = $request->comment;
  
         $commentOnPost = new PostComment;
         $commentOnPost->post_id = $post_id;
         $commentOnPost->user_id = $user_id;
         $commentOnPost->comment = $comment;
         $save = $commentOnPost->save();
  
         if($save){
          return response([ 'message' => "Successfully commented on post"]);
         }else{
          return response([ 'message' => "Failed to add post"],400);
         }
      }
  
      public function getCommentsOnPost(String $post_id){
          return PostComment::where("post_id",$post_id)->with('user')->orderBy('created_at', 'desc')->get();
      }
  
      public function deleteCommentPost(String $user_id){
          PostComment::where('user_id',$user_id)->delete();
          return response([ 'message' => "Success"]);
      }
  
      public function editCommentOnPost(Request $request){
          $comment = $request->comment;
          $comment_id = $request->comment_id;
  
          PostComment::where('id',$comment_id)->update([
              'comment'  => $comment,
          ]);
          return response([ 'message' => "Success"]);
      }

      public function getUserBookPurchaseStatus(String $id){
        $books = Book::all();
        $orders = Order::where('id',$id)->get();

        return response(
          [
            "available_books" =>  count($books),
            "purchased_books" => count($orders)
          ]

        );
      }
  
      
}
