document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('services-container');
  if (!container) return;

  fetch('services_api.php')
    .then(r => r.json())
    .then(data => {
      container.innerHTML = '';
      data.forEach(svc => {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3 mb-4';
        col.innerHTML = `
          <img src="${svc.image}"
               alt="${svc.title}"
               class="img-fluid service-img">
          <p class="mt-2 text-purple">${svc.title}</p>
          <p class="price-range text-white">${svc.price}</p>
        `;
        container.appendChild(col);
      });
    })
    .catch(console.error);
});
