<?php

namespace App\Http\Controllers;

use App\Events\StoreMessageEvent;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\User\UserResource;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        $users = UserResource::collection($users)->resolve();

        return response()->json(compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string',
        ]);

        $userIds = array_merge($data['users'], [auth()->id()]);

        // sort in ascending order
        sort($userIds);

        $stringIds = implode('-', $userIds);

        try {
            DB::beginTransaction();

            $chat = Chat::firstOrCreate([
                'users' => $stringIds
            ], [
                'title' => $data['title']
            ]);

            $chat->users()->sync($userIds);

            broadcast(new StoreMessageEvent($data))->toOthers();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
        }

        $chat = ChatResource::make($chat)->resolve();

        return Inertia::render('Chat/Show', compact('chat'));
    }
}
