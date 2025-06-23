<?php

namespace App\Http\Controllers;

use App\Models\JurisUserText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurisUserTextController extends Controller
{
    /**
     * Store a newly created text annotation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_table' => 'required|string|max:100',
            'document_section_id' => 'required|integer',
            'text_content' => 'required|string',
            'text_type' => 'required|in:note,highlight,comment',
        ]);

        $annotation = new JurisUserText([
            'user_id' => Auth::id(),
            'document_table' => $request->document_table,
            'document_section_id' => $request->document_section_id,
            'text_content' => $request->text_content,
            'text_type' => $request->text_type,
        ]);

        $annotation->save();

        return response()->json([
            'success' => true,
            'message' => 'Annotation created successfully',
            'annotation' => $annotation
        ]);
    }

    /**
     * Get all annotations for a document section.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getForSection(Request $request)
    {
        $request->validate([
            'document_table' => 'required|string|max:100',
            'document_section_id' => 'required|integer',
        ]);

        $annotations = JurisUserText::where('user_id', Auth::id())
            ->where('document_table', $request->document_table)
            ->where('document_section_id', $request->document_section_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'annotations' => $annotations
        ]);
    }

    /**
     * Update the specified annotation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'text_content' => 'required|string',
        ]);

        $annotation = JurisUserText::where('user_id', Auth::id())->findOrFail($id);
        $annotation->text_content = $request->text_content;
        $annotation->save();

        return response()->json([
            'success' => true,
            'message' => 'Annotation updated successfully',
            'annotation' => $annotation
        ]);
    }

    /**
     * Remove the specified annotation from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $annotation = JurisUserText::where('user_id', Auth::id())->findOrFail($id);
        $annotation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Annotation deleted successfully'
        ]);
    }
}
