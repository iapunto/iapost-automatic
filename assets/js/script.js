// Inicialización del gráfico con Chart.js (reemplazar con la implementación real)
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('weekly-post-count-chart').getContext('2d');
    var weeklyPostCounts = { 
        'Lunes': 5,
        'Martes': 3,
        'Miércoles': 7,
        'Jueves': 4,
        'Viernes': 2,
        'Sábado': 1,
        'Domingo': 3 
    }; // Reemplazar con datos reales

    var data = {
        labels: Object.keys(weeklyPostCounts),
        datasets: [{
            label: 'Number of Posts',
            data: Object.values(weeklyPostCounts),
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    };

    var options = {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };

    var myChart = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: options
    });
});