<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class FilesController extends Controller
{
    private array $imageExt = ['jpg', 'jpeg', 'png', 'gif'];
    private array $audioExt = ['mp3', 'ogg', 'mpga'];
    private array $videoExt = ['mp4', 'mpeg'];
    private array $documentExt = ['doc', 'docx', 'pdf', 'odt'];

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|array',
            'image.*' => 'file',
        ]);

        $files = $request->file('image');
        $username = auth()->user()->name;

        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            $file->storeAs('/uploads/' . $username, $filename);

            $ext = $file->getClientOriginalExtension();
            $type = $this->getType($ext);

            File::create([
                'name' => $filename,
                'type' => $type,
                'extension' => $ext,
                'user_id' => Auth::id(),
            ]);
        }

        return response()->json(['message' => 'success']);
    }

    public function index($type, $id = null)
    {
        if (!is_null($id)) {
            $response = File::findOrFail($id);
        } else {
            $recordsPerPage = ($type === 'video') ? 6 : 15;

            $files = File::where('type', $type)
                ->where('user_id', Auth::id())
                ->orderBy('id', 'desc')
                ->paginate($recordsPerPage);

            $response = [
                'pagination' => [
                    'total' => $files->total(),
                    'per_page' => $files->perPage(),
                    'current_page' => $files->currentPage(),
                    'last_page' => $files->lastPage(),
                    'from' => $files->firstItem(),
                    'to' => $files->lastItem(),
                ],
                'data' => $files,
            ];
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:files,name',
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        $type = $this->getType($ext);
        $storedPath = '/public/' . $this->getUserDir() . '/' . $type . '/';

        if (Storage::putFileAs($storedPath, $file, $request->name . '.' . $ext)) {
            $model = File::create([
                'name' => $request->name,
                'type' => $type,
                'extension' => $ext,
                'user_id' => Auth::id(),
            ]);

            return response()->json($model, 201);
        }

        return response()->json(false, 500);
    }

    public function edit($id, Request $request)
    {
        $file = File::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($file->name === $request->name) {
            return response()->json(false);
        }

        $request->validate(['name' => 'required|unique:files']);

        $oldFilename = '/public/' . $this->getUserDir() . '/' . $file->type . '/' . $file->name . '.' . $file->extension;
        $newFilename = '/public/' . $this->getUserDir() . '/' . $request->type . '/' . $request->name . '.' . $request->extension;

        if (Storage::disk('local')->exists($oldFilename)) {
            if (Storage::disk('local')->move($oldFilename, $newFilename)) {
                $file->name = $request->name;
                return response()->json($file->save());
            }
        }

        return response()->json(false);
    }

    public function destroy($id)
    {
        $file = File::findOrFail($id);
        $filePath = '/public/' . $this->getUserDir() . '/' . $file->type . '/' . $file->name . '.' . $file->extension;

        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }

        $file->delete();

        return response()->json(null, 204);
    }

    public function indexAll()
    {
        $files = File::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($files);
    }

    private function getType(string $ext): string
    {
        if (in_array($ext, $this->imageExt)) return 'image';
        if (in_array($ext, $this->audioExt)) return 'audio';
        if (in_array($ext, $this->videoExt)) return 'video';
        if (in_array($ext, $this->documentExt)) return 'document';

        return 'other';
    }

    private function getUserDir(): string
    {
        return Auth::user()->name . '_' . Auth::id();
    }
}
