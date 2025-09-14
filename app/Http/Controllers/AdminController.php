<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Toko;
use App\Models\GuestBook;
use App\Models\ShippingOrder;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Dashboard admin - menampilkan statistik utama
     */
    public function dashboard()
    {
        // Hitung statistik untuk dashboard admin
        $jumlahCustomer = User::where('role', 'customer')->count();
        $jumlahTokoPending = Toko::where('status', 'pending')->count();
        $jumlahFeedbackPending = GuestBook::where('status', 'pending')->count();
        $jumlahShippingPending = ShippingOrder::where('status', 'pending')->count();
        
        return view('admin.dashboard', compact(
            'jumlahCustomer', 
            'jumlahTokoPending', 
            'jumlahFeedbackPending',
            'jumlahShippingPending'
        ));
    }

    /**
     * Manage Customers - menampilkan daftar customer dengan opsi edit/hapus
     */
    public function manageCustomers()
    {
        // Ambil semua customer dengan pagination untuk performa yang baik
        $customers = User::where('role', 'customer')->paginate(10);
        $jumlahCustomer = User::where('role', 'customer')->count();
        
        return view('admin.manage-customers', compact('customers', 'jumlahCustomer'));
    }

    /**
     * Edit customer - form edit data customer
     */
    public function editCustomer($id)
    {
        // Cari customer berdasarkan ID
        $customer = User::where('id', $id)->where('role', 'customer')->firstOrFail();
        
        return view('admin.edit-customer', compact('customer'));
    }

    /**
     * Update customer - proses update data customer
     */
    public function updateCustomer(Request $request, $id)
    {
        // Validasi input form edit customer
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'contact_no' => 'nullable|string|max:20',
        ]);

        // Cari dan update data customer
        $customer = User::where('id', $id)->where('role', 'customer')->firstOrFail();
        $customer->update($request->only(['name', 'email', 'address', 'city', 'contact_no']));

        return redirect()->route('admin.customers')->with('success', 'Data customer berhasil diperbarui.');
    }

    /**
     * Delete customer - hapus customer dan semua data terkait
     */
    public function deleteCustomer($id)
    {
        // Cari customer yang akan dihapus
        $customer = User::where('id', $id)->where('role', 'customer')->firstOrFail();
        
        // Hapus customer beserta data terkait (Laravel akan handle cascade)
        $customer->delete();

        return redirect()->route('admin.customers')->with('success', 'Customer berhasil dihapus.');
    }

    /**
     * Manage Kategori - menampilkan form dan daftar kategori
     */
    public function manageKategori()
    {
        // Ambil semua kategori untuk ditampilkan dengan pagination
        $kategoris = Kategori::paginate(10);
        
        return view('admin.manage-kategori', compact('kategoris'));
    }

    /**
     * Store Kategori - simpan kategori baru dengan category type
     */
    public function storeKategori(Request $request)
    {
        // Validasi input form kategori baru dengan category_type
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'harga' => 'required|numeric|min:0', // Harga kategori wajib diisi
            'category_type' => 'required|in:obat-obatan,alat-kesehatan,suplemen-kesehatan,kesehatan-pribadi,perawatan-kecantikan,gizi-nutrisi,kesehatan-lingkungan', // Validasi category type
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar
        ]);

        // Siapkan data untuk disimpan
        $data = $request->only(['nama', 'deskripsi', 'harga', 'category_type']);

        // Handle upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('kategori', 'public');
            $data['gambar'] = $gambarPath;
        }

        // Simpan kategori baru
        Kategori::create($data);

        return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Edit Kategori - form edit kategori
     */
    public function editKategori($id)
    {
        // Cari kategori berdasarkan ID
        $kategori = Kategori::findOrFail($id);
        
        return view('admin.edit-kategori', compact('kategori'));
    }

    /**
     * Update Kategori - proses update kategori dengan category type
     */
    public function updateKategori(Request $request, $id)
    {
        // Validasi input form edit kategori dengan category_type
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'harga' => 'required|numeric|min:0', // Harga kategori wajib diisi
            'category_type' => 'required|in:obat-obatan,alat-kesehatan,suplemen-kesehatan,kesehatan-pribadi,perawatan-kecantikan,gizi-nutrisi,kesehatan-lingkungan', // Validasi category type
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Cari kategori yang akan diupdate
        $kategori = Kategori::findOrFail($id);
        
        // Siapkan data untuk update
        $data = $request->only(['nama', 'deskripsi', 'harga', 'category_type']);

        // Handle upload gambar baru jika ada
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($kategori->gambar && Storage::disk('public')->exists($kategori->gambar)) {
                Storage::disk('public')->delete($kategori->gambar);
            }
            
            // Upload gambar baru
            $gambarPath = $request->file('gambar')->store('kategori', 'public');
            $data['gambar'] = $gambarPath;
        }

        // Update kategori
        $kategori->update($data);

        return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Delete Kategori - hapus kategori
     */
    public function deleteKategori($id)
    {
        // Cari kategori yang akan dihapus
        $kategori = Kategori::findOrFail($id);
        
        // Hapus gambar jika ada
        if ($kategori->gambar && Storage::disk('public')->exists($kategori->gambar)) {
            Storage::disk('public')->delete($kategori->gambar);
        }
        
        // Hapus kategori
        $kategori->delete();

        return redirect()->route('admin.kategori')->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Manage Toko Requests - menampilkan permohonan toko pending
     */
    public function manageTokoRequests()
    {
        // Ambil semua toko dengan status pending
        $tokos = Toko::where('status', 'pending')->with('user')->get();
        
        return view('admin.manage-toko', compact('tokos'));
    }

    /**
     * Approve Toko - setujui permohonan toko
     */
    public function approveToko($id)
    {
        // Cari toko yang akan disetujui
        $toko = Toko::findOrFail($id);
        $toko->update(['status' => 'approved']);

        return redirect()->route('admin.toko.requests')->with('success', 'Toko berhasil disetujui.');
    }

    /**
     * Reject Toko - tolak permohonan toko
     */
    public function rejectToko($id)
    {
        // Cari toko yang akan ditolak
        $toko = Toko::findOrFail($id);
        $toko->update(['status' => 'rejected']);

        return redirect()->route('admin.toko.requests')->with('success', 'Toko berhasil ditolak.');
    }

    /**
     * Manage Guest Book - tampilkan daftar feedback
     */
    public function manageGuestBook()
    {
        // Ambil semua feedback dengan pagination
        $feedbacks = GuestBook::orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.manage-guestbook', compact('feedbacks'));
    }

    /**
     * Approve Feedback - setujui feedback untuk ditampilkan
     */
    public function approveFeedback($id)
    {
        // Cari feedback yang akan disetujui
        $feedback = GuestBook::findOrFail($id);
        $feedback->update(['status' => 'approved']);

        return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil disetujui.');
    }

    /**
     * Reject Feedback - tolak feedback
     */
    public function rejectFeedback($id)
    {
        // Cari feedback yang akan ditolak
        $feedback = GuestBook::findOrFail($id);
        $feedback->update(['status' => 'rejected']);

        return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil ditolak.');
    }

    /**
     * Delete Feedback - hapus feedback
     */
    public function deleteFeedback($id)
    {
        // Cari dan hapus feedback
        $feedback = GuestBook::findOrFail($id);
        $feedback->delete();

        return redirect()->route('admin.guestbook')->with('success', 'Feedback berhasil dihapus.');
    }

    /**
     * Manage Shipping Orders - kelola pengiriman
     */
    public function manageShippingOrders()
    {
        // Ambil semua shipping orders dengan pagination
        $shippingOrders = ShippingOrder::with(['transaksi.user'])
                                     ->orderBy('created_at', 'desc')
                                     ->paginate(10);
        
        return view('admin.manage-shipping', compact('shippingOrders'));
    }

    /**
     * Create Shipping Order - buat order pengiriman untuk transaksi
     */
    public function createShippingOrder($transaksi_id)
    {
        // Cari transaksi yang akan dibuatkan shipping order
        $transaksi = Transaksi::with('user')->findOrFail($transaksi_id);
        
        return view('admin.create-shipping', compact('transaksi'));
    }

    /**
     * Store Shipping Order - simpan data pengiriman
     */
    public function storeShippingOrder(Request $request)
    {
        // Validasi input form shipping order
        $request->validate([
            'transaksi_id' => 'required|exists:transaksis,id',
            'tracking_number' => 'required|string|unique:shipping_orders,tracking_number',
            'courier' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Buat shipping order baru
        ShippingOrder::create([
            'transaksi_id' => $request->transaksi_id,
            'tracking_number' => $request->tracking_number,
            'courier' => $request->courier,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.shipping')->with('success', 'Order pengiriman berhasil dibuat.');
    }

    /**
     * Update Shipping Status - update status pengiriman
     */
    public function updateShippingStatus(Request $request, $id)
    {
        // Validasi input status
        $request->validate([
            'status' => 'required|in:pending,shipped,delivered,cancelled',
            'shipped_date' => 'nullable|date',
            'delivered_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Update status shipping order
        $shippingOrder = ShippingOrder::findOrFail($id);
        $shippingOrder->update($request->only(['status', 'shipped_date', 'delivered_date', 'notes']));

        return redirect()->route('admin.shipping')->with('success', 'Status pengiriman berhasil diupdate.');
    }
}