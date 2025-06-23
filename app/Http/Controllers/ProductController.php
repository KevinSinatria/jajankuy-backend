<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $categorySlug)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'stock' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
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
                'parents' => ['1r7RzfDBRerzj43-FtrrcjW1sBR3oBMye'],
            ]);

            $content = file_get_contents($imageFile->getRealPath());
            $file = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $imageFile->getClientMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id',
                'supportsAllDrives' => true
            ]);

            $fileId = $file->id;

            if (!$fileId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal upload gambar',
                    'errors' => (object) []
                ], 500);
            }

            if (!$this->makeFilePublic($fileId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat file publik',
                    'errors' => (object) []
                ], 500);
            }

            $directUrl = 'https://drive.google.com/uc?id=' . $fileId;
            $slug = Str::slug($request->name);
            $categoryId = Category::where('slug', $categorySlug)->first()->id;

            if (Product::where('slug', $slug)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk dengan nama ini sudah ada.',
                    'errors' => (object) []
                ], 422);
            }

            $product = Product::create([
                'name' => $request->name,
                'slug' => $slug,
                'price' => $request->price,
                'description' => $request->description,
                'category_id' => $categoryId,
                'stock' => $request->stock,
                'image_url' => $directUrl
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan!',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
