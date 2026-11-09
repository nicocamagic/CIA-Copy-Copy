// ══════════════════════════════════════
// CIA - Compañía de Arte y Vida
// JavaScript - Conexión API PHP + MySQL
// ══════════════════════════════════════

// ==========================================
// CONFIGURACIÓN
// ==========================================
const API_URL = 'api.php';

// ==========================================
// API HELPERS
// ==========================================

/**
 * Realiza una petición GET a la API
 */
async function apiGet(action, params = {}) {
    const queryString = new URLSearchParams({ action, ...params }).toString();
    try {
        const response = await fetch(`${API_URL}?${queryString}`);
        const data = await response.json();
        return data;
    } catch (err) {
        console.error('API GET Error:', err);
        return { success: false, error: 'Error de conexión' };
    }
}

/**
 * Realiza una petición POST a la API
 */
async function apiPost(action, data) {
    try {
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        return result;
    } catch (err) {
        console.error('API POST Error:', err);
        return { success: false, error: 'Error de conexión' };
    }
}

// ══════════════════════════════════════
// NAVIGATION
// ══════════════════════════════════════
const pages = ['home', 'talleres', 'shows', 'nosotros', 'contacto', 'faq', 'admin'];

function goPage(p) {
    pages.forEach((id) => {
        const el = document.getElementById('page-' + id);
        if (el) el.classList.toggle('active', id === p);
        const nb = document.getElementById('nav-' + id);
        if (nb) nb.classList.toggle('active', id === p);
        const mb = document.getElementById('mnav-' + id);
        if (mb) mb.classList.toggle('active', id === p);
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Cargar datos según la página
    if (p === 'home') loadHomePubs();
    if (p === 'talleres') loadPagePubs('talleres', 'pubs-talleres');
    if (p === 'shows') loadPagePubs('shows', 'pubs-shows');
    if (p === 'admin' && adminLogged) loadAdminPanel();
    if (p === 'galeria') loadGallery();
    updateSubCounts();
}

function toggleMobile() {
    document.getElementById('mobile-menu').classList.toggle('open');
}

window.addEventListener('scroll', () => {
    document.getElementById('nav').classList.toggle('scrolled', window.scrollY > 40);
});

// ══════════════════════════════════════
// CAROUSEL
// ══════════════════════════════════════
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

function goSlide(n) {
    slides[currentSlide].classList.remove('active');
    dots[currentSlide].classList.remove('active');
    currentSlide = n;
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

setInterval(() => goSlide((currentSlide + 1) % slides.length), 5500);

// ══════════════════════════════════════
// RENDER CARDS
// ══════════════════════════════════════
function renderCard(p) {
    const tagsHtml = p.tags && p.tags.length 
        ? p.tags.map(t => `<span class="card-tag">${t}</span>`).join('') 
        : '';
    return `<div class="card">
        <img class="card-img" src="${p.image}" alt="${p.title}" onerror="this.src='https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600&q=80'" />
        <div class="card-body">
            ${tagsHtml ? `<div class="card-tags">${tagsHtml}</div>` : ''}
            <div class="card-title">${p.title}</div>
            <div class="card-desc">${p.description}</div>
            <div class="card-meta">
                <span>📅 ${p.date}</span>
                ${p.duration ? `<span>⏱ ${p.duration}</span>` : ''}
            </div>
            ${p.price ? `<div class="card-price">${p.price}</div>` : ''}
        </div>
    </div>`;
}

// ══════════════════════════════════════
// CARGAR PUBLICACIONES DESDE LA DB
// ══════════════════════════════════════

async function loadHomePubs() {
    const el = document.getElementById('home-pubs');
    if (!el) return;
    
    el.innerHTML = '<div class="empty">Cargando... 🎭</div>';
    
    const result = await apiGet('get_all_publications');
    
    if (result.success && result.data) {
        const pubs = result.data.slice(0, 3);
        el.innerHTML = pubs.length 
            ? pubs.map(renderCard).join('') 
            : '<div class="empty">Próximamente... 🎭</div>';
    } else {
        el.innerHTML = '<div class="empty">Error al cargar publicaciones</div>';
    }
}

async function loadPagePubs(page, elId) {
    const el = document.getElementById(elId);
    if (!el) return;
    
    el.innerHTML = '<div class="empty">Cargando... 🎭</div>';
    
    const result = await apiGet('get_publications', { page });
    
    if (result.success && result.data) {
        const pubs = result.data;
        el.innerHTML = pubs.length 
            ? pubs.map(renderCard).join('') 
            : `<div class="empty" style="grid-column:1/-1">Próximamente nuevos ${page} 🎭</div>`;
    } else {
        el.innerHTML = '<div class="empty">Error al cargar publicaciones</div>';
    }
}

// ══════════════════════════════════════
// SUBSCRIBE (Sin correos - solo guarda en DB)
// ══════════════════════════════════════
async function subscribe(zone) {
    const inp = document.getElementById('sub-' + zone + '-input');
    const btn = inp ? inp.parentElement.querySelector('.sub-btn') : null;
    const email = inp ? inp.value.trim() : '';

    if (!email || !email.includes('@')) {
        showToast('⚠ Por favor ingresa un correo válido');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Enviando...';
    }

    const result = await apiPost('subscribe', { email });

    if (btn) {
        btn.disabled = false;
        btn.textContent = zone === 'talleres' ? 'Notificarme' : 'Suscribirme';
    }

    if (result.success) {
        const area = document.getElementById('sub-' + zone + '-area');
        if (area) area.innerHTML = `<div class="sub-success">✅ ¡Suscripción exitosa! Bienvenido/a a CIA 🎭</div>`;
        
        updateSubCounts();
        showToast('✅ ¡Suscrito correctamente!');
    } else {
        showToast(result.error || 'Error al suscribirse');
    }
}

async function updateSubCounts() {
    const result = await apiGet('get_subscriber_count');
    const n = result.count || 0;
    
    const txt = n > 0 
        ? `${n} persona${n > 1 ? 's' : ''} ya ${n > 1 ? 'están suscrita' : 'está suscrita'}` 
        : 'Sin spam. Puedes darte de baja cuando quieras.';
    
    ['home', 'nosotros', 'talleres', 'shows', 'contacto'].forEach(z => {
        const el = document.getElementById('sub-count-' + z);
        if (el) el.textContent = txt;
    });
}

// ══════════════════════════════════════
// CONTACT FORM (Sin correos - solo guarda en DB)
// ══════════════════════════════════════
async function sendContact() {
    const nombre = document.getElementById('c-nombre').value.trim();
    const email = document.getElementById('c-email').value.trim();
    const msg = document.getElementById('c-msg').value.trim();
    const btn = document.getElementById('send-contact-btn');

    if (!nombre || !email || !msg) {
        showToast('⚠ Por favor completa todos los campos');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Enviando...';

    const result = await apiPost('contact', { name: nombre, email: email, message: msg });

    btn.disabled = false;
    btn.textContent = 'Enviar Mensaje →';

    if (result.success) {
        showContactSuccess(nombre);
    } else {
        showToast(result.error || 'Error al enviar mensaje');
    }
}

function showContactSuccess(nombre) {
    document.getElementById('contact-form-area').innerHTML = `
    <div style="background:#111;border:1px solid rgba(255,209,102,.3);border-radius:8px;padding:48px;text-align:center">
        <div style="font-size:48px;margin-bottom:16px">🎭</div>
        <h3 style="color:var(--gold);font-family:'Playfair Display',serif;font-size:24px;margin-bottom:12px">¡Mensaje Enviado!</h3>
        <p style="color:var(--muted)">Gracias por escribirnos, ${nombre}.<br/>Te responderemos a la brevedad.</p>
    </div>`;
    showToast('✅ Mensaje enviado correctamente');
}

// ══════════════════════════════════════
// ADMIN
// ══════════════════════════════════════
let adminLogged = false;
let selectedPage = 'talleres';

function adminLogin() {
    const u = document.getElementById('admin-user').value;
    const p = document.getElementById('admin-pass').value;
    
    // Credenciales hardcodeadas (en producción, usar tabla de usuarios)
    if (u === 'admin' && p === 'cia2026') {
        adminLogged = true;
        document.getElementById('admin-login').style.display = 'none';
        document.getElementById('admin-panel').style.display = 'block';
        document.getElementById('f-date').value = new Date().toISOString().split('T')[0];
        loadAdminPanel();
    } else {
        document.getElementById('login-error').style.display = 'block';
    }
}

function adminLogout() {
    adminLogged = false;
    document.getElementById('admin-login').style.display = 'flex';
    document.getElementById('admin-panel').style.display = 'none';
    document.getElementById('admin-user').value = '';
    document.getElementById('admin-pass').value = '';
    document.getElementById('login-error').style.display = 'none';
}

function selectPage(p) {
    selectedPage = p;
    document.getElementById('psel-talleres').classList.toggle('selected', p === 'talleres');
    document.getElementById('psel-shows').classList.toggle('selected', p === 'shows');
}

async function publishContent() {
    const title = document.getElementById('f-title').value.trim();
    const desc = document.getElementById('f-desc').value.trim();
    
    if (!title || !desc) {
        showToast('⚠ El título y la descripción son obligatorios');
        return;
    }

    const img = document.getElementById('f-image').value.trim();
    const price = document.getElementById('f-price').value.trim();
    const duration = document.getElementById('f-duration').value.trim();
    const date = document.getElementById('f-date').value || new Date().toISOString().split('T')[0];
    const tagsRaw = document.getElementById('f-tags').value.trim();
    const tags = tagsRaw ? tagsRaw.split(',').map(t => t.trim()).filter(Boolean) : [];

    const newPub = {
        page: selectedPage,
        title,
        description: desc,
        image: img || 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600&q=80',
        price,
        duration,
        date,
        tags
    };

    const pb = document.getElementById('publish-btn');
    pb.disabled = true;
    pb.textContent = 'Publicando...';

    const result = await apiPost('create_publication', newPub);

    pb.disabled = false;
    pb.textContent = 'Publicar →';

    if (result.success) {
        // Limpiar formulario
        ['f-title', 'f-desc', 'f-image', 'f-price', 'f-duration', 'f-tags'].forEach(id => {
            document.getElementById(id).value = '';
        });
        document.getElementById('f-date').value = new Date().toISOString().split('T')[0];

        // Mostrar éxito
        const sb = document.getElementById('pub-success');
        const sm = document.getElementById('pub-success-msg');
        sb.style.display = 'flex';
        sm.innerHTML = `✅ <strong>"${title}"</strong> publicado en <strong>${selectedPage}</strong>. Los suscriptores serán notificados en su próximo acceso.`;
        setTimeout(() => sb.style.display = 'none', 7000);

        loadAdminPanel();
        showToast(`📢 "${title}" publicado en ${selectedPage}`);
    } else {
        showToast(result.error || 'Error al publicar');
    }
}

async function deletePub(id) {
    if (!confirm('¿Eliminar esta publicación?')) return;
    
    const result = await apiPost('delete_publication', { id });
    
    if (result.success) {
        loadAdminPanel();
        showToast('🗑 Publicación eliminada');
    } else {
        showToast(result.error || 'Error al eliminar');
    }
}

async function uploadToGallery() {
    const fileInput = document.getElementById('media-upload');
    const titleInput = document.getElementById('gallery-title');
    const status = document.getElementById('upload-status');

    if (!fileInput.files[0]) {
        showToast('⚠ Selecciona un archivo');
        return;
    }

    const formData = new FormData();
    formData.append('media-upload', fileInput.files[0]);
    formData.append('title', titleInput.value);

    status.innerHTML = '<p style="color:var(--gold)">Subiendo...</p>';

    try {
        const response = await fetch(`${API_URL}?action=upload_gallery`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast('✅ Imagen subida con éxito');
            fileInput.value = '';
            titleInput.value = '';
            status.innerHTML = '';
            loadAdminPanel();
        } else {
            showToast(result.error || 'Error al subir');
        }
    } catch (e) {
        showToast('Error de conexión');
    }
}

async function loadGallery() {
    const container = document.getElementById('gallery-container');
    if (!container) return;

    const result = await apiGet('get_gallery');
    if (result.success) {
        container.innerHTML = result.data.map(img => `
            <div class="card">
                <img src="${img.image_url}" class="card-img" style="aspect-ratio:1; object-fit:cover" alt="${img.title}">
                <div class="card-body"><div class="card-title">${img.title || 'CIA - Galería'}</div></div>
            </div>
        `).join('');
    }
}

async function loadAdminPanel() {
    if (!adminLogged) return;
    
    // Cargar estadísticas
    const statsResult = await apiGet('get_stats');
    const pubsResult = await apiGet('get_all_publications');
    const subsResult = await apiGet('get_subscribers');
    
    const stats = statsResult.success ? statsResult.data : { publications: 0, subscribers: 0, messages: 0 };
    const pubs = pubsResult.success ? pubsResult.data : [];
    const subs = subsResult.success ? subsResult.data : [];

    document.getElementById('admin-sub-info').textContent = 
        `${stats.subscribers} suscriptor${stats.subscribers !== 1 ? 'es' : ''} activo${stats.subscribers !== 1 ? 's' : ''}`;
    document.getElementById('pubs-list-title').textContent = 
        `Publicaciones (${stats.publications})`;
    document.getElementById('sub-total').textContent = stats.subscribers;
    document.getElementById('pubs-total').textContent = stats.publications;

    // Renderizar publicaciones
    const listEl = document.getElementById('admin-pubs-list');
    if (!pubs.length) {
        listEl.innerHTML = '<div class="empty" style="padding:40px 0">No hay publicaciones aún</div>';
    } else {
        listEl.innerHTML = pubs.map(p => `
            <div class="pub-item">
                <img src="${p.image}" alt="" onerror="this.src='https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=60&q=60'"/>
                <div class="pub-item-info">
                    <div class="pub-item-title">${p.title}</div>
                    <div class="pub-item-meta">
                        <span class="pub-page-tag">${p.page}</span>
                        <span class="pub-date">📅 ${p.date}</span>
                        ${p.price ? `<span class="pub-date">💰 ${p.price}</span>` : ''}
                    </div>
                </div>
                <button class="delete-btn" onclick="deletePub(${p.id})">Eliminar</button>
            </div>
        `).join('');
    }

    // Renderizar suscriptores
    const subsEl = document.getElementById('admin-subs-list');
    subsEl.innerHTML = subs.length 
        ? subs.map(s => `<div style="border-bottom:1px solid rgba(255,255,255,.05);padding:8px 0">📧 ${s.email}</div>`).join('')
        : '<div style="padding:16px 0;color:var(--muted)">Aún no hay suscriptores.</div>';
}

// ══════════════════════════════════════
// TOAST
// ══════════════════════════════════════
let toastTimer;
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ══════════════════════════════════════
// FAQ
// ══════════════════════════════════════
function toggleFaq(btn) {
    const item = btn.parentElement;
    item.classList.toggle('open');
}

// ══════════════════════════════════════
// INIT
// ══════════════════════════════════════
loadHomePubs();
updateSubCounts();
