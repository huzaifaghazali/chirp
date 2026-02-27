import "./bootstrap";

document.addEventListener("DOMContentLoaded", function () {
    // Check if we're on the dashboard page
    const userChartCanvas = document.getElementById("userChart");
    const chirpChartCanvas = document.getElementById("chirpChart");

    // User Registration Chart
    if (userChartCanvas) {
        const userCtx = userChartCanvas.getContext("2d");
        new Chart(userCtx, {
            type: "line",
            data: {
                labels: userChartCanvas.dataset.labels
                    ? JSON.parse(userChartCanvas.dataset.labels)
                    : [],
                datasets: [
                    {
                        label: "New Users",
                        data: userChartCanvas.dataset.data
                            ? JSON.parse(userChartCanvas.dataset.data)
                            : [],
                        borderColor: "#5580d2",
                        backgroundColor: "rgba(85, 128, 210, 0.1)",
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                },
            },
        });
    }

    // Chirps Chart
    if (chirpChartCanvas) {
        const chirpCtx = chirpChartCanvas.getContext("2d");
        new Chart(chirpCtx, {
            type: "bar",
            data: {
                labels: chirpChartCanvas.dataset.labels
                    ? JSON.parse(chirpChartCanvas.dataset.labels)
                    : [],
                datasets: [
                    {
                        label: "Chirps",
                        data: chirpChartCanvas.dataset.data
                            ? JSON.parse(chirpChartCanvas.dataset.data)
                            : [],
                        backgroundColor: "#598b6e",
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                },
            },
        });
    }
});
