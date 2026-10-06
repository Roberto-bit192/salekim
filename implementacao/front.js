/**
 * SALEKIM Site — front.js 1.3.0
 * - Aviso de cookies (LGPD): salva a escolha e só então carrega GA4/Pixel.
 *   A escolha pode ser revista pelo botão [data-salekim-cookies] do rodapé.
 * - Telefone formatado como (11) 99999-9999.
 * - Botões com data-programa escolhem o programa no formulário de pessoas.
 * - Avisos de sucesso/erro ficam visíveis e são lidos pelo leitor de tela.
 */
(function () {
	'use strict';
	var banner = document.getElementById('salekim-cookies');
	function lerEscolha() {
		try { return localStorage.getItem('salekim_cookies'); } catch (e) { return null; }
	}
	function escolha(valor) {
		var anterior = lerEscolha();
		try { localStorage.setItem('salekim_cookies', valor); } catch (e) {}
		if (banner) { banner.hidden = true; }
		if (anterior === 'aceitos' && valor === 'essenciais') {
			window.location.reload(); // descarrega GA4/Pixel já carregados
			return;
		}
		if (valor === 'aceitos' && anterior !== 'aceitos' && window.salekimLoadTracking) {
			window.salekimLoadTracking();
		}
	}
	if (banner) {
		var jaEscolheu = lerEscolha();
		banner.hidden = !!jaEscolheu;
		if (jaEscolheu === 'aceitos' && window.salekimLoadTracking) {
			window.salekimLoadTracking();
		}
		var aceitar = document.getElementById('salekim-ck-aceitar');
		var recusar = document.getElementById('salekim-ck-recusar');
		if (aceitar) { aceitar.addEventListener('click', function () { escolha('aceitos'); }); }
		if (recusar) { recusar.addEventListener('click', function () { escolha('essenciais'); }); }
		document.addEventListener('click', function (e) {
			if (e.target.closest && e.target.closest('[data-salekim-cookies]')) {
				banner.hidden = false;
				if (aceitar) { aceitar.focus(); }
			}
		});
	}
	document.addEventListener('input', function (e) {
		var t = e.target;
		if (!t || t.type !== 'tel') { return; }
		var d = t.value.replace(/\D/g, '').slice(0, 11);
		var s = d;
		if (d.length > 2) { s = '(' + d.slice(0, 2) + ') ' + d.slice(2); }
		if (d.length > 6) { s = '(' + d.slice(0, 2) + ') ' + d.slice(2, d.length - 4) + '-' + d.slice(-4); }
		t.value = s;
	});
	document.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a[data-programa]') : null;
		if (!a) { return; }
		var campo = document.getElementById('sk-programa');
		if (campo) { campo.value = a.getAttribute('data-programa'); }
	});
	var avisos = document.querySelectorAll('.salekim-ok, .salekim-erro');
	Array.prototype.forEach.call(avisos, function (el, i) {
		el.hidden = false;
		el.setAttribute('role', 'status');
		if (i === 0) { el.scrollIntoView({ block: 'center' }); }
	});
})();
