<?php
/**
 * Textos iniciais do site, a partir do briefing e do rascunho de textos de 25/09/2026.
 *
 * As páginas são gravadas em blocos do editor do WordPress, para que a SALEKIM
 * possa mudar qualquer texto pelo painel. As partes que dependem de cadastros
 * (programas, serviços, clientes, depoimentos, formulários) entram como shortcodes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Montagem de blocos ---------- */

function salekim_b_atributos( $atributos ) {
	return $atributos ? ' ' . wp_json_encode( $atributos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
}

function salekim_b_p( $texto, $classe = '' ) {
	$atr = $classe ? array( 'className' => $classe ) : array();
	$cls = $classe ? ' class="' . $classe . '"' : '';
	return "<!-- wp:paragraph" . salekim_b_atributos( $atr ) . " -->\n<p{$cls}>{$texto}</p>\n<!-- /wp:paragraph -->\n";
}

function salekim_b_h( $texto, $nivel = 2, $classe = '' ) {
	$atr = array();
	if ( 2 !== $nivel ) {
		$atr['level'] = $nivel;
	}
	if ( $classe ) {
		$atr['className'] = $classe;
	}
	$cls = trim( 'wp-block-heading ' . $classe );
	return "<!-- wp:heading" . salekim_b_atributos( $atr ) . " -->\n<h{$nivel} class=\"{$cls}\">{$texto}</h{$nivel}>\n<!-- /wp:heading -->\n";
}

function salekim_b_lista( $itens, $classe = '', $ordenada = false ) {
	$atr = array();
	if ( $ordenada ) {
		$atr['ordered'] = true;
	}
	if ( $classe ) {
		$atr['className'] = $classe;
	}
	$tag = $ordenada ? 'ol' : 'ul';
	$cls = trim( 'wp-block-list ' . $classe );
	$out = "<!-- wp:list" . salekim_b_atributos( $atr ) . " -->\n<{$tag} class=\"{$cls}\">";
	foreach ( $itens as $item ) {
		$out .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->";
	}
	return $out . "</{$tag}>\n<!-- /wp:list -->\n";
}

/** @param array<int, array{0:string,1:string,2?:string}> $botoes texto, endereço, classe (azul, laranja, contorno). */
function salekim_b_botoes( $botoes ) {
	$out = "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\">";
	foreach ( $botoes as $b ) {
		$classe = isset( $b[2] ) ? $b[2] : '';
		$atr    = $classe ? array( 'className' => $classe ) : array();
		$cls    = trim( 'wp-block-button ' . $classe );
		$out   .= "<!-- wp:button" . salekim_b_atributos( $atr ) . " -->\n<div class=\"{$cls}\"><a class=\"wp-block-button__link wp-element-button\" href=\"" . esc_url( $b[1] ) . "\">{$b[0]}</a></div>\n<!-- /wp:button -->";
	}
	return $out . "</div>\n<!-- /wp:buttons -->\n";
}

function salekim_b_grupo( $interno, $classe = '', $ancora = '' ) {
	$atr = array();
	if ( $ancora ) {
		$atr['anchor'] = $ancora;
	}
	if ( $classe ) {
		$atr['className'] = $classe;
	}
	$id  = $ancora ? ' id="' . $ancora . '"' : '';
	$cls = trim( 'wp-block-group ' . $classe );
	return "<!-- wp:group" . salekim_b_atributos( $atr ) . " -->\n<div{$id} class=\"{$cls}\">{$interno}</div>\n<!-- /wp:group -->\n";
}

/** @param array<int, array{0:string,1?:string}> $colunas conteúdo e classe de cada coluna. */
function salekim_b_colunas( $colunas, $classe = '' ) {
	$atr = $classe ? array( 'className' => $classe ) : array();
	$cls = trim( 'wp-block-columns ' . $classe );
	$out = "<!-- wp:columns" . salekim_b_atributos( $atr ) . " -->\n<div class=\"{$cls}\">";
	foreach ( $colunas as $c ) {
		$cc   = isset( $c[1] ) ? $c[1] : '';
		$catr = $cc ? array( 'className' => $cc ) : array();
		$ccls = trim( 'wp-block-column ' . $cc );
		$out .= "<!-- wp:column" . salekim_b_atributos( $catr ) . " -->\n<div class=\"{$ccls}\">{$c[0]}</div>\n<!-- /wp:column -->";
	}
	return $out . "</div>\n<!-- /wp:columns -->\n";
}

function salekim_b_sc( $shortcode ) {
	return "<!-- wp:shortcode -->\n{$shortcode}\n<!-- /wp:shortcode -->\n";
}

/** Endereço relativo, para os links continuarem certos se o domínio mudar. */
function salekim_rel( $caminho ) {
	return wp_make_link_relative( home_url( $caminho ) );
}

/* ---------- Páginas ---------- */

/**
 * Páginas do site, na ordem do menu.
 *
 * @return array<string, array{titulo:string, descricao:string, menu:bool, conteudo:string}>
 */
function salekim_paginas_padrao() {
	$empresas  = salekim_rel( '/para-empresas/' );
	$programas = salekim_rel( '/programas/' );
	$sobre     = salekim_rel( '/sobre/' );
	$proposta  = $empresas . '#pedir-proposta';
	$saber     = $programas . '#saber-mais';

	/* Início */
	$inicio  = salekim_b_colunas(
		array(
			array(
				salekim_b_p( 'Desenvolvimento humano e organizacional desde 1996', 'rotulo' )
				. salekim_b_h( 'Equipes engajadas e com foco em resultado começam em líderes que entendem de gente.', 1 )
				. salekim_b_p( 'Há mais de 35 anos, a SALEKIM acompanha pessoas, equipes e organizações em processos de transformação. O trabalho usa uma metodologia vivencial, fundamentada em diferentes abordagens do comportamento humano, da liderança e da gestão.', 'discreto' )
				. salekim_b_botoes(
					array(
						array( 'Sou de uma empresa', $empresas, 'azul' ),
						array( 'Quero me desenvolver', $programas, 'laranja' ),
					)
				)
				. salekim_b_p( 'Transformação com profundidade e leveza.', 'assinatura' ),
				'abertura-texto',
			),
			array( salekim_b_sc( '[salekim_roda]' ), 'abertura-roda' ),
		),
		'abertura'
	);
	$inicio .= salekim_b_sc( '[salekim_clientes formato="faixa"]' );
	$inicio .= salekim_b_grupo(
		salekim_b_p( 'Por onde começar', 'rotulo' )
		. salekim_b_h( 'Dois caminhos, o mesmo cuidado com as pessoas.' )
		. salekim_b_colunas(
			array(
				array(
					salekim_b_h( 'Para empresas', 3 )
					. salekim_b_p( 'Para organizações que precisam transformar cultura, lideranças e equipes.' )
					. salekim_b_lista( array( 'Transformação organizacional', 'Desenvolvimento de lideranças e equipes', 'Palestras e workshops' ) )
					. salekim_b_botoes( array( array( 'Conhecer as soluções', $empresas, 'azul' ) ) ),
					'porta empresas',
				),
				array(
					salekim_b_h( 'Para pessoas', 3 )
					. salekim_b_p( 'Para quem quer se desenvolver, formar-se como facilitador ou construir um novo capítulo de vida.' )
					. salekim_b_lista( array( 'Método VIDA, para a transição para a aposentadoria', 'Formação, supervisão e mentoria para quem conduz grupos', 'Acompanhamento individual' ) )
					. salekim_b_botoes( array( array( 'Ver os programas', $programas, 'laranja' ) ) ),
					'porta pessoas',
				),
			),
			'portas'
		),
		'secao'
	);
	$inicio .= salekim_b_grupo(
		salekim_b_p( 'Como trabalhamos', 'rotulo' )
		. salekim_b_h( 'A organização vista como um sistema, em três níveis ao mesmo tempo.' )
		. salekim_b_colunas(
			array(
				array( salekim_b_h( 'Indivíduo', 3 ) . salekim_b_p( 'Autoconhecimento, habilidades interpessoais e o jeito de cada um liderar.', 'discreto' ), 'nivel' ),
				array( salekim_b_h( 'Grupo', 3 ) . salekim_b_p( 'Vínculos, comunicação e a forma como a equipe se organiza para a tarefa.', 'discreto' ), 'nivel' ),
				array( salekim_b_h( 'Organização', 3 ) . salekim_b_p( 'A cultura, o conjunto de práticas do dia a dia, que muda o ambiente em que a empresa atua e é mudada por ele.', 'discreto' ), 'nivel' ),
			),
			'niveis'
		)
		. salekim_b_p( 'Usamos metodologias ativas, baseadas nos princípios da educação de adultos. O aprendizado parte da experiência de quem participa e junta duas dimensões que as empresas costumam tratar separadamente: a tarefa e a emoção de quem executa a tarefa.', 'tarefa-emocao' )
		. salekim_b_p( '<a class="link-seta" href="' . esc_url( $empresas ) . '">Ver como trabalhamos com empresas</a>' ),
		'secao'
	);
	$inicio .= salekim_b_grupo(
		salekim_b_colunas(
			array(
				array( salekim_b_sc( '[salekim_foto]' ), 'retrato-coluna' ),
				array(
					salekim_b_p( 'Quem conduz', 'rotulo' )
					. salekim_b_h( 'Genira Rosa dos Santos' )
					. salekim_b_p( 'Comecei desenhando processos. Foi assim que descobri que quem sustenta um processo são as pessoas.', 'citacao' )
					. salekim_b_lista(
						array(
							'Mestre em educação pela Universidade Católica de Santos (UNISANTOS)',
							'Didata e docente da Sociedade Brasileira de Dinâmica dos Grupos (SBDG)',
							'Consultora comportamental desde 1990 e fundadora da SALEKIM em 1996',
							'Coautora do livro <em>Manual de múltiplas inteligências</em>',
						),
						'credenciais'
					)
					. salekim_b_p( '<a class="link-seta" href="' . esc_url( $sobre ) . '">Conhecer a trajetória</a>' ),
				),
			),
			'conduz'
		),
		'secao'
	);
	$inicio .= salekim_b_sc( '[salekim_depoimentos]' );
	$inicio .= salekim_b_grupo(
		salekim_b_h( 'Vamos conversar sobre o seu desafio?' )
		. salekim_b_p( 'Conte o que a sua equipe ou você está vivendo. A Genira responde pessoalmente.' )
		. salekim_b_botoes(
			array(
				array( 'Pedir proposta para empresa', $proposta ),
				array( 'Saber mais sobre os programas', $saber, 'contorno' ),
			)
		),
		'chamada'
	);

	/* Sobre */
	$sobre_c  = salekim_b_grupo(
		salekim_b_colunas(
			array(
				array( salekim_b_sc( '[salekim_foto]' ), 'retrato-coluna' ),
				array(
					salekim_b_p( 'Sobre', 'rotulo' )
					. salekim_b_h( 'Comecei desenhando processos. Foi assim que descobri que quem sustenta um processo são as pessoas.', 1 )
					. salekim_b_p( 'Genira Rosa dos Santos, sócia, consultora e fundadora da SALEKIM', 'discreto' ),
				),
			),
			'conduz'
		),
		'secao'
	);
	$sobre_c .= salekim_b_grupo(
		salekim_b_p( 'A minha história', 'rotulo' )
		. salekim_b_h( 'Uma transição de carreira que virou método.' )
		. salekim_b_lista(
			array(
				'<em>Início</em><strong>Organização e métodos na siderurgia</strong>Meu primeiro trabalho foi numa indústria siderúrgica, como secretária de um departamento de organização e métodos. Cinco anos depois, já formada em administração, fui promovida a analista da área. Escrevia normas, procedimentos e fluxos de trabalho.',
				'<em>Depois</em><strong>Bancos, editora, construtora e colégio</strong>Depois vieram os bancos, onde fui analista e coordenadora de projetos de organização, sistemas e métodos. Em seguida fui diretora administrativa de uma editora, gerente de desenvolvimento organizacional de uma construtora e gerente administrativa de um colégio.',
				'<em>A virada</em><strong>Facilitação de processos grupais</strong>Em paralelo, fiz uma formação em nível de pós-graduação para facilitadora de processos grupais, e foi ali que veio a virada. Percebi que os processos que eu desenhava não se sustentavam porque ninguém cuidava das pessoas que iam executá-los. O desenho estava certo. Faltava trabalhar com quem ia colocá-lo em prática. Descobri também que era com pessoas, e não com fluxos, que estavam o meu talento e o meu prazer.',
				'<em>1990</em><strong>Consultora comportamental</strong>Comecei a carreira de consultora comportamental, conduzindo projetos de mudança de cultura em organizações que buscavam excelência nos processos.',
				'<em>1996</em><strong>Fundação da SALEKIM</strong>Foi a transição de carreira mais importante da minha vida, e não foi simples. Precisei construir um novo jeito de enxergar o trabalho, uma nova identidade profissional e um método próprio. Fui estudar psicologia, aprender ferramentas novas e entender o comportamento humano por dentro. O desafio maior era juntar duas dimensões que as empresas costumam tratar separadamente: a tarefa e a emoção de quem executa a tarefa.',
				'<em>Mestrado</em><strong>Base acadêmica em educação</strong>Para aprofundar a base acadêmica, fiz o mestrado em educação. Ao longo do caminho, a metodologia da SALEKIM foi tomando forma, sempre fundamentada em teóricos de diferentes campos do conhecimento, e passei a ajudar outros profissionais a construir o próprio caminho.',
				'<em>Hoje</em><strong>Continuo aprendendo</strong>Hoje estudo neurociência e o uso da inteligência artificial no desenvolvimento de pessoas. Em cada trabalho, busco a mesma coisa: transformação com profundidade e leveza.',
			),
			'linha-tempo',
			true
		),
		'secao'
	);
	$sobre_c .= salekim_b_grupo(
		salekim_b_colunas(
			array(
				array(
					salekim_b_p( 'Formação', 'rotulo' )
					. salekim_b_lista(
						array(
							'Mestre em educação e graduada em administração pela UNISANTOS (Universidade Católica de Santos), onde foi pesquisadora do grupo “Formação de sujeitos: história, cultura, sociedade”',
							'Especialista em desenvolvimento de grupos pela SBDG (Sociedade Brasileira de Dinâmica dos Grupos), onde é didata e docente dos cursos de formação, pós-graduação e educação continuada, e onde já ocupou cargos de direção e de conselheira',
							'Formação em executive coach e life coach pelo ICI (Integrated Coaching Institute)',
							'Formação em psicoterapias reichianas e outras abordagens psicoterapêuticas',
							'Palestrante em eventos nacionais e internacionais de educação, processos grupais, psicoterapia, administração, gestão e liderança',
							'Coautora do livro <em>Manual de múltiplas inteligências</em>, com publicações nos anais de congressos e nas revistas da SBDG',
						),
						'credenciais'
					),
				),
				array(
					salekim_b_p( 'Nosso propósito', 'rotulo' )
					. salekim_b_p( 'Ajudar líderes e gestores no maior desafio de qualquer organização: formar equipes engajadas e com foco em resultado.', 'citacao' )
					. salekim_b_p( 'Nossa prática', 'rotulo' )
					. salekim_b_p( 'Toda organização muda o ambiente em que atua e é mudada por ele. Essa troca afeta a vida pessoal e profissional de quem trabalha ali, e por isso a cultura, que é o conjunto de práticas do dia a dia, pede atenção contínua. Olhamos para a organização como um sistema, em três níveis ao mesmo tempo: o indivíduo, o grupo e a organização.' )
					. salekim_b_p( 'Usamos metodologias ativas, baseadas nos princípios da educação de adultos. O aprendizado parte da experiência de quem participa e se apoia em diferentes abordagens teóricas do comportamento humano, da liderança e da gestão.' )
					. salekim_b_p( 'Nossos valores', 'rotulo' )
					. salekim_b_lista( array( 'Respeito às pessoas', 'Profissionalismo', 'Parceria com o cliente' ), 'valores' ),
				),
			),
			'duas-colunas'
		),
		'secao'
	);
	$sobre_c .= salekim_b_grupo(
		salekim_b_h( 'Quer trabalhar com a Genira?' )
		. salekim_b_botoes(
			array(
				array( 'Pedir proposta para empresa', $proposta ),
				array( 'Ver os programas para pessoas', $programas, 'contorno' ),
			)
		),
		'chamada'
	);

	/* Para empresas */
	$emp  = salekim_b_grupo(
		salekim_b_p( 'Para empresas', 'rotulo' )
		. salekim_b_h( 'Um processo bem desenhado só gera resultado quando as pessoas o sustentam no dia a dia.', 1 )
		. salekim_b_p( 'Toda mudança organizacional tem duas dimensões: a tarefa (processos, papéis, metas) e a emoção de quem executa (vínculos, conflitos, engajamento). A SALEKIM trabalha as duas juntas, a partir da escuta, com projetos desenhados sob medida para cada cliente.', 'discreto' )
		. salekim_b_botoes( array( array( 'Pedir uma proposta', '#pedir-proposta', 'azul' ) ) ),
		'secao cabeca empresas'
	);
	$emp .= salekim_b_grupo(
		salekim_b_h( 'Por que tantas mudanças voltam ao ponto de partida' )
		. salekim_b_p( 'A maior parte dos programas de mudança cuida de um lado só. Ou redesenha processos e ignora as pessoas, ou oferece um treinamento motivador que não conversa com a rotina. Nos dois casos, o efeito dura enquanto dura a lembrança do evento. A mudança se sustenta quando tarefa e emoção são trabalhadas juntas, dentro do grupo em que o trabalho acontece de fato.' ),
		'secao texto-largo'
	);
	$emp .= salekim_b_grupo(
		salekim_b_p( 'O que fazemos', 'rotulo' )
		. salekim_b_h( 'Quatro formas de trabalhar com a sua organização.' )
		. salekim_b_sc( '[salekim_servicos]' ),
		'secao'
	);
	$emp .= salekim_b_grupo(
		salekim_b_p( 'Como trabalhamos', 'rotulo' )
		. salekim_b_h( 'Da escuta ao acompanhamento.' )
		. salekim_b_lista(
			array(
				'<strong>Escuta</strong>Conversamos com lideranças e equipes para entender a necessidade real, que nem sempre é a mesma do pedido inicial.',
				'<strong>Desenho sob medida</strong>O programa é montado para o momento, a cultura e os objetivos da organização.',
				'<strong>Intervenção vivencial</strong>Os encontros partem da experiência dos participantes e da rotina real da empresa.',
				'<strong>Acompanhamento</strong>Avaliamos com a liderança o que mudou e o que ainda precisa de atenção.',
			),
			'passos',
			true
		),
		'secao'
	);
	$emp .= salekim_b_sc( '[salekim_clientes formato="faixa"]' );
	$emp .= salekim_b_sc( '[salekim_depoimentos]' );
	$emp .= salekim_b_grupo(
		salekim_b_p( 'Pedir proposta', 'rotulo' )
		. salekim_b_h( 'Conte o momento que a sua empresa está vivendo.' )
		. salekim_b_p( 'A partir dessa conversa, desenhamos uma proposta sob medida.', 'discreto' )
		. salekim_b_sc( '[salekim_formulario tipo="empresa"]' ),
		'secao',
		'pedir-proposta'
	);

	/* Programas */
	$prog  = salekim_b_grupo(
		salekim_b_p( 'Programas para pessoas', 'rotulo' )
		. salekim_b_h( 'Programas para quem quer se desenvolver, formar-se como facilitador de grupos ou construir um novo capítulo de vida.', 1 )
		. salekim_b_p( 'Escolha um programa e peça mais informações. Se preferir, fale direto com a Genira pelo WhatsApp.', 'discreto' ),
		'secao cabeca pessoas'
	);
	$prog .= salekim_b_grupo( salekim_b_sc( '[salekim_programas]' ), 'secao' );
	$prog .= salekim_b_grupo(
		salekim_b_p( 'Saber mais ou entrar na lista de espera', 'rotulo' )
		. salekim_b_h( 'Deixe seu contato e a Genira fala com você.' )
		. salekim_b_p( 'Se ainda estiver em dúvida sobre qual programa faz sentido para você, deixe o programa em branco e conte um pouco sobre o seu momento. Ou [salekim_whatsapp]fale com a gente pelo WhatsApp[/salekim_whatsapp].', 'discreto' )
		. salekim_b_sc( '[salekim_formulario tipo="pessoa"]' ),
		'secao',
		'saber-mais'
	);

	/* Clientes */
	$cli  = salekim_b_grupo(
		salekim_b_p( 'Clientes', 'rotulo' )
		. salekim_b_h( 'Empresas que confiaram no nosso trabalho.', 1 )
		. salekim_b_p( 'Indústrias, serviços e instituições de médio e grande porte, no Brasil e em outros países da América Latina.', 'discreto' )
		. salekim_b_sc( '[salekim_clientes formato="lista"]' ),
		'secao'
	);
	$cli .= salekim_b_sc( '[salekim_depoimentos]' );
	$cli .= salekim_b_grupo(
		salekim_b_h( 'A sua empresa pode ser a próxima.' )
		. salekim_b_p( 'Conte o desafio da sua organização e receba uma proposta sob medida.' )
		. salekim_b_botoes( array( array( 'Pedir proposta', $proposta ) ) ),
		'chamada'
	);

	/* Contato */
	$cont = salekim_b_grupo(
		salekim_b_p( 'Contato', 'rotulo' )
		. salekim_b_h( 'Fale com a SALEKIM.', 1 )
		. salekim_b_p( 'Respondemos com atenção a cada mensagem.', 'discreto' )
		. salekim_b_sc( '[salekim_contatos]' )
		. salekim_b_h( 'Como podemos ajudar?' )
		. salekim_b_sc( '[salekim_formulario tipo="escolha"]' ),
		'secao'
	);

	/* Política de privacidade */
	$priv = salekim_b_grupo(
		salekim_b_p( 'Política de privacidade', 'rotulo' )
		. salekim_b_h( 'Como tratamos os seus dados.', 1 )
		. salekim_b_p( 'Última atualização: outubro de 2026.', 'discreto' )
		. salekim_b_p( 'Esta política explica como a [salekim_dado campo="razao_social"] (CNPJ [salekim_dado campo="cnpj"]), com sede em [salekim_dado campo="cidade"], trata os dados pessoais coletados neste site, de acordo com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018).' )
		. salekim_b_h( '1. Quem é a responsável pelos dados', 3 )
		. salekim_b_p( 'A controladora dos dados é a [salekim_dado campo="razao_social"]. Para qualquer assunto sobre os seus dados, escreva para [salekim_dado campo="email_publico"].' )
		. salekim_b_h( '2. Que dados coletamos', 3 )
		. salekim_b_lista(
			array(
				'<strong>Dados que você envia pelos formulários:</strong> nome, e-mail, telefone ou WhatsApp, empresa, cargo, programa de interesse e a mensagem que você escrever.',
				'<strong>Dados de navegação, só com o seu consentimento:</strong> páginas visitadas, tipo de aparelho e origem do acesso, coletados pelo Google Analytics e pelo Meta Pixel de forma estatística.',
			)
		)
		. salekim_b_h( '3. Para que usamos', 3 )
		. salekim_b_p( 'Usamos os dados dos formulários para responder ao seu contato, enviar propostas, informar sobre programas e incluir o seu nome em listas de espera que você pediu. A base legal é o seu consentimento e, no caso de propostas para empresas, os procedimentos preliminares a um contrato. Os dados de navegação servem para entender quais conteúdos são úteis e para medir campanhas.' )
		. salekim_b_h( '4. Com quem compartilhamos', 3 )
		. salekim_b_p( 'Não vendemos nem alugamos os seus dados. Eles ficam no provedor de hospedagem do site e no serviço de e-mail da SALEKIM. Quando você aceita os cookies de medição, o Google e a Meta recebem os dados de navegação descritos acima, conforme as políticas de privacidade dessas empresas.' )
		. salekim_b_h( '5. Cookies', 3 )
		. salekim_b_p( 'Usamos cookies essenciais para o site funcionar e, só com o seu consentimento, cookies de medição (Google Analytics e Meta Pixel). Você pode mudar a sua escolha a qualquer momento em [salekim_preferencias_cookies], link que também fica no rodapé de todas as páginas.' )
		. salekim_b_h( '6. Por quanto tempo guardamos', 3 )
		. salekim_b_p( 'Guardamos as mensagens dos formulários pelo tempo necessário para atender ao seu pedido e manter o histórico do relacionamento, e as apagamos quando você pedir, salvo obrigação legal de guarda.' )
		. salekim_b_h( '7. Os seus direitos', 3 )
		. salekim_b_p( 'Você pode pedir a qualquer momento a confirmação de que tratamos os seus dados, o acesso, a correção, a portabilidade, a exclusão e a revogação do consentimento, pelo e-mail [salekim_dado campo="email_publico"]. Respondemos em até 15 dias.' )
		. salekim_b_h( '8. Segurança', 3 )
		. salekim_b_p( 'O site usa conexão segura (HTTPS), e o acesso às mensagens recebidas é restrito a pessoas autorizadas da SALEKIM.' ),
		'secao prosa'
	);

	return array(
		'inicio'                  => array(
			'titulo'    => 'Início',
			'descricao' => 'A SALEKIM acompanha pessoas, equipes e organizações em processos de transformação há mais de 35 anos, com metodologia vivencial. Soluções para empresas e programas para pessoas.',
			'menu'      => true,
			'conteudo'  => $inicio,
		),
		'sobre'                   => array(
			'titulo'    => 'Sobre',
			'descricao' => 'A trajetória de Genira Rosa dos Santos, do desenho de processos ao cuidado com as pessoas, e o propósito, a prática e os valores da SALEKIM.',
			'menu'      => true,
			'conteudo'  => $sobre_c,
		),
		'para-empresas'           => array(
			'titulo'    => 'Para empresas',
			'descricao' => 'Transformação organizacional, desenvolvimento de lideranças e equipes, palestras e workshops vivenciais. Peça uma proposta sob medida para a sua empresa.',
			'menu'      => true,
			'conteudo'  => $emp,
		),
		'programas'               => array(
			'titulo'    => 'Programas',
			'descricao' => 'Método VIDA para a aposentadoria, formação em desenvolvimento de grupos, Programa InterAgir, mentoria DNA, supervisão e acompanhamento individual.',
			'menu'      => true,
			'conteudo'  => $prog,
		),
		'clientes'                => array(
			'titulo'    => 'Clientes',
			'descricao' => 'Nestlé, Embraer, Faber-Castell, Vale, Roche, Santander, BASF, Votorantim, SEBRAE, SESC, Metrô de São Paulo e outras empresas que confiaram no trabalho da SALEKIM.',
			'menu'      => true,
			'conteudo'  => $cli,
		),
		'conteudos'               => array(
			'titulo'    => 'Conteúdos',
			'descricao' => 'Artigos e vídeos da SALEKIM sobre liderança, grupos, cultura organizacional e desenvolvimento humano.',
			'menu'      => false,
			'conteudo'  => '',
		),
		'contato'                 => array(
			'titulo'    => 'Contato',
			'descricao' => 'Fale com a SALEKIM por formulário, WhatsApp, e-mail ou Instagram. Pedidos de proposta para empresas e informações sobre os programas.',
			'menu'      => true,
			'conteudo'  => $cont,
		),
		'politica-de-privacidade' => array(
			'titulo'    => 'Política de privacidade',
			'descricao' => 'Como a SALEKIM trata os dados pessoais coletados no site, de acordo com a LGPD.',
			'menu'      => false,
			'conteudo'  => $priv,
		),
	);
}

/* ---------- Serviços para empresas ---------- */

function salekim_servicos_padrao() {
	return array(
		'transformacao' => array(
			'titulo' => 'Transformação organizacional',
			'texto'  => 'Processos de mudança de cultura que atuam ao mesmo tempo no sistema técnico (processos, estrutura, papéis) e no sistema humano (relações, lideranças, grupos). Indicado para reestruturações, fusões, chegada de novas lideranças, programas de qualidade e excelência e fases de crescimento acelerado.',
		),
		'liderancas'    => array(
			'titulo' => 'Desenvolvimento de lideranças e equipes',
			'texto'  => 'Programas para gestores e equipes, com foco em papéis, comunicação, tomada de decisão, conflitos e engajamento. O aprendizado parte de situações reais da equipe, trabalhadas em grupo.',
		),
		'palestras'     => array(
			'titulo'  => 'Palestras',
			'texto'   => 'Palestras em congressos nacionais e internacionais e em eventos corporativos, sobre liderança, grupos, cultura organizacional e desenvolvimento humano.',
			'assunto' => 'Palestra',
		),
		'workshops'     => array(
			'titulo'  => 'Workshops',
			'texto'   => 'Encontros vivenciais de curta duração, sobre um tema específico da liderança ou da equipe, desenhados para cada demanda.',
			'assunto' => 'Workshop',
		),
	);
}

/* ---------- Programas para pessoas ---------- */

function salekim_programas_padrao() {
	return array(
		'vida'        => array(
			'titulo'    => 'Método VIDA',
			'publico'   => 'Para quem está se aposentando ou já se aposentou.',
			'texto'     => 'Quem se aposenta sem um plano costuma passar os primeiros anos preenchendo o tempo em vez de escolher o que fazer com ele. Em 45 dias, você sai com um plano de vida para essa fase: o que fazer com o seu tempo, a sua renda e o seu propósito. São 8 encontros guiados pelas etapas visão, identidade, direção e ação, com o caderno de jornada como apoio.',
			'etiqueta'  => 'Inscrições abertas',
			'botao'     => 'Quero participar',
			'link'      => '',
			'whatsapp'  => 1,
		),
		'formacao'    => array(
			'titulo'    => 'Formação em desenvolvimento de grupos',
			'publico'   => 'Para psicólogos, profissionais de RH, educadores e consultores que conduzem grupos.',
			'texto'     => 'Formação para conduzir grupos com fundamento teórico e prática supervisionada, em parceria com a SBDG (Sociedade Brasileira de Dinâmica dos Grupos), instituição em que a Genira é didata e docente. O estudo passa por autores como Lewin, Bion, Pichon-Rivière, Schutz, Bleger e Zimerman, sempre ligado à prática com grupos reais.',
			'etiqueta'  => '',
			'botao'     => 'Quero saber mais',
			'link'      => '',
			'whatsapp'  => 1,
		),
		'interagir'   => array(
			'titulo'    => 'Programa InterAgir',
			'publico'   => 'Online e ao vivo, em parceria com a SBDG.',
			'texto'     => 'Nova turma em preparação. Entre na lista de espera para receber em primeira mão as datas e o formato.',
			'etiqueta'  => 'Lista de espera',
			'botao'     => 'Entrar na lista de espera',
			'link'      => '',
			'whatsapp'  => 0,
		),
		'dna'         => array(
			'titulo'    => 'DNA: mentoria para consultores comportamentais',
			'publico'   => 'Para consultores comportamentais, profissionais de RH, líderes e gestores que atendem clientes com ferramentas comportamentais.',
			'texto'     => 'Um método próprio funciona como uma estrutura flexível, que se adapta a cada cliente sem perder o rumo. Na mentoria, você organiza a sua experiência nesse método, e o seu cliente passa a enxergar, etapa por etapa, o resultado que está construindo.',
			'etiqueta'  => 'Lista de espera',
			'botao'     => 'Entrar na lista de espera',
			'link'      => '',
			'whatsapp'  => 0,
		),
		'supervisao'  => array(
			'titulo'    => 'Supervisão para facilitadores de grupo',
			'publico'   => 'Para profissionais formados em desenvolvimento de grupos.',
			'texto'     => 'Espaço para discutir os grupos que você conduz com uma supervisora com mais de 35 anos de prática. Os casos reais viram aprendizado técnico e cuidado com o próprio facilitador.',
			'etiqueta'  => '',
			'botao'     => 'Falar com a Genira',
			'link'      => '',
			'whatsapp'  => 1,
		),
		'individual'  => array(
			'titulo'    => 'Acompanhamento individual',
			'publico'   => 'Para líderes e profissionais em momento de decisão de carreira ou de vida.',
			'texto'     => 'Processo individual de desenvolvimento, com foco em autoconhecimento e habilidades interpessoais.',
			'etiqueta'  => '',
			'botao'     => 'Falar com a Genira',
			'link'      => '',
			'whatsapp'  => 1,
		),
	);
}
