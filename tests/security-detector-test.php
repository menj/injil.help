<?php
/**
 * Self-test for inc/security-detector.php. Run: php tests/security-detector-test.php
 *
 * Every page the theme writes (inc/data/pages/*.json, title, path, excerpt and body),
 * every JSON pattern, and every string in every other data file under inc/data/ (the
 * gospel text, the commentaries, the tafsir, the Thomas texts, the related map and the
 * rest) must score zero; every attack sample must score as spam. Exit code 0 on success.
 *
 * @package injil-help
 */

define( 'INJIL_HELP_SECURITY_TEST', true );
require dirname( __DIR__ ) . '/inc/security-detector.php';

$data_dir = dirname( __DIR__ ) . '/inc/data';
$fail     = 0;
$n        = 0;

/**
 * Report one false positive.
 *
 * @param string $where Label.
 * @param array  $r     Score.
 */
function ih_test_fp( $where, $r ) {
	echo 'FALSE POSITIVE (score ' . $r['score'] . '): ' . $where . ' ' . implode( '; ', $r['reasons'] ) . "\n";
}

/**
 * Every string leaf of a decoded JSON value.
 *
 * @param mixed $value Value.
 * @param array $out   Collected strings.
 */
function ih_test_strings( $value, &$out ) {
	if ( is_string( $value ) ) {
		$out[] = $value;
	} elseif ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			if ( is_string( $k ) && ! is_numeric( $k ) ) {
				$out[] = $k;
			}
			ih_test_strings( $v, $out );
		}
	}
}

$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $data_dir, FilesystemIterator::SKIP_DOTS ) );
$paths = array();
foreach ( $files as $file ) {
	if ( 'json' === strtolower( $file->getExtension() ) ) {
		$paths[] = $file->getPathname();
	}
}
sort( $paths );

$pages = 0;
foreach ( $paths as $path ) {
	$rel  = substr( $path, strlen( $data_dir ) + 1 );
	$json = json_decode( (string) file_get_contents( $path ), true );
	if ( null === $json ) {
		++$fail;
		echo "UNREADABLE: $rel\n";
		continue;
	}
	// Page definitions: scored the way the gate sees a page.
	if ( 0 === strpos( $rel, 'pages/' ) ) {
		foreach ( $json as $def ) {
			$body = implode( "\n", array_filter( (array) ( $def['body'] ?? array() ), 'is_string' ) ) . ' ' . ( $def['excerpt'] ?? '' );
			$slug = basename( (string) ( $def['path'] ?? '' ) );
			$r    = injil_help_spam_score( (string) ( $def['title'] ?? '' ), $slug, $body );
			++$n;
			++$pages;
			if ( $r['score'] > 0 ) {
				++$fail;
				ih_test_fp( $rel . ' ' . ( $def['path'] ?? '?' ), $r );
			}
		}
	}
	// Patterns: title, slug and the joined content.
	if ( 0 === strpos( $rel, 'patterns/' ) ) {
		$content = implode( "\n", (array) ( $json['content'] ?? array() ) );
		$r       = injil_help_spam_score( (string) ( $json['title'] ?? '' ), basename( (string) ( $json['slug'] ?? '' ) ), $content );
		++$n;
		if ( $r['score'] > 0 ) {
			++$fail;
			ih_test_fp( $rel, $r );
		}
	}
	// Every string on its own, as a title and as content, so nothing the builders splice in is missed.
	$strings = array();
	ih_test_strings( $json, $strings );
	foreach ( $strings as $s ) {
		++$n;
		$r = injil_help_spam_score( $s, '', $s );
		if ( $r['score'] > 0 ) {
			++$fail;
			ih_test_fp( $rel . ' "' . mb_substr( $s, 0, 80 ) . '"', $r );
		}
	}
}
echo 'Data files: ' . count( $paths ) . ", page definitions: $pages, items checked: $n\n";

$clean = array(
	'Bethlehem and Bethel in Genesis',
	'The Hebrew alphabet: aleph, bet, gimel',
	'The letter bet (beth) and the house',
	'Beth-shemesh and Bethany',
	'Between the Testaments',
	'A better reading of Isaiah',
	'What is at stake in the Council of Nicaea',
	'Stake: the word for a tent peg in Isaiah 54',
	'A review of the manuscripts',
	'Bonus pages',
	'Bonus: the infancy gospels',
	'A slot in the codex where a leaf is missing',
	'The crown of thorns',
	'Elizabeth and Bethsaida',
	'Judith in the Apocrypha',
	'The odds against a late Gospel of Thomas',
	'Casting lots for his garments',
);
foreach ( $clean as $t ) {
	$r = injil_help_spam_score( $t, '', $t );
	++$n;
	if ( $r['score'] > 0 ) {
		++$fail;
		ih_test_fp( 'sample "' . $t . '"', $r );
	}
}

$spam = array(
	array( 'Bet Big Win Big with Bodog Sports Edge', '' ),
	array( 'Exclusive Coolbet Bonus Code Unlocks Big Wins', '' ),
	array( 'Скачать Пинап КЗ: Официальный сайт', '' ),
	array( 'Krok po kroku: Kompleksowy przewodnik po Pelican Casino', '' ),
	array( 'Live-Roulette bei Mr Pacho', '' ),
	array( 'Crash-Games als neuer Trend', '' ),
	array( 'Persoonlijke ervaringen met top 10 online casino’s in Nederland 2026', '' ),
	array( 'Panduan Lengkap 1xBet Download', '' ),
	array( 'Pusulabet 2026 Giriş Adresi', '' ),
	array( 'Sweepstakes Casinos Explained', '' ),
	array( 'Stake Casino Australia', '' ),
	array( 'Sportwetten-Analyse vor dem Spiel', '' ),
	array( 'Connection check jk5g5zw7', '' ),
	array( 'cl-probe', '' ),
	array( 'Mostbet giriş', '' ),
	array( 'Surfplay review', '' ),
	array( 'Crownplay bonus', '' ),
	array( 'Trino Casino', '' ),
	array( 'Syndicate Casino login', '' ),
	array( 'Stellar Spins', '' ),
	array( 'Starda официальный сайт', '' ),
	array( 'Slotspalace', '' ),
	array( 'Sportaza', '' ),
	array( 'Spybet', '' ),
	array( 'Squeenaud', '' ),
	array( 'Spinjo', '' ),
	array( 'Spellwin', '' ),
	array( 'Spinsup', '' ),
	array( 'Spinch', '' ),
	array( 'Spin Samurai', '' ),
	array( 'Slots Gallery', '' ),
	array( 'Najboljše igralne avtomate v Sloveniji', '' ),
	array( 'Casino Oyunlarında Bankroll Yönetimi', '' ),
	array( 'Glücksspiel in der Schweiz', '' ),
	array( 'Ausländische Online Casinos', '' ),
	array( 'Untitled', 'casinos-online-mas-seguros-espana-2026' ),
	array( 'Untitled', 'zahranicne-stavkove-kancelarie-na-slovensku-3' ),
	array( 'Untitled', 'pin-ap-kazino-oficialnyj-sajt' ),
	array( 'Untitled', 'kto-brasil-cassino-online-e-apostas' ),
	array( 'Vavada зеркало', '' ),
	array( 'Melbet app', '' ),
	array( 'Betify login', '' ),
	array( 'Casinozer avis', '' ),
	array( 'AureonPlay', '' ),
	array( 'PlatinCasino', '' ),
	array( 'True Blue Casino', '' ),
	array( 'SydSpin', '' ),
	array( 'TCL99', '' ),
	array( 'Vegas Stars', '' ),
	array( 'Surge Casino', '' ),
	array( '1win', '' ),
);
foreach ( $spam as $s ) {
	$r = injil_help_spam_score( $s[0], $s[1], '' );
	if ( ! $r['spam'] ) {
		++$fail;
		echo "MISSED: {$s[0]} {$s[1]} (score {$r['score']})\n";
	}
}
$r = injil_help_spam_score( 'A guide', '', '<p>See <a href="https://best-casino.example/">here</a></p>' );
if ( ! $r['spam'] ) {
	++$fail;
	echo "MISSED: gambling link\n";
}
echo 'Spam samples checked: ' . ( count( $spam ) + 1 ) . "\n";
echo $fail ? "FAILED: $fail\n" : "PASS\n";
exit( $fail ? 1 : 0 );
