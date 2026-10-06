/**
 * SALEKIM: comportamento do site.
 * - Menu do celular.
 * - Aviso de cookies (LGPD): Google Analytics e Meta Pixel só carregam depois do "Aceitar todos".
 *   A escolha pode ser revista pelo link "Preferências de cookies" do rodapé.
 * - Botões dos programas e serviços já escolhem o programa ou o assunto no formulário.
 * - Telefone formatado como (11) 99999-9999 durante a digitação.
 * - Página de contato: mostra só o formulário escolhido.
 * - Avisos de envio ficam na tela e são lidos pelo leitor de tela.
 */
(function () {
	'use strict';

	var cfg = window.salekimConfig || {};
	var CHAVE = 'salekim_cookies';

	function $(sel, raiz) { return (raiz || document).querySelector(sel); }
	function $$(sel, raiz) { return Array.prototype.slice.call((raiz || document).querySelectorAll(sel)); }

	/* ---------- Menu do celular ---------- */
	var botaoMenu = $('#sk-botao-menu');
	var menu = $('#sk-menu');
	if (botaoMenu && menu) {
		botaoMenu.addEventListener('click', function () {
			var aberto = menu.classList.toggle('aberto');
			botaoMenu.setAttribute('aria-expanded', aberto ? 'true' : 'false');
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && menu.classList.contains('aberto')) {
				menu.classList.remove('aberto');
				botaoMenu.setAttribute('aria-expanded', 'false');
				botaoMenu.focus();
			}
		});
	}

	/* ---------- Cookies e medição ---------- */
	function lerEscolha() {
		try { return window.localStorage.getItem(CHAVE); } catch (e) { return null; }
	}
	function gravarEscolha(valor) {
		try { window.localStorage.setItem(CHAVE, valor); } catch (e) { /* navegação privada */ }
	}

	var medicaoCarregada = false;
	function carregarMedicao() {
		if (medicaoCarregada) { return; }
		medicaoCarregada = true;
		if (cfg.ga4) {
			var s = document.createElement('script');
			s.async = true;
			s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.ga4);
			document.head.appendChild(s);
			window.dataLayer = window.dataLayer || [];
			window.gtag = function () { window.dataLayer.push(arguments); };
			window.gtag('js', new Date());
			window.gtag('config', cfg.ga4, { anonymize_ip: true });
		}
		if (cfg.pixel) {
			/* Código padrão do Meta Pixel */
			!function (f, b, e, v, n, t, s) {
				if (f.fbq) { return; }
				n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); };
				if (!f._fbq) { f._fbq = n; }
				n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = [];
				t = b.createElement(e); t.async = !0; t.src = v;
				s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s);
			}(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
			window.fbq('init', cfg.pixel);
			window.fbq('track', 'PageView');
		}
	}

	function evento(nome, dados) {
		if (lerEscolha() !== 'aceitos') { return; }
		if (window.gtag) { window.gtag('event', nome, dados || {}); }
		if (window.fbq) {
			if (nome === 'generate_lead') { window.fbq('track', 'Lead', dados || {}); }
			else if (nome === 'whatsapp') { window.fbq('track', 'Contact'); }
			else { window.fbq('trackCustom', nome, dados || {}); }
		}
	}

	var aviso = $('#sk-cookies');
	if (aviso) {
		var aceitar = $('#sk-ck-aceitar');
		var essenciais = $('#sk-ck-essenciais');
		var escolhaAtual = lerEscolha();
		aviso.hidden = !!escolhaAtual;
		if (escolhaAtual === 'aceitos') { carregarMedicao(); }

		var escolher = function (valor) {
			var anterior = lerEscolha();
			gravarEscolha(valor);
			aviso.hidden = true;
			if (anterior === 'aceitos' && valor === 'essenciais') {
				window.location.reload(); /* descarrega Analytics e Pixel já carregados */
				return;
			}
			if (valor === 'aceitos') { carregarMedicao(); }
		};
		aceitar.addEventListener('click', function () { escolher('aceitos'); });
		essenciais.addEventListener('click', function () { escolher('essenciais'); });
		document.addEventListener('click', function (e) {
			if (e.target.closest && e.target.closest('[data-sk-cookies]')) {
				aviso.hidden = false;
				aceitar.focus();
			}
		});
	}

	/* ---------- Telefone ---------- */
	document.addEventListener('input', function (e) {
		var t = e.target;
		if (!t || t.type !== 'tel') { return; }
		var d = t.value.replace(/\D/g, '');
		if (d.length > 11 && d.indexOf('55') === 0) { d = d.slice(2); }
		d = d.slice(0, 11);
		var s = d;
		if (d.length > 2) { s = '(' + d.slice(0, 2) + ') ' + d.slice(2); }
		if (d.length > 6) { s = '(' + d.slice(0, 2) + ') ' + d.slice(2, d.length - 4) + '-' + d.slice(-4); }
		t.value = s;
	});

	/* ---------- Programa e assunto já escolhidos ---------- */
	function escolherOpcao(select, valor) {
		if (!select || !valor) { return false; }
		for (var i = 0; i < select.options.length; i++) {
			if (select.options[i].value === valor) { select.selectedIndex = i; return true; }
		}
		return false;
	}

	document.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a[data-programa], a[data-assunto]') : null;
		if (!a || a.target === '_blank') {
			if (a && a.getAttribute('data-sk-evento') === 'programa_externo') {
				evento('programa_externo', { programa: a.getAttribute('data-programa') });
			}
			return;
		}
		var url;
		try { url = new URL(a.href, window.location.href); } catch (err) { return; }
		if (url.pathname !== window.location.pathname || !url.hash) { return; }
		var alvo = document.getElementById(url.hash.slice(1));
		if (!alvo) { return; }
		var form = alvo.matches('form') ? alvo : $('form.formulario', alvo);
		if (!form) { return; }
		e.preventDefault();
		var programa = a.getAttribute('data-programa');
		var assunto = a.getAttribute('data-assunto');
		if (programa) { escolherOpcao($('select[name="programa"]', form), programa); }
		if (assunto) { escolherOpcao($('select[name="assunto"]', form), assunto); }
		history.replaceState(null, '', url.hash);
		alvo.scrollIntoView({ behavior: 'smooth', block: 'start' });
		var primeiro = $('input[name="nome"]', form);
		if (primeiro) { primeiro.focus({ preventScroll: true }); }
	});

	/* ---------- Contato: escolher o formulário ---------- */
	$$('[data-sk-mostrar]').forEach(function (botao) {
		botao.addEventListener('click', function () {
			var grupo = botao.closest('.escolha-form');
			$$('[data-sk-mostrar]', grupo).forEach(function (b) {
				var ativo = b === botao;
				b.setAttribute('aria-pressed', ativo ? 'true' : 'false');
				var painel = document.getElementById(b.getAttribute('data-sk-mostrar'));
				if (painel) { painel.hidden = !ativo; }
			});
		});
	});

	/* ---------- Copiar contato ---------- */
	$$('[data-copiar]').forEach(function (b) {
		b.addEventListener('click', function () {
			var el = document.getElementById(b.getAttribute('data-copiar'));
			if (!el || !navigator.clipboard) { return; }
			navigator.clipboard.writeText(el.textContent.trim()).then(function () {
				var original = b.textContent;
				b.textContent = 'Copiado';
				setTimeout(function () { b.textContent = original; }, 2000);
			});
		});
	});

	/* ---------- Formulários ---------- */
	$$('form.formulario').forEach(function (form) {
		var erro = $('[data-sk-erro]', form);
		form.addEventListener('submit', function (e) {
			var problema = '';
			var primeiroInvalido = null;
			$$('[required]', form).forEach(function (campo) {
				var vazio = campo.type === 'checkbox' ? !campo.checked : !campo.value.trim();
				var invalido = vazio || (campo.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(campo.value.trim()));
				if (campo.type !== 'checkbox') { campo.setAttribute('aria-invalid', invalido ? 'true' : 'false'); }
				if (invalido && !primeiroInvalido) {
					primeiroInvalido = campo;
					problema = campo.type === 'checkbox' ? 'Para enviar, marque a autorização de uso dos dados.'
						: campo.type === 'email' && !vazio ? 'Confira o e-mail: parece que falta alguma parte.'
						: 'Preencha os campos marcados com *.';
				}
			});
			var tel = $('input[type="tel"]', form);
			if (!problema && tel && tel.value.replace(/\D/g, '').length < 10) {
				problema = 'Confira o telefone: use o DDD e o número, por exemplo (11) 99914-3277.';
				primeiroInvalido = tel;
				tel.setAttribute('aria-invalid', 'true');
			}
			if (problema) {
				e.preventDefault();
				erro.textContent = problema;
				erro.hidden = false;
				primeiroInvalido.focus();
				return;
			}
			erro.hidden = true;
			var botao = $('button[type="submit"]', form);
			if (botao) { botao.disabled = true; botao.textContent = 'Enviando…'; }
		});
		form.noValidate = true; /* as mensagens acima substituem as do navegador */
	});
	window.addEventListener('pageshow', function () {
		$$('form.formulario button[type="submit"][disabled]').forEach(function (b) {
			b.disabled = false;
			b.textContent = b.closest('form').getAttribute('data-sk-tipo') === 'empresa' ? 'Pedir proposta' : 'Enviar';
		});
	});

	/* Depois do envio: leva o foco ao aviso e registra o contato (se os cookies foram aceitos). */
	var retorno = $('.formulario .aviso.ok, .formulario .aviso.erro:not([data-sk-erro])');
	if (retorno) {
		retorno.scrollIntoView({ block: 'center' });
		retorno.focus({ preventScroll: true });
		if (retorno.classList.contains('ok')) {
			var tipo = retorno.closest('form').getAttribute('data-sk-tipo');
			setTimeout(function () { evento('generate_lead', { formulario: tipo === 'empresa' ? 'proposta' : 'programas' }); }, 1200);
		}
	}

	document.addEventListener('click', function (e) {
		var w = e.target.closest ? e.target.closest('[data-sk-evento="whatsapp"], a[href^="https://wa.me/"]') : null;
		if (w) { evento('whatsapp', {}); }
	});
})();
