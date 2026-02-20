(function () {
	// Cargar token (usa la clave que estés guardando en localStorage, aquí 'token')
	const token = localStorage.getItem('token');

	if (!token) {
		// Redirigir si no hay token
		window.location.href = '/login';
		return;
	}

	async function api(path, options = {}) {
		options.headers = Object.assign({
			'Content-Type': 'application/json',
			'Authorization': `Bearer ${token}`
		}, options.headers || {});

		const res = await fetch(path, options);
		// Manejo básico de sesión expirada
		if (res.status === 401) {
			localStorage.removeItem('token');
			window.location.href = '/login';
			return null;
		}
		return res.json();
	}

	window.loadUsuarios = async function () {
		const data = await api('/api/usuarios');
		if (!data) return;
		const list = document.getElementById('usuarios-list');
		if (list && data.usuarios) {
			list.innerHTML = data.usuarios.map(u => `<li>${u.nombre_completo} (${u.nombre_usuario})</li>`).join('');
		}
	};

	window.loadMe = async function () {
		const data = await api('/api/me');
		if (!data) return;
		const me = document.getElementById('me-name');
		// Ajusta la propiedad según la respuesta real (user / usuario)
		const user = data.user || data.usuario || null;
		if (me && user) {
			me.textContent = user.nombre_completo || user.nombre_usuario || '';
		}
	};

	document.addEventListener('DOMContentLoaded', function () {
		loadMe();
		loadUsuarios();
	});
})();
