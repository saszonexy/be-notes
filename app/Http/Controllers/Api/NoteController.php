<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Notes;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Notes::query();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where('title', 'like', "%$search%")
                  ->orWhere('content', 'like', "%$search%");
        }

        return response()->json($query->latest()->get());
    }


    public function show($id)
    {
        $note = Notes::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }
        return response()->json($note);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $note = Notes::create($request->only(['title', 'content']));
        return response()->json($note, 201);
    }


    public function update(Request $request, $id)
    {
        $note = Notes::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $note->update($request->only(['title', 'content']));
        return response()->json($note);
    }

    public function destroy($id)
    {
        $note = Notes::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $note->delete();
        return response()->json(['message' => 'Note deleted']);
    }
}
