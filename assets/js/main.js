// Mobile nav
const hamburger = document.getElementById('hamburger');
const nav = document.getElementById('nav');
hamburger?.addEventListener('click', () => {
  const open = nav.classList.toggle('open');
  hamburger.setAttribute('aria-expanded', String(open));
});

// Footer year
document.getElementById('year').textContent = new Date().getFullYear();

// Intersection reveal
const observer = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{
    if(e.isIntersecting){ e.target.classList.add('visible'); }
  });
},{ threshold: 0.15 });
document.querySelectorAll('.reveal-up').forEach(el=>observer.observe(el));

// Accordion
document.querySelectorAll('.acc-toggle').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    btn.parentElement.classList.toggle('open');
  });
});

// Helper: AJAX wrapper
async function postJSON(url, data) {
  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    });
    
    // Check if response is OK
    if (!res.ok) {
      const text = await res.text();
      throw new Error(`Server error: ${res.status} ${res.statusText}`);
    }
    
    // Check if response is JSON
    const contentType = res.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await res.text();
      console.error('Non-JSON response:', text);
      throw new Error('Server returned invalid response format');
    }
    
    const json = await res.json();
    
    if (!json.ok) {
      throw new Error(json.error || 'Request failed');
    }
    
    return json;
  } catch (error) {
    console.error('Fetch error:', error);
    if (error.name === 'TypeError' && error.message.includes('fetch')) {
      throw new Error('Network error. Please check your connection.');
    }
    throw error;
  }
}

function formToJSON(form) {
  const data = {};
  new FormData(form).forEach((value, key) => {
    // Handle checkboxes and multiple selects
    if (data[key] !== undefined) {
      if (!Array.isArray(data[key])) {
        data[key] = [data[key]];
      }
      data[key].push(value);
    } else {
      data[key] = value;
    }
  });
  return data;
}

function attachAJAX(formId, endpoint) {
  const form = document.getElementById(formId);
  if (!form) {
    console.warn(`Form #${formId} not found`);
    return;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn?.textContent;
    const originalHTML = btn?.innerHTML;
    
    try {
      // Show loading state
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="loading-spinner">⏳</span> Sending...';
      }
      
      const payload = formToJSON(form);
      console.log('Submitting:', payload);
      
      const res = await postJSON(endpoint, payload);
      
      // Show success state
      if (btn) {
        btn.innerHTML = '<span class="success-check">✓</span> Sent!';
        btn.classList.add('success');
      }
      
      // Show success message
      alert(res.message || 'Form submitted successfully!');
      
      // Reset form
      form.reset();
      
      // Reset button after delay
      setTimeout(() => {
        if (btn) {
          btn.disabled = false;
          btn.textContent = originalText;
          btn.innerHTML = originalHTML;
          btn.classList.remove('success');
        }
      }, 3000);
      
    } catch (err) {
      console.error('Form submission error:', err);
      
      // Show error message
      alert(err.message || 'Failed to submit form. Please try again.');
      
      // Reset button
      if (btn) {
        btn.disabled = false;
        btn.textContent = originalText;
        btn.innerHTML = originalHTML;
        btn.classList.remove('success');
      }
    }
  });
}

// Set default button texts and attach handlers
document.addEventListener('DOMContentLoaded', () => {
  // Set default texts
  document.querySelectorAll('form button[type="submit"]').forEach(btn => {
    if (!btn.dataset.default) {
      btn.dataset.default = btn.textContent;
    }
  });
  
  // Attach form handlers with timeout protection
  const forms = [
    { id: 'quick-intake', endpoint: 'server/handlers/submit_contact.php' },
    { id: 'contact-form', endpoint: 'server/handlers/submit_contact.php' },
    { id: 'appointment-form', endpoint: 'server/handlers/submit_appointment.php' },
    { id: 'complaint-form', endpoint: 'server/handlers/submit_complaint.php' }
  ];
  
  forms.forEach(({ id, endpoint }) => {
    setTimeout(() => attachAJAX(id, endpoint), 100);
  });
});



// Add some basic CSS for loading states
const style = document.createElement('style');
style.textContent = `
  .loading-spinner {
    display: inline-block;
    animation: spin 1s linear infinite;
  }
  .success-check {
    color: green;
  }
  button.success {
    background-color: #4CAF50 !important;
  }
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
`;
document.head.appendChild(style);