<?php

namespace App\Http\Controllers;

use Auth;
use Storage;
use App\Post;
use App\Comment;
use App\Like;
use App\User;
use App\ReportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @SWG\Get(
     *      path="/api/v1/post",
     *      summary="Retrieves the collection of Post resources.",
     *      produces={"application/json"},
     *      tags={"Post"},
     *      @SWG\Response(
     *          response=200,
     *          description="Posts collection.",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(ref="#/definitions/post")
     *          )
     *      ),
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized action."
     *      ),
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="Example = Bearer(space)'your_token'",
     *          in="header",
     *          required=true,
     *          type="string",
     *          default="Bearer" 
     *      )    
     * )
     */
    public function index()
    {
        $posts = Post::paginate();
        return $posts;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'image' => 'image|required',
            'taste' => 'numeric|required',
            'price' => 'numeric|required',
            'service' => 'numeric|required',
            'description' => 'required',
        ]);
        if($request->hasFile('image')){
            if($request->image->isValid()){
                $path = $request->image->store('public/post/' . Auth::user()->id);
                $post = new Post();
                $post->user()->associate(Auth::user()->id);
                $post->merchant()->associate($request->merchant_id);
                $post->path = $path;
                $post->description = $request->description;
                $post->taste = $request->taste;
                $post->price = $request->price;
                $post->service = $request->service;
                $post->save();
                return $post;
            }
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    /**
     * @SWG\Post(
     *      path="/api/v1/post/{postId}/addComment",
     *      summary="Add comment in posting image.",
     *      produces={"application/json"},
     *      consumes={"application/json"},
     *      tags={"Comment"},
     *      @SWG\Response(
     *          response=200,
     *          description="Comment has successfully added.",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(ref="#/definitions/comment")
     *          )
     *      ),
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized action."
     *      ),
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="Example = Bearer(space)'your_token'",
     *          in="header",
     *          required=true,
     *          type="string",
     *          default="Bearer",
     *      ),
     *      @SWG\Parameter(
     *           name="postId",
     *           in="path",
     *           description="Please enter the postId",
     *           required=true, 
     *           type="integer"
     *      ),  
     *      @SWG\Parameter(
     *           name="body",
     *           in="body",
     *           required=true,
     *           description="Comment that needs to be added into posting image", 
     *           type="string",
     *           @SWG\Schema(
     *               @SWG\Property(
     *                   property="comment",
     *                   type="string"
     *               ) 
     *           )
     *      )             
     * )
     */
    public function addComment(Request $request, $id)
    {
        $post = Post::find($id);
        if(empty($post)){
            return response()->json(['message' => 'Post ID not found'], 404);
        }
        $comment = new Comment();
        $comment->post_id = $post->id;
        $comment->user_id = Auth::id();
        $comment->comment = $request->comment;
        $comment->save();

        return response()->json($comment);
    }

    /**
     * @SWG\Get(
     *      path="/api/v1/post/{postId}/comments",
     *      summary="Show all comments by Post's Id.",
     *      produces={"application/json"},
     *      tags={"Comment"},
     *      @SWG\Response(
     *          response=200,
     *          description="This is the data that you search.",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(ref="#/definitions/comment")
     *          )
     *      ),
     *      @SWG\Response(
     *          response=400,
     *          description="Invalid ID."
     *      ),
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized action."
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Post ID not found."
     *      ),
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="Example = Bearer(space)'your_token'",
     *          in="header",
     *          required=true,
     *          type="string",
     *          default="Bearer",
     *      ),
     *      @SWG\Parameter(
     *           name="postId",
     *           description="Please enter the postId",
     *           in="path",
     *           required=true, 
     *           type="integer"
     *      )              
     * )
     */
    public function showComments($id)
    {
        $post = Post::find($id);
        if(empty($post)){
            return response()->json(['message' => 'Post ID not found'], 404);
        }
        $comments = Comment::where('post_id', $post->id)->get();
        
        return response()->json($comments->toArray());
    }

    /**
     * @SWG\Delete(
     *      path="/api/v1/post/{postId}/deleteComment/{commentId}",
     *      summary="Remove the comment from posting image.",
     *      produces={"application/json"},
     *      tags={"Comment"},
     *      @SWG\Response(
     *          response=204,
     *          description="Comment has been deleted."
     *      ),
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized action."
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Post ID or Comment ID not found."
     *      ),
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="Example = Bearer(space)'your_token'",
     *          in="header",
     *          required=true,
     *          type="string",
     *          default="Bearer",
     *      ),
     *      @SWG\Parameter(
     *           name="postId",
     *           description="Please enter the postId",
     *           in="path",
     *           required=true, 
     *           type="integer"
     *      ),
     *      @SWG\Parameter(
     *           name="commentId",
     *           description="Please enter the commentId",
     *           in="path",
     *           required=true, 
     *           type="integer"
     *      )             
     * )
     */
    public function deleteComment($id, $comment_id)
    {   
        $post = Post::find($id);
        if(empty($post)){
            return response()->json(['message' => 'Post ID not found'], 404);
        }

        $comment = Comment::find($comment_id);
        if(empty($comment)){
            return response()->json(['message' => 'Comment ID not found'], 404);
        }

        $checkPost = Post::where('user_id', Auth::id())->count();
        $checkComment = Comment::where('user_id', Auth::id())
                               ->where('post_id', $post->id)
                               ->where('user_id','!=', $post->user_id)
                               ->count();
        
        if($checkPost != 0)
        {
            $deleteComment = Comment::where('id', $comment->id)->delete();
            
            return response()->json(['message' => 'The comment has been deleted'], 204);
        } 
        else if($checkPost == 0 && $checkComment != 0)
        {
            $deleteComment = Comment::where('id', $comment->id)
                                    ->where('user_id', Auth::id())
                                    ->delete();
            return response()->json(['message' => 'Your comment has been deleted'], 204);
        }
        else if($checkPost == 0 && $checkComment == 0)
        {
            return response()->json(['message' => 'This is not your comment!'], 403);
        }
        
    }

    public function showLikes($id)
    {
        $post = Post::find($id);
        $likes = Like::where('post_id', $post->id)
                     ->where('like', 1)
                     ->count();

        return $likes . ' likes.';
    }

    public function like($id)
    {
        $post = Post::find($id);
        $likes = Like::where('user_id', Auth::id())
                     ->where('post_id', $post->id)
                     ->count();

        if($likes == 0)
        {
            $like = new Like();
            $like->post_id = $post->id;
            $like->user_id = Auth::id();
            $like->like = 1;
            $like->save();
            
            return $like;
        } 
        else
        {
            $likes = Like::where('user_id', Auth::id())
                         ->where('post_id', $post->id)
                         ->where('like', 1)
                         ->count();
            
            if($likes != 0)
            {
                $likeOrUnlike = Like::where('user_id', Auth::id())
                                    ->where('post_id', $post->id)
                                    ->where('like', 1)
                                    ->update(array('like' => 0));

                return $likeOrUnlike;
            }
            else
            {
                $likeOrUnlike = Like::where('user_id', Auth::id())
                                    ->where('post_id', $post->id)
                                    ->where('like', 0)
                                    ->update(array('like' => 1));

                return $likeOrUnlike;
            }
        }
        
    }


    public function report($id)
    {
        $post = Post::find($id);
        if(empty($post)){
            return response()->json(['message' => 'Post ID not found'], 404);
        }
        $reports = ReportCategory::get(['id', 'name']);
        
        return response()->json($reports->toArray());
    }

}
