<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintImage;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    // 📌 Ambil semua aduan milik user
    public function index()
    {
        return Complaint::with('images')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    // 📌 Buat aduan baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string'
        ]);

        $complaint = Complaint::create($validated);

        return response()->json([
            'message' => 'Complaint created',
            'data' => $complaint
        ], 201);
    }

    // 📌 Detail aduan
    public function show($id)
    {
        $complaint = Complaint::with('images')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json($complaint);
    }

    // 📌 Hapus (soft delete)
    public function destroy($id)
    {
        $complaint = Complaint::where('user_id', auth()->id())
            ->findOrFail($id);

        $complaint->delete();

        return response()->json([
            'message' => 'Complaint deleted'
        ]);
    }

    // 📌 Upload gambar
    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|max:2048'
        ]);

        $complaint = Complaint::where('user_id', auth()->id())
            ->findOrFail($id);

        $path = $request->file('image')->store('complaints', 'public');

        $image = ComplaintImage::create([
            'complaint_id' => $complaint->id,
            'image_path' => $path
        ]);

        return response()->json([
            'message' => 'Image uploaded',
            'data' => $image
        ]);
    }
}