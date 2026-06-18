function buildChart(canvasId, rows, label) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || typeof Chart === 'undefined' || !rows?.length) {
        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: rows.map((r) => r.label),
            datasets: [{
                label,
                data: rows.map((r) => r.count),
                backgroundColor: 'rgba(108, 117, 125, 0.6)',
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { maxRotation: 45, minRotation: 0 } },
            },
        },
    });
}

function resolveCharts() {
    const data = window.statisticsData ?? {};

    if (Array.isArray(data.charts)) {
        return data.charts;
    }

    const legacy = [];
    if (data.orders?.length) {
        legacy.push({ canvasId: 'chartOrders', rows: data.orders, label: '件数' });
    }
    if (data.journals?.length) {
        legacy.push({ canvasId: 'chartJournals', rows: data.journals, label: '件数' });
    }
    if (data.municipalities?.length) {
        legacy.push({ canvasId: 'chartMunicipalities', rows: data.municipalities, label: '件数' });
    }

    return legacy;
}

function initStatisticsCharts() {
    if (typeof Chart === 'undefined') {
        return false;
    }

    resolveCharts().forEach(({ canvasId, rows, label }) => {
        buildChart(canvasId, rows, label ?? '件数');
    });

    return true;
}

function bootStatisticsCharts() {
    if (initStatisticsCharts()) {
        return;
    }

    window.setTimeout(bootStatisticsCharts, 50);
}

if (document.readyState === 'complete') {
    bootStatisticsCharts();
} else {
    window.addEventListener('load', bootStatisticsCharts);
}
