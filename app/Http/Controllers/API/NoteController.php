<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    public function watch(int $id): JsonResponse {
        $note = Note::findOrFail($id);

        if ($note->user_id == auth()->user()->id) {
            return response()->json([
                'data' => [
                    'id'      => $id,
                    'title'   => $note->title,
                    'content' => $note->content,
                    'tags'    => $note->tags
                ]
                                  ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Forbidden'
                                ], 401);
    }

    public function store(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                                        'messages' => $validator->errors(),
                                        'status' => false
                                    ], 400);
        }

        $data = [
            'user_id' => auth()->user()->id,
            'title' => $request->post('title'),
            'content' => $request->post('content')
        ];

        $note = Note::create($data);

        return response()->json([
            'message' => 'Note has been created',
            'status' => true,
            'id' => $note->id
                                ]);
    }

    public function update(Request $request, int $id): JsonResponse {
        $note = Note::findOrFail($id);

        if ($request->post('user_id')) {
            return response()->json([
                'message' => 'You cannot change note user.'
                                    ], 500);
        }

        if ($note->user_id == auth()->user()->id) {
            $newData = [
                'title' => $request->post('title'),
                'content' => $request->post('content')
            ];

            $note->update($newData);

            return response()->json([
                'message' => 'Note has been updated.',
                'status' => true
                                    ]);
        } else {
            return response()->json([
                'message' => "Forbidden",
                'status' => false
                                    ], 403);
        }
    }

    public function delete(int $id): JsonResponse {
        $note = Note::findOrFail($id);

        if ($note->user_id == auth()->user()->id) {
            $note->delete();

            return response()->json([
                                        'message' => 'Note has been deleted.',
                                        'status' => true
                                    ]);
        } else {
            return response()->json([
                                        'message' => "Forbidden",
                                        'status' => false
                                    ], 403);
        }
    }

    public function search(Request $request): JsonResponse {
        if ($request->tag) {
            $notes = Note::whereJsonContains('tags', $request->tag)
                         ->where('user_id', auth()->user()->id)
                         ->get();
        } else {
            $notes = Note::where('user_id', auth()->user()->id)
                ->get();
        }

        $notesInfo = [];
        foreach ($notes as $note) {
            $notesInfo[] = [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $notesInfo
                                ]);
    }

    public function addTags(Request $request, int $id): JsonResponse {
        $note = Note::findOrFail($id);

        if ($note->user_id == auth()->user()->id) {
            $tags = $note->tags;

            if (!is_array($tags)) {
                $tags = [];
            }

            $newTag = $request->post('tag');

            if (in_array($newTag, $tags)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Note has this tag already.'
                                        ], 400);
            }

            $tags[] = $newTag;
            $note->tags = $tags;
            $note->save();

            return response()->json([
                'status' => true,
                'message' => 'Tag has been added.'
                                    ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Forbidden.'
                                ], 403);
    }
}
