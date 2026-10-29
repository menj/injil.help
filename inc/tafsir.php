<?php
/**
 * The classical commentaries (tafsīr) on the Qur'anic passages about Jesus,
 * translated in full: Ṭabarī, Baghawī, Qurṭubī, Ibn Kathīr, Tanwīr al-Miqbās,
 * Jalālayn, al-Durr al-Manthūr and Saʿdī on 3:33-64, 4:155-159, 5:110-120,
 * 19:16-37, 21:91, 23:50, 43:57-65, 57:27 and 61:6.
 *
 * The works, the verse groups and the words of the pages are in inc/data/commentaries.json
 * and the "commentaries" group of inc/data/text.json. The translations are data, one JSON
 * file per work in inc/data/tafsir/, each
 * holding "entries": the commentary on one verse range as a list of paragraphs in
 * the compact page markup. From them this file builds the pages under
 * /quran/commentaries/ (an index, one page per verse group, and one page or set
 * of pages per work and group) and the "From the commentaries" blocks woven into
 * the sections of The Gospel According to Islam, so that every passage of the
 * gospel that rests on a Qur'anic verse carries what the commentators said of it.
 *
 * @package injil-help
 */

defined( 'ABSPATH' ) || exit;

/**
 * The works in the order they are shown, by the death of the author or compiler, from
 * inc/data/commentaries.json "works": { key: { short, title, author, died, about, source } }.
 */
function injil_help_tafsir_works() {
	static $works = null;
	if ( null === $works ) {
		$works = (array) ( injil_help_data_file( 'commentaries' )['works'] ?? array() );
	}
	return $works;
}

/**
 * The verse groups, in Qur'anic order, from inc/data/commentaries.json "groups":
 * { slug: { former, surah, from, to, name, title, intro } }. The key is the group's URL slug
 * (words only; the verse numbers are in the title, never in the URL); "former" is the slug of
 * 3.5 and earlier, and populate() renames the existing page in place.
 */
function injil_help_tafsir_groups() {
	static $groups = null;
	if ( null === $groups ) {
		$groups = (array) ( injil_help_data_file( 'commentaries' )['groups'] ?? array() );
	}
	return $groups;
}

/**
 * Which verse ranges each gospel section carries as "From the commentaries".
 * An entry of a commentary is placed in every section whose range it touches,
 * so a commentator who treats 4:155-159 together is read in full at the arrest,
 * the trial and the raising alike.
 */
function injil_help_tafsir_section_map() {
	return array(
		1  => array( array( 3, 33, 41 ) ),
		2  => array( array( 3, 42, 47 ), array( 19, 16, 33 ), array( 21, 91, 91 ), array( 23, 50, 50 ) ),
		3  => array( array( 3, 59, 63 ), array( 19, 34, 37 ) ),
		7  => array( array( 3, 52, 53 ) ),
		10 => array( array( 3, 50, 51 ) ),
		13 => array( array( 57, 27, 27 ) ),
		14 => array( array( 3, 48, 49 ), array( 5, 110, 110 ) ),
		16 => array( array( 5, 111, 115 ) ),
		21 => array( array( 3, 64, 64 ) ),
		22 => array( array( 43, 57, 65 ) ),
		24 => array( array( 61, 6, 6 ) ),
		25 => array( array( 3, 54, 54 ), array( 4, 155, 156 ) ),
		26 => array( array( 4, 157, 157 ) ),
		27 => array( array( 3, 55, 58 ), array( 4, 158, 159 ), array( 5, 116, 120 ) ),
	);
}

/** The slugs of the verse groups whose range touches gospel section $n, from the section map. */
function injil_help_tafsir_groups_for_section( $n ) {
	$map = injil_help_tafsir_section_map();
	$out = array();
	foreach ( (array) ( $map[ $n ] ?? array() ) as $r ) {
		foreach ( injil_help_tafsir_groups() as $slug => $g ) {
			if ( $g['surah'] === $r[0] && $g['to'] >= $r[1] && $g['from'] <= $r[2] ) {
				$out[] = $slug;
			}
		}
	}
	return array_values( array_unique( $out ) );
}

/** The gospel sections (paths) whose verses fall in a group, and the page on Jesus in the Qur'an the group belongs with. */
function injil_help_tafsir_group_related( $slug ) {
	$groups  = injil_help_tafsir_groups();
	$g       = $groups[ $slug ] ?? null;
	$related = array();
	if ( ! $g ) {
		return $related;
	}
	foreach ( injil_help_tafsir_section_map() as $n => $ranges ) {
		foreach ( $ranges as $r ) {
			if ( $r[0] === $g['surah'] && $r[2] >= $g['from'] && $r[1] <= $g['to'] && function_exists( 'injil_help_gospel_section_path' ) ) {
				$related[] = injil_help_gospel_section_path( $n );
			}
		}
	}
	$themes = array(
		'family-of-imran'                  => array( 'quran/birth', 'quran/signs' ),
		'neither-killed-nor-crucified'     => array( 'quran/raised' ),
		'table-and-questioning'    => array( 'quran/table', 'quran/signs' ),
		'birth-of-jesus'                   => array( 'quran/birth' ),
		'she-who-guarded-her-chastity'     => array( 'quran/birth' ),
		'refuge-on-high-ground'            => array( 'quran/birth' ),
		'son-of-mary-as-example'        => array( 'quran/servant' ),
		'compassion-mercy-and-monasticism' => array( 'quran/servant' ),
		'good-news-of-ahmad'               => array( 'quran/ahmad' ),
	);
	$related = array_merge( $related, $themes[ $slug ] ?? array(), array( 'quran/passages' ) );
	return array_values( array_unique( $related ) );
}

/** One work's data file: { "entries": [ { "surah", "from", "to", "arabic_words", "english_words", "paragraphs": [...] } ] }. */
function injil_help_tafsir_data( $work ) {
	static $cache = array();
	if ( isset( $cache[ $work ] ) ) {
		return $cache[ $work ];
	}
	$file    = get_stylesheet_directory() . '/inc/data/tafsir/' . $work . '.json';
	$entries = array();
	if ( is_readable( $file ) ) {
		$json = json_decode( (string) file_get_contents( $file ), true );
		foreach ( (array) ( $json['entries'] ?? array() ) as $e ) {
			if ( ! empty( $e['paragraphs'] ) && isset( $e['surah'], $e['from'], $e['to'] ) ) {
				$entries[] = $e;
			}
		}
	}
	usort(
		$entries,
		function ( $a, $b ) {
			return array( (int) $a['surah'], (int) $a['from'] ) <=> array( (int) $b['surah'], (int) $b['from'] );
		}
	);
	$cache[ $work ] = $entries;
	return $entries;
}

/** Entries of a work whose verse range touches surah:from-to. */
function injil_help_tafsir_entries( $work, $surah, $from, $to ) {
	$out = array();
	foreach ( injil_help_tafsir_data( $work ) as $e ) {
		if ( (int) $e['surah'] === (int) $surah && (int) $e['to'] >= (int) $from && (int) $e['from'] <= (int) $to ) {
			$out[] = $e;
		}
	}
	return $out;
}

/** "3:33" or "3:33-36". */
function injil_help_tafsir_ref( $e ) {
	return (int) $e['from'] === (int) $e['to'] ? $e['surah'] . ':' . $e['from'] : $e['surah'] . ':' . $e['from'] . '-' . $e['to'];
}

/** Words in a list of markup paragraphs, tags and footnotes stripped. */
function injil_help_tafsir_words( $paragraphs ) {
	$text = preg_replace( '/\{\{fn:.*?\}\}/s', '', implode( ' ', (array) $paragraphs ) );
	return str_word_count( wp_strip_all_tags( (string) $text ) );
}

/** The markup lines of one entry: a heading for the verse range, then its paragraphs. */
function injil_help_tafsir_entry_lines( $e, $level = '## ' ) {
	$lines   = array( $level . sprintf( injil_help_text( 'commentaries', 'entry_heading' ), injil_help_tafsir_ref( $e ) ) );
	foreach ( (array) $e['paragraphs'] as $p ) {
		$lines[] = $p;
	}
	return $lines;
}

/** Whether any translation is on disk at all. */
function injil_help_tafsir_available() {
	foreach ( array_keys( injil_help_tafsir_works() ) as $w ) {
		if ( injil_help_tafsir_data( $w ) ) {
			return true;
		}
	}
	return false;
}

/** The note that closes every commentary page: what the text is and how to check it. */
function injil_help_tafsir_colophon( $work ) {
	$w = injil_help_tafsir_works()[ $work ];
	return sprintf(
		injil_help_text( 'commentaries', 'colophon' ),
		esc_html( $w['title'] ),
		esc_html( $w['source'] )
	);
}

/** Pages: the index, a page per verse group, and a page (or a numbered set) per work and group. */
/** A small number as a word, for URL slugs: 2 => "two". Numbers above twenty come back as digits. */
function injil_help_number_word( $n ) {
	$words = array( 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty' );
	return isset( $words[ (int) $n ] ) ? $words[ (int) $n ] : (string) (int) $n;
}

/** The path of part $no of a work's commentary on a group: the first part is the work page, later parts are its siblings "{work}-part-two". */
function injil_help_tafsir_part_path( $base, $group, $work, $no ) {
	return $base . '/' . $group . '/' . $work . ( $no > 1 ? '-part-' . injil_help_number_word( $no ) : '' );
}

function injil_help_tafsir_pages() {
	if ( ! injil_help_tafsir_available() ) {
		return array();
	}
	$works  = injil_help_tafsir_works();
	$groups = injil_help_tafsir_groups();
	$base   = 'quran/commentaries';
	$pages  = array();
	$limit  = 14000; // English words per page before a work's commentary on a group is split into numbered parts.

	// Totals for the index.
	$totals = array();
	foreach ( $works as $key => $w ) {
		$n = 0;
		foreach ( injil_help_tafsir_data( $key ) as $e ) {
			$n += injil_help_tafsir_words( $e['paragraphs'] );
		}
		$totals[ $key ] = $n;
	}

	$index   = (array) injil_help_text( 'commentaries', 'index_intro' );
	$index[] = injil_help_text( 'commentaries', 'index_passages_heading' );
	foreach ( $groups as $slug => $g ) {
		$index[] = sprintf( injil_help_text( 'commentaries', 'index_group_item' ), $base, $slug, $g['title'], $g['intro'] );
	}
	$index[] = injil_help_text( 'commentaries', 'index_works_heading' );
	foreach ( $works as $key => $w ) {
		if ( ! $totals[ $key ] ) {
			continue;
		}
		$index[] = sprintf( injil_help_text( 'commentaries', 'index_work_item' ), $w['short'], $w['title'], $w['author'], $w['died'], $w['about'], number_format_i18n( round( $totals[ $key ], -2 ) ) );
	}
	$index   = array_merge( $index, (array) injil_help_text( 'commentaries', 'index_after' ) );
	$pages[]  = array(
		'path'    => $base,
		'title'   => injil_help_text( 'commentaries', 'index_title' ),
		'excerpt' => injil_help_text( 'commentaries', 'index_excerpt' ),
		'body'    => $index,
	);

	foreach ( $groups as $slug => $g ) {
		$body = array( $g['intro'] );
		$body[] = sprintf( injil_help_text( 'commentaries', 'group_verses' ), $g['title'] );
		$body[] = injil_help_text( 'commentaries', 'group_works_heading' );
		$links  = array();
		foreach ( $works as $key => $w ) {
			$entries = injil_help_tafsir_entries( $key, $g['surah'], $g['from'], $g['to'] );
			if ( ! $entries ) {
				continue;
			}
			$words = 0;
			foreach ( $entries as $e ) {
				$words += injil_help_tafsir_words( $e['paragraphs'] );
			}
			// Split into parts of about $limit words, never splitting an entry.
			$parts = array( array() );
			$acc   = 0;
			foreach ( $entries as $e ) {
				$n = injil_help_tafsir_words( $e['paragraphs'] );
				if ( $acc && $acc + $n > $limit ) {
					$parts[] = array();
					$acc     = 0;
				}
				$parts[ count( $parts ) - 1 ][] = $e;
				$acc += $n;
			}
			$total_parts = count( $parts );
			$wpath       = $base . '/' . $slug . '/' . $key;
			$part_links = array();
			for ( $k = 2; $k <= $total_parts; $k++ ) {
				$part_links[] = sprintf( injil_help_text( 'commentaries', 'group_part_link' ), injil_help_tafsir_part_path( $base, $slug, $key, $k ), $k );
			}
			$links[] = sprintf( injil_help_text( 'commentaries', 'group_work_item' ), $wpath, $w['short'], $w['died'], number_format_i18n( round( $words, -1 ) ), $total_parts > 1 ? sprintf( injil_help_text( 'commentaries', 'group_work_parts' ), $total_parts, implode( ', ', $part_links ) ) : '' );
			foreach ( $parts as $i => $part ) {
				$no    = $i + 1;
				$path  = injil_help_tafsir_part_path( $base, $slug, $key, $no );
				// The URLs of 3.5 and earlier: the group slug carried the verse numbers and a later part was a numbered child of the work page.
				$former = array( $base . '/' . $g['former'] . '/' . $key . ( $no > 1 ? '/' . $no : '' ) );
				if ( $no > 1 ) {
					$former[] = $wpath . '/' . $no;
				}
				$first = injil_help_tafsir_ref( $part[0] );
				$last  = injil_help_tafsir_ref( $part[ count( $part ) - 1 ] );
				$range = $first === $last ? $first : sprintf( injil_help_text( 'commentaries', 'part_range' ), $first, $last );
				$pb    = array(
					sprintf( injil_help_text( 'commentaries', 'part_lede' ), $w['title'], $w['author'], $w['died'], $w['about'] ),
					$total_parts > 1
						? sprintf( injil_help_text( 'commentaries', 'part_of' ), $no, $total_parts, $g['title'], $range )
						: sprintf( injil_help_text( 'commentaries', 'part_complete' ), $g['title'], $range ),
				);
				foreach ( $part as $e ) {
					$pb = array_merge( $pb, injil_help_tafsir_entry_lines( $e ) );
				}
				$pb[] = injil_help_text( 'commentaries', 'part_about_heading' );
				$pb[] = injil_help_tafsir_colophon( $key );
				$nav  = array();
				if ( $no > 1 ) {
					$nav[] = sprintf( injil_help_text( 'commentaries', 'part_prev' ), injil_help_tafsir_part_path( $base, $slug, $key, $no - 1 ), $no - 1 );
				}
				$nav[] = sprintf( injil_help_text( 'commentaries', 'part_all' ), $base, $slug, $g['title'] );
				if ( $no < $total_parts ) {
					$nav[] = sprintf( injil_help_text( 'commentaries', 'part_next' ), injil_help_tafsir_part_path( $base, $slug, $key, $no + 1 ), $no + 1 );
				}
				$nav[]   = sprintf( injil_help_text( 'commentaries', 'part_index' ), $base );
				$pb[]    = implode( ' · ', $nav );
				$siblings = array();
				for ( $k = 1; $k <= $total_parts; $k++ ) {
					if ( $k !== $no ) {
						$siblings[] = injil_help_tafsir_part_path( $base, $slug, $key, $k );
					}
				}
				$pages[] = array(
					'path'    => $path,
					'former'  => $former,
					'related' => array_merge( $siblings, array( $base . '/' . $slug ), injil_help_tafsir_group_related( $slug ), array( $base ) ),
					'title'   => sprintf( injil_help_text( 'commentaries', 'part_title' ), $w['short'], $g['title'], $total_parts > 1 ? sprintf( injil_help_text( 'commentaries', 'part_title_part' ), $no ) : '' ),
					'excerpt' => sprintf( injil_help_text( 'commentaries', 'part_excerpt' ), $w['short'], $w['title'], $g['title'], $total_parts > 1 ? sprintf( injil_help_text( 'commentaries', 'part_excerpt_part' ), $no, $total_parts ) : '' ),
					'body'    => $pb,
				);
			}
		}
		$body = array_merge( $body, $links );
		$body[] = injil_help_text( 'commentaries', 'group_sections_heading' );
		$secs   = array();
		foreach ( injil_help_tafsir_section_map() as $n => $ranges ) {
			foreach ( $ranges as $r ) {
				if ( $r[0] === $g['surah'] && $r[2] >= $g['from'] && $r[1] <= $g['to'] ) {
					$secs[ $n ] = sprintf( injil_help_text( 'commentaries', 'group_section_link' ), injil_help_gospel_section_path( $n ), $n, $r[0], $r[1], $r[2] );
				}
			}
		}
		$body[] = $secs ? sprintf( injil_help_text( 'commentaries', 'group_woven' ), implode( ', ', $secs ) ) : injil_help_text( 'commentaries', 'group_woven_none' );
		$body[] = sprintf( injil_help_text( 'commentaries', 'group_back' ), $base );
		array_splice(
			$pages,
			1 + count( array_filter( $pages, function ( $p ) use ( $base ) {
				return 0 === strpos( $p['path'], $base . '/' );
			} ) ),
			0,
			array( array(
				'path'    => $base . '/' . $slug,
				'former'  => array( $base . '/' . $g['former'] ),
				'related' => array_merge( injil_help_tafsir_group_related( $slug ), array( $base ) ),
				'title'   => $g['title'],
				'excerpt' => sprintf( injil_help_text( 'commentaries', 'group_excerpt' ), $g['title'] ),
				'body'    => $body,
			) )
		);
	}
	// Parents must come before children: sort by path depth within the set, keeping the index first.
	usort(
		$pages,
		function ( $a, $b ) {
			return substr_count( $a['path'], '/' ) <=> substr_count( $b['path'], '/' );
		}
	);
	return $pages;
}

/** The "From the commentaries" lines for one gospel section, or an empty list. */
function injil_help_tafsir_section_lines( $n ) {
	$map = injil_help_tafsir_section_map();
	if ( empty( $map[ $n ] ) || ! injil_help_tafsir_available() ) {
		return array();
	}
	$works  = injil_help_tafsir_works();
	$groups = injil_help_tafsir_groups();
	$refs   = array();
	foreach ( $map[ $n ] as $r ) {
		$refs[] = $r[1] === $r[2] ? $r[0] . ':' . $r[1] : $r[0] . ':' . $r[1] . '-' . $r[2];
	}
	$lines   = array( sprintf( injil_help_text( 'commentaries', 'section_heading' ), implode( ', ', $refs ) ) );
	$lines[] = injil_help_text( 'commentaries', 'section_intro' );
	$any     = false;
	foreach ( $works as $key => $w ) {
		$entries = array();
		foreach ( $map[ $n ] as $r ) {
			foreach ( injil_help_tafsir_entries( $key, $r[0], $r[1], $r[2] ) as $e ) {
				$entries[ $e['surah'] . ':' . $e['from'] . '-' . $e['to'] ] = $e;
			}
		}
		if ( ! $entries ) {
			continue;
		}
		$any    = true;
		$words  = 0;
		$own    = array();
		foreach ( $entries as $e ) {
			$words += injil_help_tafsir_words( $e['paragraphs'] );
			foreach ( $groups as $slug => $g ) {
				if ( $g['surah'] === (int) $e['surah'] && $g['to'] >= (int) $e['from'] && $g['from'] <= (int) $e['to'] ) {
					$own[ $slug ] = sprintf( injil_help_text( 'commentaries', 'section_own_link' ), $slug, $key, $w['short'], $g['title'] );
				}
			}
		}
		$lines[] = sprintf( injil_help_text( 'commentaries', 'section_details' ), $w['short'], $w['title'], $w['died'], number_format_i18n( round( $words, -1 ) ) );
		foreach ( $entries as $e ) {
			$lines = array_merge( $lines, injil_help_tafsir_entry_lines( $e, '### ' ) );
		}
		if ( $own ) {
			$lines[] = sprintf( injil_help_text( 'commentaries', 'section_read_more' ), implode( '; ', $own ) );
		}
		$lines[] = '[/details]';
	}
	return $any ? $lines : array();
}
