<?php
/**
 * Plugin Name:       SALEKIM Pré-projeto
 * Plugin URI:        https://www.salekim.com.br
 * Description:       Troca o bloco "Método VIDA" das listas de programas (Portfólio e Programas) pelo "Pré-projeto", com texto novo, e troca a chamada final da página Sobre por um título "Conheça nossos programas" que leva às ofertas do Portfólio. Acrescenta o bloco "Programa Vida após a aposentadoria", com a página de vendas em /vida-apos-aposentadoria/. Troca o título e o texto da página Sobre. Funciona junto com o plugin SALEKIM Site, sem substituí-lo. Para voltar ao bloco antigo, basta desativar este plugin.
 * Version:           1.6.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            SALEKIM Treinamento e Desenvolvimento Ltda.
 * License:           GPL-2.0-or-later
 * Text Domain:       salekim-pre-projeto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SALEKIM_PP_VERSAO', '1.6.0' );

/** Textos do bloco. Para mudar, edite aqui. */
function salekim_pp_textos() {
	return array(
		'titulo' => 'Pré-projeto',
		'texto'  => array(
			'O Pré-Projeto Vida Após Aposentadoria ajuda você a organizar uma primeira visão da sua vida após a aposentadoria.',
			'Destina-se a pessoas aposentadas, próximas à aposentadoria ou a quem quer se preparar com antecedência.',
		),
	);
}

/**
 * Os blocos de programas vêm de shortcodes do plugin SALEKIM Site ([salekim_portfolio], [salekim_programas]).
 * Aqui só o trecho do Método VIDA é reescrito, depois que o shortcode monta a página.
 */
add_filter( 'do_shortcode_tag', 'salekim_pp_filtrar', 20, 2 );

function salekim_pp_filtrar( $saida, $tag ) {
	if ( ! in_array( $tag, array( 'salekim_portfolio', 'salekim_programas' ), true ) || ! is_string( $saida ) ) {
		return $saida;
	}
	$saida = salekim_pp_trocar( $saida );
	if ( 'salekim_portfolio' === $tag ) {
		$saida = salekim_pp_novo_bloco( $saida );
	}
	return $saida;
}

function salekim_pp_trocar( $html ) {
	// Título do bloco: o primeiro elemento cujo texto é só "Método VIDA" (de preferência um título <h2>…<h6>).
	$nome = '\s*M(?:é|&eacute;|&#233;)todo\s+VIDA\s*';
	if ( ! preg_match( '~<(h[2-6])([^>]*)>' . $nome . '</\1>~iu', $html, $m, PREG_OFFSET_CAPTURE )
		&& ! preg_match( '~<(strong|b|p|div|span|a)([^>]*)>' . $nome . '</\1>~iu', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		return $html;
	}
	$inicio = $m[0][1];
	$nivel  = $m[1][0];
	$t      = salekim_pp_textos();

	// O bloco vai até o título do programa seguinte (mesma etiqueta e mesmos atributos).
	$depois = $inicio + strlen( $m[0][0] );
	$abre   = strpos( $html, '<' . $nivel . $m[2][0] . '>', $depois );
	$fim    = false !== $abre ? $abre : strlen( $html );
	$bloco  = substr( $html, $depois, $fim - $depois );

	// A linha "Para quem está se aposentando…" sai: o texto novo já diz para quem é.
	$publico = 'Para quem est(?:á|&aacute;|&#225;) se aposentando ou j(?:á|&aacute;|&#225;) se aposentou\.?';
	$sem     = preg_replace( '~<(\w+)[^>]*>\s*(?:<(\w+)[^>]*>\s*)?' . $publico . '\s*(?:</\2>\s*)?</\1>~u', '', $bloco, 1, $quantos );
	$bloco   = $quantos ? $sem : preg_replace( '~' . $publico . '~u', '', $bloco, 1 );
	$bloco = preg_replace(
		'#Quem se aposenta sem um plano.*?como\s+apoio\.?#su',
		implode( '</p><p>', array_map( 'esc_html', $t['texto'] ) ),
		$bloco,
		1
	);

	$titulo = '<' . $nivel . $m[2][0] . '>' . esc_html( $t['titulo'] ) . '</' . $nivel . '>';

	return substr( $html, 0, $inicio ) . $titulo . $bloco . substr( $html, $fim );
}

/**
 * Página Sobre: a chamada final ("Faça contato conosco" e dois botões) vira só o título
 * "Conheça nossos programas", que leva às ofertas do Portfólio.
 * A troca é feita na página já pronta, porque esse bloco pode vir do editor, do Elementor ou de um shortcode.
 */
add_action( 'template_redirect', 'salekim_pp_sobre' );

function salekim_pp_sobre() {
	if ( is_admin() || ! is_page( array( 'sobre', 'Sobre' ) ) ) {
		return;
	}
	ob_start(
		function ( $html ) {
			return salekim_pp_sobre_texto( salekim_pp_sobre_trocar( $html ) );
		}
	);
}

function salekim_pp_sobre_trocar( $html ) {
	if ( ! preg_match( '~(<h[1-6][^>]*>)\s*Fa(?:ç|&ccedil;|&#231;)a contato conosco\s*(</h[1-6]>)~iu', $html, $m, PREG_OFFSET_CAPTURE ) ) {
		return $html;
	}
	$link   = home_url( '/portfolio/#ofertas' );
	$titulo = $m[1][0] . '<a class="salekim-pp-link" href="' . esc_url( $link ) . '">Conheça nossos programas</a>' . $m[2][0];
	$inicio = $m[0][1];
	$depois = $inicio + strlen( $m[0][0] );

	// Os botões ficam logo depois do título, ainda dentro do quadro: o trecho vai até o próximo título ou o rodapé.
	$fim = strlen( $html );
	if ( preg_match( '~<h[1-6][\s>]|<footer[\s>]~i', $html, $p, PREG_OFFSET_CAPTURE, $depois ) ) {
		$fim = $p[0][1];
	}
	$trecho = substr( $html, $depois, $fim - $depois );
	$trecho = preg_replace( '~<a\b[^>]*>\s*(?:<[^>]+>\s*)*(?:Pedir proposta para empresa|Ver os programas para pessoas)\s*(?:</[^>]+>\s*)*?</a>~iu', '', $trecho );
	// Tira as caixas que ficaram vazias (onde estavam os botões).
	do {
		$trecho = preg_replace( '~<(div|p|span|li|ul)\b[^>]*class="[^"]*(?:button|botao|acoes)[^"]*"[^>]*>\s*</\1>|<p>\s*</p>~i', '', $trecho, -1, $quantos );
	} while ( $quantos );

	$estilo = '<style>.salekim-pp-link{color:inherit!important;text-decoration:none}.salekim-pp-link::after{content:" →"}.salekim-pp-link:hover{text-decoration:underline;text-underline-offset:.15em}</style>';
	return substr( $html, 0, $inicio ) . $titulo . $trecho . $estilo . substr( $html, $fim );
}

/* ---------- Programa Vida após a aposentadoria ---------- */

/** Endereço da página de vendas, servida pelo próprio plugin. */
function salekim_pp_vida_url() {
	return home_url( '/vida-apos-aposentadoria/' );
}

function salekim_pp_vida_textos() {
	return array(
		'titulo'  => 'Programa Vida após a aposentadoria',
		'publico' => 'Para quem se aposentou e quer um plano para os próximos anos.',
		'texto'   => 'Um processo guiado, em grupo pequeno e com acompanhamento individual, para transformar a falta de direção do início da aposentadoria em um plano real para os próximos anos. São 45 dias, conduzidos pelo Método V.I.D.A.',
	);
}

/**
 * Cria o bloco do Programa Vida no Portfólio copiando o bloco "Formação em desenvolvimento de grupos"
 * (mesmo formato), com título e texto próprios e "Saiba mais" no lugar de "Em breve".
 * Entra logo antes da Formação, depois do Pré-projeto.
 */
function salekim_pp_novo_bloco( $html ) {
	$t = salekim_pp_vida_textos();
	if ( false !== strpos( $html, $t['titulo'] ) || ! class_exists( 'DOMDocument' ) ) {
		return $html;
	}
	$modelo = 'Formação em desenvolvimento de grupos';

	$doc   = new DOMDocument( '1.0', 'UTF-8' );
	$antes = libxml_use_internal_errors( true );
	$ok    = $doc->loadHTML( '<?xml encoding="UTF-8"><div id="salekim-pp-raiz">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $antes );
	if ( ! $ok ) {
		return $html;
	}
	$raiz  = $doc->getElementById( 'salekim-pp-raiz' );
	$xpath = new DOMXPath( $doc );

	// Título da Formação.
	$titulo = null;
	foreach ( $xpath->query( '//h2|//h3|//h4|//h5|//h6' ) as $h ) {
		if ( salekim_pp_texto( $h ) === $modelo ) {
			$titulo = $h;
			break;
		}
	}
	if ( ! $titulo || ! $raiz ) {
		return $html;
	}

	// O bloco é o maior elemento em volta do título que não contém outro título de programa.
	$bloco = $titulo;
	while ( $bloco->parentNode && ! $bloco->parentNode->isSameNode( $raiz ) && 1 === $xpath->query( './/' . $titulo->nodeName, $bloco->parentNode )->length ) {
		$bloco = $bloco->parentNode;
	}
	if ( $bloco->isSameNode( $titulo ) ) {
		return $html;
	}

	// Botão "Saiba mais" do Pré-projeto: serve de modelo para o novo botão.
	$modelo_botao = null;
	foreach ( $xpath->query( '//a' ) as $a ) {
		if ( 'Saiba mais' === salekim_pp_texto( $a ) ) {
			$modelo_botao = $a;
			break;
		}
	}

	$novo = $bloco->cloneNode( true );
	$xn   = new DOMXPath( $doc );
	foreach ( $xn->query( './/*[@id]', $novo ) as $el ) {
		$el->removeAttribute( 'id' );
	}
	if ( $novo instanceof DOMElement && $novo->hasAttribute( 'id' ) ) {
		$novo->setAttribute( 'id', 'programa-vida-apos-aposentadoria' );
	}

	// Título.
	foreach ( $xn->query( './/' . $titulo->nodeName, $novo ) as $h ) {
		salekim_pp_por_texto( $h, $t['titulo'] );
	}

	// Público e descrição: os parágrafos de texto do bloco (fora dos botões).
	$paragrafos = array();
	foreach ( $xn->query( './/p[not(ancestor::a) and not(.//a) and not(.//button)]', $novo ) as $p ) {
		$texto = salekim_pp_texto( $p );
		if ( '' !== $texto && 'Em breve' !== $texto ) {
			$paragrafos[] = $p;
		}
	}
	foreach ( $paragrafos as $i => $p ) {
		if ( 0 === $i ) {
			salekim_pp_por_texto( $p, $t['publico'] );
		} elseif ( 1 === $i ) {
			salekim_pp_por_texto( $p, $t['texto'] );
		} else {
			$p->parentNode->removeChild( $p );
		}
	}

	// "Em breve" vira o botão "Saiba mais" com o link da página de vendas.
	$em_breve = null;
	foreach ( $xn->query( './/*', $novo ) as $el ) {
		if ( 'Em breve' === salekim_pp_texto( $el ) ) {
			$em_breve = $el; // O último encontrado é o mais interno.
		}
	}
	while ( $em_breve && $em_breve->parentNode && ! $em_breve->parentNode->isSameNode( $novo ) && 'Em breve' === salekim_pp_texto( $em_breve->parentNode ) && 1 === $em_breve->parentNode->childNodes->length ) {
		$em_breve = $em_breve->parentNode;
	}
	if ( $modelo_botao ) {
		$botao = $modelo_botao->cloneNode( true );
	} else {
		$botao = $doc->createElement( 'a', 'Saiba mais' );
		$botao->setAttribute( 'class', 'botao laranja' );
	}
	$botao->setAttribute( 'href', salekim_pp_vida_url() );
	$botao->removeAttribute( 'target' );
	$botao->removeAttribute( 'id' );
	if ( $em_breve ) {
		$em_breve->parentNode->replaceChild( $botao, $em_breve );
	} else {
		$whats = $xn->query( './/a[contains(@href, "wa.me")]', $novo )->item( 0 );
		if ( $whats ) {
			$whats->parentNode->insertBefore( $botao, $whats );
		}
	}

	// Mensagem pronta do WhatsApp com o nome do programa novo.
	foreach ( $xn->query( './/a[contains(@href, "wa.me")]', $novo ) as $a ) {
		$a->setAttribute( 'href', str_replace( array( rawurlencode( $modelo ), urlencode( $modelo ) ), rawurlencode( $t['titulo'] ), $a->getAttribute( 'href' ) ) );
	}

	$bloco->parentNode->insertBefore( $novo, $bloco );

	$saida = '';
	foreach ( $raiz->childNodes as $no ) {
		$saida .= $doc->saveHTML( $no );
	}
	return $saida;
}

function salekim_pp_texto( $no ) {
	return trim( preg_replace( '/\s+/u', ' ', $no->textContent ) );
}

/** Troca o texto de um elemento, mantendo a etiqueta interna única (ex.: <strong>), se houver. */
function salekim_pp_por_texto( $el, $texto ) {
	$alvo = $el;
	while ( 1 === $alvo->childNodes->length && $alvo->firstChild instanceof DOMElement ) {
		$alvo = $alvo->firstChild;
	}
	while ( $alvo->firstChild ) {
		$alvo->removeChild( $alvo->firstChild );
	}
	$alvo->appendChild( $alvo->ownerDocument->createTextNode( $texto ) );
}

/** Página de vendas em /vida-apos-aposentadoria/, enquanto não houver uma página do WordPress nesse endereço. */
add_action( 'template_redirect', 'salekim_pp_pagina_vendas', 0 );

function salekim_pp_pagina_vendas() {
	if ( ! is_404() ) {
		return;
	}
	$caminho = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$base    = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( '' !== $base && 0 === strpos( $caminho, $base . '/' ) ) {
		$caminho = substr( $caminho, strlen( $base ) + 1 );
	}
	if ( 'vida-apos-aposentadoria' !== $caminho ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	readfile( plugin_dir_path( __FILE__ ) . 'paginas/vida-apos-aposentadoria.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}

/* ---------- Página Sobre: título e texto novos ---------- */

function salekim_pp_sobre_titulo() {
	return 'Consultora comportamental há mais de 35 anos | Facilitadora de processos de mudança e transição para pessoas, equipes e organizações | Especialista em Processos Grupais (SBDG) | Mestre em Educação | Fundadora da SALEKIM';
}

function salekim_pp_sobre_conteudo() {
	$p = function ( $texto ) {
		return '<p>' . $texto . '</p>';
	};
	$lista = function ( $itens ) {
		return '<ul class="credenciais"><li>' . implode( '</li><li>', $itens ) . '</li></ul>';
	};

	$sobre = '<h2>Sobre</h2>'
		. $p( 'Comecei a carreira desenhando processos. Fui secretária e analista de Organização e Métodos numa siderúrgica, coordenadora de projetos em bancos, diretora administrativa de uma editora, gerente de Desenvolvimento Organizacional de uma construtora e gerente administrativa de um colégio particular.' )
		. $p( 'Com cerca de 12 anos de carreira, o banco onde eu trabalhava foi liquidado. De um dia para o outro fiquei sem rumo, perguntando “e agora?”. Foi a minha primeira transição de verdade, e ninguém tinha me preparado para ela. Um convite me levou para Porto Alegre, onde os cargos de gestão me colocaram diante dos dilemas das pessoas nas organizações.' )
		. $p( 'A virada veio na formação em processos grupais da SBDG. Ali entendi por que tantos processos, por mais bem desenhados que fossem, não se sustentavam: faltava cuidar das pessoas e dos grupos que iam executá-los. Uma mudança só se completa quando quem a vive consegue atravessar a própria transição.' )
		. $p( 'Em 1990, no fim da formação, comecei a atuar como consultora comportamental independente, associada à JD Tonioli e Associados. Em 1996, fundei a SALEKIM Treinamento e Desenvolvimento. Desde então conduzo processos de mudança de cultura, desenvolvimento de equipes e liderança em empresas como Nestlé, Embraer, Vale, Faber-Castell, Roche e Metrô de São Paulo.' );

	$como = '<h2>Como trabalho</h2>'
		. $p( 'Atuo em três níveis:' )
		. $lista(
			array(
				'<strong>Pessoas:</strong> transições de carreira, de papel e de fase da vida, incluindo a aposentadoria.',
				'<strong>Grupos e equipes:</strong> processos grupais como integração, conflitos, liderança, comunicação e decisões.',
				'<strong>Organizações:</strong> mudança de cultura que integra os aspectos psicossociais (subjetividade) aos resultados (objetividade).',
			)
		)
		. $p( 'Hoje dedico parte do meu trabalho a quem está se aposentando, com o Método V.I.D.A., que trabalha ao mesmo tempo o plano de ação (objetividade) e o comportamento de quem vai vivê-lo (subjetividade).' );

	$formacao = '<h2>Formação</h2>'
		. $lista(
			array(
				'Mestre em Educação e graduada em Administração de Empresas pela Universidade Católica de Santos (UNISANTOS), onde integrei o grupo de pesquisa “Formação de Sujeitos: História, Cultura, Sociedade”.',
				'Especialista em Processos Grupais pela SBDG (Sociedade Brasileira de Dinâmica dos Grupos), onde sou facilitadora didata, docente e ex-diretora, e conduzo grupos de formação desde 1998.',
				'Executive Coach e Life Coach pelo ICI.',
				'Formação em Psicoterapias Reichianas (abordagem que trabalha cognição, corpo e emoção juntos).',
			)
		)
		. $p( 'Coautora do livro <em>Manual de Múltiplas Inteligências</em>, autora de artigos científicos e palestrante em congressos nacionais e internacionais de educação, processos grupais, psicoterapia, liderança e gestão.' );

	$secao = function ( $interno ) {
		return '<div class="wp-block-group secao texto-largo sk-sobre-texto">' . $interno . '</div>';
	};
	return $secao( $sobre ) . $secao( $como ) . $secao( $formacao );
}

/** Estilo do título e das seções novas da página Sobre. */
function salekim_pp_sobre_estilo() {
	return '<style id="salekim-pp-sobre">h1.sk-sobre-titulo{font-size:clamp(1.45rem,2.6vw,2.05rem)!important;line-height:1.3!important}'
		. '.sk-sobre-texto>*+*{margin-top:1rem}.sk-sobre-texto>h2+*{margin-top:1.1rem}.sk-sobre-texto ul{margin-top:1rem}</style>';
}

/**
 * Troca o título (h1) da página Sobre e substitui as seções de texto (de "A minha história" até
 * o fim do texto, antes do quadro final) pelo texto novo. A abertura com a foto e o quadro final ficam.
 */
function salekim_pp_sobre_texto( $html ) {
	// Título, com letra menor (o estilo vai no próprio título, para valer em qualquer layout).
	$html = preg_replace_callback(
		'~<h1\b([^>]*)>\s*(?:<[^>]+>\s*)*Comecei desenhando processos\..*?</h1>~su',
		function ( $m ) {
			$atributos = $m[1];
			if ( preg_match( '~\bclass="([^"]*)"~', $atributos ) ) {
				$atributos = preg_replace( '~\bclass="([^"]*)"~', 'class="$1 sk-sobre-titulo"', $atributos, 1 );
			} else {
				$atributos .= ' class="sk-sobre-titulo"';
			}
			$atributos = preg_replace( '~\sstyle="[^"]*"~', '', $atributos );
			return '<h1' . $atributos . ' style="font-size:clamp(1.45rem,2.6vw,2.05rem);line-height:1.3">' . esc_html( salekim_pp_sobre_titulo() ) . '</h1>';
		},
		$html,
		1
	);

	// Estilo das partes novas.
	$html = preg_replace( '~</head>~i', salekim_pp_sobre_estilo() . '</head>', $html, 1 );

	return salekim_pp_sobre_secoes( $html );
}

/**
 * Seções antigas: tudo o que vem do bloco com "A minha história" até o bloco do quadro final
 * ("Conheça nossos programas" ou "Faça contato conosco") sai, e entram as seções novas.
 * Os blocos são achados pela estrutura da página (o ancestral comum dos dois pontos), sem depender
 * de nomes de classes, para funcionar com o editor do WordPress ou com o Elementor.
 */
function salekim_pp_sobre_secoes( $html ) {
	if ( false !== strpos( $html, ' sk-sobre-texto"' ) || ! class_exists( 'DOMDocument' ) ) {
		return $html;
	}
	// Trabalha só no conteúdo principal (<main>); sem ele, na página inteira (<body>).
	$tag = preg_match( '~<main\b[^>]*>~i', $html, $a, PREG_OFFSET_CAPTURE ) ? 'main' : 'body';
	if ( 'body' === $tag && ! preg_match( '~<body\b[^>]*>~i', $html, $a, PREG_OFFSET_CAPTURE ) ) {
		return $html;
	}
	$ini_main = $a[0][1];
	$fim_main = strripos( $html, '</' . $tag . '>' );
	if ( false === $fim_main ) {
		return $html;
	}
	$fim_main += strlen( '</' . $tag . '>' );
	$main = substr( $html, $ini_main, $fim_main - $ini_main );

	$doc   = new DOMDocument( '1.0', 'UTF-8' );
	$antes = libxml_use_internal_errors( true );
	$ok    = $doc->loadHTML( '<?xml encoding="UTF-8">' . $main, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $antes );
	if ( ! $ok ) {
		return $html;
	}
	$xpath = new DOMXPath( $doc );

	$historia = salekim_pp_no_com_texto( $xpath, array( 'A minha história' ) );
	$final    = salekim_pp_no_com_texto( $xpath, array( 'Conheça nossos programas', 'Faça contato conosco' ) );
	if ( ! $historia || ! $final ) {
		return $html;
	}

	// Ancestral comum e, dentro dele, o filho que contém cada ponto.
	$cadeia = array();
	for ( $n = $historia; $n; $n = $n->parentNode ) {
		$cadeia[] = $n;
	}
	$comum = null;
	$filho_final = null;
	for ( $n = $final; $n && $n->parentNode; $n = $n->parentNode ) {
		foreach ( $cadeia as $c ) {
			if ( $c->isSameNode( $n->parentNode ) ) {
				$comum       = $n->parentNode;
				$filho_final = $n;
				break 2;
			}
		}
	}
	if ( ! $comum ) {
		return $html;
	}
	$filho_historia = null;
	foreach ( $cadeia as $c ) {
		if ( $c->parentNode && $c->parentNode->isSameNode( $comum ) ) {
			$filho_historia = $c;
			break;
		}
	}
	if ( ! $filho_historia || $filho_historia->isSameNode( $filho_final ) ) {
		return $html;
	}

	// Remove do bloco da história até o bloco antes do quadro final.
	$n = $filho_historia;
	while ( $n && ! $n->isSameNode( $filho_final ) ) {
		$proximo = $n->nextSibling;
		$comum->removeChild( $n );
		$n = $proximo;
	}

	$novo = $doc->createDocumentFragment();
	$novo->appendXML( salekim_pp_xml( salekim_pp_sobre_conteudo() ) );
	$comum->insertBefore( $novo, $filho_final );

	$saida = '';
	foreach ( $doc->childNodes as $no ) {
		if ( XML_PI_NODE !== $no->nodeType ) {
			$saida .= $doc->saveHTML( $no );
		}
	}
	return substr( $html, 0, $ini_main ) . $saida . substr( $html, $fim_main );
}

/** O elemento mais interno cujo texto é exatamente um dos textos dados. */
function salekim_pp_no_com_texto( $xpath, $textos ) {
	$achado = null;
	$fundo  = -1;
	$textos = array_map( 'mb_strtolower', $textos );
	foreach ( $xpath->query( '//*' ) as $el ) {
		if ( ! in_array( mb_strtolower( salekim_pp_texto( $el ) ), $textos, true ) ) {
			continue;
		}
		$profundidade = 0;
		for ( $n = $el; $n->parentNode; $n = $n->parentNode ) {
			$profundidade++;
		}
		if ( $profundidade > $fundo ) {
			$achado = $el;
			$fundo  = $profundidade;
		}
	}
	return $achado;
}

function salekim_pp_contem( $pai, $filho ) {
	for ( $n = $filho; $n; $n = $n->parentNode ) {
		if ( $n->isSameNode( $pai ) ) {
			return true;
		}
	}
	return false;
}

/** HTML simples das seções novas em XML válido, para o DOM. */
function salekim_pp_xml( $html ) {
	return str_replace( '&nbsp;', '&#160;', $html );
}
