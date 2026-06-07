<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lacak Pesanan — SMKN 9 Muaro Jambi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: { brand: { 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' } }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans bg-slate-900">

    {{-- ── NAVBAR ──────────────────────────────────────────────────────────── --}}
    <nav class="bg-gradient-to-r from-emerald-700 to-emerald-500 text-white">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h1 class="text-xl font-bold">Lacak Pesanan Anda</h1>
            </div>
            <a href="{{ route('login') }}" class="text-emerald-100 hover:text-white text-sm font-semibold transition">← Kembali ke Login</a>
        </div>
    </nav>

    {{-- ── MAIN CONTENT ───────────────────────────────────────────────────── --}}
    <div class="min-h-screen bg-slate-900 py-8">
        <div class="max-w-6xl mx-auto px-6">

            {{-- ── SEARCH SECTION ──────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Cari Pesanan Anda</h2>
                <p class="text-slate-600 text-sm mb-6">Masukkan nomor pesanan atau nama pelanggan untuk melihat status pengerjaan</p>

                <form id="searchForm" class="space-y-4" onsubmit="handleSearch(event)">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Pesanan</label>
                            <input 
                                type="text" 
                                id="orderNumberInput" 
                                name="order_number"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition text-sm"
                                placeholder="Contoh: ORD-20260604-0009">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Pelanggan</label>
                            <input 
                                type="text" 
                                id="customerNameInput"
                                name="customer_name"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition text-sm"
                                placeholder="Contoh: Pak Tekno">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button 
                            type="submit"
                            class="flex-1 md:flex-none px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition-colors"
                        >
                            Cari Pesanan
                        </button>
                        <button 
                            type="button"
                            onclick="clearSearch()"
                            class="flex-1 md:flex-none px-8 py-3 bg-slate-200 hover:bg-slate-300 text-slate-900 font-semibold rounded-lg transition-colors"
                        >
                            Bersihkan
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── RESULTS SECTION ────────────────────────────────────────── --}}
            <div id="resultsContainer">
                {{-- Results akan dimuat di sini via JavaScript --}}
            </div>

        </div>
    </div>

    {{-- ── MODAL: Detail Timeline ─────────────────────────────────────────── --}}
    <div id="timelineModal" class="fixed inset-0 bg-black/50 backdrop-blur z-50 hidden items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-emerald-700 to-emerald-500 px-6 py-6 flex items-center justify-between">
                <div>
                    <h3 class="text-white font-bold text-lg" id="modalOrderNumber">ORD-20260523-0001</h3>
                    <p class="text-emerald-100 text-sm" id="modalCustomerName">Customer Name</p>
                </div>
                <button onclick="closeTimelineModal()" class="text-white hover:bg-white/20 p-2 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-4">
                {{-- Order Info --}}
                <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-200">
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Status Pembayaran</p>
                        <p class="font-semibold text-slate-900" id="modalPaymentStatus">Belum Bayar</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Total Harga</p>
                        <p class="font-semibold text-slate-900" id="modalTotalPrice">Rp 500.000</p>
                    </div>
                </div>

                {{-- Timeline --}}
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-3">Alur Pengerjaan</p>
                    <div id="modalTimeline" class="space-y-3">
                        {{-- Timeline items akan di-insert oleh JavaScript --}}
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="border-t border-slate-200 px-6 py-4 bg-slate-50">
                <button onclick="closeTimelineModal()" class="w-full bg-slate-300 hover:bg-slate-400 text-slate-900 font-semibold py-2.5 rounded-lg transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto-load order jika ada order number di URL
        document.addEventListener('DOMContentLoaded', function() {
            const preloadOrderNumber = '{{ $preloadOrderNumber ?? "" }}';
            if (preloadOrderNumber) {
                const orderNumber = preloadOrderNumber;
                document.getElementById('orderNumberInput').value = orderNumber;
                searchOrders(orderNumber, '');
            }
        });

        /**
         * Handle form submission untuk search
         */
        function handleSearch(event) {
            event.preventDefault();
            const orderNumber = document.getElementById('orderNumberInput').value.trim();
            const customerName = document.getElementById('customerNameInput').value.trim();

            if (!orderNumber && !customerName) {
                alert('Silakan masukkan nomor pesanan atau nama pelanggan');
                return;
            }

            searchOrders(orderNumber, customerName);
        }

        /**
         * Search pesanan berdasarkan order number atau customer name
         */
        async function searchOrders(orderNumber, customerName) {
            try {
                const container = document.getElementById('resultsContainer');
                container.innerHTML = `
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center mb-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-400"></div>
                        </div>
                        <p class="text-slate-400 text-sm">Mencari pesanan...</p>
                    </div>
                `;

                const params = new URLSearchParams();
                if (orderNumber) params.append('order_number', orderNumber);
                if (customerName) params.append('customer_name', customerName);

                const response = await fetch(`/api/tracking/search?${params.toString()}`);
                const data = await response.json();

                if (data.success) {
                    if (data.data.length > 0) {
                        displaySearchResults(data.data);
                    } else {
                        showNoResults();
                    }
                } else {
                    showError(data.message || 'Terjadi kesalahan saat mencari pesanan');
                }
            } catch (error) {
                console.error('Search error:', error);
                showError('Terjadi kesalahan koneksi');
            }
        }

        /**
         * Tampilkan hasil search dalam card/tabel
         */
        function displaySearchResults(orders) {
            const container = document.getElementById('resultsContainer');
            const resultsHTML = `
                <div class="space-y-4">
                    <div class="text-sm text-slate-400">
                        Ditemukan <span class="font-semibold text-white">${orders.length}</span> pesanan
                    </div>
                    ${orders.map((order, index) => `
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                            <div class="bg-gradient-to-r from-emerald-700 to-emerald-500 px-6 py-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-white font-bold text-lg">${order.order_number}</h3>
                                        <p class="text-emerald-100 text-sm">${order.customer_name}</p>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${getStatusBadgeClass(order.status)} bg-white/20">
                                        ${order.status_label}
                                    </span>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Produk</p>
                                        <p class="font-semibold text-slate-900">${order.products || '-'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Total Harga</p>
                                        <p class="font-semibold text-slate-900">${formatCurrency(order.total_price)}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Status Pembayaran</p>
                                        <p class="font-semibold text-slate-900">${order.payment_status_label}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">Tanggal Pesanan</p>
                                        <p class="font-semibold text-slate-900">${order.created_at}</p>
                                    </div>
                                </div>
                                <button 
                                    type="button"
                                    onclick="showTimeline(${order.id}, '${order.order_number}')"
                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition-colors"
                                >
                                    Lihat Detail Alur Pengerjaan →
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;

            container.innerHTML = resultsHTML;
        }

        /**
         * Tampilkan no results state
         */
        function showNoResults() {
            const container = document.getElementById('resultsContainer');
            container.innerHTML = `
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Pesanan Tidak Ditemukan</h3>
                    <p class="text-slate-600 mb-6">Silakan cek kembali nomor pesanan atau nama pelanggan Anda</p>
                    <button 
                        type="button"
                        onclick="clearSearch()"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition-colors"
                    >
                        Coba Lagi
                    </button>
                </div>
            `;
        }

        /**
         * Tampilkan error message
         */
        function showError(message) {
            const container = document.getElementById('resultsContainer');
            container.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-red-900">Terjadi Kesalahan</h3>
                            <p class="text-red-700 text-sm">${message}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Clear search form
         */
        function clearSearch() {
            document.getElementById('searchForm').reset();
            document.getElementById('resultsContainer').innerHTML = '';
        }

        /**
         * Tampilkan timeline modal untuk order tertentu
         */
        async function showTimeline(orderId, orderNumber) {
            try {
                const response = await fetch('/api/tracking/order-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ order_number: orderNumber })
                });

                const data = await response.json();

                if (data.success) {
                    displayTimelineModal(data.data);
                }
            } catch (error) {
                console.error('Error loading order detail:', error);
                alert('Gagal memuat detail pesanan');
            }
        }

        /**
         * Display timeline dalam modal
         */
        function displayTimelineModal(order) {
            // Update modal header
            document.getElementById('modalOrderNumber').textContent = order.order_number;
            document.getElementById('modalCustomerName').textContent = order.customer_name;

            // Update modal body
            document.getElementById('modalPaymentStatus').textContent = order.payment_status_label;
            
            // Format price
            document.getElementById('modalTotalPrice').textContent = formatCurrency(order.total_price);

            // Build timeline
            const timelineDiv = document.getElementById('modalTimeline');
            timelineDiv.innerHTML = '';

            order.timeline.forEach((item, index) => {
                const timelineItem = document.createElement('div');
                timelineItem.className = 'flex gap-3';

                let iconColor = 'bg-slate-200 text-slate-400';
                if (item.completed) {
                    iconColor = item.active ? 'bg-emerald-500 text-white' : 'bg-emerald-200 text-emerald-600';
                }

                timelineItem.innerHTML = `
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full ${iconColor} flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        ${index < order.timeline.length - 1 ? '<div class="w-0.5 h-6 bg-slate-200 mt-2"></div>' : ''}
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-900">${item.label}</p>
                        ${item.updated_at ? `<p class="text-xs text-slate-500">${item.updated_at}</p>` : '<p class="text-xs text-slate-400">Belum dimulai</p>'}
                    </div>
                `;

                timelineDiv.appendChild(timelineItem);
            });

            // Show modal
            const modal = document.getElementById('timelineModal');
            modal.style.display = 'flex';
        }

        /**
         * Tutup timeline modal
         */
        function closeTimelineModal() {
            const modal = document.getElementById('timelineModal');
            modal.style.display = 'none';
        }

        /**
         * Helper: format currency to IDR
         */
        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
            }).format(value);
        }

        /**
         * Helper: get status badge color class
         */
        function getStatusBadgeClass(status) {
            const classes = {
                'pending': 'text-amber-100',
                'in_progress': 'text-blue-100',
                'completed': 'text-emerald-100',
                'delivered': 'text-green-100',
                'cancelled': 'text-red-100'
            };
            return classes[status] || 'text-slate-100';
        }

        // Close modal saat click di luar modal
        document.getElementById('timelineModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeTimelineModal();
            }
        });
    </script>

</body>
</html>
