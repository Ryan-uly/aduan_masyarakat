<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintImage;
use Illuminate\Http\Request;
use App\Http\Requests\CreateComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Http\Resources\ComplaintResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{
    use AuthorizesRequests;
    // 📌 Ambil semua aduan milik user
    public function index(Request $request)
    {
        $complaints = Complaint::with('images')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return ComplaintResource::collection($complaints);
    }

    // 📌 Buat aduan baru
    public function store(CreateComplaintRequest $request)
    {
        DB::transaction(function () use ($request,&$complaint) {
            $complaint = Complaint::create($request->validated());

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {

                    $path = $image->store(
                        "complaints/{$complaint->user_id}/{$complaint->id}",
                        'public'
                    );

                    $complaint->images()->create([
                        'image_path' => $path
                    ]);
                }
            }
        $complaint->load('images');
        });

        return (new ComplaintResource($complaint))
            ->additional([
                'message' => 'Aduan berhasil ditambahkan',
            ])
            ->response()
            ->setStatusCode(201);
    }

    // Update aduan
    public function update(UpdateComplaintRequest $request, $id)
    {
        DB::transaction(function () use ($request,&$complaint) {
            $complaint = Complaint::findOrFail($id);
            $this->authorize('update', $complaint);

            $complaint->update($request->validated());

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {

                    $path = $image->store(
                        "complaints/{$complaint->user_id}/{$complaint->id}",
                        'public'
                    );

                    $complaint->images()->create([
                        'image_path' => $path
                    ]);
                }
            }
            $complaint->load('images');
        });

        return (new ComplaintResource($complaint))
            ->additional([
                'message' => 'Aduan berhasil diupdate',
            ])
            ->response()
            ->setStatusCode(200);
    }
    

    // 📌 Detail aduan
    public function show($id)
    {
        $complaint = Complaint::with('images')->findOrFail($id);
        $this->authorize('view', $complaint);
        return (new ComplaintResource($complaint))
            ->response()
            ->setStatusCode(200);
    }

    // 📌 Hapus (soft delete)
    public function destroy($id)
    {
        $complaint = Complaint::findOrFail($id);
        $this->authorize('delete', $complaint);

        $complaint->delete();

        return response()->json([
            'message' => 'Complaint deleted'
        ]);
    }

    // 📌 Upload gambar
    public function uploadImages(Request $request, Complaint $complaint)
    {
        // dd($request->all(), $request->file('images'));
        $this->authorize('update', $complaint);  
        $request->validate([
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        foreach ($request->file('images') as $image) {

            $path = $image->storePublicly(
                "complaints/{$complaint->user_id}/{$complaint->id}",
                'public'
            );

            $complaint->images()->create([
                'image_path' => $path
            ]);
        }

        $complaint->load('images');

        return (new ComplaintResource($complaint))
            ->additional([
                'message' => 'Images uploaded successfully'
            ]);
    }
}