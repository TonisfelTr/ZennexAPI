<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Tag;
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
        // Не самая лучшая реализация.
        if ($request->tag) {
            $notes = Tag::where('tags.title', $request->tag)
                         ->join('notes', 'notes.id', '=', 'tags.note_id')
                         ->where('notes.user_id', auth()->user()->id)
                         ->get();
        } else {
            $notes = Tag::join('notes', 'notes.id', '=', 'tags.note_id')
                        ->where('tags.user_id', auth()->user()->id)
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

            $newTag = $request->post('tag');

            if ($tags->where('title', $newTag)->count() > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Note has this tag already.'
                                        ], 400);
            }

            $tag = new Tag();
            $tag->title = $newTag;
            $tag->note_id = $note->id;
            $tag->save();

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
