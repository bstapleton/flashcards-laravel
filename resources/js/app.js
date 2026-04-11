import './bootstrap';
import ApexCharts from 'apexcharts';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Initialize dashboard charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboardCharts();
});

function initializeDashboardCharts() {
    // Weekly comparison chart
    const weeklyOptions = {
        series: [{
            name: 'This Week',
            data: [
                window.userData.this_week.total_attempts,
                window.userData.this_week.fresh_learning_questions,
                window.userData.this_week.intermediate_mastery_questions,
                window.userData.this_week.high_mastery_questions
            ]
        }, {
            name: 'Last Week',
            data: [
                window.userData.last_week.total_attempts,
                window.userData.last_week.fresh_learning_questions,
                window.userData.last_week.intermediate_mastery_questions,
                window.userData.last_week.high_mastery_questions
            ]
        }],
        chart: {
            type: 'bar',
            height: 250,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: ['Total Attempts', 'Fresh Learning', 'Intermediate', 'High Mastery'],
        },
        yaxis: {
            title: {
                text: 'Questions'
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " questions"
                }
            }
        },
        colors: ['#6366f1', '#94a3b8']
    };

    // Monthly comparison chart
    const monthlyOptions = {
        series: [{
            name: 'This Month',
            data: [
                window.userData.this_month.total_attempts,
                window.userData.this_month.fresh_learning_questions,
                window.userData.this_month.intermediate_mastery_questions,
                window.userData.this_month.high_mastery_questions
            ]
        }, {
            name: 'Last Month',
            data: [
                window.userData.last_month.total_attempts,
                window.userData.last_month.fresh_learning_questions,
                window.userData.last_month.intermediate_mastery_questions,
                window.userData.last_month.high_mastery_questions
            ]
        }],
        chart: {
            type: 'bar',
            height: 250,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: ['Total Attempts', 'Fresh Learning', 'Intermediate', 'High Mastery'],
        },
        yaxis: {
            title: {
                text: 'Questions'
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " questions"
                }
            }
        },
        colors: ['#10b981', '#94a3b8']
    };

    // Initialize charts if elements exist
    if (document.getElementById('weekly-chart')) {
        new ApexCharts(document.querySelector("#weekly-chart"), weeklyOptions).render();
    }
    
    if (document.getElementById('monthly-chart')) {
        new ApexCharts(document.querySelector("#monthly-chart"), monthlyOptions).render();
    }
}
