<?php
/**
 * Configurações do site (SALEKIM > Configurações) e funções de apoio.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Valores iniciais de cada configuração. */
function salekim_padroes() {
	return array(
		'email_destino'   => 'genira@salekim.com.br',
		'email_publico'   => 'genira@salekim.com.br',
		'whatsapp'        => '5511999143277',
		'whatsapp_texto'  => '(11) 99914-3277',
		'whatsapp_msg'    => 'Olá! Vim pelo site da SALEKIM e gostaria de saber mais.',
		'instagram'       => 'genirarosa',
		'razao_social'    => 'SALEKIM Treinamento e Desenvolvimento Ltda.',
		'cnpj'            => '01.134.015/0001-05',
		'cidade'          => 'São Vicente/SP',
		'endereco'        => '',
		'mapa_url'        => '',
		'lema'            => 'Transformação com profundidade e leveza.',
		'desde'           => 'Desenvolvimento humano e organizacional desde 1996.',
		'logo_url'        => '',
		'foto_url'        => '',
		'ga4_id'          => '',
		'pixel_id'        => '',
		'gsc_verificacao' => '',
		'clientes_faixa'  => "Nestlé\nEmbraer\nFaber-Castell\nVale\nRoche\nSantander\nBASF\nVotorantim\nSEBRAE\nSESC\nMetrô de São Paulo",
		'clientes_lista'  => "Nestlé | Brasil, Colômbia, Venezuela e Equador\nEmbraer\nFaber-Castell\nVale\nRoche\nSantander/Banespa\nBASF/Ciba\nVotorantim\nContinental Pneus\nM. Dias Branco\nMaxion Wheels\nFreudenberg/NOK\nCromex\nMetrô de São Paulo\nSEBRAE\nSESC\nSENAC",
		'assuntos'        => "Transformação organizacional\nDesenvolvimento de lideranças e equipes\nPalestra\nWorkshop\nOutro assunto",
	);
}

/** Lê uma configuração, com o valor inicial quando estiver vazia. */
function salekim_opcao( $chave ) {
	$opcoes  = get_option( 'salekim_opcoes', array() );
	$padroes = salekim_padroes();
	if ( isset( $opcoes[ $chave ] ) && '' !== $opcoes[ $chave ] ) {
		return $opcoes[ $chave ];
	}
	return isset( $padroes[ $chave ] ) ? $padroes[ $chave ] : '';
}

/** Link do WhatsApp, com mensagem pronta. */
function salekim_whatsapp_url( $mensagem = '' ) {
	$numero = preg_replace( '/\D/', '', salekim_opcao( 'whatsapp' ) );
	if ( '' === $mensagem ) {
		$mensagem = salekim_opcao( 'whatsapp_msg' );
	}
	return 'https://wa.me/' . $numero . '?text=' . rawurlencode( $mensagem );
}

function salekim_instagram_url() {
	return 'https://www.instagram.com/' . ltrim( salekim_opcao( 'instagram' ), '@' ) . '/';
}

function salekim_logo_url() {
	$url = salekim_opcao( 'logo_url' );
	return $url ? $url : SALEKIM_URL . 'assets/img/logo.webp';
}

function salekim_foto_url() {
	$url = salekim_opcao( 'foto_url' );
	return $url ? $url : SALEKIM_URL . 'assets/img/genira.webp';
}

/**
 * Lista de linhas "Nome | detalhe" (clientes) em pares.
 *
 * @return array<int, array{0:string,1:string}>
 */
function salekim_linhas( $texto ) {
	$itens = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $texto ) as $linha ) {
		$linha = trim( $linha );
		if ( '' === $linha ) {
			continue;
		}
		$partes  = array_map( 'trim', explode( '|', $linha, 2 ) );
		$itens[] = array( $partes[0], isset( $partes[1] ) ? $partes[1] : '' );
	}
	return $itens;
}

/** Endereço de uma página criada pelo plugin, pela chave (inicio, sobre, para-empresas...). */
function salekim_link( $chave, $ancora = '' ) {
	$ids = get_option( 'salekim_paginas', array() );
	$url = '';
	if ( ! empty( $ids[ $chave ] ) && 'publish' === get_post_status( $ids[ $chave ] ) ) {
		$url = get_permalink( $ids[ $chave ] );
	}
	if ( ! $url ) {
		$url = 'inicio' === $chave ? home_url( '/' ) : home_url( '/' . $chave . '/' );
	}
	return $url . ( $ancora ? '#' . $ancora : '' );
}
