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

document.addEventListener('DOMContentLoaded', () => {
    const data = window.statisticsData ?? {};
    buildChart('chartOrders', data.orders, '件数');
    buildChart('chartJournals', data.journals, '件数');
    buildChart('chartMunicipalities', data.municipalities, '件数');
});
