<?php

namespace App\Http\Controllers;

use App\Events\ClearChatEvent;
use App\Events\StoreMessageEvent;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\User\UserResource;
use App\Models\Chat;
use App\Models\ChatUser;
use App\Models\Message;
use App\Models\MessageStatus;
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
            'users' => 'required|array',
            'users.*' => 'required|integer|exists:users,id',
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

            $chat['messages'] = Message::where('chat_id', $chat['id'])->get();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
        }

        return $chat;
    }

    public function destroy($chatID)
    {
        // check if user owns this chat
        $chat = Chat::where('id', $chatID)->first();
        $userID = auth()->user()->id;

        $chatUsers = explode('-', $chat['users']);

        if (!in_array($userID, $chatUsers)) {
            return response()->json([
                'message' => 'You are not authorized to delete this chat'
            ], 403);
        }

        try {
            DB::beginTransaction();

            MessageStatus::where('chat_id', $chatID)->delete();
            Message::where('chat_id', $chatID)->delete();

            broadcast(new ClearChatEvent($chatID))->toOthers();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }

        return Message::where('chat_id', $chatID)->get()->toArray();
    }

    public function sendMessage(Request $request)
    {
        $newMessage = $request->validate([
            'chat_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        $userId = auth()->user()->id;

        try {
            DB::beginTransaction();

            $newMessage = Message::create([
                'user_id' => $userId,
                'chat_id' => $newMessage['chat_id'],
                'message' => $newMessage['message'],
            ]);

            MessageStatus::create([
                'chat_id' => $newMessage['chat_id'],
                'message_id' => $newMessage->id,
                'user_id' => $userId,
                'is_read' => false,
            ]);

            broadcast(new StoreMessageEvent($newMessage))->toOthers();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }

        return true;
    }
}
