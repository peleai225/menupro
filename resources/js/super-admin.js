import Chart from 'chart.js/auto';

window.Chart = Chart;

// Signal que Chart.js est prêt pour les scripts inline
window.dispatchEvent(new CustomEvent('chartjs:ready'));
