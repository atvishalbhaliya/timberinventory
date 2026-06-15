@extends('layouts.app')

@section('title', 'Dashboard - Timber Inventory')

@section('content')
    <div class="dashboard-hero">
        <div>
            <span class="dashboard-eyebrow"><i data-lucide="radar"></i> Live operations</span>
            <h1>Operations Dashboard</h1>
            <p>Inventory movement, stock health, dispatch pressure, and recent activity in one operational view.</p>
            <div class="dashboard-hero-meta">
                <span><i data-lucide="database"></i> Backend live</span>
                <span><i data-lucide="shield-check"></i> Role filtered</span>
                <span><i data-lucide="clock-3"></i> Updated on refresh</span>
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button class="btn-erp" onclick="reloadDashboard(event)"><i data-lucide="refresh-cw"></i> Refresh</button>
        </div>
    </div>

    <section class="grid-kpis">
        @foreach ([
            ['Raw Material Stock', 'raw_material_stock', 'layers-3'],
            ['Finished Goods Stock', 'finished_goods_stock', 'boxes'],
            ['Total Stock Qty', 'total_stock_qty', 'chart-column'],
            ['Total Items', 'total_items', 'package'],
            ['Low Stock Items', 'low_stock_items', 'triangle-alert'],
            ['Out Of Stock Items', 'out_of_stock_items', 'circle-alert'],
            ['Active Branches', 'active_branches', 'building-2'],
            ['Pending Dispatch', 'pending_dispatch', 'truck'],
        ] as [$label, $field, $icon])
            <article class="erp-kpi-card dashboard-kpi dashboard-kpi-{{ $loop->iteration }}">
                <div class="kpi-top">
                    <div>
                        <div class="erp-kpi-label">{{ $label }}</div>
                        <div class="erp-kpi-value" data-summary-field="{{ $field }}">--</div>
                    </div>
                    <span class="kpi-icon"><i data-lucide="{{ $icon }}"></i></span>
                </div>
                <div class="erp-kpi-trend">
                    <span>Backend API</span>
                    <span class="trend-up" data-summary-status="{{ $field }}">Waiting</span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="layout-grid">
        <article class="erp-card dashboard-panel dashboard-chart-panel">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">Stock Movement Trend</h3>
                    <p class="text-muted" style="margin:4px 0 0;font-size:12px;">Net inventory movement from recent transactions</p>
                </div>
            </div>
            <div id="productionChart" style="min-height: 330px; padding: 6px 10px 12px;"></div>
        </article>

        <article class="erp-card dashboard-panel">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">Alert Center</h3>
                    <p class="text-muted" style="margin:4px 0 0;font-size:12px;">Backend-generated operational alerts</p>
                </div>
            </div>
            <div class="alert-list" id="alerts-container">
                <div class="alert-item"><i data-lucide="loader"></i><span><strong>Loading alerts</strong><span>Waiting for backend response</span></span></div>
            </div>
        </article>
    </section>

    <section class="layout-grid">
        <article class="erp-card dashboard-panel">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">Recent Activity</h3>
                    <p class="text-muted" style="margin:4px 0 0;font-size:12px;">Latest backend stock ledger activity</p>
                </div>
            </div>
            <div class="timeline" id="recent-container">
                <div class="timeline-item"><i data-lucide="loader"></i><span><strong>Loading activity</strong><span>Waiting for backend response</span></span></div>
            </div>
        </article>

        <article class="erp-card dashboard-panel">
            <div class="table-toolbar">
                <div>
                    <h3 class="erp-card-title">Low Stock Items</h3>
                    <p class="text-muted" style="margin:4px 0 0;font-size:12px;">Loaded from dashboard alerts API</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end">Minimum Stock</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody id="low-stock-tbody">
                        <tr><td colspan="4" class="text-muted">Loading low stock data...</td></tr>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection

@push('scripts')
<style>
    .dashboard-hero {
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:18px;
        margin-bottom:18px;
        padding:22px;
        border:1px solid color-mix(in srgb, var(--primary) 22%, var(--border));
        border-radius:14px;
        background:
            linear-gradient(135deg, rgba(var(--primary-rgb), .13), transparent 52%),
            linear-gradient(180deg, var(--surface), color-mix(in srgb, var(--surface-soft) 70%, var(--surface)));
        box-shadow:0 18px 44px rgba(15,23,42,.08);
    }
    .dashboard-eyebrow {
        display:inline-flex;
        align-items:center;
        gap:7px;
        color:var(--primary);
        font-size:12px;
        font-weight:900;
        text-transform:uppercase;
    }
    .dashboard-eyebrow svg { width:16px; height:16px; }
    .dashboard-hero h1 { margin:8px 0 0; font-size:30px; line-height:1.08; font-weight:900; }
    .dashboard-hero p { max-width:720px; margin:8px 0 0; color:var(--muted); }
    .dashboard-hero-meta { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
    .dashboard-hero-meta span {
        display:inline-flex;
        align-items:center;
        gap:6px;
        min-height:30px;
        padding:0 10px;
        border:1px solid var(--border);
        border-radius:999px;
        background:var(--surface);
        color:var(--muted);
        font-size:12px;
        font-weight:800;
    }
    .dashboard-hero-meta svg { width:14px; height:14px; color:var(--primary); }
    .dashboard-kpi {
        border-color:color-mix(in srgb, var(--primary) 14%, var(--border));
        background:linear-gradient(180deg, var(--surface), color-mix(in srgb, var(--surface-soft) 58%, var(--surface)));
    }
    .dashboard-kpi::before {
        content:'';
        position:absolute;
        inset:0 auto 0 0;
        width:4px;
        background:var(--primary);
        opacity:.72;
    }
    .dashboard-kpi .kpi-icon { box-shadow:inset 0 0 0 1px rgba(var(--primary-rgb), .16); }
    .dashboard-panel {
        border-color:color-mix(in srgb, var(--primary) 10%, var(--border));
        box-shadow:0 12px 30px rgba(15,23,42,.06);
    }
    .dashboard-chart-panel { min-height:430px; }
    .dashboard-panel .erp-card-header, .dashboard-panel .table-toolbar {
        border-bottom:1px solid color-mix(in srgb, var(--border) 70%, transparent);
        background:linear-gradient(180deg, color-mix(in srgb, var(--surface-soft) 40%, var(--surface)), var(--surface));
    }
    @media(max-width:768px) {
        .dashboard-hero { align-items:stretch; flex-direction:column; padding:18px; }
        .dashboard-hero h1 { font-size:25px; }
    }
</style>
<script>
    let dashboardCharts = [];
    const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    function chartColors() {
        return [cssVar('--primary'), cssVar('--info'), cssVar('--warning'), cssVar('--danger')];
    }

    function formatNumber(value) {
        if (value === null || value === undefined || value === '') return '0';
        const number = Number(value);
        return Number.isFinite(number) ? number.toLocaleString(undefined, { maximumFractionDigits: 3 }) : value;
    }

    function renderTrend(labels, values) {
        const target = document.querySelector('#productionChart');
        target.innerHTML = '';
        dashboardCharts.forEach(chart => chart.destroy());
        dashboardCharts = [];

        const chart = new ApexCharts(target, {
            chart: { type: 'area', height: 330, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
            colors: chartColors(),
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { opacityFrom: .34, opacityTo: .04 } },
            grid: { borderColor: 'rgba(100,116,139,.18)' },
            series: [{ name: 'Net Stock Movement', data: values }],
            xaxis: { categories: labels },
        });
        chart.render();
        dashboardCharts.push(chart);
    }

    function renderAlert(notification) {
        const typeClass = notification.type === 'warning' ? 'alert-warning' : notification.type === 'danger' ? 'alert-danger' : '';
        return `
            <div class="alert-item ${typeClass}">
                <i data-lucide="${notification.type === 'warning' ? 'triangle-alert' : 'bell-ring'}"></i>
                <span><strong>${notification.type || 'Alert'}</strong><span>${notification.message || ''}</span></span>
                <i data-lucide="chevron-right"></i>
            </div>
        `;
    }

    function renderActivity(activity) {
        return `
            <div class="timeline-item">
                <i data-lucide="activity"></i>
                <span><strong>${activity.transaction_type || 'Activity'}</strong><span>${activity.item_name || 'Item'} - In: ${formatNumber(activity.qty_in)}, Out: ${formatNumber(activity.qty_out)}</span></span>
                <span class="text-muted">${activity.transaction_date ? new Date(activity.transaction_date).toLocaleString() : ''}</span>
            </div>
        `;
    }

    function renderLowStock(items) {
        document.getElementById('low-stock-tbody').innerHTML = items.length ? items.map(item => `
            <tr>
                <td>${item.item_name || ''}</td>
                <td class="text-end">${formatNumber(item.current_qty)}</td>
                <td class="text-end">${formatNumber(item.minimum_qty)}</td>
                <td class="text-end"><span class="badge badge-danger">Low</span></td>
            </tr>
        `).join('') : '<tr><td colspan="4" class="text-muted">No low stock items found.</td></tr>';
    }

    async function loadDashboardData() {
        const [summary, stockSummary, trend, alerts, recent] = await Promise.all([
            window.axios.get('/v1/dashboard/summary'),
            window.axios.get('/v1/stock-summary').catch(() => ({ data: { metrics: {} } })),
            window.axios.get('/v1/dashboard/trend'),
            window.axios.get('/v1/dashboard/alerts'),
            window.axios.get('/v1/dashboard/recent')
        ]);

        const dashboardSummary = { ...(summary.data.data || {}), ...(stockSummary.data.metrics || {}) };

        Object.entries(dashboardSummary).forEach(([field, value]) => {
            const el = document.querySelector(`[data-summary-field="${field}"]`);
            const status = document.querySelector(`[data-summary-status="${field}"]`);
            if (el) el.textContent = formatNumber(value);
            if (status) status.textContent = 'Live';
        });

        renderTrend(trend.data.data.labels || [], trend.data.data.values || []);
        document.getElementById('alerts-container').innerHTML = (alerts.data.data.notifications || []).map(renderAlert).join('') || '<div class="alert-item"><i data-lucide="check-circle"></i><span><strong>No alerts</strong><span>No backend alerts returned</span></span></div>';
        document.getElementById('recent-container').innerHTML = (recent.data.data || []).map(renderActivity).join('') || '<div class="timeline-item"><i data-lucide="activity"></i><span><strong>No activity</strong><span>No recent backend records returned</span></span></div>';
        renderLowStock(alerts.data.data.low_stock || []);

        if (window.lucide) window.lucide.createIcons();
    }

    function reloadDashboard(event) {
        const btn = event.currentTarget;
        btn.style.opacity = '0.55';
        btn.disabled = true;
        loadDashboardData()
            .catch(error => console.error('Failed to load dashboard data', error))
            .finally(() => {
                btn.style.opacity = '1';
                btn.disabled = false;
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData().catch(error => console.error('Failed to load dashboard data', error));
    });

    window.addEventListener('erp-theme-change', function() {
        dashboardCharts.forEach(chart => chart.updateOptions({ colors: chartColors() }));
    });
</script>
@endpush
