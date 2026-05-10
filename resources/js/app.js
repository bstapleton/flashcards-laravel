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

    // Subject correctness stacked bar chart
    const tagCorrectnessStackedOptions = {
        series: [
            {
                name: 'Correct',
                data: window.userData.tag_correctness_breakdown.map(tag => tag.correct_percentage)
            },
            {
                name: 'Partially Correct',
                data: window.userData.tag_correctness_breakdown.map(tag => tag.partial_percentage)
            },
            {
                name: 'Incorrect',
                data: window.userData.tag_correctness_breakdown.map(tag => tag.incorrect_percentage)
            }
        ],
        chart: {
            type: 'bar',
            height: 350,
            stacked: true,
            stackType: '100%',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                columnWidth: '90%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 1,
            colors: ['#fff']
        },
        xaxis: {
            categories: window.userData.tag_correctness_breakdown.map(tag => tag.tag),
            labels: {
                formatter: function (val) {
                    return val + "%";
                }
            },
            title: {
                text: 'Percentage of Attempts'
            }
        },
        yaxis: {
            title: {
                text: 'Subjects'
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val, { seriesIndex, dataPointIndex, w }) {
                    const tag = window.userData.tag_correctness_breakdown[dataPointIndex];
                    const labels = ['Correct', 'Partially Correct', 'Incorrect'];
                    const counts = [
                        tag.correct_count,
                        tag.partial_count,
                        tag.incorrect_count
                    ];
                    return `${labels[seriesIndex]}: ${val}% (${counts[seriesIndex]} attempts)`;
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        colors: ['#3b82f6', '#fbbf24', '#6b7280']
    };

    // Initialize charts if elements exist
    if (document.getElementById('weekly-chart')) {
        new ApexCharts(document.querySelector("#weekly-chart"), weeklyOptions).render();
    }

    if (document.getElementById('monthly-chart')) {
        new ApexCharts(document.querySelector("#monthly-chart"), monthlyOptions).render();
    }

    if (document.getElementById('tag-correctness-stacked-chart')) {
        new ApexCharts(document.querySelector("#tag-correctness-stacked-chart"), tagCorrectnessStackedOptions).render();
    }
}
