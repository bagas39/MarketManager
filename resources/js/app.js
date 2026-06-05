import './bootstrap';

function escapeHtml(value) {
	return String(value ?? '')
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;')
		.replace(/`/g, '&#96;');
}
window.escapeHtml = escapeHtml;

document.addEventListener('DOMContentLoaded', () => {
	const sidebar = document.getElementById('sidebar');
	const overlay = document.getElementById('sidebar-overlay');
	const openButton = document.getElementById('sidebar-open-button');
	const closeButton = document.getElementById('sidebar-close-button');

	if (!sidebar || !overlay || !openButton || !closeButton) {
		return;
	}

	const openSidebar = () => {
		sidebar.classList.remove('-translate-x-full');
		overlay.classList.remove('hidden');
		document.body.classList.add('overflow-hidden');
	};

	const closeSidebar = () => {
		sidebar.classList.add('-translate-x-full');
		overlay.classList.add('hidden');
		document.body.classList.remove('overflow-hidden');
	};

	openButton.addEventListener('click', openSidebar);
	closeButton.addEventListener('click', closeSidebar);
	overlay.addEventListener('click', closeSidebar);

	window.addEventListener('resize', () => {
		if (window.innerWidth >= 1024) {
			closeSidebar();
		}
	});

		const applyForceMd = () => {
			try {
				if (window.innerWidth >= 769) document.body.classList.add('force-md-active');
				else document.body.classList.remove('force-md-active');
			} catch (e) { }
		};

		applyForceMd();
		window.addEventListener('resize', applyForceMd);
});
