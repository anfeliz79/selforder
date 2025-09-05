let salesChart;

function loadDashboard() {
  const filter = document.getElementById("dateFilter").value;
  const start = document.getElementById("startDate").value;
  const end = document.getElementById("endDate").value;

  fetch(`/dashboard?filter=${filter}&start=${start}&end=${end}`)
    .then(r => {
      if (!r.ok) {
        console.error("Error HTTP:", r.status);
        return null;
      }
      return r.json();
    })
    .then(data => {
      // fallback si no hay respuesta válida
      if (!data) {
        data = {
          branches: 0,
          products: 0,
          customers: 0,
          orders: 0,
          income: 0,
          sales: { labels: [new Date().toISOString().slice(0,10)], orders:[0], income:[0] },
          top_products: [],
          top_customers: []
        };
      }

      // aseguramos estructura válida
      if (!data.sales) {
        data.sales = { labels: [new Date().toISOString().slice(0,10)], orders:[0], income:[0] };
      }

      // métricas
      document.getElementById("metricBranches").innerText = data.branches ?? 0;
      document.getElementById("metricProducts").innerText = data.products ?? 0;
      document.getElementById("metricCustomers").innerText = data.customers ?? 0;
      document.getElementById("metricOrders").innerText = data.orders ?? 0;
      document.getElementById("metricIncome").innerText =
        "RD$ " + new Intl.NumberFormat("es-DO", { minimumFractionDigits: 2 }).format(data.income ?? 0);

      // gráfica combinada
      if (salesChart) salesChart.destroy();
      const ctx = document.getElementById("salesChart").getContext("2d");
      salesChart = new Chart(ctx, {
        data: {
          labels: data.sales.labels,
          datasets: [
            {
              type: "line",
              label: "Pedidos",
              data: data.sales.orders,
              borderColor: "#0d6efd",
              backgroundColor: "#0d6efd",
              yAxisID: "yOrders"
            },
            {
              type: "bar",
              label: "Ingresos",
              data: data.sales.income,
              backgroundColor: "rgba(25, 135, 84, 0.6)",
              borderColor: "rgba(25, 135, 84, 1)",
              yAxisID: "yIncome"
            }
          ]
        },
        options: {
          responsive: true,
          interaction: { mode: 'index', intersect: false },
          stacked: false,
          scales: {
            yOrders: {
              type: 'linear',
              position: 'left',
              title: { display: true, text: 'Pedidos' },
              ticks: { stepSize: 1 }
            },
            yIncome: {
              type: 'linear',
              position: 'right',
              title: { display: true, text: 'Ingresos (RD$)' },
              grid: { drawOnChartArea: false },
              ticks: { callback: v => "RD$ " + v }
            }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(ctx) {
                  if (ctx.dataset.label === "Ingresos") {
                    return "Ingresos: RD$ " + new Intl.NumberFormat("es-DO").format(ctx.parsed.y);
                  } else {
                    return "Pedidos: " + ctx.parsed.y;
                  }
                }
              }
            }
          }
        }
      });

      // top productos
      const topProducts = document.getElementById("topProducts");
      topProducts.innerHTML = (data.top_products ?? []).map(p =>
        `<li class="list-group-item d-flex justify-content-between align-items-center">
          ${p.name}
          <span class="badge bg-primary rounded-pill">${p.total}</span>
        </li>`).join("");

      // top clientes
      const topCustomers = document.getElementById("topCustomers");
      topCustomers.innerHTML = (data.top_customers ?? []).map(c =>
        `<li class="list-group-item d-flex justify-content-between align-items-center">
          ${c.name}
          <span class="badge bg-success rounded-pill">${c.total}</span>
        </li>`).join("");
    })
    .catch(err => {
      console.error("Error cargando dashboard:", err);
    });
}

// Mostrar fechas si es custom
document.getElementById("dateFilter").addEventListener("change", function() {
  if (this.value === "custom") {
    document.getElementById("startDate").classList.remove("d-none");
    document.getElementById("endDate").classList.remove("d-none");
  } else {
    document.getElementById("startDate").classList.add("d-none");
    document.getElementById("endDate").classList.add("d-none");
  }
});

loadDashboard();
