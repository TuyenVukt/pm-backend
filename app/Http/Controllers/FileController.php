<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('file');

        if (!$file) {
            return response()->json(['error' => 'No file selected.'], 400);
        }

        try {
            $serviceAccount = ServiceAccount::fromJsonFile(config('firebase.credential_file'));
            $firebase = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->create();

            $storage = $firebase->getStorage();
            $bucket = $storage->getBucket();

            $fileName = uniqid() . '_' . $file->getClientOriginalName();
            $object = $bucket->upload(fopen($file->getPathname(), 'r'), [
                'name' => $fileName,
            ]);

            // Optionally, you can save the file URL in Firestore or return it as a response.
            $fileUrl = $object->signedUrl(new \DateTime('next year'));
            return response()->json(['url' => $fileUrl], 200);
        } catch (FirebaseException $e) {
            return response()->json(['error' => 'Failed to upload the file.'], 500);
        }
    }
}
