<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kategori;
use App\Models\GuestBook;
use App\Models\ShippingOrder;
use App\Models\TokoRequest;
use App\Models\Toko;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Dashboard Admin dengan statistik
     */
    public function dashboard()
    {
        // Hitung statistik untuk dashboard
        $jumlahCustomer = User::where('role', 'customer')->count();
        $jumlahPemilikToko = User::where('role', 'pemilik_toko')->count();
        $jumlahTokoPending = TokoRequest::where('status', 'pending')->count();
        $jumlahFeedbackPending = GuestBook::where('status', 'pending')->count();
        $jumlahShippingPending = ShippingOrder::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'jumlahCustomer',
            'jumlahPemilikToko', 
            'jumlahTokoPending',
            'jumlahFeedbackPending',
            'jumlahShippingPending'
        ));
    }

    /**
     * Kelola Customer - tampilkan, edit, hapus customer
     */
    public function manageCustomers()
    {
        $customers = User::where('role', 'customer')
            ->orWhere('role', 'pemilik_toko')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.manage-customers', compact('customers'));
    }

    /**
     * Edit Customer
     */
    public function editCustomer($id)
    {
        $customer = User::findOrFail($id);
        return view('admin.edit-customer', compact('customer'));
    }

    /**
     * Update Customer
     */
    public function updateCustomer(Request $request, $id)
    {
        try {
            $customer = User::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:users,email,' . $id,
                'dob' => 'nullable|date',
                'gender' => 'nullable|in:male,female',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'contact_no' => 'nullable|string|max:20',
                'paypal_id' => 'nullable|string|max:100',
                'role' => 'required|in:customer,pemilik_toko',
            ]);

            $customer->update($validated);

            return redirect()->route('admin.customers')->with('success', 'Data customer berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate customer: ' . $e->getMessage());
        }
    }

    /**
     * Delete Customer
     */
    public function deleteCustomer($id)
    {
        try {
            $customer = User::findOrFail($id);
            
            // Cek apakah customer memiliki toko
            if ($customer->toko) {
                $customer->toko->delete();
            }
            
            // Hapus permohonan toko jika ada
            TokoRequest::where('user_id', $id)->delete();
            
            $customer->delete();

            return redirect()->route('admin.customers')->with('success', 'Customer berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus customer: ' . $e->getMessage());
        }
    }

    /**
     * Kelola Kategori - CRUD kategori produk
     */
    public function manageKategori()
    {
        $kategoris = Kategori::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.manage-kategori', compact('kategoris'));
    }

    /**
     * Store Kategori Baru
     */
    public function storeKategori(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:100|unique:kategoris,nama',
                'deskripsi' => 'nullable|string|max:500',
                'harga' => 'required|numeric|min:0',
                'category_type' => 'required|string|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            // Handle upload gambar
            if ($request->hasFile('gambar')) {
                $path = $request->file('gambar')->store('kategoris', 'public');
                $validated['gambar'] = $path;
            }

            Kategori::create($validated);

            return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    /**
     * Edit Kategori
     */
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.edit-kategori', compact('kategori'));
    }

    /**
     * Update Kategori
     */
    public function updateKategori(Request $request, $id)
    {
        try {
            $kategori = Kategori::findOrFail($id);
            
            $validated = $request->validate([
                'nama' => 'required|string|max:100|unique:kategoris,nama,' . $id,
                'deskripsi' => 'nullable|string|max:500',
                'harga' => 'required|numeric|min:0',
                'category_type' => 'required|string|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            // Handle upload gambar baru
            if ($request->hasFile('gambar')) {
                // Hapus gambar lama jika ada
                if ($kategori->gambar) {
                    Storage::disk('public')->delete($kategori->gambar);
                }
                $path = $request->file('gambar')->store('kategoris', 'public');
                $validated['gambar'] = $path;
            }

            $kategori->update($validated);

            return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate kategori: ' . $e->getMessage());
        }
    }

    /**
     * Delete Kategori
     */
    public function deleteKategori($id)
    {
        try {
            $kategori = Kategori::findOrFail($id);
            
            // Hapus gambar jika ada
            if ($kategori->gambar) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            
            $kategori->delete();

            return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    /**
     * Kelola Permohonan Toko - tampilkan semua dengan aksi delete
     */
    public function manageTokoRequests()
    {
        // Ambil semua permohonan toko dengan pagination
        $tokoRequests = TokoRequest::with('user')->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistik
        $totalPending = TokoRequest::where('status', 'pending')->count();
        $totalApproved = TokoRequest::where('status', 'approved')->count();
        $totalRejected = TokoRequest::where('status', 'rejected')->count();
        
        return view('admin.manage-toko-requests', compact(
            'tokoRequests', 
            'totalPending', 
            'totalApproved', 
            'totalRejected'
        ));
    }

    /**
     * View Detail Permohonan Toko
     */
    public function viewTokoRequestDetail($id)
    {
        $tokoRequest = TokoRequest::with('user')->findOrFail($id);
        return view('admin.toko-request-detail', compact('tokoRequest'));
    }

    /**
     * Approve Permohonan Toko
     */
    public function approveTokoRequest(Request $request, $id)
    {
        try {
            $tokoRequest = TokoRequest::with('user')->findOrFail($id);
            
            // Validasi catatan admin
            $validated = $request->validate([
                'catatan_admin' => 'nullable|string|max:1000',
            ]);

            // Update status permohonan
            $tokoRequest->update([
                'status' => 'approved',
                'catatan_admin' => $validated['catatan_admin'] ?? 'Permohonan toko Anda telah disetujui.',
            ]);

            // Update role user menjadi pemilik_toko
            $tokoRequest->user->update(['role' => 'pemilik_toko']);

            // Buat data toko baru
            Toko::create([
                'nama' => $tokoRequest->nama_toko,
                'user_id' => $tokoRequest->user_id,
                'status' => 'approved',
                'alamat' => $tokoRequest->alamat_toko,
                'deskripsi' => $tokoRequest->deskripsi_toko,
                'kategori_usaha' => $tokoRequest->kategori_usaha,
                'no_telepon' => $tokoRequest->no_telepon,
            ]);

            return redirect()->route('admin.toko.requests')->with('success', 
                'Permohonan toko dari ' . $tokoRequest->user->name . ' telah disetujui.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyetujui permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Reject Permohonan Toko
     */
    public function rejectTokoRequest(Request $request, $id)
    {
        try {
            $tokoRequest = TokoRequest::with('user')->findOrFail($id);
            
            // Validasi catatan admin (wajib untuk rejection)
            $validated = $request->validate([
                'catatan_admin' => 'required|string|max:1000',
            ], [
                'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
            ]);

            // Update status permohonan
            $tokoRequest->update([
                'status' => 'rejected',
                'catatan_admin' => $validated['catatan_admin'],
            ]);

            return redirect()->route('admin.toko.requests')->with('success', 
                'Permohonan toko dari ' . $tokoRequest->user->name . ' telah ditolak.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menolak permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Delete Permohonan Toko
     */
    public function deleteTokoRequest($id)
    {
        try {
            $tokoRequest = TokoRequest::with('user')->findOrFail($id);
            
            // Jika toko sudah approved, ubah role user kembali ke customer
            if ($tokoRequest->status === 'approved' && $tokoRequest->user) {
                $tokoRequest->user->update(['role' => 'customer']);
                
                // Hapus toko jika ada
                if ($tokoRequest->user->toko) {
                    $tokoRequest->user->toko->delete();
                }
            }
            
            $tokoRequest->delete();
            
            return redirect()->route('admin.toko.requests')->with('success', 'Permohonan toko berhasil dihapus.');
            
        } catch (\Exception $e) {
            return redirect()->route('admin.toko.requests')->with('error', 'Gagal menghapus permohonan toko.');
        }
    }

    /**
     * Kelola Guest Book - tampilkan feedback dari visitor DAN customer
     */
    public function manageGuestBook()
    {
        // Ambil semua feedback (visitor dan customer)
        $allFeedbacks = GuestBook::with('user')->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistik
        $totalFeedback = GuestBook::count();
        $totalPending = GuestBook::where('status', 'pending')->count();
        $totalApproved = GuestBook::where('status', 'approved')->count();
        $totalRejected = GuestBook::where('status', 'rejected')->count();
        
        return view('admin.manage-guest-book', compact(
            'allFeedbacks',
            'totalFeedback', 
            'totalPending', 
            'totalApproved', 
            'totalRejected'
        ));
    }

    /**
     * Approve Feedback
     */
    public function approveFeedback($id)
    {
        try {
            $feedback = GuestBook::findOrFail($id);
            $feedback->update(['status' => 'approved']);

            return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil diapprove.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal approve feedback.');
        }
    }

    /**
     * Reject Feedback
     */
    public function rejectFeedback($id)
    {
        try {
            $feedback = GuestBook::findOrFail($id);
            $feedback->update(['status' => 'rejected']);

            return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil direject.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal reject feedback.');
        }
    }

    /**
     * Delete Feedback
     */
    public function deleteFeedback($id)
    {
        try {
            $feedback = GuestBook::findOrFail($id);
            $feedback->delete();

            return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus feedback.');
        }
    }

    /**
     * Kelola Shipping Orders
     */
    public function manageShippingOrders()
    {
        $shippingOrders = ShippingOrder::with(['transaksi.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.manage-shipping', compact('shippingOrders'));
    }

    /**
     * Create Shipping Order
     */
    public function createShippingOrder($transaksi_id)
    {
        $transaksi = \App\Models\Transaksi::with('user')->findOrFail($transaksi_id);
        return view('admin.create-shipping', compact('transaksi'));
    }

    /**
     * Store Shipping Order
     */
    public function storeShippingOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'transaksi_id' => 'required|exists:transaksis,id',
                'shipping_address' => 'required|string|max:500',
                'estimated_delivery' => 'required|date|after:today',
                'notes' => 'nullable|string|max:1000',
            ]);

            $validated['status'] = 'pending';
            $validated['tracking_number'] = 'OSS-' . strtoupper(uniqid());

            ShippingOrder::create($validated);

            return redirect()->route('admin.shipping')->with('success', 'Shipping order berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat shipping order: ' . $e->getMessage());
        }
    }

    /**
     * Update Shipping Status - DIPERBAIKI untuk update status transaksi juga
     */
    public function updateShippingStatus(Request $request, $id)
    {
        try {
            $shipping = ShippingOrder::findOrFail($id);
            
            $validated = $request->validate([
                'status' => 'required|in:pending,shipped,delivered,cancelled',
                'courier' => 'nullable|string|max:100',
                'shipped_date' => 'nullable|date',
                'delivered_date' => 'nullable|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Update data shipping
            $shipping->update([
                'status' => $validated['status'],
                'courier' => $validated['courier'] ?? $shipping->courier,
                'shipped_date' => $validated['shipped_date'] ? \Carbon\Carbon::parse($validated['shipped_date']) : $shipping->shipped_date,
                'delivered_date' => $validated['delivered_date'] ? \Carbon\Carbon::parse($validated['delivered_date']) : $shipping->delivered_date,
                'notes' => $validated['notes'] ?? $shipping->notes,
            ]);

            // Update status transaksi berdasarkan status shipping
            if ($validated['status'] === 'delivered') {
                $shipping->transaksi->update(['status' => 'completed']);
            } elseif ($validated['status'] === 'cancelled') {
                $shipping->transaksi->update(['status' => 'cancelled']);
            } elseif ($validated['status'] === 'shipped') {
                $shipping->transaksi->update(['status' => 'processing']);
            }

            return redirect()->route('admin.shipping')->with('success', 'Status pengiriman berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Error updating shipping status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate status pengiriman.');
        }
    }
}