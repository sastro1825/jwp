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
    // Fungsi dashboard untuk menampilkan statistik admin
    public function dashboard()
    {
        // Menghitung jumlah customer
        $jumlahCustomer = User::where('role', 'customer')->count();
        // Menghitung jumlah pemilik toko
        $jumlahPemilikToko = User::where('role', 'pemilik_toko')->count();
        // Menghitung jumlah permohonan toko yang pending
        $jumlahTokoPending = TokoRequest::where('status', 'pending')->count();
        // Menghitung jumlah feedback yang pending
        $jumlahFeedbackPending = GuestBook::where('status', 'pending')->count();
        // Menghitung jumlah shipping order yang pending
        $jumlahShippingPending = ShippingOrder::where('status', 'pending')->count();

        // Mengembalikan view dashboard dengan data statistik
        return view('admin.dashboard', compact(
            'jumlahCustomer',
            'jumlahPemilikToko', 
            'jumlahTokoPending',
            'jumlahFeedbackPending',
            'jumlahShippingPending'
        ));
    }

    // Fungsi untuk mengelola daftar customer
    public function manageCustomers()
    {
        // Menghitung total customer dan pemilik toko
        $jumlahCustomer = User::where('role', 'customer')
            ->orWhere('role', 'pemilik_toko')
            ->count();
        
        // Mengambil data customer dan pemilik toko dengan pagination
        $customers = User::where('role', 'customer')
            ->orWhere('role', 'pemilik_toko')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Mengembalikan view manage-customers dengan data customers dan jumlah
        return view('admin.manage-customers', compact('customers', 'jumlahCustomer'));
    }

    // Fungsi untuk menampilkan form edit customer
    public function editCustomer($id)
    {
        // Mengambil data customer berdasarkan ID
        $customer = User::findOrFail($id);
        // Mengembalikan view edit-customer dengan data customer
        return view('admin.edit-customer', compact('customer'));
    }

    // Fungsi untuk memperbarui data customer
    public function updateCustomer(Request $request, $id)
    {
        try {
            // Mengambil data customer berdasarkan ID
            $customer = User::findOrFail($id);
            
            // Validasi input dari request dengan pesan error kustom
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
            ], [
                'name.required' => 'Nama customer wajib diisi.',
                'email.required' => 'Email customer wajib diisi.',
                'email.unique' => 'Email sudah digunakan customer lain.',
                'role.required' => 'Role customer wajib dipilih.',
            ]);

            // Memperbarui data customer dengan data yang divalidasi
            $customer->update($validated);

            // Log aktivitas admin untuk audit trail
            \Log::info('Admin updated customer', [
                'admin_id' => auth()->id(),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name
            ]);

            // Redirect ke halaman manage customers dengan pesan sukses
            return redirect()->route('admin.customers')
                ->with('success', 'Data customer "' . $customer->name . '" berhasil diupdate.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Menangani error validasi secara khusus
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Gagal mengupdate customer. Periksa kembali data yang dimasukkan.');
                
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error updating customer: ' . $e->getMessage(), [
                'customer_id' => $id,
                'admin_id' => auth()->id()
            ]);
            
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate customer: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menghapus customer
    public function deleteCustomer($id)
    {
        try {
            // Mengambil data customer berdasarkan ID
            $customer = User::findOrFail($id);
            
            // Mengecek apakah customer memiliki toko, jika ada hapus
            if ($customer->toko) {
                $customer->toko->delete();
            }
            
            // Menghapus permohonan toko jika ada
            TokoRequest::where('user_id', $id)->delete();
            
            // Menghapus data customer
            $customer->delete();

            // Redirect ke halaman manage customers dengan pesan sukses
            return redirect()->route('admin.customers')->with('success', 'Customer berhasil dihapus.');
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal menghapus customer: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mengelola daftar kategori
    public function manageKategori()
    {
        // Mengambil data kategori dengan pagination
        $kategoris = Kategori::orderBy('created_at', 'desc')->paginate(15);
        // Mengembalikan view manage-kategori dengan data kategori
        return view('admin.manage-kategori', compact('kategoris'));
    }

    // Fungsi untuk menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        try {
            // Validasi input dari request
            $validated = $request->validate([
                'nama' => 'required|string|max:100|unique:kategoris,nama',
                'deskripsi' => 'nullable|string|max:500',
                'harga' => 'required|numeric|min:0',
                'category_type' => 'required|string|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            // Mengelola upload gambar jika ada
            if ($request->hasFile('gambar')) {
                // Menyimpan gambar ke storage
                $path = $request->file('gambar')->store('kategoris', 'public');
                $validated['gambar'] = $path;
            }

            // Membuat kategori baru
            Kategori::create($validated);

            // Redirect ke halaman manage kategori dengan pesan sukses
            return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menampilkan form edit kategori
    public function editKategori($id)
    {
        // Mengambil data kategori berdasarkan ID
        $kategori = Kategori::findOrFail($id);
        // Mengembalikan view edit-kategori dengan data kategori
        return view('admin.edit-kategori', compact('kategori'));
    }

    // Fungsi untuk memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        try {
            // Mengambil data kategori berdasarkan ID
            $kategori = Kategori::findOrFail($id);
            
            // Validasi input dari request
            $validated = $request->validate([
                'nama' => 'required|string|max:100|unique:kategoris,nama,' . $id,
                'deskripsi' => 'nullable|string|max:500',
                'harga' => 'required|numeric|min:0',
                'category_type' => 'required|string|in:alat-kesehatan,obat-obatan,suplemen-kesehatan,perawatan-kecantikan,kesehatan-pribadi',
                'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            // Mengelola upload gambar baru jika ada
            if ($request->hasFile('gambar')) {
                // Menghapus gambar lama jika ada
                if ($kategori->gambar) {
                    Storage::disk('public')->delete($kategori->gambar);
                }
                // Menyimpan gambar baru ke storage
                $path = $request->file('gambar')->store('kategoris', 'public');
                $validated['gambar'] = $path;
            }

            // Memperbarui data kategori
            $kategori->update($validated);

            // Redirect ke halaman manage kategori dengan pesan sukses
            return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil diupdate.');
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal mengupdate kategori: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menghapus kategori
    public function deleteKategori($id)
    {
        try {
            // Mengambil data kategori berdasarkan ID
            $kategori = Kategori::findOrFail($id);
            
            // Menghapus gambar jika ada
            if ($kategori->gambar) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            
            // Menghapus data kategori
            $kategori->delete();

            // Redirect ke halaman manage kategori dengan pesan sukses
            return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mengelola permohonan toko
    public function manageTokoRequests()
    {
        // Mengambil semua permohonan toko dengan pagination
        $tokoRequests = TokoRequest::with('user')->orderBy('created_at', 'desc')->paginate(15);
        
        // Menghitung statistik permohonan toko
        $totalPending = TokoRequest::where('status', 'pending')->count();
        $totalApproved = TokoRequest::where('status', 'approved')->count();
        $totalRejected = TokoRequest::where('status', 'rejected')->count();
        
        // Mengembalikan view manage-toko-requests dengan data dan statistik
        return view('admin.manage-toko-requests', compact(
            'tokoRequests', 
            'totalPending', 
            'totalApproved', 
            'totalRejected'
        ));
    }

    // Fungsi untuk menampilkan detail permohonan toko
    public function viewTokoRequestDetail($id)
    {
        // Mengambil data permohonan toko berdasarkan ID
        $tokoRequest = TokoRequest::with('user')->findOrFail($id);
        // Mengembalikan view toko-request-detail dengan data permohonan
        return view('admin.toko-request-detail', compact('tokoRequest'));
    }

    // Fungsi untuk menyetujui permohonan toko
    public function approveTokoRequest(Request $request, $id)
    {
        try {
            // Mengambil data permohonan toko berdasarkan ID
            $tokoRequest = TokoRequest::with('user')->findOrFail($id);
            
            // Validasi catatan admin
            $validated = $request->validate([
                'catatan_admin' => 'nullable|string|max:1000',
            ]);

            // Memperbarui status permohonan toko menjadi approved
            $tokoRequest->update([
                'status' => 'approved',
                'catatan_admin' => $validated['catatan_admin'] ?? 'Permohonan toko Anda telah disetujui.',
            ]);

            // Memperbarui role user menjadi pemilik_toko
            $tokoRequest->user->update(['role' => 'pemilik_toko']);

            // Membuat data toko baru
            Toko::create([
                'nama' => $tokoRequest->nama_toko,
                'user_id' => $tokoRequest->user_id,
                'status' => 'approved',
                'alamat' => $tokoRequest->alamat_toko,
                'deskripsi' => $tokoRequest->deskripsi_toko,
                'kategori_usaha' => $tokoRequest->kategori_usaha,
                'no_telepon' => $tokoRequest->no_telepon,
            ]);

            // Redirect ke halaman manage toko requests dengan pesan sukses
            return redirect()->route('admin.toko.requests')->with('success', 
                'Permohonan toko dari ' . $tokoRequest->user->name . ' telah disetujui.');

        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal menyetujui permohonan: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menolak permohonan toko
    public function rejectTokoRequest(Request $request, $id)
    {
        try {
            // Mengambil data permohonan toko berdasarkan ID
            $tokoRequest = TokoRequest::with('user')->findOrFail($id);
            
            // Validasi catatan admin untuk penolakan
            $validated = $request->validate([
                'catatan_admin' => 'required|string|max:1000',
            ], [
                'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
            ]);

            // Memperbarui status permohonan toko menjadi rejected
            $tokoRequest->update([
                'status' => 'rejected',
                'catatan_admin' => $validated['catatan_admin'],
            ]);

            // Redirect ke halaman manage toko requests dengan pesan sukses
            return redirect()->route('admin.toko.requests')->with('success', 
                'Permohonan toko dari ' . $tokoRequest->user->name . ' telah ditolak.');

        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal menolak permohonan: ' . $e->getMessage());
        }
    }

    // Fungsi untuk menghapus permohonan toko
    public function deleteTokoRequest($id)
    {
        try {
            // Mengambil data permohonan toko berdasarkan ID
            $tokoRequest = TokoRequest::with('user')->findOrFail($id);
            
            // Jika permohonan sudah approved, ubah role user ke customer
            if ($tokoRequest->status === 'approved' && $tokoRequest->user) {
                $tokoRequest->user->update(['role' => 'customer']);
                
                // Menghapus toko jika ada
                if ($tokoRequest->user->toko) {
                    $tokoRequest->user->toko->delete();
                }
            }
            
            // Menghapus permohonan toko
            $tokoRequest->delete();
            
            // Redirect ke halaman manage toko requests dengan pesan sukses
            return redirect()->route('admin.toko.requests')->with('success', 'Permohonan toko berhasil dihapus.');
            
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->route('admin.toko.requests')->with('error', 'Gagal menghapus permohonan toko.');
        }
    }

    // Fungsi untuk mengelola guest book (feedback)
    public function manageGuestBook()
    {
        // Mengambil semua feedback dengan pagination
        $allFeedbacks = GuestBook::with('user')->orderBy('created_at', 'desc')->paginate(15);
        
        // Menghitung statistik feedback
        $totalFeedback = GuestBook::count();
        $totalPending = GuestBook::where('status', 'pending')->count();
        $totalApproved = GuestBook::where('status', 'approved')->count();
        $totalRejected = GuestBook::where('status', 'rejected')->count();
        
        // Mengembalikan view manage-guest-book dengan data dan statistik
        return view('admin.manage-guest-book', compact(
            'allFeedbacks',
            'totalFeedback', 
            'totalPending', 
            'totalApproved', 
            'totalRejected'
        ));
    }

    // Fungsi untuk menyetujui feedback
    public function approveFeedback($id)
    {
        try {
            // Mengambil data feedback berdasarkan ID
            $feedback = GuestBook::findOrFail($id);
            // Memperbarui status feedback menjadi approved
            $feedback->update(['status' => 'approved']);

            // Redirect ke halaman manage guest book dengan pesan sukses
            return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil diapprove.');
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal approve feedback.');
        }
    }

    // Fungsi untuk menolak feedback
    public function rejectFeedback($id)
    {
        try {
            // Mengambil data feedback berdasarkan ID
            $feedback = GuestBook::findOrFail($id);
            // Memperbarui status feedback menjadi rejected
            $feedback->update(['status' => 'rejected']);

            // Redirect ke halaman manage guest book dengan pesan sukses
            return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil direject.');
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal reject feedback.');
        }
    }

    // Fungsi untuk menghapus feedback
    public function deleteFeedback($id)
    {
        try {
            // Mengambil data feedback berdasarkan ID
            $feedback = GuestBook::findOrFail($id);
            // Menghapus feedback
            $feedback->delete();

            // Redirect ke halaman manage guest book dengan pesan sukses
            return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil dihapus.');
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal menghapus feedback.');
        }
    }

    // Fungsi untuk mengelola shipping orders
    public function manageShippingOrders()
    {
        // Mengambil shipping orders yang bukan dari toko kategori dengan pagination
        $shippingOrders = ShippingOrder::with(['transaksi.user'])
            ->whereHas('transaksi', function($query) {
                // Filter transaksi yang tidak mengandung item toko_kategori
                $query->whereDoesntHave('detailTransaksi', function($subQuery) {
                    $subQuery->where('item_type', 'toko_kategori');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Mengembalikan view manage-shipping dengan data shipping orders
        return view('admin.manage-shipping', compact('shippingOrders'));
    }

    // Fungsi untuk menampilkan form pembuatan shipping order
    public function createShippingOrder($transaksi_id)
    {
        // Mengambil data transaksi berdasarkan ID
        $transaksi = \App\Models\Transaksi::with('user')->findOrFail($transaksi_id);
        // Mengembalikan view create-shipping dengan data transaksi
        return view('admin.create-shipping', compact('transaksi'));
    }

    // Fungsi untuk menyimpan shipping order baru
    public function storeShippingOrder(Request $request)
    {
        try {
            // Validasi input dari request
            $validated = $request->validate([
                'transaksi_id' => 'required|exists:transaksis,id',
                'shipping_address' => 'required|string|max:500',
                'estimated_delivery' => 'required|date|after:today',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Menambahkan status pending dan nomor tracking
            $validated['status'] = 'pending';
            $validated['tracking_number'] = 'OSS-' . strtoupper(uniqid());

            // Membuat shipping order baru
            ShippingOrder::create($validated);

            // Redirect ke halaman manage shipping dengan pesan sukses
            return redirect()->route('admin.shipping')->with('success', 'Shipping order berhasil dibuat.');
        } catch (\Exception $e) {
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal membuat shipping order: ' . $e->getMessage());
        }
    }

    // Fungsi untuk memperbarui status shipping order
    public function updateShippingStatus(Request $request, $id)
    {
        try {
            // Mengambil data shipping order berdasarkan ID
            $shipping = ShippingOrder::findOrFail($id);
            
            // Validasi input dari request
            $validated = $request->validate([
                'status' => 'required|in:pending,shipped,delivered,cancelled',
                'courier' => 'nullable|string|max:100',
                'shipped_date' => 'nullable|date',
                'delivered_date' => 'nullable|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Memperbarui data shipping order
            $shipping->update([
                'status' => $validated['status'],
                'courier' => $validated['courier'] ?? $shipping->courier,
                'shipped_date' => $validated['shipped_date'] ? \Carbon\Carbon::parse($validated['shipped_date']) : $shipping->shipped_date,
                'delivered_date' => $validated['delivered_date'] ? \Carbon\Carbon::parse($validated['delivered_date']) : $shipping->delivered_date,
                'notes' => $validated['notes'] ?? $shipping->notes,
            ]);

            // Memperbarui status transaksi berdasarkan status shipping
            if ($validated['status'] === 'delivered') {
                $shipping->transaksi->update(['status' => 'completed']);
            } elseif ($validated['status'] === 'cancelled') {
                $shipping->transaksi->update(['status' => 'cancelled']);
            } elseif ($validated['status'] === 'shipped') {
                $shipping->transaksi->update(['status' => 'processing']);
            }

            // Redirect ke halaman manage shipping dengan pesan sukses
            return redirect()->route('admin.shipping')->with('success', 'Status pengiriman berhasil diupdate.');
        } catch (\Exception $e) {
            // Mencatat error ke log
            Log::error('Error updating shipping status: ' . $e->getMessage());
            // Redirect kembali dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal mengupdate status pengiriman.');
        }
    }
}