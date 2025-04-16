document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('services-container');
  if (container) {
    fetch('services.json')
      .then(response => response.json())
      .then(data => {
        data.forEach(service => {
          const div = document.createElement('div');
          div.classList.add('service-card');
          div.innerHTML = `
            <h3>${service.name}</h3>
            <p>${service.description}</p>
            <strong>$${service.price}</strong>
          `;
          container.appendChild(div);
        });
      })
      .catch(error => console.error("Error loading services:", error));
  }
});
