<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Google\Service\Drive;
use Google\Client;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Log;
use League\CommonMark\Normalizer\SlugNormalizer;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private function initializeGoogleClient()
    {
        $client = new Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->setAccessType('offline');
        $client->setScopes(Drive::DRIVE_FILE);
        $client->addScope(Drive::DRIVE);
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        return $client;
    }

    private function makeFilePublic($fileId)
    {
        try {
            $client = $this->initializeGoogleClient();
            $service = new Drive($client);

            $permission = new Permission();
            $permission->setType('anyone');
            $permission->setRole('reader');
            $service->permissions->create($fileId, $permission);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "image" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'errors' => (object) []
            ], 422);
        }

        try {
            $imageFile = $request->file('image');
            $fileName = time() . '_' . $imageFile->getClientOriginalName();

            // Init google client
            $client = $this->initializeGoogleClient();
            $service = new Drive($client);

            // Upload file to Google Drive
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => ['1Remu2dScKf0t1aeeVeM_J3XVFc5RlPPa'],
            ]);

            $content = file_get_contents($imageFile->getRealPath());
            $file = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $imageFile->getMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id',
                'supportsAllDrives' => true
            ]);

            $fileId = $file->id;

            if (!$fileId) {
                throw new \Exception('File ID tidak ditemukan setelah upload');
            }

            if (!$this->makeFilePublic($fileId)) {
                throw new \Exception('Gagal membuat file publik');
            }

            $directUrl = "https://drive.google.com/uc?id=" . $fileId;
            $slug = Str::slug($request->name);

            if (Category::where('slug', $slug)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori sudah ada',
                    'errors' => (object) []
                ], 422);
            }

            $category = Category::create([
                'name' => $request->name,
                'image_url' => $directUrl,
                'slug' => $slug
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }

    public function update(Request $request, $slug)
    {
        $validator = Validator::make($request->all(), [
            "name" => "sometimes|required|string|max:255",
            "image" => "sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'errors' => (object) []
            ] . 422);
        }

        try {
            $category = Category::where('slug', $slug)->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            $client = $this->initializeGoogleClient();
            $service = new Drive($client);
            $newFileId = null;
            $directUrl = $category->image_url;

            if ($request->hasFile('image')) {
                // Extract old file ID from image_url
                preg_match('/id=([^&]+)/', $category->image_url, $matches);
                $oldFileId = $matches[1] ?? null;

                $imageFile = $request->file('image');
                $fileName = time() . '_' . $imageFile->getClientOriginalName();

                $fileMetadata = new DriveFile([
                    'name' => $fileName,
                    'parents' => ['1Remu2dScKf0t1aeeVeM_J3XVFc5RlPPa'],
                ]);

                $content = file_get_contents($imageFile->getRealPath());
                $file = $service->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => $imageFile->getMimeType(),
                    'uploadType' => 'multipart',
                    'fields' => 'id',
                    'supportsAllDrives' => true
                ]);

                $newFileId = $file->id;

                if (!$newFileId) {
                    throw new \Exception('File ID tidak ditemukan setelah upload');
                }

                if (!$this->makeFilePublic($newFileId)) {
                    throw new \Exception('Gagal membuat file publik');
                }

                if ($oldFileId) {
                    try {
                        $service->files->delete($oldFileId, ['supportsAllDrives' => true]);
                    } catch (\Exception $e) {
                        Log::warning($e->getMessage());
                    }
                }

                $directUrl = "https://drive.google.com/uc?id=" . $newFileId;
            }

            $slug = Str::slug($request->name ?? $category->name);

            if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori sudah ada',
                    'errors' => (object) []
                ], 422);
            }

            $category->update([
                'name' => $request->name ?? $category->name,
                'image_url' => $directUrl,
                'slug' => $slug
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui',
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }

    public function destroy($slug)
    {
        try {
            $category = Category::where('slug', $slug)->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            // Extract file ID from image_url
            preg_match('/id=([^&]+)/', $category->image_url, $matches);
            $fileId = $matches[1] ?? null;

            if($fileId) {
                $client = $this->initializeGoogleClient();
                $service = new Drive($client);
                try {
                    $service->files->delete($fileId, ['supportsAllDrives' => true]);
                } catch (\Exception $e) {
                    Log::warning($e->getMessage());

                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'errors' => (object) []
                    ], 500);
                }
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus',
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }
}
